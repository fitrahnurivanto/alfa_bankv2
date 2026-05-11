<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display trainer dashboard
     */
    public function index(Request $request)
    {
        $trainer = $this->trainer();

        $period = $request->get('period', 'month_' . date('m'));
        $year = $request->get('year', date('Y'));
        $status = $request->get('status', 'all');

        $classQuery = $trainer->classes()->with(['kategori']);

        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $classQuery->whereBetween('clas.start_date', $dateFilter);
        } elseif ($year !== 'all') {
            $classQuery->whereYear('clas.start_date', $year);
        }

        if ($status !== 'all') {
            $classQuery->where('clas.status', $status);
        }

        $paymentRequestQuery = PaymentRequest::where('user_id', $trainer->id);

        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $paymentRequestQuery->whereBetween('created_at', $dateFilter);
        }

        if ($year !== 'all') {
            $paymentRequestQuery->whereYear('created_at', $year);
        }

        // Total kelas yang diajar trainer ini
        $totalClasses = (clone $classQuery)->count();

        // Kelas bulan ini
        $classesThisMonth = $trainer->classes()
            ->whereMonth('clas.start_date', Carbon::now()->month)
            ->whereYear('clas.start_date', Carbon::now()->year)
            ->count();

        // Total honor dari semua kelas (menggunakan trainer_honor dari tabel clas)
        $totalHonor = (clone $classQuery)->sum('clas.trainer_honor');

        // Pending payment requests
        $pendingPayments = (clone $paymentRequestQuery)
            ->where('status', 'pending')
            ->count();

        // Total earnings (payment requests yang sudah paid)
        $totalEarnings = (clone $paymentRequestQuery)
            ->where('status', 'paid')
            ->sum('approved_amount');

        // Kelas by Status
        $klasByStatus = [
            'pending' => (clone $classQuery)->where('clas.status', 'pending')->count(),
            'approved' => (clone $classQuery)->where('clas.status', 'approved')->count(),
            'done' => (clone $classQuery)->where('clas.status', 'done')->count(),
            'rejected' => (clone $classQuery)->where('clas.status', 'rejected')->count(),
        ];

        $paymentRequestStats = [
            'pending' => [
                'count' => (clone $paymentRequestQuery)->where('status', 'pending')->count(),
                'amount' => (float) (clone $paymentRequestQuery)->where('status', 'pending')->sum('requested_amount'),
            ],
            'waiting' => [
                'count' => (clone $paymentRequestQuery)->whereIn('status', ['admin_approved', 'finance_approved'])->count(),
                'amount' => (float) (clone $paymentRequestQuery)->whereIn('status', ['admin_approved', 'finance_approved'])->sum('approved_amount'),
            ],
            'rejected' => [
                'count' => (clone $paymentRequestQuery)->whereIn('status', ['admin_rejected', 'finance_rejected'])->count(),
                'amount' => (float) (clone $paymentRequestQuery)->whereIn('status', ['admin_rejected', 'finance_rejected'])->sum('requested_amount'),
            ],
            'paid' => [
                'count' => (clone $paymentRequestQuery)->where('status', 'paid')->count(),
                'amount' => (float) (clone $paymentRequestQuery)->where('status', 'paid')->sum('approved_amount'),
            ],
        ];

        $availableYears = collect()
            ->merge($trainer->classes()->selectRaw('YEAR(clas.start_date) as year')->whereNotNull('clas.start_date')->distinct()->pluck('year'))
            ->merge(PaymentRequest::where('user_id', $trainer->id)->selectRaw('YEAR(created_at) as year')->whereNotNull('created_at')->distinct()->pluck('year'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        // Chart Income (Payment Requests yang sudah paid)
        // Always show Jan-Dec to keep trend comparison consistent month to month.
        [$chartStart, $chartEnd, $chartDescription] = $this->getChartRange((string) $year);

        $monthlyIncome = (clone $paymentRequestQuery)
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$chartStart, $chartEnd])
            ->selectRaw('MONTH(updated_at) as month, YEAR(updated_at) as year, SUM(approved_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        // Prepare chart data for all months in selected year (fill missing months with 0)
        $chartData = [];
        $cursor = $chartStart->copy()->startOfMonth();
        $chartLastMonth = $chartEnd->copy()->startOfMonth();

        while ($cursor->lte($chartLastMonth)) {
            $date = $cursor->copy();
            $key = $date->format('Y-m');
            $chartData[] = [
                'month' => $date->format('M'),
                'income' => (float) ($monthlyIncome->get($key)->total ?? 0),
            ];

            $cursor->addMonth();
        }

        // Recent classes (5 latest)
        $recentClasses = (clone $classQuery)
            ->orderByDesc('clas.start_date')
            ->take(5)
            ->get();

        // Upcoming classes (status approved, belum selesai)
        $upcomingClasses = $trainer->classes()
            ->with(['kategori'])
            ->where('status', 'approved')
            ->when($year !== 'all', function ($query) use ($year) {
                $query->whereYear('clas.start_date', $year);
            })
            ->when($period !== 'all', function ($query) use ($period, $year) {
                $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
                $dateFilter = $this->getDateFilter($period, $actualYear);
                $query->whereBetween('clas.start_date', $dateFilter);
            })
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get();

        // Recent payment requests (5 latest)
        $recentPayments = (clone $paymentRequestQuery)
            ->with(['class'])
            ->latest()
            ->take(5)
            ->get();

        return view('trainer.dashboard', compact(
            'trainer',
            'totalClasses',
            'classesThisMonth',
            'totalHonor',
            'pendingPayments',
            'totalEarnings',
            'klasByStatus',
            'paymentRequestStats',
            'availableYears',
            'period',
            'year',
            'status',
            'chartData',
            'chartDescription',
            'recentClasses',
            'upcomingClasses',
            'recentPayments'
        ));
    }

    private function getChartRange(string $year): array
    {
        if ($year !== 'all') {
            $selectedYear = (int) $year;
        } else {
            $selectedYear = (int) date('Y');
        }

        return [
            Carbon::create($selectedYear, 1, 1)->startOfYear(),
            Carbon::create($selectedYear, 12, 31)->endOfDay(),
            'Januari - Desember ' . $selectedYear,
        ];
    }

    private function getDateFilter(string $period, int $year = 0): array
    {
        $year = $year ?: (int) date('Y');

        if (preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            return [$start, $end];
        }

        $now = Carbon::now();
        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
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
