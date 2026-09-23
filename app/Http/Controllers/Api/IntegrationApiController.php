<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRegistrant;
use App\Models\Clas;
use App\Models\InboundPayment;
use App\Models\SsoToken;
use App\Models\TrainerAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'title' => $session->title,
                'start_time' => $session->start_time?->format('H:i:s'),
                'end_time' => $session->end_time?->format('H:i:s'),
            ])->values(),
        ]]);
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
        ])->values()]);
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'proof_url' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $registrant = ClassRegistrant::query()
            ->where('class_id', $validated['class_id'])
            ->where('registration_id', $validated['registration_id'])
            ->where('status', 'confirmed')
            ->when(isset($validated['student_id']), fn ($q) => $q->where('student_id', $validated['student_id']))
            ->first();

        if (!$registrant) {
            return response()->json(['message' => 'Pendaftaran peserta tidak ditemukan atau belum dikonfirmasi.'], 422);
        }

        $payment = DB::transaction(function () use ($validated) {
            $payment = InboundPayment::firstOrCreate(
                ['external_transaction_id' => $validated['external_transaction_id']],
                array_merge($validated, ['status' => 'verified'])
            );

            if ($payment->wasRecentlyCreated) {
                $class = Clas::query()->lockForUpdate()->findOrFail($validated['class_id']);
                $class->increment('paid_amount', (float) $payment->amount);
                $class->increment('income', (float) $payment->amount);
            }

            return $payment;
        });

        $total = InboundPayment::where('class_id', $payment->class_id)->sum('amount');
        return response()->json(['message' => 'Pembayaran berhasil diterima.', 'data' => [
            'id' => $payment->id,
            'external_transaction_id' => $payment->external_transaction_id,
            'class_id' => $payment->class_id,
            'amount' => $payment->amount,
            'class_verified_paid_total' => $total,
        ]], 201);
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