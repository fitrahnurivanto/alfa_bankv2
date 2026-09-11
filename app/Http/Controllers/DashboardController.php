<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\Client;
use App\Models\PaymentRequest;
use App\Models\ClassExpense;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with class overview (Academy).
     */
    public function adminDashboard(Request $request)
    {
        $user = $this->currentUser();

        if (!($user->isAdmin() || $user->isMarketing() || $user->isAkademik())) {
            abort(403, 'Akses dashboard ditolak.');
        }

        // Filter parameters
        $status = $request->get('status', 'all');
        $period = $request->get('period', 'month_' . date('m')); // Default: Bulan berjalan saat ini
        $year = $request->get('year', date('Y'));

        // Base query for classes
        $classQuery = Clas::query();

        // Apply period filter (month_01 .. month_12 incorporates year in range)
        // Use COALESCE to fallback to created_at if start_date is NULL (samakan dengan akademik dashboard)
        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $classQuery->whereBetween(DB::raw('COALESCE(start_date, created_at)'), $dateFilter);
        } elseif ($year !== 'all') {
            $classQuery->whereYear(DB::raw('COALESCE(start_date, created_at)'), $year);
        }

        $classQueryByPeriodYear = clone $classQuery;

        // Apply status filter
        if ($status === 'completed') {
            $classQuery->where('status', 'done');
        } elseif ($status === 'active') {
            $classQuery->where('status', 'approved');
        }

        // Statistics
        $totalClasses = $classQuery->count();
        $completedClasses = (clone $classQuery)->where('status', 'done')->count();
        $activeClasses = (clone $classQuery)->where('status', 'approved')->count();
        $pendingClasses = (clone $classQuery)->where('status', 'pending')->count();
        
        // Separate classes by status for three tables
        $classQueryBase = clone $classQuery;
        $completedClassesList = (clone $classQueryBase)->where('status', 'done')->with('trainers:id,name')->orderByDesc('end_date')->get();
        $activeClassesList = (clone $classQueryBase)->where('status', 'approved')->with('trainers:id,name')->orderBy('start_date')->get();
        $pendingClassesList = (clone $classQueryBase)->where('status', 'pending')->with('trainers:id,name')->orderByDesc('created_at')->get();

        // Revenue calculation now follows cash basis from confirmed payments.
        // Class counts still follow operational class status, but omzet comes from payment confirmation date.
        $classes = (clone $classQuery)->whereIn('status', ['approved', 'done'])
            ->with('kategori')
            ->get();

        $totalRevenue = 0;
foreach ($classes as $class) {
    $totalRevenue += (float) ($class->price ?? 0);
}

$totalPaid = (clone $classQuery)->whereIn('status', ['approved', 'done'])
    ->sum('paid_amount');

