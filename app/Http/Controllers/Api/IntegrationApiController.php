<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRegistrant;
use App\Models\ClassSession;
use App\Models\Clas;
use App\Models\InboundPayment;
use App\Models\SsoToken;
use App\Models\TrainerAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IntegrationApiController extends Controller
{
    public function sessions(Clas $class): JsonResponse
    {
        $class->load(['sessions', 'trainerAttendances.trainer']);

        return response()->json(['data' => [
            'class_id' => $class->id,
            'meeting_target' => (int) $class->meet,
            'start_date' => $class->start_date ? date('Y-m-d', strtotime((string) $class->start_date)) : null,
            'end_date' => $class->end_date ? date('Y-m-d', strtotime((string) $class->end_date)) : null,
            'sessions' => $class->sessions->map(fn ($session) => [
                'id' => $session->id,
                'session_number' => $session->session_number,
                'session_date' => $session->session_date?->toDateString(),
                'status' => $session->status,
                'is_dummy' => false,
                'attendance_open' => $class->trainerAttendances
                    ->where('session_number', $session->session_number)
                    ->contains(fn ($attendance) => $attendance->check_in_at !== null
                        && (!$session->session_date
                            || $attendance->attendance_date?->toDateString() === $session->session_date->toDateString())),
                'title' => $session->title,
                'start_time' => $session->start_time?->format('H:i:s'),
                'end_time' => $session->end_time?->format('H:i:s'),
            ])->values(),
        ]])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function trainerAttendance(Request $request, Clas $class): JsonResponse
    {
        $attendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->when($request->filled('session_number'), fn ($q) => $q->where('session_number', $request->integer('session_number')))
            ->orderByDesc('attendance_date')->orderByDesc('session_number')
            ->with('trainer:id,name')
            ->get();

        return response()->json(['data' => $attendance->map(fn ($item) => [
            'class_id' => $class->id,
            'session_number' => $item->session_number,
            'attendance_date' => $item->attendance_date?->toDateString(),
            'trainer_id' => $item->trainer_id,
            'trainer_name' => $item->trainer?->name,
            'checked_in' => $item->check_in_at !== null,
            'check_in_at' => $item->check_in_at?->toIso8601String(),
            'check_out_at' => $item->check_out_at?->toIso8601String(),
        ])->values()])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function studentAttendanceCount(Request $request, Clas $class, ClassSession $session): JsonResponse
    {
        $validated = $request->validate([
            'present_count' => ['required', 'integer', 'min:0'],
        ]);

        if ((int) $session->class_id !== (int) $class->id) {
            return response()->json(['message' => 'Sesi tidak sesuai dengan kelas.'], 422);
        }

        $attendance = TrainerAttendance::query()
            ->where('clas_id', $class->id)
            ->where('session_number', $session->session_number)
            ->whereDate('attendance_date', $session->session_date ?: now()->toDateString())
            ->whereNotNull('check_in_at')
            ->orderByDesc('id')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Absensi trainer untuk sesi ini belum dibuka.'], 409);
        }

        $attendance->update(['students_present' => $validated['present_count']]);

        return response()->json([
            'message' => 'Jumlah siswa hadir berhasil disinkronkan.',
            'data' => [
                'class_id' => $class->id,
                'session_number' => $session->session_number,
                'students_present' => (int) $attendance->students_present,
            ],
        ]);
    }

    public function gradeFile(Request $request, Clas $class): JsonResponse
    {
        $registrant = $this->confirmedRegistrant($request, $class);
        if (!$registrant) {
            return response()->json(['message' => 'Peserta belum dikonfirmasi pada kelas ini.'], 403);
        }

        $file = $class->gradeFiles()->where('status', 'approved')->where('is_active', true)->first();
        return response()->json(['data' => [
            'class_id' => $class->id,
            'participant' => $this->participantData($registrant),
            'file' => $file ? [
                'id' => $file->id,
                'version' => $file->version,
                'name' => $file->file_name,
                'mime' => $file->file_mime,
                'download_url' => $file->file_url,
                'approved_at' => $file->reviewed_at?->toIso8601String(),
            ] : null,
            'summary' => [
                'passed_students' => (int) ($class->passed_students ?? 0),
                'failed_students' => (int) ($class->failed_students ?? 0),
            ],
        ]]);
    }

    public function payment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:clas,id'],
            'student_id' => ['nullable', 'integer'],
            'registration_id' => ['required', 'integer'],
            'nis' => ['nullable', 'string', 'max:50'],
            'external_transaction_id' => ['required', 'string', 'max:150'],
            'payment_type' => ['required', 'in:registration,installment,remedial'],
            'amount' => ['required_unless:status,reversed', 'nullable', 'numeric', 'min:0.01'],
            'status' => ['sometimes', 'in:verified,reversed'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'proof_url' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['status'] = $validated['status'] ?? 'verified';

        $result = DB::transaction(function () use ($validated) {
            $class = Clas::query()->with('kategori')->lockForUpdate()->findOrFail($validated['class_id']);
            $payment = InboundPayment::query()
                ->where('external_transaction_id', $validated['external_transaction_id'])
                ->lockForUpdate()
                ->first();
            $isNewPayment = $payment === null;
            $previousContribution = $payment && $payment->status === 'verified'
                ? (float) $payment->amount
                : 0;

            if ($payment && (
                (int) $payment->class_id !== (int) $validated['class_id']
                || (int) $payment->registration_id !== (int) $validated['registration_id']
                || $payment->payment_type !== $validated['payment_type']
                || (isset($validated['student_id']) && (int) $payment->student_id !== (int) $validated['student_id'])
            )) {
                throw ValidationException::withMessages([
                    'external_transaction_id' => 'ID transaksi sudah digunakan untuk pembayaran lain.',
                ]);
            }

            if ($validated['status'] === 'reversed') {
                if (!$payment) {
                    throw ValidationException::withMessages([
                        'external_transaction_id' => 'Pembayaran yang akan dibatalkan tidak ditemukan.',
                    ]);
                }
                if (isset($validated['amount']) && round((float) $validated['amount'], 2) !== round((float) $payment->amount, 2)) {
                    throw ValidationException::withMessages([
                        'amount' => 'Nominal pembatalan harus sama dengan nominal transaksi sebelumnya.',
                    ]);
                }

                $payment->status = 'reversed';
                $payment->save();
            } else {
                $registrant = ClassRegistrant::query()
                    ->where('class_id', $validated['class_id'])
                    ->where(function ($query) use ($validated) {
                        $query->where('registration_id', $validated['registration_id'])
                            ->orWhere('external_registration_id', 'CI4-BOOKING-' . $validated['registration_id']);
                    })
                    ->when(isset($validated['student_id']), fn ($query) => $query->where(function ($query) use ($validated) {
                        $query->where('student_id', $validated['student_id'])
                            ->orWhereNull('student_id');
                    }))
                    ->lockForUpdate()
                    ->first();

                $isCi4PrivateConfirmation = $registrant
                    && $class->kategori?->slug === 'private'
                    && $validated['payment_type'] === 'registration'
                    && $registrant->status === 'pending'
                    && $registrant->external_registration_id === 'CI4-BOOKING-' . $validated['registration_id'];

                if (!$registrant || ($registrant->status !== 'confirmed' && !$isCi4PrivateConfirmation)) {
                    throw ValidationException::withMessages([
                        'registration_id' => 'Pendaftaran peserta tidak ditemukan atau belum dikonfirmasi.',
                    ]);
                }

                if ($isCi4PrivateConfirmation) {
                    $registrant->update([
                        'student_id' => $validated['student_id'] ?? null,
                        'registration_id' => $validated['registration_id'],
                        'nis' => $validated['nis'] ?? null,
                        'status' => 'confirmed',
                    ]);
                }

                $paymentData = $validated;
                $paymentData['status'] = 'verified';

                if ($payment) {
                    $payment->fill($paymentData)->save();
                } else {
                    $payment = InboundPayment::create($paymentData);
                }
            }

            $isCi4ManagedClass = $class->registrants()
                ->whereNotNull('external_registration_id')
                ->exists();

            if ($isCi4ManagedClass) {
                $verifiedTotal = InboundPayment::query()
                    ->where('class_id', $class->id)
                    ->where('status', 'verified')
                    ->sum('amount');

                $class->update([
                    'paid_amount' => $verifiedTotal,
                    'income' => $verifiedTotal,
                ]);
            } else {
                $newContribution = $payment->status === 'verified' ? (float) $payment->amount : 0;
                $difference = $newContribution - $previousContribution;

                $class->update([
                    'paid_amount' => max(0, (float) $class->paid_amount + $difference),
                    'income' => max(0, (float) $class->income + $difference),
                ]);
            }

            return [
                'payment' => $payment,
                'is_new' => $isNewPayment,
            ];
        });

        $payment = $result['payment'];
        $total = InboundPayment::query()
            ->where('class_id', $payment->class_id)
            ->where('status', 'verified')
            ->sum('amount');

        return response()->json(['message' => 'Pembayaran berhasil diterima.', 'data' => [
            'id' => $payment->id,
            'external_transaction_id' => $payment->external_transaction_id,
            'class_id' => $payment->class_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'class_verified_paid_total' => $total,
        ]], $result['is_new'] ? 201 : 200);
    }

    public function consumeSso(Request $request): JsonResponse
    {
        $validated = $request->validate(['token' => ['required', 'string', 'size:64']]);
        $sso = SsoToken::with('user')->where('token_hash', hash('sha256', $validated['token']))->first();

        if (!$sso || $sso->target !== 'sim' || $sso->used_at || $sso->expires_at->isPast()) {
            return response()->json(['message' => 'Token SSO tidak valid atau sudah kedaluwarsa.'], 401);
        }

        $sso->update(['used_at' => now()]);
        return response()->json(['data' => [
            'user_id' => $sso->user->id,
            'email' => $sso->user->email,
            'name' => $sso->user->name,
            'role' => $sso->user->role,
        ]]);
    }

    private function confirmedRegistrant(Request $request, Clas $class): ?ClassRegistrant
    {
        return $class->registrants()->where('status', 'confirmed')
            ->when($request->filled('registration_id'), fn ($q) => $q->where('registration_id', $request->integer('registration_id')))
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('nis'), fn ($q) => $q->where('nis', $request->string('nis')))
            ->first();
    }

    private function participantData(ClassRegistrant $registrant): array
    {
        return [
            'student_id' => $registrant->student_id,
            'registration_id' => $registrant->registration_id,
            'nis' => $registrant->nis,
            'name' => $registrant->full_name,
        ];
    }
}