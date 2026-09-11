<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\ProjectExpense;
use App\Services\SupabaseStorageService;
// use App\Notifications\PaymentRequestStatusNotification; // DISABLED - Email not configured
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentRequestController extends Controller
{
    protected $supabaseStorage;

    public function __construct(SupabaseStorageService $supabaseStorage)
    {
        $this->supabaseStorage = $supabaseStorage;
    }

    /**
     * Display listing of payment requests pending finance approval
     */
    public function index(Request $request)
    {
        // Mark all payment request notifications as read when finance opens this menu
        \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereNull('read_at')
            ->where('type', 'payment_request_pending')
            ->update(['read_at' => now()]);

        // Payment request tetap perlu terlihat sampai diproses, meskipun dibuat pada bulan sebelumnya.
        $period = $request->get('period', 'all');
        $year = (int) $request->get('year', date('Y'));

        $query = PaymentRequest::with(['user', 'project', 'clas', 'approver', 'financeApprover']);

        // Filter by period/year (default: bulan berjalan)
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateTimeString();
            $endDate = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateTimeString();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->whereYear('created_at', $year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show admin_approved (pending finance approval)
            $query->where('status', 'admin_approved');
        }

        $requests = $query->latest()->paginate(20)->appends($request->query());

        // Calculate stats (filtered by period/year)
        $statsQuery = PaymentRequest::query();
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateTimeString();
            $endDate = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateTimeString();
            $statsQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $statsQuery->whereYear('created_at', $year);
        }

        $stats = [
            'admin_approved' => (clone $statsQuery)->where('status', 'admin_approved')->count(),
            'finance_approved' => (clone $statsQuery)->where('status', 'finance_approved')->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'finance_rejected' => (clone $statsQuery)->where('status', 'finance_rejected')->count(),
            'total_pending' => (clone $statsQuery)->where('status', 'admin_approved')->sum('approved_amount'),
            'total_paid' => (clone $statsQuery)->where('status', 'paid')->sum('approved_amount'),
        ];

        $years = PaymentRequest::query()
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        } elseif (!$years->contains($year)) {
            $years = $years->push($year)->unique()->sortDesc()->values();
        }

        return view('finance.payment-requests.index', compact('requests', 'stats', 'period', 'year', 'years'));
    }

    /**
     * Display single payment request detail
     */
    public function show(PaymentRequest $paymentRequest)
    {
        $paymentRequest->load(['user', 'project', 'clas', 'approver', 'financeApprover', 'payer']);
        
        return view('finance.payment-requests.show', compact('paymentRequest'));
    }

    /**
     * Approve payment request (finance level)
     */
    public function approve(Request $request, PaymentRequest $paymentRequest)
    {
        // Only admin_approved can be processed by finance
        if ($paymentRequest->status !== 'admin_approved') {
            return back()->withErrors(['status' => 'Hanya payment request dengan status Admin Approved yang bisa diproses']);
        }

        $validated = $request->validate([
            'finance_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update payment request status
            $updateData = [
                'status' => 'finance_approved',
            ];

            if (Schema::hasColumn('payment_requests', 'finance_approved_by')) {
                $updateData['finance_approved_by'] = \Illuminate\Support\Facades\Auth::id();
            }
            if (Schema::hasColumn('payment_requests', 'finance_approved_at')) {
                $updateData['finance_approved_at'] = now();
            }
            if (Schema::hasColumn('payment_requests', 'finance_notes')) {
                $updateData['finance_notes'] = $validated['finance_notes'] ?? null;
            }

            $paymentRequest->update($updateData);

            // Auto-create Project Expense
            if ($paymentRequest->project_id) {
                // Get team member dari payment request user melalui teams
                $teamMember = \App\Models\TeamMember::whereHas('team', function($query) use ($paymentRequest) {
                        $query->where('project_id', $paymentRequest->project_id);
                    })
                    ->where('user_id', $paymentRequest->user_id)
                    ->first();
                
                $expense = ProjectExpense::create([
                    'project_id' => $paymentRequest->project_id,
                    'payment_request_id' => $paymentRequest->id,
                    'category' => 'honor', // Gaji/Honor karyawan
                    'recipient_id' => $teamMember ? $teamMember->id : null,
                    'recipient_name' => $paymentRequest->user->name,
                    'description' => "Honor untuk {$paymentRequest->user->name} - Payment Request #{$paymentRequest->id}",
                    'amount' => $paymentRequest->approved_amount,
                    'expense_date' => now(),
                    'notes' => $paymentRequest->notes,
                    'created_by' => \Illuminate\Support\Facades\Auth::id(),
                    // Auto-approved karena sudah melalui approval Admin & Finance
                    'approval_status' => 'approved',
                    'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            // Send in-app notification to user (trainer/employee)
            if ($paymentRequest->user) {
                $userRole = $paymentRequest->user->role;
                $actionUrl = $userRole === 'trainer' 
                    ? route('trainer.payment-requests.show', $paymentRequest->id)
                    : route('employee.payment-requests.show', $paymentRequest->id);
                
                \App\Models\Notification::create([
                    'user_id' => $paymentRequest->user->id,
                    'type' => 'payment_request',
                    'title' => 'Payment Request Disetujui Finance',
                    'message' => 'Payment request Anda sebesar Rp ' . number_format($paymentRequest->approved_amount, 0, ',', '.') . ' telah disetujui Finance. Pembayaran akan segera diproses.',
                    'data' => [
                        'payment_request_id' => $paymentRequest->id,
                        'amount' => $paymentRequest->approved_amount,
                        'icon' => 'check-double',
                        'action_url' => $actionUrl,
                    ],
                ]);
            }

            // Redirect with success message
            return redirect()->route('finance.payment-requests.index')
                ->with('success', 'Payment request berhasil di-approve. Expense otomatis ditambahkan ke project.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal approve payment request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject payment request (finance level)
     */
    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        // Only admin_approved can be processed by finance
        if ($paymentRequest->status !== 'admin_approved') {
            return back()->withErrors(['status' => 'Hanya payment request dengan status Admin Approved yang bisa diproses']);
        }

        $validated = $request->validate([
            'finance_notes' => 'required|string|max:1000',
        ]);

        $updateData = [
            'status' => 'finance_rejected',
        ];

        if (Schema::hasColumn('payment_requests', 'finance_approved_by')) {
            $updateData['finance_approved_by'] = \Illuminate\Support\Facades\Auth::id();
        }
        if (Schema::hasColumn('payment_requests', 'finance_approved_at')) {
            $updateData['finance_approved_at'] = now();
        }
        if (Schema::hasColumn('payment_requests', 'finance_notes')) {
            $updateData['finance_notes'] = $validated['finance_notes'];
        }

        $paymentRequest->update($updateData);

        // Send in-app notification to user (trainer/employee)
        if ($paymentRequest->user) {
            $userRole = $paymentRequest->user->role;
            $actionUrl = $userRole === 'trainer' 
                ? route('trainer.payment-requests.show', $paymentRequest->id)
                : route('employee.payment-requests.show', $paymentRequest->id);
            
            \App\Models\Notification::create([
                'user_id' => $paymentRequest->user->id,
                'type' => 'payment_request',
                'title' => 'Payment Request Ditolak Finance',
                'message' => 'Payment request Anda ditolak oleh Finance. Alasan: ' . $validated['finance_notes'],
                'data' => [
                    'payment_request_id' => $paymentRequest->id,
                    'amount' => $paymentRequest->approved_amount,
                    'icon' => 'times-circle',
                    'rejection_reason' => $validated['finance_notes'],
                    'action_url' => $actionUrl,
                ],
            ]);
        }

        return redirect()->route('finance.payment-requests.index')
            ->with('success', 'Payment request ditolak. Employee akan menerima notifikasi.');
    }

    /**
     * Mark as paid and upload bukti transfer
     */
    public function markAsPaid(Request $request, PaymentRequest $paymentRequest)
    {
        // Only finance_approved can be marked as paid
        if ($paymentRequest->status !== 'finance_approved') {
            return back()->withErrors(['status' => 'Hanya payment request yang sudah Finance Approved yang bisa dibayar']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        try {
            // Upload bukti transfer ke Supabase
            $buktiUrl = null;
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $path = 'payment-proofs/' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                Log::info('Finance: Attempting to upload bukti transfer', [
                    'payment_request_id' => $paymentRequest->id,
                    'user' => \Illuminate\Support\Facades\Auth::user()->name,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'path' => $path,
                    'bucket' => config('services.supabase.bucket')
                ]);
                
                $buktiUrl = $this->supabaseStorage->upload($file, $path);
                
                if (!$buktiUrl) {
                    Log::error('Finance: Bukti transfer upload failed', [
                        'payment_request_id' => $paymentRequest->id,
                        'path' => $path,
                        'bucket' => config('services.supabase.bucket')
                    ]);
                    return back()->with('error', 'Gagal upload bukti transfer. Cek storage/logs/laravel.log untuk detail error.');
                }
                
                Log::info('Finance: Bukti transfer uploaded successfully', [
                    'payment_request_id' => $paymentRequest->id,
                    'url' => $buktiUrl
                ]);
            }

            $updateData = [
                'status' => 'paid',
            ];

            if (Schema::hasColumn('payment_requests', 'paid_at')) {
                $updateData['paid_at'] = now();
            }
            if (Schema::hasColumn('payment_requests', 'paid_by')) {
                $updateData['paid_by'] = \Illuminate\Support\Facades\Auth::id();
            }
            if (Schema::hasColumn('payment_requests', 'payment_method')) {
                $updateData['payment_method'] = $validated['payment_method'];
            }
            if (Schema::hasColumn('payment_requests', 'payment_reference')) {
                $updateData['payment_reference'] = $validated['payment_reference'] ?? null;
            }
            if (Schema::hasColumn('payment_requests', 'bukti_transfer_url')) {
                $updateData['bukti_transfer_url'] = $buktiUrl;
            }

            $paymentRequest->update($updateData);
            
            Log::info('Finance: Payment request marked as paid', [
                'payment_request_id' => $paymentRequest->id,
                'status' => 'paid',
                'paid_by' => \Illuminate\Support\Facades\Auth::user()->name,
                'bukti_url' => $buktiUrl
            ]);

            // Send notification to employee with bukti transfer
            if ($paymentRequest->user) {
                $userRole = $paymentRequest->user->role;
                $actionUrl = $userRole === 'trainer' 
                    ? route('trainer.payment-requests.show', $paymentRequest->id)
                    : route('employee.payment-requests.show', $paymentRequest->id);
                
                \App\Models\Notification::create([
                    'user_id' => $paymentRequest->user->id,
                    'type' => 'payment_request',
                    'title' => 'Pembayaran Berhasil',
                    'message' => 'Pembayaran sebesar Rp ' . number_format($paymentRequest->approved_amount, 0, ',', '.') . ' telah selesai diproses. Lihat bukti transfer di detail payment request.',
                    'data' => [
                        'payment_request_id' => $paymentRequest->id,
                        'amount' => $paymentRequest->approved_amount,
                        'icon' => 'money-check-alt',
                        'bukti_transfer_url' => $buktiUrl,
                        'action_url' => $actionUrl,
                    ],
                ]);
            }

            return redirect()->route('finance.payment-requests.index')
                ->with('success', 'Pembayaran berhasil dicatat dan bukti transfer berhasil di-upload! Status telah diubah menjadi PAID.');
                
        } catch (\Exception $e) {
            Log::error('Finance: markAsPaid exception', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Gagal upload bukti transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Download bukti transfer via internal endpoint (finance access).
     */
    public function downloadProof(PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'paid') {
            return redirect()->route('finance.payment-requests.show', $paymentRequest)
                ->with('error', 'Bukti pembayaran hanya tersedia untuk payment request berstatus paid.');
        }

        if (empty($paymentRequest->bukti_transfer_url)) {
            return redirect()->route('finance.payment-requests.show', $paymentRequest)
                ->with('error', 'Bukti pembayaran belum tersedia.');
        }

        try {
            /** @var \Illuminate\Http\Client\Response $fileResponse */
            $fileResponse = Http::timeout(30)->get($paymentRequest->bukti_transfer_url);

            if (!$fileResponse->successful()) {
                return redirect()->route('finance.payment-requests.show', $paymentRequest)
                    ->with('error', 'Gagal mengambil file bukti pembayaran dari storage.');
            }

            $urlPath = parse_url($paymentRequest->bukti_transfer_url, PHP_URL_PATH) ?: '';
            $basename = basename($urlPath);
            $originalName = $basename !== '' ? $basename : ('bukti-pembayaran-' . ($paymentRequest->request_number ?? $paymentRequest->id));
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $contentType = $fileResponse->header('Content-Type') ?: 'application/octet-stream';

            return response($fileResponse->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('finance.payment-requests.show', $paymentRequest)
                ->with('error', 'Gagal mendownload bukti pembayaran. Silakan coba lagi.');
        }
    }
}