$classRevenue = $totalRevenue;
$classValueRevenue = $totalRevenue;

        // Invoice belum digunakan pada deployment ini karena tabel invoices belum tersedia.
        $totalRemainingPayment = 0;

        // Certification (BNSP) stats
        $hasBnspFinancialColumns = Schema::hasColumns('clas', [
            'bnsp_student_count',
            'bnsp_fee_per_student',
        ]);

        $certificationClasses = collect();
        if ($hasBnspFinancialColumns) {
            $certificationClasses = (clone $classQuery)
                ->where('sertifikasi_bnsp', true)
                ->whereIn('status', ['approved', 'done'])
                ->get(['bnsp_student_count', 'bnsp_fee_per_student']);
        }

        $certificationClassCount = $certificationClasses->count();
        $certificationStudentCount = (int) $certificationClasses->sum(function ($item) {
            return (int) ($item->bnsp_student_count ?? 0);
        });
        $certificationRevenue = (float) $certificationClasses->sum(function ($item) {
            return ((int) ($item->bnsp_student_count ?? 0)) * ((float) ($item->bnsp_fee_per_student ?? 0));
        });
        
        // For compatibility with view
        $totalProjects = $totalClasses;
        $completedProjects = $completedClasses;

        // Cost calculation (ONLY FROM APPROVED EXPENSES):
        // Total biaya operasional HANYA dari ClassExpense yang sudah approved oleh Finance
        // Expenses yang masih pending TIDAK dihitung
        $classIds = $classQuery->pluck('id');
        
        $totalCost = ClassExpense::whereIn('clas_id', $classIds)
            ->where('approval_status', 'approved')
            ->sum('amount');

        $totalProfit = $totalPaid - $totalCost;
        
        // Total Class Income (Cash Basis: paid_amount - biaya operasional)
        // Untuk termin 2x: hanya hitung uang yang SUDAH MASUK
        $totalClassIncome = $totalProfit;
        $profitMargin = $totalPaid > 0 ? ($totalProfit / $totalPaid) * 100 : 0;

        // Training type stats (berdasarkan type: corporate, reguler, private)
       $trainingStats = DB::table('trainings')
    ->leftJoin('clas', 'trainings.id', '=', 'clas.training_id')
    ->select(
        DB::raw("CASE 
            WHEN trainings.type = 'corporate' THEN 'Corporate Training'
            WHEN trainings.type = 'reguler' THEN 'Regular Training'
            WHEN trainings.type = 'private' THEN 'Private Training'
            ELSE 'Unknown'
        END as name"),
        DB::raw('COUNT(DISTINCT clas.id) as total_classes'),
        DB::raw('SUM(clas.price) as total_revenue')
    )
    ->whereNotNull('clas.id')
    ->groupBy('trainings.type')
    ->orderByDesc('total_classes')
    ->get();

        $regularClassesCount = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
            })
            ->count();

        $corporateClassesCount = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
            })
            ->count();

        $privateClassesCount = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
            })
            ->count();

       $regularRevenue = (clone $classQuery)
    ->whereIn('status', ['approved', 'done'])
        ->whereHas('kategori', function ($query) {
            $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
    })
    ->sum('price');

$corporateRevenue = (clone $classQuery)
    ->whereIn('status', ['approved', 'done'])
    ->whereHas('kategori', function ($query) {
        $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
    })
    ->sum('price');

