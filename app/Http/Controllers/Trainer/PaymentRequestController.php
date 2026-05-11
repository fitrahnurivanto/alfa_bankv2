<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Clas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentRequestController extends Controller
{
    /**
     * Display list of payment requests
     */
    public function index(Request $request)
    {
        $trainer = $this->trainer();

        // Mark all payment request notifications as read when trainer opens this menu
        \App\Models\Notification::where('user_id', $trainer->id)
            ->whereNull('read_at')
            ->where('type', 'payment_request')
            ->update(['read_at' => now()]);

        $query = PaymentRequest::where('user_id', $trainer->id)
            ->with(['class.kategori']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $paymentRequests = $query->latest()->paginate(15)->appends($request->query());

        // Stats
        $stats = [
            'total' => PaymentRequest::where('user_id', $trainer->id)->count(),
            'pending' => PaymentRequest::where('user_id', $trainer->id)->where('status', 'pending')->count(),
            'approved' => PaymentRequest::where('user_id', $trainer->id)
                ->whereIn('status', ['admin_approved', 'finance_approved'])
                ->count(),
            'rejected' => PaymentRequest::where('user_id', $trainer->id)
                ->whereIn('status', ['admin_rejected', 'finance_rejected'])
                ->count(),
            'paid' => PaymentRequest::where('user_id', $trainer->id)->where('status', 'paid')->count(),
        ];

        return view('trainer.payment-requests.index', compact('paymentRequests', 'stats'));
    }

    /**
     * Show form to create payment request
     */
    public function create()
    {
        $trainer = $this->trainer();

        // Get classes that:
        // 1. Trainer is assigned to
        // 2. Status DONE only (kelas sudah selesai)
        // 3. Don't have active payment request (pending/approved/paid)
        //    Note: Classes with 'rejected' requests CAN request again
        $availableClasses = $trainer->classes()
            ->with(['kategori'])
            ->where('status', 'done')
            ->where('trainer_honor', '>', 0)
            ->whereHas('activeGradeFile', function ($query) {
                $query->where('status', 'approved');
            })
            ->whereDoesntHave('paymentRequests', function($query) use ($trainer) {
                $query->where('user_id', $trainer->id)
                      ->whereIn('status', ['pending', 'admin_approved', 'finance_approved', 'paid']);
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return view('trainer.payment-requests.create', compact('availableClasses'));
    }

    /**
     * Store payment request
     */
    public function store(Request $request)
    {
        $trainer = $this->trainer();

        $validated = $request->validate([
            'class_id' => 'required|exists:clas,id',
            'description' => 'nullable|string|max:1000',
        ]);

        // Get class
        $class = Clas::findOrFail($validated['class_id']);

        // Check if trainer is assigned to this class
        if (!$class->trainers->contains($trainer->id)) {
            return back()->with('error', 'Anda tidak memiliki akses ke kelas ini.');
        }

        // Check if class status is 'done'
        if ($class->status !== 'done') {
            return back()->with('error', 'Payment request hanya bisa dibuat untuk kelas yang sudah selesai (status: done).');
        }

        if ((float) ($class->trainer_honor ?? 0) <= 0) {
            return back()->with('error', 'Payment request belum bisa dibuat. Honor trainer belum diisi oleh Akademik/Admin.');
        }

        $class->load('activeGradeFile');
        if (!$class->activeGradeFile || $class->activeGradeFile->status !== 'approved') {
            return back()->with('error', 'Payment request belum bisa dibuat. File nilai siswa harus diupload trainer dan disetujui admin terlebih dahulu.');
        }

        // Check if payment request already exists and is not rejected
        // Note: If previous request was rejected, trainer can request again
        $existingRequest = PaymentRequest::where('user_id', $trainer->id)
            ->where('class_id', $class->id)
            ->whereIn('status', ['pending', 'admin_approved', 'finance_approved', 'paid'])
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'Payment request untuk kelas ini sudah dibuat dan sedang diproses.');
        }

        // Generate unique request number
        $requestNumber = 'PR-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        
        // Ensure uniqueness
        while (PaymentRequest::where('request_number', $requestNumber)->exists()) {
            $requestNumber = 'PR-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        }

        // Create payment request (compatible for old/new production schema)
        $requestData = [
            'user_id' => $trainer->id,
            'class_id' => $class->id,
            'request_number' => $requestNumber,
            'requested_amount' => $class->trainer_honor ?? 0,
            'status' => 'pending',
        ];

        $requestNotes = $validated['description'] ?? 'Honor mengajar kelas ' . $class->name;
        if (Schema::hasColumn('payment_requests', 'notes')) {
            $requestData['notes'] = $requestNotes;
        }
        if (Schema::hasColumn('payment_requests', 'description')) {
            $requestData['description'] = $requestNotes;
        }
        if (Schema::hasColumn('payment_requests', 'payment_status')) {
            $requestData['payment_status'] = 'pending';
        }
        if (Schema::hasColumn('payment_requests', 'type')) {
            $requestData['type'] = 'trainer_honor';
        }

        $paymentRequest = PaymentRequest::create($requestData);

        return redirect()->route('trainer.payment-requests.show', $paymentRequest)
            ->with('success', 'Payment request berhasil dibuat dan menunggu approval dari Admin Alfa Bank.');
    }

    /**
     * Show payment request detail
     */
    public function show(PaymentRequest $paymentRequest)
    {
        $trainer = $this->trainer();

        // Debug logging
        Log::info('Trainer Payment Request Show Debug', [
            'payment_request_id' => $paymentRequest->id,
            'payment_request_user_id' => $paymentRequest->user_id,
            'payment_request_user_id_type' => gettype($paymentRequest->user_id),
            'trainer_id' => $trainer->id,
            'trainer_id_type' => gettype($trainer->id),
            'trainer_name' => $trainer->name,
            'strict_match' => $paymentRequest->user_id === $trainer->id,
            'loose_match' => $paymentRequest->user_id == $trainer->id,
        ]);

        // Check ownership - using loose comparison and casting to int
        if ((int)$paymentRequest->user_id != (int)$trainer->id) {
            Log::warning('Trainer accessing other trainer payment request', [
                'payment_request_id' => $paymentRequest->id,
                'payment_request_user_id' => $paymentRequest->user_id,
                'trainer_id' => $trainer->id,
            ]);
            
            return redirect()->route('trainer.payment-requests.index')
                ->with('error', 'Anda tidak memiliki akses ke payment request ini. Payment Request ID: ' . $paymentRequest->id . ' dibuat oleh user ID: ' . $paymentRequest->user_id . ', tetapi Anda login sebagai user ID: ' . $trainer->id);
        }

        $paymentRequest->load(['class.kategori', 'class.trainers', 'approvedBy', 'financeApprovedBy', 'paidBy']);

        return view('trainer.payment-requests.show', compact('paymentRequest'));
    }

    /**
     * Download bukti pembayaran honor via internal endpoint.
     */
    public function downloadProof(PaymentRequest $paymentRequest)
    {
        $trainer = $this->trainer();

        if ((int) $paymentRequest->user_id !== (int) $trainer->id) {
            return redirect()->route('trainer.payment-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk mendownload bukti pembayaran ini.');
        }

        if ($paymentRequest->status !== 'paid') {
            return redirect()->route('trainer.payment-requests.show', $paymentRequest)
                ->with('error', 'Bukti pembayaran hanya tersedia untuk payment request berstatus paid.');
        }

        if (empty($paymentRequest->bukti_transfer_url)) {
            return redirect()->route('trainer.payment-requests.show', $paymentRequest)
                ->with('error', 'Bukti pembayaran belum tersedia.');
        }

        try {
            /** @var \Illuminate\Http\Client\Response $fileResponse */
            $fileResponse = Http::timeout(30)->get($paymentRequest->bukti_transfer_url);

            if (!$fileResponse->successful()) {
                return redirect()->route('trainer.payment-requests.show', $paymentRequest)
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
            return redirect()->route('trainer.payment-requests.show', $paymentRequest)
                ->with('error', 'Gagal mendownload bukti pembayaran. Silakan coba lagi.');
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
