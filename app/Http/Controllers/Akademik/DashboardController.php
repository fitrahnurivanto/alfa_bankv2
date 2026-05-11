<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter period/year mengikuti pola dashboard lain
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));
        $years = collect(range((int) date('Y'), (int) date('Y') - 5));

        if ($period === 'all' || $period === 'all_time') {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 1, 1)->endOfYear();
        } elseif (preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $startDate = Carbon::create($year, (int) date('m'), 1)->startOfMonth();
            $endDate = Carbon::create($year, (int) date('m'), 1)->endOfMonth();
            $period = 'month_' . date('m');
        }

        $selectedYear = $year;
        $clasDateExpr = DB::raw('COALESCE(clas.start_date, clas.created_at)');

        $applyClasDateFilter = function ($query) use ($period, $year, $startDate, $endDate, $clasDateExpr) {
            if ($period === 'all' || $period === 'all_time') {
                $query->whereYear($clasDateExpr, $year);
            } else {
                $query->whereBetween($clasDateExpr, [$startDate, $endDate]);
            }

            return $query;
        };

        // 1) Kelas berjalan + breakdown kategori
        $runningClassesCount = $applyClasDateFilter(
            Clas::query()->whereIn('status', ['approved', 'done'])
        )->count();

        $runningClassByCategory = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->whereIn('clas.status', ['approved', 'done'])
            ->selectRaw('kategoris.nama_kategori as category_name, COUNT(*) as total_classes')
            ->tap($applyClasDateFilter)
            ->groupBy('kategoris.id', 'kategoris.nama_kategori')
            ->orderByDesc('total_classes')
            ->get();

        // 2) Jumlah peserta tiap kelas (sumber saat ini: clas.amount)
        $totalParticipants = (int) $applyClasDateFilter(Clas::query())->sum('amount');
        $totalParticipantsRunning = (int) $applyClasDateFilter(
            Clas::query()->where('status', 'approved')
        )->sum('amount');

        $avgParticipantsPerClass = $runningClassesCount > 0
            ? round($totalParticipantsRunning / $runningClassesCount, 1)
            : 0;

        // 3) Lulus / tidak lulus
        // Sumber utama: input akademik di detail kelas (passed_students, failed_students).
        // Fallback: jika belum diisi, kelas done dengan file nilai approved dianggap lulus seluruh peserta.
        $passedParticipants = (int) Clas::query()
            ->where('status', 'done')
            ->selectRaw("SUM(COALESCE(passed_students, CASE WHEN grade_file_status = 'approved' THEN COALESCE(amount, 0) ELSE 0 END)) as total")
            ->tap($applyClasDateFilter)
            ->value('total');

        $failedParticipants = (int) Clas::query()
            ->where('status', 'done')
            ->selectRaw('SUM(COALESCE(failed_students, 0)) as total')
            ->tap($applyClasDateFilter)
            ->value('total');

        // 3b) Peserta per kategori
        // Samakan logika dengan dashboard admin:
        // - kelas approved yang overlap periode terpilih
        // - kelas done yang selesai pada periode terpilih
        $applyParticipantScope = function ($query) use ($period, $year, $startDate, $endDate, $clasDateExpr) {
            if ($period === 'all' || $period === 'all_time') {
                $query->where(function ($scopeQuery) use ($year, $clasDateExpr) {
                    $scopeQuery->where(function ($approvedQuery) use ($year, $clasDateExpr) {
                        $approvedQuery->where('clas.status', 'approved')
                            ->whereYear($clasDateExpr, $year);
                    })->orWhere(function ($doneQuery) use ($year) {
                        $doneQuery->where('clas.status', 'done')
                            ->whereYear(DB::raw('DATE(COALESCE(clas.done_at, clas.end_date, clas.start_date, clas.created_at))'), $year);
                    });
                });

                return $query;
            }

            $query->where(function ($scopeQuery) use ($startDate, $endDate) {
                $scopeQuery->where(function ($approvedQuery) use ($startDate, $endDate) {
                    $approvedQuery->where('clas.status', 'approved')
                        ->whereDate(DB::raw('COALESCE(clas.start_date, clas.created_at)'), '<=', $endDate)
                        ->where(function ($runningQuery) use ($startDate) {
                            $runningQuery->whereNull('clas.end_date')
                                ->orWhereDate('clas.end_date', '>=', $startDate);
                        });
                })->orWhere(function ($doneQuery) use ($startDate, $endDate) {
                    $doneQuery->where('clas.status', 'done')
                        ->whereBetween(
                            DB::raw('DATE(COALESCE(clas.done_at, clas.end_date, clas.start_date, clas.created_at))'),
                            [$startDate, $endDate]
                        );
                });
            });

            return $query;
        };

        $regularParticipants = (int) Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%regular%'])
            ->whereIn('clas.status', ['approved', 'done'])
            ->tap($applyClasDateFilter)
            ->sum('clas.amount');

        $corporateParticipants = (int) Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%corporate%'])
            ->whereIn('clas.status', ['approved', 'done'])
            ->tap($applyClasDateFilter)
            ->sum('clas.amount');

        // For Private training: 1 class = 1 student (count classes, not amount)
        // Use $applyClasDateFilter (same as running classes) to match "Kelas Private" card logic
        $privateParticipants = (int) Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%private%'])
            ->whereIn('clas.status', ['approved', 'done'])
            ->tap($applyClasDateFilter)
            ->count();

        // 3c) Lulus / Tidak Lulus per kategori
        $regularPassFail = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%regular%'])
            ->selectRaw("SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(clas.failed_students, 0)) as failed_total')
            ->tap($applyClasDateFilter)
            ->first();

        $corporatePassFail = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%corporate%'])
            ->selectRaw("SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(clas.failed_students, 0)) as failed_total')
            ->tap($applyClasDateFilter)
            ->first();

        $privatePassFail = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%private%'])
            ->selectRaw("SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->selectRaw('SUM(COALESCE(clas.failed_students, 0)) as failed_total')
            ->tap($applyClasDateFilter)
            ->first();

        $regularPassedParticipants = (int) ($regularPassFail->passed_total ?? 0);
        $regularFailedParticipants = (int) ($regularPassFail->failed_total ?? 0);
        $corporatePassedParticipants = (int) ($corporatePassFail->passed_total ?? 0);
        $corporateFailedParticipants = (int) ($corporatePassFail->failed_total ?? 0);
        $privatePassedParticipants = (int) ($privatePassFail->passed_total ?? 0);
        $privateFailedParticipants = (int) ($privatePassFail->failed_total ?? 0);

        // 4) Rekap absensi instruktur (% kehadiran + % keterlambatan)
        $trainerTargets = DB::table('clas_trainer as ct')
            ->join('users as u', 'u.id', '=', 'ct.user_id')
            ->join('clas as c', 'c.id', '=', 'ct.clas_id')
            ->whereIn('c.status', ['approved', 'done'])
            ->where(function ($query) use ($period, $year, $startDate, $endDate) {
                if ($period === 'all' || $period === 'all_time') {
                    $query->whereYear(DB::raw('COALESCE(c.start_date, c.created_at)'), $year);
                } else {
                    $query->whereBetween(DB::raw('COALESCE(c.start_date, c.created_at)'), [$startDate, $endDate]);
                }
            })
            ->selectRaw('u.id as trainer_id, u.name as trainer_name, SUM(COALESCE(c.meet, 0)) as target_sessions')
            ->groupBy('u.id', 'u.name')
            ->orderBy('u.name')
            ->get()
            ->keyBy('trainer_id');

        $trainerAttendanceStats = DB::table('clas_trainer as ct')
            ->join('clas as c', 'c.id', '=', 'ct.clas_id')
            ->leftJoin('trainer_attendances as ta', function ($join) {
                $join->on('ta.clas_id', '=', 'ct.clas_id')
                    ->on('ta.trainer_id', '=', 'ct.user_id');
            })
            ->whereIn('c.status', ['approved', 'done'])
            ->where(function ($query) use ($period, $year, $startDate, $endDate) {
                if ($period === 'all' || $period === 'all_time') {
                    $query->whereYear(DB::raw('COALESCE(c.start_date, c.created_at)'), $year);
                } else {
                    $query->whereBetween(DB::raw('COALESCE(c.start_date, c.created_at)'), [$startDate, $endDate]);
                }
            })
            ->selectRaw("ct.user_id as trainer_id, SUM(CASE WHEN ta.check_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended_sessions, SUM(CASE WHEN ta.check_in_at IS NOT NULL AND COALESCE(ta.shifted_start_time, ta.planned_start_time, c.start_time) IS NOT NULL AND TIME(ta.check_in_at) > ADDTIME(COALESCE(ta.shifted_start_time, ta.planned_start_time, c.start_time), '00:05:00') THEN 1 ELSE 0 END) as late_sessions, SUM(CASE WHEN ta.check_in_at IS NOT NULL AND COALESCE(ta.shifted_start_time, ta.planned_start_time, c.start_time) IS NOT NULL AND TIME(ta.check_in_at) > ADDTIME(COALESCE(ta.shifted_start_time, ta.planned_start_time, c.start_time), '00:05:00') THEN TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.attendance_date, ADDTIME(COALESCE(ta.shifted_start_time, ta.planned_start_time, c.start_time), '00:05:00')), ta.check_in_at) ELSE 0 END) as late_minutes_total")
            ->groupBy('ct.user_id')
            ->get()
            ->keyBy('trainer_id');

        $instructorAttendanceRecap = $trainerTargets->map(function ($targetRow, $trainerId) use ($trainerAttendanceStats) {
            $stats = $trainerAttendanceStats->get($trainerId);

            $target = (int) ($targetRow->target_sessions ?? 0);
            $attended = (int) ($stats->attended_sessions ?? 0);
            $late = (int) ($stats->late_sessions ?? 0);
            $lateMinutesTotal = (int) ($stats->late_minutes_total ?? 0);
            $percentage = $target > 0 ? round(($attended / $target) * 100, 1) : 0;
            $latePercentage = $attended > 0 ? round(($late / $attended) * 100, 1) : 0;
            $avgLateMinutes = $late > 0 ? round($lateMinutesTotal / $late, 1) : 0;

            return [
                'trainer_name' => $targetRow->trainer_name,
                'target_sessions' => $target,
                'attended_sessions' => $attended,
                'attendance_percentage' => min($percentage, 100),
                'late_sessions' => $late,
                'late_percentage' => min($latePercentage, 100),
                'avg_late_minutes' => $avgLateMinutes,
            ];
        })->values();

        $avgInstructorAttendance = $instructorAttendanceRecap->count() > 0
            ? round($instructorAttendanceRecap->avg('attendance_percentage'), 1)
            : 0;

        // Grafik: jumlah kelas per kategori per bulan (Jan-Des, tahun berjalan)
        $monthLabels = collect(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']);

        $monthlyClassByCategory = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->whereIn('clas.status', ['approved', 'done'])
            ->whereYear(DB::raw('COALESCE(clas.start_date, clas.created_at)'), $selectedYear)
            ->selectRaw('MONTH(COALESCE(clas.start_date, clas.created_at)) as month_num, LOWER(kategoris.nama_kategori) as category_name, COUNT(*) as total')
            ->groupBy('month_num', 'category_name')
            ->get();

        $regularMonthlyClasses = array_fill(0, 12, 0);
        $corporateMonthlyClasses = array_fill(0, 12, 0);
        $privateMonthlyClasses = array_fill(0, 12, 0);

        foreach ($monthlyClassByCategory as $row) {
            $monthIndex = max(((int) $row->month_num) - 1, 0);
            if ($monthIndex > 11) {
                continue;
            }

            $categoryName = (string) $row->category_name;
            $total = (int) $row->total;

            if (str_contains($categoryName, 'regular')) {
                $regularMonthlyClasses[$monthIndex] += $total;
            } elseif (str_contains($categoryName, 'corporate')) {
                $corporateMonthlyClasses[$monthIndex] += $total;
            } elseif (str_contains($categoryName, 'private')) {
                $privateMonthlyClasses[$monthIndex] += $total;
            }
        }

        // Grafik: jumlah siswa per pelatihan pada bulan berjalan (semua pelatihan ditampilkan)
        $allTrainings = Training::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $chartMonth = (int) ($startDate->month);
        $chartYear = (int) $year;

        if (preg_match('/^month_(\d{2})$/', $period, $m)) {
            $chartMonth = (int) $m[1];
        }

        $studentsByTrainingCurrentMonthRaw = Clas::query()
            ->join('trainings', 'trainings.id', '=', 'clas.training_id')
            ->whereIn('clas.status', ['approved', 'done'])
            ->whereMonth(DB::raw('COALESCE(clas.start_date, clas.created_at)'), $chartMonth)
            ->whereYear(DB::raw('COALESCE(clas.start_date, clas.created_at)'), $chartYear)
            ->selectRaw("trainings.id as training_id, SUM(COALESCE(clas.amount, 0)) as total")
            ->groupBy('training_id')
            ->get();

        $trainingCurrentMonthMap = [];
        foreach ($allTrainings as $training) {
            $trainingCurrentMonthMap[(int) $training->id] = 0;
        }

        foreach ($studentsByTrainingCurrentMonthRaw as $row) {
            $trainingId = (int) $row->training_id;

            if (!array_key_exists($trainingId, $trainingCurrentMonthMap)) {
                continue;
            }

            $trainingCurrentMonthMap[$trainingId] = (int) $row->total;
        }

        // Chart: Peserta Lulus per Kategori (bukan per program)
        $programPassLabels = collect(['Regular', 'Corporate', 'Private']);
        $programPassTotals = collect([
            $regularPassedParticipants,
            $corporatePassedParticipants,
            $privatePassedParticipants
        ]);
        $currentMonthLabel = Carbon::create($chartYear, $chartMonth, 1)->translatedFormat('F Y');

        // NEW: Chart per training di setiap kategori (3 mini charts)
        $regularTrainingsPassData = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->join('trainings', 'trainings.id', '=', 'clas.training_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%regular%'])
            ->selectRaw("trainings.name as training_name, SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->tap($applyClasDateFilter)
            ->groupBy('trainings.id', 'trainings.name')
            ->orderBy('trainings.name')
            ->get();

        $corporateTrainingsPassData = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->join('trainings', 'trainings.id', '=', 'clas.training_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%corporate%'])
            ->selectRaw("trainings.name as training_name, SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->tap($applyClasDateFilter)
            ->groupBy('trainings.id', 'trainings.name')
            ->orderBy('trainings.name')
            ->get();

        $privateTrainingsPassData = Clas::query()
            ->join('kategoris', 'kategoris.id', '=', 'clas.kategori_id')
            ->join('trainings', 'trainings.id', '=', 'clas.training_id')
            ->where('clas.status', 'done')
            ->whereRaw('LOWER(kategoris.nama_kategori) LIKE ?', ['%private%'])
            ->selectRaw("trainings.name as training_name, SUM(COALESCE(clas.passed_students, CASE WHEN clas.grade_file_status = 'approved' THEN COALESCE(clas.amount, 0) ELSE 0 END)) as passed_total")
            ->tap($applyClasDateFilter)
            ->groupBy('trainings.id', 'trainings.name')
            ->orderBy('trainings.name')
            ->get();

        // Tabel: peserta lulus / tidak lulus per kelas
        $graduationClassesQuery = Clas::query()
            ->where('status', 'done')
            ->tap($applyClasDateFilter)
            ->where(function ($query) {
                $query->whereNotNull('passed_students')
                    ->orWhereNotNull('failed_students')
                    ->orWhere('grade_file_status', 'approved');
            });

        $graduationTable = (clone $graduationClassesQuery)
            ->with(['kategori:id,nama_kategori', 'training:id,name'])
            ->orderByDesc('done_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($class) {
                $participants = (int) ($class->amount ?? 0);
                $passed = $class->passed_students !== null
                    ? (int) $class->passed_students
                    : ($class->grade_file_status === 'approved' ? $participants : 0);

                $failed = $class->failed_students !== null
                    ? (int) $class->failed_students
                    : max($participants - $passed, 0);

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'category_name' => $class->kategori?->nama_kategori ?? '-',
                    'program_name' => $class->training?->name ?? '-',
                    'participants' => $participants,
                    'passed' => $passed,
                    'failed' => $failed,
                ];
            });

        // Rekap Kelas by Status
        $completedClassesList = Clas::query()
            ->where('status', 'done')
            ->tap($applyClasDateFilter)
            ->with(['trainers:id,name', 'kategori:id,nama_kategori'])
            ->orderByDesc('done_at')
            ->orderByDesc('id')
            ->get();

        $activeClassesList = Clas::query()
            ->where('status', 'approved')
            ->tap($applyClasDateFilter)
            ->with(['trainers:id,name', 'kategori:id,nama_kategori'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $pendingClassesList = Clas::query()
            ->where('status', 'pending')
            ->tap($applyClasDateFilter)
            ->with(['trainers:id,name', 'kategori:id,nama_kategori'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('akademik.dashboard', compact(
            'period',
            'year',
            'years',
            'selectedYear',
            'runningClassesCount',
            'runningClassByCategory',
            'totalParticipants',
            'totalParticipantsRunning',
            'avgParticipantsPerClass',
            'passedParticipants',
            'failedParticipants',
            'regularParticipants',
            'corporateParticipants',
            'privateParticipants',
            'regularPassedParticipants',
            'regularFailedParticipants',
            'corporatePassedParticipants',
            'corporateFailedParticipants',
            'privatePassedParticipants',
            'privateFailedParticipants',
            'avgInstructorAttendance',
            'monthLabels',
            'regularMonthlyClasses',
            'corporateMonthlyClasses',
            'privateMonthlyClasses',
            'programPassLabels',
            'programPassTotals',
            'currentMonthLabel',
            'graduationTable',
            'instructorAttendanceRecap',
            'completedClassesList',
            'activeClassesList',
            'pendingClassesList',
            'regularTrainingsPassData',
            'corporateTrainingsPassData',
            'privateTrainingsPassData'
        ));
    }
}
