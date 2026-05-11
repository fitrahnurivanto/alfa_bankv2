<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\ClassGradeFile;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    protected $supabaseStorage;

    public function __construct(SupabaseStorageService $supabaseStorage)
    {
        $this->supabaseStorage = $supabaseStorage;
    }

    /**
     * Display list of classes taught by this trainer
     */
    public function index(Request $request)
    {
        $trainer = $this->trainer();

        // Mark all class-related notifications as read when trainer opens "Kelas Saya"
        \App\Models\Notification::where('user_id', $trainer->id)
            ->whereNull('read_at')
            ->whereIn('type', ['class_assignment', 'class_status', 'class_update'])
            ->update(['read_at' => now()]);

        $query = $trainer->classes()->with(['kategori', 'trainers']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'done') {
                $query->where('status', 'done');
            } elseif ($request->status === 'ongoing') {
                $query->where('status', 'approved');
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending');
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('instansi', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by period/year (default: bulan ini)
        $period = $request->get('period', 'month_' . date('m'));
        $year = $request->get('year', date('Y'));
        
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $filterYear = (int) $year;
            $startDate = \Carbon\Carbon::create($filterYear, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($filterYear, $month, 1)->endOfMonth();
            $query->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($year !== 'all') {
            $query->whereYear('start_date', $year);
        }

        // Sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $classes = $query->paginate(12)->appends($request->query());

        // Stats (filtered by period/year)
        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'ongoing' => $trainer->classes()
                ->where('status', 'approved')
                ->whereBetween('start_date', $this->getDateFilter($period, $year))
                ->count(),
            'done' => $trainer->classes()
                ->where('status', 'done')
                ->whereBetween('start_date', $this->getDateFilter($period, $year))
                ->count(),
            'pending' => $trainer->classes()
                ->where('status', 'pending')
                ->whereBetween('start_date', $this->getDateFilter($period, $year))
                ->count(),
        ];

        return view('trainer.classes.index', compact('classes', 'stats', 'period', 'year'));
    }

    /**
     * Get date filter range based on period
     */
    private function getDateFilter($period, $year)
    {
        $year = (int) $year;
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
            return [$startDate, $endDate];
        }
        
        // Default to full year if period is 'all'
        $startDate = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
        $endDate = \Carbon\Carbon::create($year, 12, 31)->endOfYear();
        return [$startDate, $endDate];
    }

    /**
     * Display specific class detail
     */
    public function show(Clas $class)
    {
        $trainer = $this->trainer();

        // Check if trainer is assigned to this class
        if (!$class->trainers->contains($trainer->id)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        $class->load([
            'kategori',
            'trainers',
            'activeGradeFile',
            'gradeFiles.uploader',
            'gradeFiles.reviewer',
            'trainerAttendances.trainer',
        ]);

        return view('trainer.classes.show', compact('class'));
    }

    public function uploadGradeFile(Request $request, Clas $class)
    {
        $trainer = $this->trainer();

        if (!$class->trainers->contains($trainer->id)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        if ($class->status !== 'approved') {
            return back()->with('error', 'Upload file nilai hanya bisa dilakukan saat kelas status approved.');
        }

        $validated = $request->validate([
            'grade_files' => 'required|array|min:1',
            'grade_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,csv|max:10240',
        ]);

        $files = $validated['grade_files'];

        foreach ($files as $file) {
            $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'class-grade-files/class_' . $class->id . '/' . $filename;

            $uploadedUrl = $this->supabaseStorage->upload($file, $path);
            if (!$uploadedUrl) {
                return back()->with('error', 'Gagal upload salah satu file nilai. Silakan coba lagi.');
            }

            DB::transaction(function () use ($class, $trainer, $path, $uploadedUrl, $file) {
                $nextVersion = ((int) $class->gradeFiles()->max('version')) + 1;

                // Keep only the latest uploaded file as active for completion rules.
                $class->gradeFiles()->update(['is_active' => false]);

                $class->gradeFiles()->create([
                    'version' => $nextVersion,
                    'is_active' => true,
                    'file_path' => $path,
                    'file_url' => $uploadedUrl,
                    'file_name' => $file->getClientOriginalName(),
                    'file_mime' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $trainer->id,
                    'uploaded_at' => now(),
                    'status' => 'pending_review',
                    'review_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);

                $class->syncActiveGradeFileSummary();
            });
        }

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'class_grade_file_uploaded',
                'title' => 'File Nilai Menunggu Review',
                'message' => 'Trainer mengupload file nilai untuk kelas "' . $class->name . '". Silakan review sebelum kelas diselesaikan.',
                'data' => [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'icon' => 'file-upload',
                    'action_url' => route('admin.classes.show', $class->id),
                ],
            ]);
        }

        return back()->with('success', 'File nilai berhasil diupload dan menunggu review admin.');
    }

    public function updateGraduationSummary(Request $request, Clas $class)
    {
        return back()->with('error', 'Rekap lulus/tidak lulus diinput oleh tim akademik melalui detail kelas akademik.');
    }

    public function downloadGradeFile(Clas $class, ClassGradeFile $gradeFile)
    {
        $trainer = $this->trainer();

        if (!$class->trainers->contains($trainer->id)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        if ((int) $gradeFile->class_id !== (int) $class->id) {
            abort(404);
        }

        if (empty($gradeFile->file_url)) {
            return back()->with('error', 'File nilai belum tersedia.');
        }

        try {
            /** @var \Illuminate\Http\Client\Response $fileResponse */
            $fileResponse = Http::timeout(30)->get($gradeFile->file_url);

            if (!$fileResponse->successful()) {
                return back()->with('error', 'Gagal mengambil file nilai dari storage.');
            }

            $originalName = $gradeFile->file_name ?: basename(parse_url($gradeFile->file_url, PHP_URL_PATH) ?: 'file-nilai-kelas-' . $class->id);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $contentType = $gradeFile->file_mime ?: ($fileResponse->header('Content-Type') ?: 'application/octet-stream');

            return response($fileResponse->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mendownload file nilai. Silakan coba lagi.');
        }
    }

    private function trainer(): User
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user instanceof User || $user->role !== 'trainer') {
            abort(403, 'Akun trainer tidak valid.');
        }

        return $user;
    }
}
