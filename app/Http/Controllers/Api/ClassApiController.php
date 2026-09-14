<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRegistrant;
use App\Models\Clas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassApiController extends Controller
{
    private const REGISTRATION_CATEGORY_SLUGS = [
        'regular-training',
        'private-training',
    ];

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $classes = Clas::query()
            ->with([
                'kategori:id,nama_kategori',
                'training:id,type,name',
                'trainers:id,name,email,phone,address',
            ])
            ->where('status', 'approved')
            ->whereHas('kategori', function ($query) {
                $query->whereIn('slug', self::REGISTRATION_CATEGORY_SLUGS);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('start_date')
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $classes->getCollection()->map(fn (Clas $class) => $this->classData($class))->values(),
            'meta' => [
                'current_page' => $classes->currentPage(),
                'last_page' => $classes->lastPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
            ],
        ]);
    }

    public function show(Clas $class): JsonResponse
    {
        if ($class->status !== 'approved' || !in_array(
            $class->kategori?->slug,
            self::REGISTRATION_CATEGORY_SLUGS,
            true
        )) {
            return response()->json([
                'message' => 'Kelas tidak tersedia untuk pendaftaran.',
            ], 404);
        }

        $class->load([
            'kategori:id,nama_kategori',
            'training:id,type,name',
            'trainers:id,name,email,phone,address',
        ]);

        return response()->json([
            'data' => $this->classData($class),
        ]);
    }

    public function storeRegistrant(Request $request, Clas $class): JsonResponse
    {
        if ($class->status !== 'approved' || !in_array(
            $class->kategori?->slug,
            self::REGISTRATION_CATEGORY_SLUGS,
            true
        )) {
            return response()->json([
                'message' => 'Kelas tidak tersedia untuk pendaftaran.',
            ], 404);
        }

        $validated = $request->validate([
            'external_registration_id' => 'required|string|max:100',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|in:registered,paid,confirmed,cancelled,expired',
            'registered_at' => 'nullable|date',
        ]);

        $registrant = DB::transaction(function () use ($class, $validated) {
            $registrant = ClassRegistrant::updateOrCreate(
                [
                    'class_id' => $class->id,
                    'external_registration_id' => $validated['external_registration_id'],
                ],
                [
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'status' => $validated['status'],
                    'registered_at' => $validated['registered_at'] ?? now(),
                ]
            );

            $this->syncClassSummary($class);

            return $registrant;
        });

        $class->refresh();

        return response()->json([
            'message' => 'Data pendaftar berhasil disinkronkan.',
            'data' => [
                'external_registration_id' => $registrant->external_registration_id,
                'class_id' => $class->id,
                'status' => $registrant->status,
                'registered_students' => $class->enrolled,
                'calculated_revenue' => $this->calculatedRevenue($class),
            ],
        ], 201);
    }

    private function syncClassSummary(Clas $class): void
    {
        $activeStudents = $class->registrants()->active()->count();

        $class->update([
            'enrolled' => $activeStudents,
            'amount' => $activeStudents,
        ]);
    }

    private function calculatedRevenue(Clas $class): float
    {
        $unitPrice = (float) ($class->price_per_student ?: $class->price);

        return round(((int) $class->enrolled) * $unitPrice, 2);
    }

    private function classData(Clas $class): array
    {
        return [
            'id' => $class->id,
            'slug' => $class->slug,
            'status' => $class->status,
            'category' => $class->kategori ? [
                'id' => $class->kategori->id,
                'name' => $class->kategori->nama_kategori,
            ] : null,
            'training' => $class->training ? [
                'id' => $class->training->id,
                'type' => $class->training->type,
                'name' => $class->training->name,
            ] : null,
            'name' => $class->name,
            'private_student_name' => $class->private_student_name,
            'organization' => [
                'name' => $class->instansi,
                'address' => $class->alamat,
                'pic_name' => $class->no_pic,
                'pic_phone' => $class->no_kontak,
            ],
            'trainers' => $class->trainers->map(fn ($trainer) => [
                'name' => $trainer->name,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'address' => $trainer->address,
            ])->values()->all(),
            'legacy_trainer' => $class->trainer,
            'schedule' => [
                'start_date' => $class->start_date ? Carbon::parse($class->start_date)->toDateString() : null,
                'end_date' => $class->end_date ? Carbon::parse($class->end_date)->toDateString() : null,
                'start_time' => $class->start_time?->format('H:i:s'),
                'end_time' => $class->end_time?->format('H:i:s'),
                'meetings' => $class->meet,
                'jpl_per_meeting' => $class->duration,
                'method' => $class->method,
            ],
            'capacity' => [
                'target_students' => $class->capacity,
                'enrolled_students' => $class->enrolled,
                'participant_count' => $class->amount,
            ],
            'pricing' => [
                'price' => $class->price,
                'price_per_student' => $class->price_per_student,
                'registration_unit_price' => $class->price_per_student ?: $class->price,
                'target_revenue' => $class->target_revenue,
                'payment_type' => $class->payment_type,
                'calculated_revenue' => $this->calculatedRevenue($class),
            ],
            'price' => $class->price,
            'price_per_student' => $class->price_per_student,
            'registration_unit_price' => $class->price_per_student ?: $class->price,
            'calculated_revenue' => $this->calculatedRevenue($class),
            'payment_type' => $class->payment_type,
            'jenis_reguler' => $class->jenis_reguler,
            'description' => $class->description,
            'location' => $class->alamat,
            'bnsp_certification' => [
                'enabled' => $class->sertifikasi_bnsp,
                'certification_date' => $class->bnsp_tanggal_sertifikasi
                    ? Carbon::parse($class->bnsp_tanggal_sertifikasi)->toDateString()
                    : null,
                'student_count' => $class->bnsp_student_count,
                'fee_per_student' => $class->bnsp_fee_per_student,
            ],
            'created_at' => $class->created_at?->toIso8601String(),
            'updated_at' => $class->updated_at?->toIso8601String(),
        ];
    }
}