$privateRevenue = (clone $classQuery)
    ->whereIn('status', ['approved', 'done'])
    ->whereHas('kategori', function ($query) {
        $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
    })
    ->sum('price');

        // Rekap Kelas section (period/year-aware, independent from status filter dropdown)
        $runningRegularClasses = (clone $classQueryByPeriodYear)
            ->where('status', 'approved')
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
            })
            ->count();

        $runningCorporateClasses = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
            })
            ->count();

        $runningPrivateClasses = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
            })
            ->count();

        $regularParticipants = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
            })
            ->sum('amount');

        $corporateParticipants = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
            })
            ->sum('amount');

        // For Private training: 1 class = 1 student (count classes, not amount)
        $privateParticipants = (clone $classQueryByPeriodYear)
            ->whereIn('status', ['approved', 'done'])
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
            })
            ->count();

        $regularPassFail = (clone $classQueryByPeriodYear)
            ->where('status', 'done')
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
            })
            ->selectRaw("SUM(COALESCE(passed_students, CASE WHEN grade_file_status = 'approved' THEN COALESCE(amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(failed_students, 0)) as failed_total')
            ->first();

        $corporatePassFail = (clone $classQueryByPeriodYear)
            ->where('status', 'done')
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
            })
            ->selectRaw("SUM(COALESCE(passed_students, CASE WHEN grade_file_status = 'approved' THEN COALESCE(amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(failed_students, 0)) as failed_total')
            ->first();

        $privatePassFail = (clone $classQueryByPeriodYear)
            ->where('status', 'done')
            ->whereHas('kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
            })
            ->selectRaw("SUM(COALESCE(passed_students, CASE WHEN grade_file_status = 'approved' THEN COALESCE(amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(failed_students, 0)) as failed_total')
            ->first();

        $regularPassedParticipants = (int) ($regularPassFail->passed_total ?? 0);
        $regularFailedParticipants = (int) ($regularPassFail->failed_total ?? 0);
        $corporatePassedParticipants = (int) ($corporatePassFail->passed_total ?? 0);
        $corporateFailedParticipants = (int) ($corporatePassFail->failed_total ?? 0);
        $privatePassedParticipants = (int) ($privatePassFail->passed_total ?? 0);
        $privateFailedParticipants = (int) ($privatePassFail->failed_total ?? 0);

        // Rekap Honor section: honor expenses that have been approved.
        $honorExpenseBaseQuery = ClassExpense::query()
            ->whereIn('category', ['honor', 'trainer_honor'])
            ->where('approval_status', 'approved')
            ->whereHas('clas', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'rejected']);
            });

        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $honorExpenseBaseQuery->whereBetween('expense_date', $dateFilter);
        } elseif ($year !== 'all') {
            $honorExpenseBaseQuery->whereYear('expense_date', $year);
        }

        $regularHonorPayment = (clone $honorExpenseBaseQuery)
            ->whereHas('clas.kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%regul%']);
            })
            ->sum('amount');

        $trainingHonorPayment = (clone $honorExpenseBaseQuery)
            ->whereHas('clas.kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%']);
            })
            ->sum('amount');

        $privateHonorPayment = (clone $honorExpenseBaseQuery)
            ->whereHas('clas.kategori', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) LIKE ?', ['%private%']);
            })
            ->sum('amount');

        $totalHonorPayment = (float) $regularHonorPayment + (float) $trainingHonorPayment + (float) $privateHonorPayment;

        // User and division variables (DIPINDAHKAN KE ATAS)
        $user = $this->currentUser();
        $activeDivision = 'training'; // System is Training only
        $selectedYear = $year !== 'all' ? $year : date('Y');

        // Growth calculations (compare with previous period)
        $revenueGrowth = 0; // Default: no growth data
        $costGrowth = 0;
        $profitGrowth = 0;
        $marginChange = 0;

        // Revenue by service/training (for pie chart)
        $revenueByService = $trainingStats;

        // Outstanding payments (belum lunas)
        $outstandingPayments = [];

        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();

        // For month-based revenue chart we want to count classes created in the current month
        // and in 'approved' state. This changes the definition to use the class creation date
        // as the reference (tanggal dibuat kelas) instead of overlapping start/end dates.
        $applyCurrentMonthClassScope = function ($query) use ($currentMonthStart, $currentMonthEnd) {
            $query->where('clas.status', 'approved')
                  ->whereBetween(DB::raw('DATE(clas.created_at)'), [$currentMonthStart, $currentMonthEnd]);
        };

        $applyCurrentMonthActiveStartScope = function ($query) use ($currentMonthStart, $currentMonthEnd) {
            $query->where('clas.status', 'approved')
                ->whereBetween('clas.start_date', [$currentMonthStart, $currentMonthEnd]);
        };

        $currentMonthStudentsByTrainingQuery = DB::table('clas')
            ->leftJoin('trainings', 'clas.training_id', '=', 'trainings.id')
            ->select(
                'trainings.id as training_id',
                DB::raw("COALESCE(trainings.name, 'Tanpa Pelatihan') as training_name"),
                DB::raw('SUM(COALESCE(clas.amount, 0)) as total_students')
            );

        $applyCurrentMonthActiveStartScope($currentMonthStudentsByTrainingQuery);

        $currentMonthStudentsByTraining = $currentMonthStudentsByTrainingQuery
            ->groupBy('trainings.id', 'trainings.name')
            ->havingRaw('SUM(COALESCE(clas.amount, 0)) > 0')
            ->orderByDesc('total_students')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'training' => (object) ['name' => $item->training_name],
                    'total_students' => (int) $item->total_students,
                ];
            });

        $currentMonthClassRevenueByClassQuery = DB::table('clas')
    ->leftJoin('trainings', 'clas.training_id', '=', 'trainings.id')
    ->select(
        'clas.id as class_id',
        'clas.name as class_name',
        DB::raw("COALESCE(trainings.name, 'Tanpa Pelatihan') as training_name"),
        DB::raw('SUM(COALESCE(clas.price, 0)) as total_revenue')
    );

$currentMonthClassRevenueByClassQuery->whereIn('clas.status', ['approved', 'done'])
    ->whereDate('clas.start_date', '<=', $currentMonthEnd)
    ->where(function ($query) use ($currentMonthStart) {
        $query->whereNull('clas.end_date')
            ->orWhereDate('clas.end_date', '>=', $currentMonthStart);
    });

