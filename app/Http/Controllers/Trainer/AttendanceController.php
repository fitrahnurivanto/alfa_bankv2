<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\TrainerAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $trainer = $this->trainer();
    $today = now()->toDateString();
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));

    // Tampilkan semua kelas trainer yang statusnya masih approved.
    // Selama belum done, kelas tetap bisa diabsen di hari berikutnya.
        $classes = $trainer->classes()
            ->where('status', 'approved')
            ->orderBy('start_date')
            ->get(['clas.id', 'clas.name', 'clas.instansi', 'clas.start_date', 'clas.end_date']);

        $selectedClassId = (int) ($request->get('class_id') ?: ($classes->first()->id ?? 0));

        $selectedClass = null;
        $todayAttendance = null;
    $currentAttendance = null;
    $todayAttendances = collect();
        $recentAttendances = collect();

        $availableYears = TrainerAttendance::query()
            ->where('trainer_id', $trainer->id)
            ->selectRaw('YEAR(attendance_date) as year')
            ->whereNotNull('attendance_date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) date('Y')]);
        } elseif (!$availableYears->contains($year)) {
            $availableYears = $availableYears->push($year)->unique()->sortDesc()->values();
        }

        if ($selectedClassId > 0) {
            $selectedClass = $classes->firstWhere('id', $selectedClassId);

            if ($selectedClass) {
                $todayAttendances = TrainerAttendance::query()
                    ->where('clas_id', $selectedClassId)
                    ->where('trainer_id', $trainer->id)
                    ->whereDate('attendance_date', now()->toDateString())
                    ->orderBy('session_number')
                    ->get();

                $todayAttendance = $todayAttendances->last();
                $currentAttendance = $todayAttendances->first(function ($attendance) {
                    return !$attendance->check_out_at;
                });

                $recentAttendances = TrainerAttendance::query()
                    ->where('clas_id', $selectedClassId)
                    ->where('trainer_id', $trainer->id)
                    ->when($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m), function ($q) use ($year, $m) {
                        $month = (int) $m[1];
                        $start = now()->setYear($year)->setMonth($month)->startOfMonth();
                        $end = now()->setYear($year)->setMonth($month)->endOfMonth();
                        $q->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()]);
                    })
                    ->when($period === 'all', function ($q) use ($year) {
                        $q->whereYear('attendance_date', $year);
                    })
                    ->orderByDesc('attendance_date')
                    ->orderByDesc('session_number')
                    ->orderByDesc('check_in_at')
                    ->limit(10)
                    ->get();
            }
        }

        return view('trainer.attendance.index', compact(
            'classes',
            'selectedClass',
            'todayAttendance',
            'currentAttendance',
            'todayAttendances',
            'recentAttendances',
            'selectedClassId',
            'period',
            'year',
            'availableYears'
        ));
    }

    public function checkIn(Request $request)
    {
        $trainer = $this->trainer();

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:clas,id'],
            'check_in_latitude' => ['required', 'numeric', 'between:-90,90'],
            'check_in_longitude' => ['required', 'numeric', 'between:-180,180'],
            'check_in_accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $class = Clas::query()
            ->where('id', $validated['class_id'])
            ->where('status', 'approved')
            ->whereHas('trainers', function ($q) use ($trainer) {
                $q->where('users.id', $trainer->id);
            })
            ->first();

        if (!$class) {
            return back()->with('error', 'Kelas tidak valid atau bukan kelas berjalan yang Anda ajar.');
        }

        $today = now()->toDateString();

        $attendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->where('trainer_id', $trainer->id)
            ->whereDate('attendance_date', $today)
            ->whereNull('check_out_at')
            ->orderByDesc('session_number')
            ->first();

        if ($attendance && $attendance->check_in_at && !$attendance->check_out_at) {
            return back()->with('error', 'Anda sudah melakukan absen berangkat untuk kelas ini hari ini.');
        }

        if (!$attendance) {
            $attendance = new TrainerAttendance([
                'clas_id' => $class->id,
                'trainer_id' => $trainer->id,
                'attendance_date' => $today,
                'session_number' => $this->getNextSessionNumber($class->id, $trainer->id, $today),
                'planned_start_time' => $class->start_time,
            ]);
        }

        if (!$attendance->planned_start_time) {
            $attendance->planned_start_time = $class->start_time;
        }

        $attendance->check_in_at = now();
        $attendance->check_in_latitude = $validated['check_in_latitude'];
        $attendance->check_in_longitude = $validated['check_in_longitude'];
        $attendance->check_in_accuracy = $validated['check_in_accuracy'];
        $attendance->save();

        return back()->with('success', 'Absen berangkat berhasil dicatat secara real-time.');
    }

    public function checkOut(Request $request)
    {
        $trainer = $this->trainer();

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:clas,id'],
            'material_covered' => ['required', 'string', 'min:3'],
            'students_present' => ['required', 'integer', 'min:0'],
            'check_out_latitude' => ['required', 'numeric', 'between:-90,90'],
            'check_out_longitude' => ['required', 'numeric', 'between:-180,180'],
            'check_out_accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $class = Clas::query()
            ->where('id', $validated['class_id'])
            ->where('status', 'approved')
            ->whereHas('trainers', function ($q) use ($trainer) {
                $q->where('users.id', $trainer->id);
            })
            ->first();

        if (!$class) {
            return back()->with('error', 'Kelas tidak valid atau bukan kelas berjalan yang Anda ajar.');
        }

        $attendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->where('trainer_id', $trainer->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderByDesc('session_number')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Anda harus melakukan absen berangkat terlebih dahulu.');
        }

        if ($attendance->check_out_at) {
            return back()->with('error', 'Anda sudah melakukan absen pulang untuk kelas ini hari ini.');
        }

        $attendance->check_out_at = now();
        $attendance->check_out_latitude = $validated['check_out_latitude'];
        $attendance->check_out_longitude = $validated['check_out_longitude'];
        $attendance->check_out_accuracy = $validated['check_out_accuracy'];
        $attendance->material_covered = $validated['material_covered'];
        $attendance->students_present = $validated['students_present'];
        $attendance->save();

        return back()->with('success', 'Absen pulang berhasil dicatat. Materi dan jumlah siswa hadir sudah tersimpan.');
    }

    public function addSession(Request $request)
    {
        $trainer = $this->trainer();

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:clas,id'],
        ]);

        $class = Clas::query()
            ->where('id', $validated['class_id'])
            ->where('status', 'approved')
            ->whereHas('trainers', function ($q) use ($trainer) {
                $q->where('users.id', $trainer->id);
            })
            ->first();

        if (!$class) {
            return back()->with('error', 'Kelas tidak valid atau bukan kelas berjalan yang Anda ajar.');
        }

        $today = now()->toDateString();

        $openAttendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->where('trainer_id', $trainer->id)
            ->whereDate('attendance_date', $today)
            ->whereNull('check_out_at')
            ->first();

        if ($openAttendance && !$openAttendance->check_in_at) {
            return back()->with('error', 'Sesi tambahan sudah dibuka. Silakan lakukan absen untuk sesi tersebut.');
        }

        if ($openAttendance && $openAttendance->check_in_at && !$openAttendance->check_out_at) {
            return back()->with('error', 'Sesi sebelumnya belum ditutup. Silakan absen pulang terlebih dahulu.');
        }

        $latestAttendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->where('trainer_id', $trainer->id)
            ->whereDate('attendance_date', $today)
            ->orderByDesc('session_number')
            ->first();

        if (!$latestAttendance) {
            return back()->with('error', 'Belum ada sesi absensi hari ini untuk kelas ini.');
        }

        TrainerAttendance::create([
            'clas_id' => $class->id,
            'trainer_id' => $trainer->id,
            'attendance_date' => $today,
            'session_number' => ((int) $latestAttendance->session_number) + 1,
            'planned_start_time' => $class->start_time,
        ]);

        return back()->with('success', 'Sesi tambahan berhasil dibuka. Silakan lanjut absen pada sesi berikutnya.');
    }

    private function getNextSessionNumber(int $classId, int $trainerId, string $attendanceDate): int
    {
        $latestSessionNumber = TrainerAttendance::query()
            ->where('clas_id', $classId)
            ->where('trainer_id', $trainerId)
            ->whereDate('attendance_date', $attendanceDate)
            ->max('session_number');

        return ((int) $latestSessionNumber) + 1;
    }

    private function trainer(): User
    {
        $trainer = Auth::user();

        if (!$trainer instanceof User) {
            abort(403, 'Akses absensi pengajar ditolak.');
        }

        return $trainer;
    }
}
