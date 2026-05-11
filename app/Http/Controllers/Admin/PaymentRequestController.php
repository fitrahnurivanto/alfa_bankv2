<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
// use App\Notifications\PaymentRequestStatusNotification; // DISABLED - Email not configured

class PaymentRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $query = PaymentRequest::with(['user', 'project.order', 'clas', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period/year (default: bulan ini)
        $period = $request->get('period', 'month_' . date('m'));
        $year = $request->get('year', date('Y'));
        
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $filterYear = (int) $year;
            $startDate = \Carbon\Carbon::create($filterYear, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($filterYear, $month, 1)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        // Calculate pending count
        $pendingCount = PaymentRequest::where('status', 'pending')->count();

        // Base query for stats (filtered by period/year)
        $statsQuery = PaymentRequest::query();
        
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $filterYear = (int) $year;
            $startDate = \Carbon\Carbon::create($filterYear, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($filterYear, $month, 1)->endOfMonth();
            $statsQuery->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($year !== 'all') {
            $statsQuery->whereYear('created_at', $year);
        }

        // Calculate stats (filtered)
        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'admin_approved' => (clone $statsQuery)->where('status', 'admin_approved')->count(),
            'finance_approved' => (clone $statsQuery)->where('status', 'finance_approved')->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'admin_rejected' => (clone $statsQuery)->where('status', 'admin_rejected')->count(),
            'finance_rejected' => (clone $statsQuery)->where('status', 'finance_rejected')->count(),
            'total_approved' => (clone $statsQuery)->whereIn('status', ['admin_approved', 'finance_approved'])->sum('approved_amount'),
            'total_paid' => (clone $statsQuery)->where('status', 'paid')->sum('approved_amount'),
        ];

        return view('admin.payment-requests.index', compact('requests', 'pendingCount', 'stats', 'period', 'year'));
    }

    public function show(PaymentRequest $paymentRequest)
    {
        $paymentRequest->load(['user', 'project', 'approver']);
        
        return view('admin.payment-requests.show', compact('paymentRequest'));
    }

    public function update(Request $request, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Permintaan sudah diproses sebelumnya']);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approved_amount' => 'required_if:action,approve|nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $status = $validated['action'] === 'approve' ? 'admin_approved' : 'admin_rejected';

        $updateData = [
            'status' => $status,
            'approved_amount' => $validated['action'] === 'approve' ? $validated['approved_amount'] : null,
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
        ];

        // Compatibility for mixed production schemas.
        if (Schema::hasColumn('payment_requests', 'admin_notes')) {
            $updateData['admin_notes'] = $validated['admin_notes'] ?? null;
        }

        $paymentRequest->update($updateData);

        // Send in-app notification to user (trainer/employee)
        if ($paymentRequest->user) {
            $userRole = $paymentRequest->user->role;
            $action = $validated['action'] === 'approve' ? 'admin_approved' : 'admin_rejected';
            
            // Determine action URL based on user role
            $actionUrl = $userRole === 'trainer' 
                ? route('trainer.payment-requests.show', $paymentRequest->id)
                : route('employee.payment-requests.show', $paymentRequest->id);
            
            if ($action === 'admin_approved') {
                \App\Models\Notification::create([
                    'user_id' => $paymentRequest->user->id,
                    'type' => 'payment_request',
                    'title' => 'Payment Request Disetujui Admin',
                    'message' => 'Payment request Anda sebesar Rp ' . number_format($validated['approved_amount'], 0, ',', '.') . ' telah disetujui oleh admin. Menunggu validasi Finance.',
                    'data' => [
                        'payment_request_id' => $paymentRequest->id,
                        'amount' => $validated['approved_amount'],
                        'icon' => 'check-circle',
                        'action_url' => $actionUrl,
                    ],
                ]);
                
                // Notify all finance users (new payment request needs their approval)
                $financeUsers = \App\Models\User::where('role', 'finance')->get();
                foreach ($financeUsers as $financeUser) {
                    \App\Models\Notification::create([
                        'user_id' => $financeUser->id,
                        'type' => 'payment_request_pending',
                        'title' => 'Payment Request Perlu Validasi',
                        'message' => 'Payment request dari ' . $paymentRequest->user->name . ' sebesar Rp ' . number_format($validated['approved_amount'], 0, ',', '.') . ' telah disetujui Admin dan menunggu validasi Finance.',
                        'data' => [
                            'payment_request_id' => $paymentRequest->id,
                            'amount' => $validated['approved_amount'],
                            'requester_name' => $paymentRequest->user->name,
                            'icon' => 'clock',
                            'action_url' => route('finance.payment-requests.show', $paymentRequest->id),
                        ],
                    ]);
                }
            } else {
                \App\Models\Notification::create([
                    'user_id' => $paymentRequest->user->id,
                    'type' => 'payment_request',
                    'title' => 'Payment Request Ditolak Admin',
                    'message' => 'Payment request Anda ditolak oleh admin.' . ($validated['admin_notes'] ? ' Alasan: ' . $validated['admin_notes'] : ''),
                    'data' => [
                        'payment_request_id' => $paymentRequest->id,
                        'icon' => 'times-circle',
                        'rejection_reason' => $validated['admin_notes'] ?? null,
                        'action_url' => $actionUrl,
                    ],
                ]);
            }
        }

        $message = $status === 'admin_approved' 
            ? 'Permintaan pembayaran berhasil disetujui. Menunggu approval Finance.'
            : 'Permintaan pembayaran ditolak dan employee dinotifikasi';

        return redirect()->route('admin.payment-requests.index')
            ->with('success', $message);
    }
}