$currentMonthClassRevenueByClass = $currentMonthClassRevenueByClassQuery
    ->groupBy('clas.id', 'clas.name', 'trainings.name')
    ->havingRaw('SUM(COALESCE(clas.price, 0)) > 0')
    ->orderByDesc('total_revenue')
    ->get()
    ->map(function ($item) {
        return (object) [
            'class_name' => $item->class_name,
            'training_name' => $item->training_name,
            'total_revenue' => (float) $item->total_revenue,
        ];
    });

        $currentMonthLabel = Carbon::now()->translatedFormat('F Y');

        $certificationByTraining = collect();
        if ($hasBnspFinancialColumns) {
            $certificationByTrainingQuery = DB::table('clas')
                ->join('trainings', 'clas.training_id', '=', 'trainings.id')
                ->select(
                    'trainings.id as training_id',
                    'trainings.name as training_name',
                    DB::raw('SUM(COALESCE(clas.bnsp_student_count, 0)) as total_students')
                )
                ->where('clas.sertifikasi_bnsp', true)
                ->where('clas.status', '!=', 'cancelled')
                ->where('clas.status', '!=', 'rejected');

            if ($period !== 'all') {
                $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
                $dateFilter = $this->getDateFilter($period, $actualYear);
                $certificationByTrainingQuery->whereBetween(
                    DB::raw('COALESCE(clas.bnsp_tanggal_sertifikasi, clas.start_date)'),
                    $dateFilter
                );
            } elseif ($year !== 'all') {
                $certificationByTrainingQuery->whereYear(
                    DB::raw('COALESCE(clas.bnsp_tanggal_sertifikasi, clas.start_date)'),
                    $year
                );
            }

            if ($status === 'completed') {
                $certificationByTrainingQuery->where('clas.status', 'done');
            } elseif ($status === 'active') {
                $certificationByTrainingQuery->where('clas.status', 'approved');
            }

            $certificationByTraining = $certificationByTrainingQuery
                ->groupBy('trainings.id', 'trainings.name')
                ->havingRaw('SUM(COALESCE(clas.bnsp_student_count, 0)) > 0')
                ->orderByDesc('total_students')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'training' => (object) ['name' => $item->training_name],
                        'total_students' => (int) $item->total_students,
                    ];
                });
        }

        // Project chart year
        $projectChartYear = $selectedYear;

        // Recent classes (filtered by period)
        $recentClassesQuery = Clas::with(['training', 'client', 'trainers']);
        
        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $recentClassesQuery->whereBetween('start_date', $dateFilter);
        } elseif ($year !== 'all') {
            $recentClassesQuery->whereYear('start_date', $year);
        }
        
        $recentClasses = $recentClassesQuery
            ->latest()
            ->limit(10)
            ->get();

        // Monthly revenue chart (for selected year)
       $chartYear = $year !== 'all' ? $year : date('Y');
$monthlyClasses = Clas::query()
    ->whereYear('start_date', $chartYear)
    ->whereNotIn('status', ['cancelled', 'rejected'])
    ->get()
    ->groupBy(function ($item) {
        return date('Y-m', strtotime($item->start_date));
    });

