<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\ClassGradeFile;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ClasController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->currentUser();
        
        if (!$user->canAccessAcademy()) {
            abort(403, 'Unauthorized access.');
        }
        
        // AJAX request to check if class name exists
        if ($request->ajax() && $request->has('check_name')) {
            $slug = $request->check_name;
            $query = Clas::where('slug', $slug);
            
            // Exclude current class if editing
            if ($request->has('exclude_id')) {
                $query->where('id', '!=', $request->exclude_id);
            }
            
            $exists = $query->exists();
            return response()->json(['exists' => $exists]);
        }
        
        // Eager load trainers and kategori relations to avoid N+1 query
        $query = Clas::with(['trainers', 'kategori']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('trainers', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
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
        
        $classes = $query
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();
        
        return view('admin.classes.index', compact('classes', 'period', 'year'));
    }

    public function create()
    {
        $user = $this->currentUser();
        
            if (!$user->canCreateEditClass()) {
                abort(403, 'Hanya Admin atau Marketing yang dapat membuat kelas.');
            }
        
        // Get all trainers from users table with role 'trainer'
        $trainers = \App\Models\User::where('role', 'trainer')->orderBy('name')->get();
        
        // Get all kategoris
        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();
        
        // Get trainings for Academy
        $trainings = \App\Models\Training::active()->orderBy('name')->get();
        
        return view('admin.classes.create', compact('trainers', 'kategoris', 'trainings'));
    }

    public function store(Request $request)
    {
        $user = $this->currentUser();
        
        if (!$user->canCreateEditClass()) {
            abort(403, 'Hanya Admin atau Marketing yang dapat membuat kelas.');
        }
        
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'training_id' => 'required|exists:trainings,id',
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'private_student_name' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'no_pic' => 'nullable|string|max:100',
            'no_kontak' => 'nullable|string|max:20',
            'payment_type' => 'nullable|in:full,termin_2x',
            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_type === 'termin_2x' && !$value) {
                        $fail('Pembayaran DP (Termin 1) wajib diisi untuk payment type Termin 2x.');
                    }
                },
            ],
            'trainer_id' => [
                'nullable',
                'exists:trainers,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $exists = Clas::where('trainer_id', $value)
                            ->whereIn('status', ['pending', 'approved'])
                            ->exists();
                        
                        if ($exists) {
                            $fail('Trainer ini sudah digunakan di kelas lain yang masih aktif.');
                        }
                    }
                },
            ],
            'price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|integer|min:0',
            'meet' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
            'method' => 'required|in:online,offline,mix',
            'jenis_reguler' => 'nullable|in:mandiri,lain_lain',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'trainer_ids' => 'nullable|array',
            'trainer_ids.*' => 'nullable|exists:users,id',
            'new_trainers' => 'nullable|array',
            'new_trainers.*' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sertifikasi_bnsp' => 'nullable|boolean',
            'bnsp_tanggal_sertifikasi' => 'nullable|required_if:sertifikasi_bnsp,1|date',
            'bnsp_student_count' => 'nullable|required_if:sertifikasi_bnsp,1|integer|min:1',
            'bnsp_fee_per_student' => 'nullable|required_if:sertifikasi_bnsp,1|numeric|min:0',
        ]);

        $validated['sertifikasi_bnsp'] = $request->boolean('sertifikasi_bnsp');
        $validated['bnsp_asesor'] = null;
        $validated['bnsp_ajj'] = false;

        if (!$validated['sertifikasi_bnsp']) {
            $validated['bnsp_tanggal_sertifikasi'] = null;
            $validated['bnsp_student_count'] = null;
            $validated['bnsp_fee_per_student'] = null;
        }

        $selectedKategori = Kategori::find($validated['kategori_id']);
        $isPrivateKategori = str_contains(strtolower($selectedKategori->nama_kategori ?? ''), 'private');
        $privateStudentName = trim((string) ($validated['private_student_name'] ?? ''));
        $validated['private_student_name'] = $isPrivateKategori && $privateStudentName !== '' ? $privateStudentName : null;

        // Validasi: Kategori Private hanya boleh 1 siswa
        if ($isPrivateKategori && isset($validated['amount']) && !empty($validated['amount'])) {
            if ((int)$validated['amount'] !== 1) {
                return back()->withErrors([
                    'amount' => 'Kategori Private hanya boleh 1 siswa per kelas. Jumlah siswa harus diisi dengan 1.'
                ])->withInput();
            }
        }

        // Custom validation: pastikan minimal ada 1 trainer (dari dropdown existing atau input baru)
        // Filter nilai kosong dari dropdown agar tidak menghasilkan user_id null di tabel pivot.
        $trainerIds = collect($request->input('trainer_ids', []))
            ->filter(function ($id) {
                return !is_null($id) && $id !== '';
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
        $newTrainers = array_filter($request->input('new_trainers', []), function($name) {
            return !empty(trim($name));
        });
        
        if (empty($trainerIds) && empty($newTrainers)) {
            return back()->withErrors([
                'trainer_ids' => 'Minimal pilih 1 trainer yang sudah ada atau masukkan nama trainer baru.'
            ])->withInput();
        }

        // Set default amount = 1 untuk kategori Private
        if (!isset($validated['amount']) || empty($validated['amount'])) {
            $validated['amount'] = 1;
        }

        // Set default payment_type = 'full' if not provided
        if (!isset($validated['payment_type']) || empty($validated['payment_type'])) {
            $validated['payment_type'] = 'full';
        }

        if (!isset($validated['price']) || $validated['price'] === '' || $validated['price'] === null) {
            $validated['price'] = 0;
        }
        
        // Ensure paid_amount is never null - set to 0 if empty
        if (!isset($validated['paid_amount']) || $validated['paid_amount'] === '' || $validated['paid_amount'] === null) {
            $validated['paid_amount'] = 0;
        }

        // Generate unique slug (for create)
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        
        // Add timestamp to make it more unique and avoid race condition
        while (Clas::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . time() . '-' . $counter;
            $counter++;
        }
        
        $validated['slug'] = $slug;
        
        $validated['income'] = $validated['price'];
        
        $validated['user_id'] = $this->currentUser()->id;
        $validated['status'] = 'pending'; // Default status pending

        try {
            // Store trainer_ids and new_trainers before creating class
            $trainerIds = collect($validated['trainer_ids'] ?? [])
                ->filter(function ($id) {
                    return !is_null($id) && $id !== '';
                })
                ->map(function ($id) {
                    return (int) $id;
                })
                ->unique()
                ->values()
                ->all();
            $newTrainers = array_filter($validated['new_trainers'] ?? [], function($name) {
                return !empty(trim($name));
            });
            unset($validated['trainer_ids']); 
            unset($validated['new_trainers']);
            unset($validated['trainer']);  // Remove old trainer field if exists
            
            $class = Clas::create($validated);
            
            // Create accounts for new trainers and collect their IDs
            foreach ($newTrainers as $trainerName) {
                $trainerName = trim($trainerName);
                if (empty($trainerName)) continue;
                
                // Generate email from name: "Ivan Pratama" -> "ivanpratama@gmail.com"
                $emailName = strtolower(str_replace(' ', '', $trainerName));
                $email = $emailName . '@gmail.com';
                
                // Check if email already exists, if yes add number suffix
                $counter = 1;
                $originalEmail = $email;
                while (\App\Models\User::where('email', $email)->exists()) {
                    $email = str_replace('@gmail.com', $counter . '@gmail.com', $originalEmail);
                    $counter++;
                }
                
                // Create trainer account with default password
                $newTrainer = \App\Models\User::create([
                    'name' => $trainerName,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
                    'role' => 'trainer',
                    'division' => 'academy',
                ]);
                
                $trainerIds[] = $newTrainer->id;
            }
            
            // Attach all trainers (from dropdown + newly created) to class
            if (!empty($trainerIds)) {
                $class->trainers()->attach($trainerIds);
                
                // Send notification to all assigned trainers
                foreach ($trainerIds as $trainerId) {
                    \App\Models\Notification::create([
                        'user_id' => $trainerId,
                        'type' => 'class_assignment',
                        'title' => 'Kelas Baru Ditugaskan',
                        'message' => 'Anda telah ditugaskan untuk mengajar kelas "' . $class->name . '". Tanggal mulai: ' . ($class->start_date ? date('d M Y', strtotime($class->start_date)) : 'Belum ditentukan'),
                        'data' => [
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'icon' => 'chalkboard-teacher',
                            'action_url' => route('trainer.classes.show', $class->id),
                        ],
                    ]);
                }
            }

            return redirect()->route('admin.classes.index')
                ->with('success', 'Kelas berhasil ditambahkan. ' . (count($newTrainers) > 0 ? count($newTrainers) . ' akun trainer baru telah dibuat secara otomatis.' : ''));
        } catch (\Illuminate\Database\QueryException $e) {
            // Log error for debugging
            Log::error('Database error saat create class: ' . $e->getMessage());
            
            // Handle duplicate entry error
            if ($e->getCode() === '23000') {
                $errorMessage = $e->getMessage();

                // Handle null trainer id in pivot insert (not duplicate data)
                if (str_contains($errorMessage, 'clas_trainer') && str_contains($errorMessage, "Column 'user_id' cannot be null")) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['trainer_ids' => 'Trainer belum dipilih dengan benar. Pilih trainer dari dropdown atau isi nama trainer baru, lalu coba simpan lagi.']);
                }
                
                // Check if it's slug duplicate
                if (str_contains($errorMessage, 'slug')) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['name' => 'Kelas dengan nama serupa sudah ada. Silakan gunakan nama yang lebih spesifik.']);
                }

                // Generic duplicate key message
                if (str_contains(strtolower($errorMessage), 'duplicate')) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['error' => 'Data duplikat terdeteksi. Pastikan data kelas belum pernah dibuat sebelumnya.']);
                }

                // Other SQL constraint issues
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Gagal menyimpan data karena constraint database. Silakan periksa kembali isian form.']);
            }
            
            // Handle foreign key constraint
            if ($e->getCode() === '23503' || str_contains($e->getMessage(), 'foreign key')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Data yang dipilih tidak valid. Pastikan kategori dan training sudah benar.']);
            }
            
            // Handle other database errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan database: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error saat create class: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show(Clas $clas)
    {
        $user = $this->currentUser();
        
            if (!$user->canAccessAcademy()) {
                abort(403, 'Unauthorized access.');
        }
        
        // Load related data including grade file version history
        $clas->load([
            'kategori',
            'trainers',
            'expenses.user',
            'activeGradeFile',
            'gradeFiles.uploader',
            'gradeFiles.reviewer',
            'trainerAttendances.trainer',
            'passFailUpdatedBy',
        ]);
        
        return view('admin.classes.show', compact('clas'));
    }

    public function updateGraduationSummary(Request $request, Clas $clas)
    {
        $user = $this->currentUser();

        if (!$user->canManageClass()) {
            abort(403, 'Hanya admin/marketing yang dapat menginput rekap kelulusan.');
        }

        if (!in_array($clas->status, ['approved', 'done'], true)) {
            return back()->with('error', 'Rekap kelulusan hanya dapat diisi saat kelas berstatus approved atau done.');
        }

        $validated = $request->validate([
            'total_students' => 'required|integer|min:0',
            'passed_students' => 'required|integer|min:0',
            'failed_students' => 'required|integer|min:0',
        ]);

        $totalStudents = (int) $validated['total_students'];
        $passedStudents = (int) $validated['passed_students'];
        $failedStudents = (int) $validated['failed_students'];

        if (($passedStudents + $failedStudents) !== $totalStudents) {
            return back()
                ->withInput()
                ->withErrors([
                    'failed_students' => 'Jumlah lulus + tidak lulus harus sama dengan jumlah siswa.',
                ]);
        }

        $clas->update([
            'amount' => $totalStudents,
            'passed_students' => $passedStudents,
            'failed_students' => $failedStudents,
            'pass_fail_updated_by' => $user->id,
            'pass_fail_updated_at' => now(),
        ]);

        return back()->with('success', 'Rekap siswa lulus/tidak lulus berhasil disimpan.');
    }

    public function edit(Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canCreateEditClass()) {
            abort(403, 'Hanya Admin atau Marketing yang dapat mengedit kelas.');
        }
        
        // Get all trainers from users table with role 'trainer'
        $trainers = \App\Models\User::where('role', 'trainer')->orderBy('name')->get();
        
        // Get all kategoris
        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();
        
        // Get trainings for Academy
        $trainings = \App\Models\Training::active()->orderBy('name')->get();
        
        // Load current trainers for this class
        $clas->load('trainers');
        
        return view('admin.classes.edit', compact('clas', 'trainers', 'kategoris', 'trainings'));
    }

    public function update(Request $request, Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canCreateEditClass()) {
            abort(403, 'Hanya Admin atau Marketing yang dapat memperbarui kelas.');
        }
        
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'training_id' => 'required|exists:trainings,id',
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'private_student_name' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'no_pic' => 'nullable|string|max:100',
            'no_kontak' => 'nullable|string|max:20',
            'payment_type' => 'nullable|in:full,termin_2x',
            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_type === 'termin_2x' && !$value) {
                        $fail('Pembayaran DP (Termin 1) wajib diisi untuk payment type Termin 2x.');
                    }
                },
            ],
            'trainer_id' => [
                'nullable',
                'exists:trainers,id',
                function ($attribute, $value, $fail) use ($clas) {
                    if ($value) {
                        $exists = Clas::where('trainer_id', $value)
                            ->where('id', '!=', $clas->id) // Kecuali kelas yang sedang diedit
                            ->whereIn('status', ['pending', 'approved'])
                            ->exists();
                        
                        if ($exists) {
                            $fail('Trainer ini sudah digunakan di kelas lain yang masih aktif.');
                        }
                    }
                },
            ],
            'price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|integer|min:0',
            'meet' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
            'method' => 'required|in:online,offline,mix',
            'jenis_reguler' => 'nullable|in:mandiri,lain_lain',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'trainer_ids' => 'nullable|array',
            'trainer_ids.*' => 'nullable|exists:users,id',
            'new_trainers' => 'nullable|array',
            'new_trainers.*' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected,done',
            'sertifikasi_bnsp' => 'nullable|boolean',
            'bnsp_tanggal_sertifikasi' => 'nullable|required_if:sertifikasi_bnsp,1|date',
            'bnsp_student_count' => 'nullable|required_if:sertifikasi_bnsp,1|integer|min:1',
            'bnsp_fee_per_student' => 'nullable|required_if:sertifikasi_bnsp,1|numeric|min:0',
        ]);

        $validated['sertifikasi_bnsp'] = $request->boolean('sertifikasi_bnsp');
        $validated['bnsp_asesor'] = null;
        $validated['bnsp_ajj'] = false;

        if (!$validated['sertifikasi_bnsp']) {
            $validated['bnsp_tanggal_sertifikasi'] = null;
            $validated['bnsp_student_count'] = null;
            $validated['bnsp_fee_per_student'] = null;
        }

        $selectedKategori = Kategori::find($validated['kategori_id']);
        $isPrivateKategori = str_contains(strtolower($selectedKategori->nama_kategori ?? ''), 'private');
        $privateStudentName = trim((string) ($validated['private_student_name'] ?? ''));
        $validated['private_student_name'] = $isPrivateKategori && $privateStudentName !== '' ? $privateStudentName : null;

        // Validasi: Kategori Private hanya boleh 1 siswa
        if ($isPrivateKategori && isset($validated['amount']) && !empty($validated['amount'])) {
            if ((int)$validated['amount'] !== 1) {
                return back()->withErrors([
                    'amount' => 'Kategori Private hanya boleh 1 siswa per kelas. Jumlah siswa harus diisi dengan 1.'
                ])->withInput();
            }
        }

        // Custom validation: pastikan minimal ada 1 trainer (dari dropdown existing atau input baru)
        // Filter nilai kosong dari dropdown agar tidak menghasilkan user_id null di tabel pivot.
        $trainerIds = collect($request->input('trainer_ids', []))
            ->filter(function ($id) {
                return !is_null($id) && $id !== '';
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
        $newTrainers = array_filter($request->input('new_trainers', []), function($name) {
            return !empty(trim($name));
        });
        
        if (empty($trainerIds) && empty($newTrainers)) {
            return back()->withErrors([
                'trainer_ids' => 'Minimal pilih 1 trainer yang sudah ada atau masukkan nama trainer baru.'
            ])->withInput();
        }

        // Set default amount = 1 untuk kategori Private
        if (!isset($validated['amount']) || empty($validated['amount'])) {
            $validated['amount'] = 1;
        }

        // Set default payment_type = 'full' if not provided
        if (!isset($validated['payment_type']) || empty($validated['payment_type'])) {
            $validated['payment_type'] = 'full';
        }

        if (!isset($validated['price']) || $validated['price'] === '' || $validated['price'] === null) {
            $validated['price'] = 0;
        }
        
        // Ensure paid_amount is never null - set to 0 if empty
        if (!isset($validated['paid_amount']) || $validated['paid_amount'] === '' || $validated['paid_amount'] === null) {
            $validated['paid_amount'] = 0;
        }

        // Generate unique slug (for update, exclude current class)
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        
        // Add timestamp to make it more unique and avoid race condition
        while (Clas::where('slug', $slug)->where('id', '!=', $clas->id)->exists()) {
            $slug = $baseSlug . '-' . time() . '-' . $counter;
            $counter++;
        }
        
        $validated['slug'] = $slug;
        
        $validated['income'] = $validated['price'];

        try {
            // Store old values before updating to detect changes
            $oldStartDate = $clas->start_date;
            $oldEndDate = $clas->end_date;
            $oldName = $clas->name;
            
            // Store trainer_ids and new_trainers before updating class
            $trainerIds = collect($validated['trainer_ids'] ?? [])
                ->filter(function ($id) {
                    return !is_null($id) && $id !== '';
                })
                ->map(function ($id) {
                    return (int) $id;
                })
                ->unique()
                ->values()
                ->all();
            $newTrainers = array_filter($validated['new_trainers'] ?? [], function($name) {
                return !empty(trim($name));
            });
            unset($validated['trainer_ids']); 
            unset($validated['new_trainers']);
            unset($validated['trainer']);  // Remove old trainer field if exists
            
            $clas->update($validated);
            
            // Detect if significant changes occurred
            $hasSignificantChanges = ($oldStartDate != $clas->start_date) || 
                                     ($oldEndDate != $clas->end_date) || 
                                     ($oldName != $clas->name);
            
            // Create accounts for new trainers and collect their IDs
            foreach ($newTrainers as $trainerName) {
                $trainerName = trim($trainerName);
                if (empty($trainerName)) continue;
                
                // Generate email from name: "Ivan Pratama" -> "ivanpratama@gmail.com"
                $emailName = strtolower(str_replace(' ', '', $trainerName));
                $email = $emailName . '@gmail.com';
                
                // Check if email already exists, if yes add number suffix
                $counter = 1;
                $originalEmail = $email;
                while (\App\Models\User::where('email', $email)->exists()) {
                    $email = str_replace('@gmail.com', $counter . '@gmail.com', $originalEmail);
                    $counter++;
                }
                
                // Create trainer account with default password
                $newTrainer = \App\Models\User::create([
                    'name' => $trainerName,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
                    'role' => 'trainer',
                    'division' => 'academy',
                ]);
                
                $trainerIds[] = $newTrainer->id;
            }
            
            // Sync all trainers (from dropdown + newly created)
            if (!empty($trainerIds)) {
                // Get current trainers before sync to detect new assignments
                $currentTrainerIds = $clas->trainers->pluck('id')->toArray();
                $clas->trainers()->sync($trainerIds);
                
                // Detect newly assigned trainers
                $newlyAssignedTrainerIds = array_diff($trainerIds, $currentTrainerIds);
                
                // Send notification to newly assigned trainers
                foreach ($newlyAssignedTrainerIds as $trainerId) {
                    \App\Models\Notification::create([
                        'user_id' => $trainerId,
                        'type' => 'class_assignment',
                        'title' => 'Kelas Baru Ditugaskan',
                        'message' => 'Anda telah ditugaskan untuk mengajar kelas "' . $clas->name . '". Tanggal mulai: ' . ($clas->start_date ? date('d M Y', strtotime($clas->start_date)) : 'Belum ditentukan'),
                        'data' => [
                            'class_id' => $clas->id,
                            'class_name' => $clas->name,
                            'icon' => 'chalkboard-teacher',
                            'action_url' => route('trainer.classes.show', $clas->id),
                        ],
                    ]);
                }
                
                // Send update notification to existing trainers if there are significant changes
                $existingTrainerIds = array_intersect($trainerIds, $currentTrainerIds);
                if (!empty($existingTrainerIds) && $hasSignificantChanges) {
                    foreach ($existingTrainerIds as $trainerId) {
                        \App\Models\Notification::create([
                            'user_id' => $trainerId,
                            'type' => 'class_update',
                            'title' => 'Kelas Diupdate',
                            'message' => 'Informasi kelas "' . $clas->name . '" telah diperbarui. Silakan cek detail kelas untuk informasi terbaru.',
                            'data' => [
                                'class_id' => $clas->id,
                                'class_name' => $clas->name,
                                'icon' => 'edit',
                                'action_url' => route('trainer.classes.show', $clas->id),
                            ],
                        ]);
                    }
                }
                
            } else {
                $clas->trainers()->detach();
            }

            $message = 'Kelas berhasil diperbarui.';
            if (count($newTrainers) > 0) {
                $message .= ' ' . count($newTrainers) . ' akun trainer baru telah dibuat secara otomatis.';
            }

            return redirect()->route('admin.classes.index')
                ->with('success', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error saat update class: ' . $e->getMessage());
            
            // Handle duplicate entry error
            if ($e->getCode() === '23000') {
                $errorMessage = $e->getMessage();

                // Handle null trainer id in pivot insert (not duplicate data)
                if (str_contains($errorMessage, 'clas_trainer') && str_contains($errorMessage, "Column 'user_id' cannot be null")) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['trainer_ids' => 'Trainer belum dipilih dengan benar. Pilih trainer dari dropdown atau isi nama trainer baru, lalu coba simpan lagi.']);
                }
                
                // Check if it's slug duplicate
                if (str_contains($errorMessage, 'slug')) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['name' => 'Kelas dengan nama serupa sudah ada. Silakan gunakan nama yang lebih spesifik.']);
                }

                // Generic duplicate key message
                if (str_contains(strtolower($errorMessage), 'duplicate')) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['error' => 'Data duplikat terdeteksi. Pastikan data kelas belum pernah dibuat sebelumnya.']);
                }

                // Other SQL constraint issues
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Gagal memperbarui data karena constraint database. Silakan periksa kembali isian form.']);
            }
            
            // Handle foreign key constraint
            if ($e->getCode() === '23503' || str_contains($e->getMessage(), 'foreign key')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Data yang dipilih tidak valid. Pastikan kategori dan training sudah benar.']);
            }
            
            // Handle other database errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan database saat memperbarui: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi atau hubungi administrator.']);
        }
    }

    public function destroy(Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!($user->isAdmin() || $user->role === 'marketing')) {
            abort(403, 'Hanya Admin dan Marketing yang dapat menghapus kelas.');
        }
        
        $clas->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    public function approve(Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canApproveClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat menyetujui kelas.');
        }
        
        // Auto-fill paid_amount for full payment (non-termin)
        $updateData = ['status' => 'approved'];
        
        if ($clas->payment_type === 'full') {
            // Price is now total class revenue for all categories
            $grossIncome = $clas->price;
            
            // Set paid_amount = gross income for full payment
            $updateData['paid_amount'] = $grossIncome;
        }
        
        $clas->update($updateData);
        
        // Send notification to all assigned trainers
        $clas->load('trainers');
        foreach ($clas->trainers as $trainer) {
            \App\Models\Notification::create([
                'user_id' => $trainer->id,
                'type' => 'class_status',
                'title' => 'Kelas Dimulai',
                'message' => 'Kelas "' . $clas->name . '" telah di-approve dan siap untuk dimulai. Tanggal: ' . ($clas->start_date ? date('d M Y', strtotime($clas->start_date)) : 'Segera'),
                'data' => [
                    'class_id' => $clas->id,
                    'class_name' => $clas->name,
                    'status' => 'approved',
                    'icon' => 'play-circle',
                    'action_url' => route('trainer.classes.show', $clas->id),
                ],
            ]);
        }

        return redirect()->back()
            ->with('success', 'Kelas berhasil di-approve.');
    }

    public function reject(Request $request, Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canApproveClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat menolak kelas.');
        }
        
        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500'
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter'
        ]);
        
        $clas->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason']
        ]);
        
        // Send notification to all assigned trainers
        $clas->load('trainers');
        foreach ($clas->trainers as $trainer) {
            \App\Models\Notification::create([
                'user_id' => $trainer->id,
                'type' => 'class_status',
                'title' => 'Kelas Ditolak',
                'message' => 'Kelas "' . $clas->name . '" telah ditolak oleh admin. Alasan: ' . $validated['rejection_reason'],
                'data' => [
                    'class_id' => $clas->id,
                    'class_name' => $clas->name,
                    'status' => 'rejected',
                    'rejection_reason' => $validated['rejection_reason'],
                    'icon' => 'times-circle',
                    'action_url' => route('trainer.classes.show', $clas->id),
                ],
            ]);
        }

        return redirect()->back()
            ->with('success', 'Kelas berhasil ditolak dengan alasan: ' . $validated['rejection_reason']);
    }

    public function markAsDone(Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canManageClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat menyelesaikan kelas.');
        }
        
        // Hanya kelas dengan status approved yang bisa diselesaikan
        if ($clas->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Hanya kelas dengan status Approved yang dapat diselesaikan.');
        }

        $activeGradeFile = $clas->activeGradeFile;

        if (!$activeGradeFile) {
            return redirect()->back()
                ->with('error', 'Kelas belum bisa diselesaikan. Trainer wajib upload file nilai siswa terlebih dahulu.');
        }

        if ($activeGradeFile->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Kelas belum bisa diselesaikan. File nilai siswa harus di-approve admin terlebih dahulu.');
        }

        if ((float) ($clas->price ?? 0) <= 0) {
            return redirect()->back()
                ->with('error', 'Pendapatan kelas belum diisi. Isi pendapatan kelas terlebih dahulu sebelum menyelesaikan kelas.');
        }
        
        $clas->update([
            'status' => 'done',
            'done_at' => now(),
        ]);
        
        // Send notification to all assigned trainers
        $clas->load('trainers');
        foreach ($clas->trainers as $trainer) {
            \App\Models\Notification::create([
                'user_id' => $trainer->id,
                'type' => 'class_status',
                'title' => 'Kelas Selesai',
                'message' => 'Kelas "' . $clas->name . '" telah selesai. Terima kasih atas kontribusinya!',
                'data' => [
                    'class_id' => $clas->id,
                    'class_name' => $clas->name,
                    'status' => 'done',
                    'icon' => 'check-circle',
                    'action_url' => route('trainer.classes.show', $clas->id),
                ],
            ]);
        }

        return redirect()->back()
            ->with('success', 'Kelas berhasil diselesaikan.');
    }

    public function updateRevenue(Request $request, Clas $clas)
    {
        $user = $this->currentUser();

        if (!$user->canManageClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat mengubah pendapatan kelas.');
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $clas->update([
            'price' => $validated['price'],
            'income' => $validated['price'],
        ]);

        return redirect()->back()->with('success', 'Pendapatan kelas berhasil diperbarui.');
    }

    public function reviewGradeFile(Request $request, Clas $clas, ClassGradeFile $gradeFile)
    {
        $user = $this->currentUser();

        if (!$user->canManageClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat mereview file nilai.');
        }

        if ((int) $gradeFile->class_id !== (int) $clas->id) {
            abort(404);
        }

        if (empty($gradeFile->file_url)) {
            return redirect()->back()->with('error', 'Belum ada file nilai yang diupload trainer.');
        }

        $validated = $request->validate([
            'action' => 'nullable|in:approve,reject',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $action = $validated['action'] ?? 'approve';
        $reviewNotes = isset($validated['review_notes']) ? trim((string) $validated['review_notes']) : null;

        if ($action === 'reject' && empty($reviewNotes)) {
            return redirect()->back()
                ->withErrors(['review_notes' => 'Alasan penolakan wajib diisi saat reject file nilai.'])
                ->withInput();
        }

        $isApproved = $action === 'approve';

        try {
            $gradeFile->update([
                'status' => $isApproved ? 'approved' : 'rejected',
                'review_notes' => $reviewNotes,
                'reviewed_by' => $this->currentUser()->id,
                'reviewed_at' => now(),
            ]);

            if ($gradeFile->is_active) {
                $clas->syncActiveGradeFileSummary();
            }
        } catch (\Throwable $e) {
            Log::error('Gagal update review file nilai kelas', [
                'class_id' => $clas->id,
                'grade_file_id' => $gradeFile->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal menyimpan review file nilai. Silakan coba lagi.');
        }

        foreach ($clas->trainers as $trainer) {
            \App\Models\Notification::create([
                'user_id' => $trainer->id,
                'type' => 'class_grade_file_review',
                'title' => $isApproved ? 'File Nilai Disetujui' : 'File Nilai Ditolak',
                'message' => $isApproved
                    ? 'File nilai versi ' . $gradeFile->version . ' untuk kelas "' . $clas->name . '" sudah disetujui admin.'
                    : 'File nilai versi ' . $gradeFile->version . ' untuk kelas "' . $clas->name . '" ditolak admin. Silakan upload ulang file yang benar.',
                'data' => [
                    'class_id' => $clas->id,
                    'class_name' => $clas->name,
                    'grade_file_id' => $gradeFile->id,
                    'version' => $gradeFile->version,
                    'status' => $gradeFile->status,
                    'icon' => $isApproved ? 'check-circle' : 'times-circle',
                    'action_url' => route('trainer.classes.show', $clas->id),
                ],
            ]);
        }

        return redirect()->back()->with('success', $isApproved
            ? 'File nilai versi ' . $gradeFile->version . ' berhasil disetujui.'
            : 'File nilai versi ' . $gradeFile->version . ' berhasil ditolak.');
    }

    public function downloadGradeFile(Clas $clas, ClassGradeFile $gradeFile)
    {
        $user = $this->currentUser();

        if (!$user->canAccessAcademy()) {
            abort(403, 'Unauthorized access.');
        }

        if ((int) $gradeFile->class_id !== (int) $clas->id) {
            abort(404);
        }

        if (empty($gradeFile->file_url)) {
            return redirect()->back()->with('error', 'File nilai belum tersedia.');
        }

        try {
            /** @var \Illuminate\Http\Client\Response $fileResponse */
            $fileResponse = Http::timeout(30)->get($gradeFile->file_url);

            if (!$fileResponse->successful()) {
                return redirect()->back()->with('error', 'Gagal mengambil file nilai dari storage.');
            }

            $originalName = $gradeFile->file_name ?: ('file-nilai-kelas-' . $clas->id . '-v' . $gradeFile->version);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $contentType = $gradeFile->file_mime ?: ($fileResponse->header('Content-Type') ?: 'application/octet-stream');

            return response($fileResponse->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mendownload file nilai. Silakan coba lagi.');
        }
    }

    public function updatePayment(Request $request, Clas $clas)
    {
        $user = $this->currentUser();
        
        if (!$user->canManageClass()) {
            abort(403, 'Hanya Akademik atau Admin yang dapat mengisi pelunasan/payment request.');
        }

        // Validasi hanya untuk kelas dengan payment_type termin_2x
        if ($clas->payment_type !== 'termin_2x') {
            return redirect()->back()
                ->with('error', 'Update pembayaran hanya untuk kelas dengan payment type Termin 2x.');
        }

        $validated = $request->validate([
            'settlement_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_note' => 'nullable|string|max:1000',
        ]);

        // Hitung total yang sudah dibayar (DP + settlement)
        $totalPaid = ($clas->paid_amount ?? 0) + $validated['settlement_amount'];
        
        // Total kontrak yang harus dibayar (price is now total revenue)
        $totalContract = $clas->price;

        // Validasi tidak boleh melebihi total kontrak
        if ($totalPaid > $totalContract) {
            return redirect()->back()
                ->withErrors(['settlement_amount' => 'Total pembayaran tidak boleh melebihi total kontrak (Rp ' . number_format($totalContract, 0, ',', '.') . ')'])
                ->withInput();
        }

        // Tambahkan catatan pembayaran
        $existingNotes = $clas->payment_notes ? $clas->payment_notes . "\n\n" : '';
        $newNote = "Pelunasan (Termin 2) - " . date('d M Y', strtotime($validated['payment_date'])) . ":\n";
        $newNote .= "Jumlah: Rp " . number_format($validated['settlement_amount'], 0, ',', '.') . "\n";
        if ($validated['payment_note']) {
            $newNote .= "Catatan: " . $validated['payment_note'];
        }

        // Update paid_amount dan payment_notes
        $clas->update([
            'paid_amount' => $totalPaid,
            'payment_notes' => $existingNotes . $newNote,
        ]);

        // Cek apakah sudah lunas
        $remaining = $totalContract - $totalPaid;
        $message = $remaining <= 0 
            ? 'Pelunasan berhasil disimpan. Pembayaran sudah lunas!' 
            : 'Pelunasan berhasil disimpan. Sisa pembayaran: Rp ' . number_format($remaining, 0, ',', '.');

        return redirect()->back()
            ->with('success', $message);
    }


       public function updateSchedule(Request $request, Clas $clas)
       {
           $user = $this->currentUser();
       
           if (!$user->canCreateEditClass()) {
               abort(403, 'Hanya Admin atau Marketing yang dapat mengedit jadwal kelas.');
           }

           // Validasi input
           $validated = $request->validate([
               'start_date' => 'required|date|before_or_equal:end_date',
               'end_date' => 'required|date|after_or_equal:start_date',
               'start_time' => 'nullable|date_format:H:i',
               'end_time' => 'nullable|date_format:H:i',
               'meet' => 'required|integer|min:1|max:999',
               'duration' => 'required|integer|min:1|max:999',
           ], [
               'start_date.required' => 'Tanggal mulai harus diisi.',
               'start_date.date' => 'Tanggal mulai harus format tanggal yang valid.',
               'start_date.before_or_equal' => 'Tanggal mulai tidak boleh melebihi tanggal selesai.',
               'end_date.required' => 'Tanggal selesai harus diisi.',
               'end_date.date' => 'Tanggal selesai harus format tanggal yang valid.',
               'end_date.after_or_equal' => 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai.',
               'start_time.date_format' => 'Jam mulai harus format HH:MM (24 jam).',
               'end_time.date_format' => 'Jam selesai harus format HH:MM (24 jam).',
               'meet.required' => 'Jumlah pertemuan harus diisi.',
               'meet.integer' => 'Jumlah pertemuan harus berupa angka.',
               'meet.min' => 'Jumlah pertemuan minimal 1.',
               'duration.required' => 'JPL per pertemuan harus diisi.',
               'duration.integer' => 'JPL per pertemuan harus berupa angka.',
               'duration.min' => 'JPL per pertemuan minimal 1.',
           ]);

           // Validasi tambahan: jika kedua jam diisi, end_time harus > start_time
           if ($validated['start_time'] && $validated['end_time']) {
               // Parse times sebagai strings untuk perbandingan
               $startTimeStr = $validated['start_time'];
               $endTimeStr = $validated['end_time'];
           
               if ($endTimeStr <= $startTimeStr) {
                   return redirect()->back()
                       ->withErrors(['end_time' => 'Jam selesai harus lebih besar dari jam mulai.'])
                       ->withInput();
               }
           }

           try {
               // Siapkan data untuk update
               $updateData = [
                   'start_date' => $validated['start_date'],
                   'end_date' => $validated['end_date'],
                   'meet' => $validated['meet'],
                   'duration' => $validated['duration'],
               ];

               // Update jam jika diisi
               if ($validated['start_time']) {
                   $updateData['start_time'] = $validated['start_time'] . ':00'; // Tambahkan :00 untuk seconds
               }
               if ($validated['end_time']) {
                   $updateData['end_time'] = $validated['end_time'] . ':00'; // Tambahkan :00 untuk seconds
               }

               $clas->update($updateData);

               return redirect()->back()
                   ->with('success', 'Jadwal kelas berhasil diperbarui!');
           } catch (\Throwable $e) {
               Log::error('Gagal update jadwal kelas', [
                   'class_id' => $clas->id,
                   'error' => $e->getMessage(),
               ]);
           
               return redirect()->back()
                   ->with('error', 'Gagal menyimpan perubahan jadwal kelas. Silakan coba lagi.')
                   ->withInput();
           }
       }

   public function track(){

        return view('admin.tracking.index');

   }



    public function showclas(Request $request)
    {
        $user = $this->currentUser();
        
        if (!$user->canAccessAcademy()) {
            abort(403, 'Unauthorized access.');
        }
        
        // Get categories from kategoris table
        $categories = Kategori::all();
        
        // Query untuk semua kelas
        $query = Clas::query();

        // Filter by period (default: bulan berjalan)
        $period = $request->get('period', 'month_' . date('m'));
        if ($period === 'all') {
            // No period filter
        } elseif (preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $query->whereYear('start_date', now()->year)
                  ->whereMonth('start_date', $month);
        } else {
            // fallback ke bulan berjalan
            $query->whereYear('start_date', now()->year)
                  ->whereMonth('start_date', now()->month);
            $period = 'month_' . date('m');
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Default: tampilkan SEMUA kelas (termasuk pending)
        // User bisa filter manual via dropdown
        
        // Filter by kategori_id if provided
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }
        
        // Urutkan berdasarkan tanggal pembuatan terbaru (latest)
        $approvedClasses = $query->with(['kategori', 'activeGradeFile', 'trainers:id,name'])->latest()->get();
        
        return view('admin.classes.showclas', compact('approvedClasses', 'categories', 'period'));
    }

    private function currentUser(): \App\Models\User
    {
        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(403, 'Unauthorized access.');
        }

        return $user;
    }
}
