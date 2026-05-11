<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\TrainerAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
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

        $dateExpr = DB::raw('COALESCE(clas.start_date, clas.created_at)');

        $query = Clas::query()
            ->with(['kategori:id,nama_kategori', 'training:id,name'])
            ->whereIn('status', ['approved', 'done'])
            ->when($period === 'all' || $period === 'all_time', function ($q) use ($dateExpr, $year) {
                $q->whereYear($dateExpr, $year);
            }, function ($q) use ($dateExpr, $startDate, $endDate) {
                $q->whereBetween($dateExpr, [$startDate, $endDate]);
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('instansi', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        $classes = $query->paginate(12)->appends($request->query());

        return view('akademik.classes.index', compact(
            'classes',
            'period',
            'year',
            'years',
            'status',
            'search'
        ));
    }

    public function show(Request $request, Clas $class)
    {
        if (!in_array($class->status, ['approved', 'done'], true)) {
            abort(404);
        }

        $class->load([
            'kategori:id,nama_kategori',
            'training:id,name',
            'trainers:id,name,email',
            'passFailUpdatedBy:id,name',
        ]);

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

        $selectedTrainerId = (int) $request->get('trainer_id', 0);
        $trainerIds = $class->trainers->pluck('id')->map(fn ($id) => (int) $id);

        if ($selectedTrainerId > 0 && !$trainerIds->contains($selectedTrainerId)) {
            $selectedTrainerId = 0;
        }

        $trainerAttendanceRecap = DB::table('clas_trainer as ct')
            ->join('users as u', 'u.id', '=', 'ct.user_id')
            ->join('clas as c', 'c.id', '=', 'ct.clas_id')
            ->leftJoin('trainer_attendances as ta', function ($join) use ($period, $year, $startDate, $endDate) {
                $join->on('ta.clas_id', '=', 'ct.clas_id')
                    ->on('ta.trainer_id', '=', 'ct.user_id');

                if ($period === 'all' || $period === 'all_time') {
                    $join->whereRaw('YEAR(ta.attendance_date) = ?', [$year]);
                } else {
                    $join->whereBetween('ta.attendance_date', [$startDate->toDateString(), $endDate->toDateString()]);
                }
            })
            ->where('ct.clas_id', $class->id)
            ->when($selectedTrainerId > 0, function ($query) use ($selectedTrainerId) {
                $query->where('ct.user_id', $selectedTrainerId);
            })
            ->selectRaw("u.name as trainer_name, MAX(COALESCE(c.meet, 0)) as target_sessions, COUNT(ta.id) as attended_sessions, SUM(CASE WHEN ta.check_in_at IS NOT NULL AND c.start_time IS NOT NULL AND TIME(ta.check_in_at) > ADDTIME(c.start_time, '00:05:00') THEN 1 ELSE 0 END) as late_sessions, SUM(CASE WHEN ta.check_in_at IS NOT NULL AND c.start_time IS NOT NULL AND TIME(ta.check_in_at) > ADDTIME(c.start_time, '00:05:00') THEN TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.attendance_date, ADDTIME(c.start_time, '00:05:00')), ta.check_in_at) ELSE 0 END) as late_minutes_total")
            ->groupBy('u.id', 'u.name')
            ->orderBy('u.name')
            ->get()
            ->map(function ($row) {
                $target = (int) ($row->target_sessions ?? 0);
                $attended = (int) ($row->attended_sessions ?? 0);
                $late = (int) ($row->late_sessions ?? 0);
                $lateMinutesTotal = (int) ($row->late_minutes_total ?? 0);
                $lateAverageMinutes = $late > 0 ? round($lateMinutesTotal / $late, 1) : 0;

                return [
                    'trainer_name' => $row->trainer_name,
                    'target_sessions' => $target,
                    'attended_sessions' => $attended,
                    'late_sessions' => $late,
                    'attendance_percentage' => $target > 0 ? round(($attended / $target) * 100, 1) : 0,
                    'late_percentage' => $attended > 0 ? round(($late / $attended) * 100, 1) : 0,
                    'avg_late_minutes' => $lateAverageMinutes,
                ];
            });

        $startTimeValue = $class->start_time ? Carbon::parse($class->start_time)->format('H:i:s') : null;

        $attendanceLogs = TrainerAttendance::query()
            ->with(['trainer:id,name'])
            ->where('clas_id', $class->id)
            ->when($selectedTrainerId > 0, function ($query) use ($selectedTrainerId) {
                $query->where('trainer_id', $selectedTrainerId);
            })
            ->when($period === 'all' || $period === 'all_time', function ($query) use ($year) {
                $query->whereYear('attendance_date', $year);
            }, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->orderByDesc('attendance_date')
            ->limit(30)
            ->get()
            ->map(function ($attendance) use ($startTimeValue) {
                $isLate = false;
                $lateMinutes = 0;

                if ($attendance->check_in_at && $attendance->attendance_date && $startTimeValue) {
                    $scheduled = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $startTimeValue);
                    $diffFromSchedule = $scheduled->diffInMinutes($attendance->check_in_at, false);

                    if ($diffFromSchedule > 5) {
                        $isLate = true;
                        $lateMinutes = $diffFromSchedule - 5;
                    }
                }

                return [
                    'attendance_date' => $attendance->attendance_date,
                    'trainer_name' => $attendance->trainer->name ?? '-',
                    'check_in_at' => $attendance->check_in_at,
                    'check_out_at' => $attendance->check_out_at,
                    'students_present' => $attendance->students_present,
                    'material_covered' => $attendance->material_covered,
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes,
                ];
            });

        return view('akademik.classes.show', compact(
            'class',
            'trainerAttendanceRecap',
            'attendanceLogs',
            'selectedTrainerId',
            'period',
            'year',
            'years'
        ));
    }

    public function updateGraduationSummary(Request $request, Clas $class)
    {
        if (!in_array($class->status, ['approved', 'done'], true)) {
            return back()->with('error', 'Rekap kelulusan hanya dapat diisi saat kelas berjalan atau selesai.');
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

        $class->update([
            'amount' => $totalStudents,
            'passed_students' => $passedStudents,
            'failed_students' => $failedStudents,
            'pass_fail_updated_by' => \Illuminate\Support\Facades\Auth::id(),
            'pass_fail_updated_at' => now(),
        ]);

        return back()->with('success', 'Rekap siswa lulus/tidak lulus berhasil disimpan oleh akademik.');
    }
}