$monthlyData = collect();
foreach ($monthlyClasses as $month => $classesInMonth) {
    $grossRevenue = 0;
    $remainingFromApproved = 0;
    $remainingFromUnapproved = 0;

    foreach ($classesInMonth as $class) {
        $price = (float) ($class->price ?? 0);
        $paidAmount = (float) ($class->paid_amount ?? 0);

        if (in_array($class->status, ['approved', 'done'], true)) {
            $grossRevenue += $price;
            $remaining = $price - $paidAmount;
            if ($remaining > 0) {
                $remainingFromApproved += $remaining;
            }
        } else {
            $remainingFromUnapproved += $price;
        }
    }

    $monthlyData->push((object) [
        'month' => $month,
        'total_classes' => $classesInMonth->count(),
        'total_price' => $grossRevenue,
        'total_paid' => $grossRevenue,
        'total_remaining' => $remainingFromApproved + $remainingFromUnapproved,
    ]);
}

        $monthlyData = $monthlyData->sortBy('month')->values();

        // Payment requests pending
        $pendingPayments = PaymentRequest::with(['clas', 'trainer'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Top trainers (by class count, filtered by period)
        $topTrainersQuery = DB::table('users')
            ->join('clas_trainer', 'users.id', '=', 'clas_trainer.user_id')
            ->join('clas', 'clas_trainer.clas_id', '=', 'clas.id')
            ->where('users.role', 'trainer')
            ->where('clas.status', 'done');
        
        if ($period !== 'all') {
            $actualYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $dateFilter = $this->getDateFilter($period, $actualYear);
            $topTrainersQuery->whereBetween('clas.start_date', $dateFilter);
        } elseif ($year !== 'all') {
            $topTrainersQuery->whereYear('clas.start_date', $year);
        }
        
        $topTrainers = $topTrainersQuery
            ->select('users.*', DB::raw('COUNT(clas.id) as total_classes'))
            ->groupBy('users.id')
            ->orderByDesc('total_classes')
            ->limit(5)
            ->get();

        // Recent activities
        $recentActivities = \App\Models\ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Monthly target (based on filtered period)
        $targetKey = 'target_omset_academy_' . date('Y-m'); // Default ke bulan sekarang
        
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            // Extract month from period (month_01 -> 01)
            $month = (int) $m[1];
            $filterYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $targetKey = 'target_omset_academy_' . sprintf('%04d-%02d', $filterYear, $month);
        } elseif ($year !== 'all' && $period === 'all') {
            // Year only, default ke bulan sekarang di tahun itu
            $filterYear = (int) $year;
            $currentMonth = (int) date('m');
            $targetKey = 'target_omset_academy_' . sprintf('%04d-%02d', $filterYear, $currentMonth);
        }
        
        $targetAmount = (float) Setting::get($targetKey, 0);
        $selectedTargetAmount = $targetAmount;
        $selectedTargetNotes = '';

        // Additional chart data for Academy
        $topProjects = collect([]); // Empty collection (no projects in Academy)
        $monthlyRevenue = $monthlyData; // Reuse monthlyData
        $monthlyProjectCount = []; // Empty array
        $weeklyData = []; // Empty array
        
        // Initialize 12-month arrays (Jan-Dec) for charts
        $monthlyClassRevenue = array_fill(0, 12, 0);
        $monthlyRemainingPayment = array_fill(0, 12, 0);
        $monthlyClassCost = array_fill(0, 12, 0);
        $monthlyClassCount = array_fill(0, 12, 0);
        
        // Populate monthly arrays from database data
        foreach ($monthlyData as $data) {
            $monthIndex = (int) date('n', strtotime($data->month . '-01')) - 1; // 0-11
            // Green line follows gross revenue card value (approved/done only).
            $monthlyClassRevenue[$monthIndex] = (float) ($data->total_price ?? 0);
            $monthlyRemainingPayment[$monthIndex] = (float) ($data->total_remaining ?? 0);
            $monthlyClassCount[$monthIndex] = (int) ($data->total_classes ?? 0);
        }
        
        // Calculate monthly costs from class expenses.
        // Include approved + pending so operational line reflects all operational entries (expense + honor).
        $monthlyCostData = ClassExpense::select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total_cost')
            )
            ->whereYear('expense_date', $chartYear)
            ->whereIn('approval_status', ['approved', 'pending'])
            ->whereIn('clas_id', function($query) {
                $query->select('id')->from('clas')
                    ->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        foreach ($monthlyCostData as $data) {
            $monthIndex = (int) date('n', strtotime($data->month . '-01')) - 1; // 0-11
            $monthlyClassCost[$monthIndex] = (float) ($data->total_cost ?? 0);
        }

        // Certification monthly data (for selected chart year)
        $monthlyCertificationRevenue = array_fill(0, 12, 0);
        $monthlyCertificationCount = array_fill(0, 12, 0);
        $monthlyCertificationClassCount = array_fill(0, 12, 0);

        $certificationChartQuery = Clas::query()
            ->where('sertifikasi_bnsp', true)
            ->where(function ($query) use ($chartYear) {
                $query->whereYear('bnsp_tanggal_sertifikasi', $chartYear)
                    ->orWhere(function ($fallback) use ($chartYear) {
                        $fallback->whereNull('bnsp_tanggal_sertifikasi')
                            ->whereYear('start_date', $chartYear);
                    });
            });

        if ($status === 'completed') {
            $certificationChartQuery->where('status', 'done');
        } elseif ($status === 'active') {
            $certificationChartQuery->where('status', 'approved');
        } else {
            $certificationChartQuery->whereIn('status', ['approved', 'done']);
        }

        $certificationChartClasses = collect();
        if ($hasBnspFinancialColumns) {
            $certificationChartClasses = $certificationChartQuery
                ->get(['bnsp_tanggal_sertifikasi', 'start_date', 'bnsp_student_count', 'bnsp_fee_per_student']);
        }

        foreach ($certificationChartClasses as $certClass) {
            $referenceDate = $certClass->bnsp_tanggal_sertifikasi ?? $certClass->start_date;
            if (!$referenceDate) {
                continue;
            }

            $monthIndex = Carbon::parse($referenceDate)->month - 1;
            $monthlyCertificationClassCount[$monthIndex]++;
            $monthlyCertificationCount[$monthIndex] += (int) ($certClass->bnsp_student_count ?? 0);
            $monthlyCertificationRevenue[$monthIndex] += ((int) ($certClass->bnsp_student_count ?? 0)) * ((float) ($certClass->bnsp_fee_per_student ?? 0));
        }
        
        // Fallback: jika semua bulan kosong (expenses belum approved), pakai cost + trainer_honor dari field
        $totalApprovedExpenses = array_sum($monthlyClassCost);
        if ($totalApprovedExpenses == 0) {
            // Group classes by month and calculate cost from fields
            $classesForCostByMonth = Clas::whereYear('start_date', $chartYear)
                ->whereIn('status', ['approved', 'done'])
                ->get()
                ->groupBy(function($class) {
                    return date('n', strtotime($class->start_date)) - 1; // 0-11
                });
            
            foreach ($classesForCostByMonth as $monthIndex => $classes) {
                $costForMonth = 0;
                foreach ($classes as $class) {
                    $costForMonth += ($class->cost ?? 0) + ($class->trainer_honor ?? 0);
                }
                $monthlyClassCost[$monthIndex] = $costForMonth;
            }
        }

        // Target omset uses gross revenue as baseline (not paid amount/net).
        $targetPercentage = $targetAmount > 0 ? ($totalRevenue / $targetAmount) * 100 : 0;
        $targetRevenue = $totalRevenue;
        
        // For Agency mode (although this is Academy system)
        $activeProjects = $activeClasses; // Same as activeClasses for compatibility
        $revenueChartYear = $selectedYear; // Use selectedYear for chart title

        return view('admin.dashboard', compact(
            'user',
            'activeDivision',
            'selectedYear',
            'selectedTargetAmount',
            'selectedTargetNotes',
            'targetAmount',
            'totalClasses',
            'totalProjects',
            'completedClasses',
            'completedProjects',
            'activeClasses',
            'activeProjects',
            'pendingClasses',
            'completedClassesList',
            'activeClassesList',
            'pendingClassesList',
            'totalRevenue',
            'targetPercentage',
            'targetRevenue',
            'revenueChartYear',
            'classRevenue',
            'classValueRevenue',
            'totalPaid',
            'totalClassIncome',
            'totalRemainingPayment',
            'totalCost',
            'totalProfit',
            'profitMargin',
            'revenueGrowth',
            'costGrowth',
            'profitGrowth',
            'marginChange',
            'revenueByService',
            'outstandingPayments',
            'currentMonthStudentsByTraining',
            'currentMonthClassRevenueByClass',
            'currentMonthLabel',
            'certificationByTraining',
            'projectChartYear',
            'recentClasses',
            'recentActivities',
            'monthlyData',
            'monthlyRevenue',
            'monthlyProjectCount',
            'weeklyData',
            'monthlyClassRevenue',
            'monthlyRemainingPayment',
            'monthlyClassCost',
            'monthlyClassCount',
            'certificationClassCount',
            'certificationStudentCount',
            'certificationRevenue',
            'monthlyCertificationRevenue',
            'monthlyCertificationCount',
            'monthlyCertificationClassCount',
            'topProjects',
            'pendingPayments',
            'topTrainers',
            'trainingStats',
            'regularClassesCount',
            'corporateClassesCount',
            'privateClassesCount',
            'regularRevenue',
            'corporateRevenue',
            'privateRevenue',
            'runningRegularClasses',
            'runningCorporateClasses',
            'runningPrivateClasses',
            'regularParticipants',
            'corporateParticipants',
            'privateParticipants',
            'regularPassedParticipants',
            'regularFailedParticipants',
            'corporatePassedParticipants',
            'corporateFailedParticipants',
            'privatePassedParticipants',
            'privateFailedParticipants',
            'regularHonorPayment',
            'trainingHonorPayment',
            'privateHonorPayment',
            'totalHonorPayment',
            'status',
            'period',
            'year'
        ));
    }

    /**
     * Client dashboard
     */
    public function clientDashboard()
    {
        $user = $this->currentUser();
        $client = Client::where('email', $user->email)->first();

        if (!$client) {
            return view('client.dashboard-empty');
        }

        // Client's classes
        $myClasses = Clas::where('client_id', $client->id)
            ->with(['training', 'trainers'])
            ->latest()
            ->get();

        $totalClasses = $myClasses->count();
        $completedClasses = $myClasses->where('status', 'done')->count();
        $activeClasses = $myClasses->where('status', 'approved')->count();
        $upcomingClasses = $myClasses->where('status', 'approved')
            ->where('start_date', '>', now())
            ->count();

        // Get client's orders if they exist
        $orders = $client->orders()
            ->with('items')
            ->latest()
            ->take(5)
            ->get();

        // Get client's projects if they exist
        $projects = $client->projects()
            ->latest()
            ->take(5)
            ->get();

        // Calculate stats
        $stats = [
            'total_orders' => $client->orders()->count(),
            'total_spent' => $client->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'active_projects' => $client->projects()->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_projects' => $client->projects()->where('status', 'completed')->count(),
        ];

        return view('client.dashboard', compact(
            'client',
            'myClasses',
            'totalClasses',
            'completedClasses',
            'activeClasses',
            'upcomingClasses',
            'orders',
            'projects',
            'stats'
        ));
    }

    /**
     * Get date filter range based on period (month_01 .. month_12)
     */
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

    /**
     * Get filtered data for AJAX requests
     */
    public function getFilteredData(Request $request)
    {
        // Same logic as adminDashboard but return JSON
        // This can be used for dynamic filtering without page reload
        return response()->json([
            'success' => true,
            'message' => 'Data filtered successfully'
        ]);
    }

    /**
     * Save monthly target
     */
    public function saveTarget(Request $request)
    {
        if (!$this->currentUser()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengubah target.');
        }

        $request->validate([
            'target_amount' => 'required|numeric|min:0',
            'division' => 'required|in:academy',
            'period' => 'nullable|in:all,month_01,month_02,month_03,month_04,month_05,month_06,month_07,month_08,month_09,month_10,month_11,month_12',
            'year' => 'nullable|numeric|min:2000|max:2099'
        ]);

        // Determine target key based on period/year filters
        $targetKey = 'target_omset_academy_' . date('Y-m');
        $period = $request->get('period', 'all');
        $year = $request->get('year', date('Y'));

        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $filterYear = ($year !== 'all') ? (int) $year : (int) date('Y');
            $targetKey = 'target_omset_academy_' . sprintf('%04d-%02d', $filterYear, $month);
        } elseif ($year !== 'all' && $period === 'all') {
            $filterYear = (int) $year;
            $currentMonth = (int) date('m');
            $targetKey = 'target_omset_academy_' . sprintf('%04d-%02d', $filterYear, $currentMonth);
        }

        Setting::set($targetKey, $request->target_amount);

        return redirect()->route('admin.dashboard', [
            'period' => $period,
            'year' => $year
        ])->with('success', 'Target omset berhasil disimpan!');
    }

    /**
     * Get calendar events (for dashboard calendar view)
     */
    public function getCalendarEvents(Request $request)
    {
        $classes = Clas::with(['training', 'client'])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function($class) {
                return [
                    'id' => $class->id,
                    'title' => $class->training->name ?? 'Kelas',
                    'start' => $class->start_date,
                    'end' => $class->end_date,
                    'color' => $this->getEventColor($class->status),
                    'url' => route('admin.classes.show', $class->id)
                ];
            });

        return response()->json($classes);
    }

    /**
     * Get event color based on status
     */
    private function getEventColor($status)
    {
        return match($status) {
            'pending' => '#fbbf24', // yellow
            'approved' => '#3b82f6', // blue
            'in_progress' => '#10b981', // green
            'completed' => '#6b7280', // gray
            'cancelled' => '#ef4444', // red
            default => '#3b82f6'
        };
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403, 'Akses dashboard ditolak.');
        }

        return $user;
    }
}
