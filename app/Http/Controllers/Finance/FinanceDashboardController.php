<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ClassExpense;
use App\Models\PaymentRequest;
use App\Models\Clas;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceDashboardController extends Controller
{
    private const FINANCE_EXPENSE_CATEGORIES = ['trainer_honor', 'honor'];

    public function index(Request $request)
    {
        // Get filter period/year (default: bulan berjalan + tahun ini)
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));
        $years = collect(range((int) date('Y'), (int) date('Y') - 5));

        if ($period === 'all' || $period === 'all_time') {
            // Semua bulan pada tahun terpilih
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate   = Carbon::create($year, 1, 1)->endOfYear();
        } elseif (preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month     = (int) $m[1];
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            // fallback ke bulan berjalan
            $startDate = Carbon::create($year, (int) date('m'), 1)->startOfMonth();
            $endDate   = Carbon::create($year, (int) date('m'), 1)->endOfMonth();
            $period = 'month_' . date('m');
        }

        // Chart year: ikuti tahun terpilih
        $chartYear   = $year;
        $selectedYear = $chartYear;

        // ========== AGENCY REVENUE (from Orders) ==========
        $agencyRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('confirmed_at', [$startDate, $endDate])
            ->sum('paid_amount');

        // ========== TRAINING REVENUE (from Classes) ==========
        // Revenue dari kelas yang sudah selesai (done) dan ada pembayaran
        $trainingRevenue = Clas::where('status', 'done')
            ->where('paid_amount', '>', 0)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('paid_amount');

        // Total Revenue (Agency + Training)
        $totalRevenue = $agencyRevenue + $trainingRevenue;

        // Pending Revenue - Sisa pembayaran dari kelas dengan pembayaran 2x termin
        $classWith2xTermin = Clas::where('payment_type', 'like', '%2x%')
            ->where('price', '>', 0)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();
        
        $pendingRevenue = 0;
        foreach ($classWith2xTermin as $class) {
            $remaining = $class->price - ($class->paid_amount ?? 0);
            if ($remaining > 0) {
                $pendingRevenue += $remaining;
            }
        }

        // ========== AGENCY EXPENSES (Project Expenses) ==========
        // Alfa Bank hanya menggunakan Training/Pelatihan, tidak ada Agency/Project
        $agencyExpenses = 0;

        // ========== TRAINING EXPENSES (Class Expenses) ==========
        // Hanya hitung expenses yang sudah approved
        $trainingExpenses = ClassExpense::where('approval_status', 'approved')
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Total Expenses (Agency + Training) - TIDAK TERMASUK PAYMENT REQUEST
        $totalExpenses = $agencyExpenses + $trainingExpenses;

        // Pending Expenses (need approval) - Dari Class Expenses
        $pendingExpenses = ClassExpense::where('approval_status', 'pending')
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->count();
        $pendingExpensesAmount = ClassExpense::where('approval_status', 'pending')
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // ========== PAYMENT REQUESTS (Employee + Trainer) - TERPISAH ==========
        // Payment Request yang sudah dibayar (paid)
        $paidPaymentRequests = PaymentRequest::where('status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('approved_amount');

        $paidPaymentCount = PaymentRequest::where('status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // Payment Request yang menunggu approval Finance (admin_approved)
        $pendingPayments = PaymentRequest::where('status', 'admin_approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('approved_amount');

        $pendingPaymentCount = PaymentRequest::where('status', 'admin_approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Total Payment Requests (hanya yang sudah paid, pending ada card sendiri)
        $totalPaymentRequests = $paidPaymentRequests;

        // ========== HONOR RECAP BY CATEGORY ==========
        // Keep period logic consistent with Finance Payment Request table (created_at)
        $honorRecapByCategory = Kategori::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(function ($category) use ($startDate, $endDate, $year, $period) {
                $baseQuery = PaymentRequest::whereHas('clas', function ($q) use ($category) {
                    $q->where('kategori_id', $category->id);
                });

                if ($period === 'all' || $period === 'all_time') {
                    $baseQuery->whereYear('created_at', $year);
                } else {
                    $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
                }

                $paidAmount = (clone $baseQuery)
                    ->where('status', 'paid')
                    ->sum('approved_amount');

                $paidCount = (clone $baseQuery)
                    ->where('status', 'paid')
                    ->count();

                $pendingAmount = (clone $baseQuery)
                    ->where('status', 'admin_approved')
                    ->sum('approved_amount');

                $pendingCount = (clone $baseQuery)
                    ->where('status', 'admin_approved')
                    ->count();

                return [
                    'id' => $category->id,
                    'name' => $category->nama_kategori,
                    'paid_amount' => $paidAmount,
                    'paid_count' => $paidCount,
                    'pending_amount' => $pendingAmount,
                    'pending_count' => $pendingCount,
                    'total_amount' => $paidAmount + $pendingAmount,
                    'total_count' => $paidCount + $pendingCount,
                ];
            });

        // Calculate totals across all categories
        $totalHonorByCategory = [
            'total_paid' => $paidPaymentRequests,
            'total_paid_count' => $paidPaymentCount,
            'total_pending' => $pendingPayments,
            'total_pending_count' => $pendingPaymentCount,
        ];

        // ========== ADMIN-LIKE CLASS METRICS (untuk card dashboard finance) ==========
        $adminClassQuery = Clas::query()
            ->whereBetween(DB::raw('COALESCE(start_date, created_at)'), [$startDate, $endDate]);

        $adminTotalClasses = (clone $adminClassQuery)->count();

        $adminRevenueQuery = (clone $adminClassQuery)
            ->whereIn('status', ['approved', 'done']);

        $adminTotalRevenue = (float) (clone $adminRevenueQuery)->sum('price');

        $adminRegularClasses = (clone $adminClassQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'reguler');
            })
            ->count();

        $adminCorporateClasses = (clone $adminClassQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'corporate');
            })
            ->count();

        $adminPrivateClasses = (clone $adminClassQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'private');
            })
            ->count();

        $adminRegularRevenue = (float) (clone $adminRevenueQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'reguler');
            })
            ->sum('price');

        $adminCorporateRevenue = (float) (clone $adminRevenueQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'corporate');
            })
            ->sum('price');

        $adminPrivateRevenue = (float) (clone $adminRevenueQuery)
            ->whereHas('training', function ($query) {
                $query->where('type', 'private');
            })
            ->sum('price');

        // ========== PARTICIPANT COUNTS BY CATEGORY (untuk card dashboard) ==========
        $regularParticipants = (clone $adminClassQuery)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('training', function ($query) {
                $query->where('type', 'reguler');
            })
            ->sum('amount');

        $corporateParticipants = (clone $adminClassQuery)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('training', function ($query) {
                $query->where('type', 'corporate');
            })
            ->sum('amount');

        // For Private training: 1 class = 1 student (count classes, not amount)
        $privateParticipants = (clone $adminClassQuery)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('training', function ($query) {
                $query->where('type', 'private');
            })
            ->count();

        $adminCertificationRevenue = 0.0;
        $adminCertificationStudentCount = 0;
        $adminCertificationClassCount = 0;

        if (Schema::hasColumns('clas', ['bnsp_student_count', 'bnsp_fee_per_student'])) {
            $adminCertificationClasses = (clone $adminRevenueQuery)
                ->where('sertifikasi_bnsp', true)
                ->get(['bnsp_student_count', 'bnsp_fee_per_student']);

            $adminCertificationClassCount = $adminCertificationClasses->count();
            $adminCertificationStudentCount = (int) $adminCertificationClasses->sum(function ($item) {
                return (int) ($item->bnsp_student_count ?? 0);
            });
            $adminCertificationRevenue = (float) $adminCertificationClasses->sum(function ($item) {
                return ((int) ($item->bnsp_student_count ?? 0)) * ((float) ($item->bnsp_fee_per_student ?? 0));
            });
        }

        // Net Profit = Revenue - (Expenses + Payment Requests)
        $netProfit = $totalRevenue - ($totalExpenses + $paidPaymentRequests);

        // Recent Expenses (pending approval) - Dari Class Expenses
        $recentExpenses = ClassExpense::with(['clas', 'user'])
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES)
            ->where('approval_status', 'pending')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        // Recent Payment Requests (admin_approved) - Include both Employee & Trainer
        $recentPaymentRequests = PaymentRequest::with(['user', 'project', 'clas'])
            ->where('status', 'admin_approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        // ========== MONTHLY CLASS VALUE BY CATEGORY CHART (12 months for selected year) ==========
        // Nilai kelas menggunakan price (omset kotor) dengan status approved & done
        $monthlyRegularRevenueArray = array_fill(0, 12, 0);
        $monthlyCorporateRevenueArray = array_fill(0, 12, 0);
        $monthlyPrivateRevenueArray = array_fill(0, 12, 0);

        $monthlyTrainingRevenueArray = array_fill(0, 12, 0);
        $monthlyExpensesArray = array_fill(0, 12, 0);
        $monthlyPaymentRequestsArray = array_fill(0, 12, 0);

        $monthlyRegularData = Clas::whereIn('status', ['approved', 'done'])
            ->whereYear('start_date', $chartYear)
            ->whereHas('training', function ($query) {
                $query->where('type', 'reguler');
            })
            ->selectRaw('MONTH(start_date) as month, SUM(price) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($monthlyRegularData as $month => $data) {
            $monthIndex = (int) $month - 1;
            $monthlyRegularRevenueArray[$monthIndex] = (float) $data->total;
        }

        $monthlyCorporateData = Clas::whereIn('status', ['approved', 'done'])
            ->whereYear('start_date', $chartYear)
            ->whereHas('training', function ($query) {
                $query->where('type', 'corporate');
            })
            ->selectRaw('MONTH(start_date) as month, SUM(price) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($monthlyCorporateData as $month => $data) {
            $monthIndex = (int) $month - 1;
            $monthlyCorporateRevenueArray[$monthIndex] = (float) $data->total;
        }

        $monthlyPrivateData = Clas::whereIn('status', ['approved', 'done'])
            ->whereYear('start_date', $chartYear)
            ->whereHas('training', function ($query) {
                $query->where('type', 'private');
            })
            ->selectRaw('MONTH(start_date) as month, SUM(price) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($monthlyPrivateData as $month => $data) {
            $monthIndex = (int) $month - 1;
            $monthlyPrivateRevenueArray[$monthIndex] = (float) $data->total;
        }
        
        // Get Training Revenue data for the chart year
        $monthlyRevenueData = Clas::where('status', 'done')
            ->where('paid_amount', '>', 0)
            ->whereYear('updated_at', $chartYear)
            ->selectRaw('MONTH(updated_at) as month, SUM(paid_amount) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');
        
        // Populate monthly revenue array (index 0-11 for Jan-Dec)
        foreach ($monthlyRevenueData as $month => $data) {
            $monthIndex = (int)$month - 1; // Convert to 0-based index
            $monthlyTrainingRevenueArray[$monthIndex] = (float)$data->total;
        }
        
        // Get Expenses data for the chart year (approved only)
        $monthlyExpensesData = ClassExpense::where('approval_status', 'approved')
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES)
            ->whereYear('expense_date', $chartYear)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');
        
        // Populate monthly expenses array
        foreach ($monthlyExpensesData as $month => $data) {
            $monthIndex = (int)$month - 1;
            $monthlyExpensesArray[$monthIndex] = (float)$data->total;
        }
        
        // Get Payment Requests data for the chart year (paid only)
        $monthlyPaymentData = PaymentRequest::where('status', 'paid')
            ->whereYear('updated_at', $chartYear)
            ->selectRaw('MONTH(updated_at) as month, SUM(approved_amount) as total')
            ->groupBy('month')
            ->get()
            ->keyBy('month');
        
        // Populate monthly payment requests array
        foreach ($monthlyPaymentData as $month => $data) {
            $monthIndex = (int)$month - 1;
            $monthlyPaymentRequestsArray[$monthIndex] = (float)$data->total;
        }

        return view('finance.dashboard', compact(
            'totalRevenue',
            'agencyRevenue',
            'trainingRevenue',
            'pendingRevenue',
            'totalExpenses',
            'agencyExpenses',
            'trainingExpenses',
            'pendingExpenses',
            'pendingExpensesAmount',
            'totalPaymentRequests',
            'paidPaymentRequests',
            'paidPaymentCount',
            'pendingPayments',
            'pendingPaymentCount',
            'netProfit',
            'recentExpenses',
            'recentPaymentRequests',
            'monthlyRegularRevenueArray',
            'monthlyCorporateRevenueArray',
            'monthlyPrivateRevenueArray',
            'monthlyTrainingRevenueArray',
            'monthlyExpensesArray',
            'monthlyPaymentRequestsArray',
            'selectedYear',
            'adminTotalClasses',
            'adminTotalRevenue',
            'adminRegularClasses',
            'adminCorporateClasses',
            'adminPrivateClasses',
            'adminRegularRevenue',
            'adminCorporateRevenue',
            'adminPrivateRevenue',
            'adminCertificationRevenue',
            'adminCertificationClassCount',
            'adminCertificationStudentCount',
            'regularParticipants',
            'corporateParticipants',
            'privateParticipants',
            'period',
            'year',
            'years',
            'honorRecapByCategory',
            'totalHonorByCategory'
        ));
    }
}
