<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRegistrant;
use App\Models\Clas;
use App\Models\Kategori;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassApiController extends Controller
{
    private const REGISTRATION_CATEGORY_SLUGS = [
        'reguler',
        'private',
    ];

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $classes = Clas::query()
            ->with([
                'kategori:id,nama_kategori',
                'training:id,type,name',
                'trainers:id,name,email,phone,address,specialization,bio,photo_path,cv_path,status',
            ])
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->where('status', 'approved')
                            ->whereDate('start_date', '>=', today());
                    })
                    ->orWhere(function ($query) {
                        $query->where('status', 'pending')
                            ->whereDate('start_date', '>', today())
                            ->whereHas('kategori', function ($query) {
                                $query->where('slug', 'reguler');
                            });
                    });
            })
            ->whereHas('kategori', function ($query) {
                $query->where('slug', 'reguler');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('kategori', function ($query) use ($request) {
                    $query->where('slug', $request->input('category'));
                });
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
        if (!$this->isRegistrationOpen($class)) {
            return response()->json([
                'message' => 'Kelas tidak tersedia untuk pendaftaran.',
            ], 404);
        }

        $class->load([
            'kategori:id,nama_kategori',
            'training:id,type,name',
            'trainers:id,name,email,phone,address,specialization,bio,photo_path,cv_path,status',
        ]);

        return response()->json([
            'data' => $this->classData($class),
        ]);
    }

    public function storeRegistrant(Request $request, Clas $class): JsonResponse
    {
        if (!$this->isRegistrationOpen($class)) {
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
            $class = Clas::query()->lockForUpdate()->findOrFail($class->id);

            if (!$this->isRegistrationOpen($class)) {
                throw ValidationException::withMessages([
                    'class' => 'Kelas sudah tidak dibuka untuk pendaftaran.',
                ]);
            }

            $existingRegistrant = $class->registrants()
                ->where('external_registration_id', $validated['external_registration_id'])
                ->first();

            if (!$existingRegistrant?->status || !in_array($existingRegistrant->status, ['registered', 'paid', 'confirmed'], true)) {
                $activeStudents = $class->registrants()->active()->count();

                if ((int) $class->capacity > 0 && $activeStudents >= (int) $class->capacity) {
                    throw ValidationException::withMessages([
                        'class' => 'Kapasitas kelas sudah penuh.',
                    ]);
                }
            }

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

    public function storePrivateClassRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_registration_id' => 'required|string|max:100',
            'training_id' => 'required|exists:trainings,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'preferred_start_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $training = Training::query()
            ->whereKey($validated['training_id'])
            ->where('type', 'private')
            ->where('is_active', true)
            ->first();

        if (!$training) {
            return response()->json([
                'message' => 'Materi private tidak tersedia atau tidak aktif.',
            ], 422);
        }

        $privateCategory = Kategori::query()->where('slug', 'private')->first();

        if (!$privateCategory) {
            return response()->json([
                'message' => 'Kategori private belum tersedia di master data Laravel.',
            ], 422);
        }

        $class = DB::transaction(function () use ($validated, $training, $privateCategory) {
            $existingRegistrant = ClassRegistrant::query()
                ->where('external_registration_id', $validated['external_registration_id'])
                ->with('class')
                ->first();

            if ($existingRegistrant) {
                return $existingRegistrant->class;
            }

            $baseName = 'Private - ' . $training->name . ' - ' . $validated['full_name'];
            $baseSlug = Str::slug($baseName);
            $slug = $baseSlug;
            $counter = 1;

            while (Clas::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $class = Clas::create([
                'kategori_id' => $privateCategory->id,
                'training_id' => $training->id,
                'name' => $baseName,
                'slug' => $slug,
                'private_student_name' => $validated['full_name'],
                'price_per_student' => $training->price,
                'price' => $training->price,
                'target_revenue' => $training->price,
                'capacity' => 1,
                'enrolled' => 1,
                'amount' => 1,
                'method' => 'offline',
                'meet' => 1,
                'duration' => $training->duration ?: 1,
                'start_date' => $validated['preferred_start_date'] ?? null,
                'description' => $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_type' => 'full',
                'paid_amount' => 0,
                'income' => $training->price,
            ]);

            ClassRegistrant::create([
                'class_id' => $class->id,
                'external_registration_id' => $validated['external_registration_id'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => 'registered',
                'registered_at' => now(),
            ]);

            return $class;
        });

        return response()->json([
            'message' => 'Permintaan kelas private berhasil dikirim ke admin.',
            'data' => [
                'class_id' => $class->id,
                'status' => $class->status,
                'training_id' => $class->training_id,
                'external_registration_id' => $validated['external_registration_id'],
            ],
        ], 201);
    }

    private function syncClassSummary(Clas $class): void
    {
        $activeStudents = $class->registrants()->active()->count();

        $summary = [
            'enrolled' => $activeStudents,
            'amount' => $activeStudents,
        ];

        if (in_array($class->kategori?->slug, self::REGISTRATION_CATEGORY_SLUGS, true)) {
            $summary['price'] = $this->calculatedRevenueForStudents($class, $activeStudents);
        }

        $class->update($summary);
    }

    private function calculatedRevenue(Clas $class): float
    {
        return $this->calculatedRevenueForStudents($class, (int) $class->enrolled);
    }

    private function calculatedRevenueForStudents(Clas $class, int $studentCount): float
    {
        $unitPrice = (float) ($class->price_per_student ?: $class->price);

        return round($studentCount * $unitPrice, 2);
    }

    private function isRegistrationOpen(Clas $class): bool
    {
        if (!in_array($class->status, ['pending', 'approved'], true)) {
            return false;
        }

        if (!in_array($class->kategori?->slug, self::REGISTRATION_CATEGORY_SLUGS, true)) {
            return false;
        }

        if (!$class->start_date) {
            return false;
        }

        $startDate = Carbon::parse($class->start_date);

        return $class->status === 'pending'
            ? $startDate->isAfter(today())
            : !$startDate->isBefore(today());
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
                'id' => $trainer->id,
                'name' => $trainer->name,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'contact' => $trainer->phone,
                'address' => $trainer->address,
                'specialization' => $trainer->specialization,
                'bio' => $trainer->bio,
                'photo_url' => $trainer->photo_path ? asset('storage/' . $trainer->photo_path) : null,
                'cv_url' => $trainer->cv_path ? asset('storage/' . $trainer->cv_path) : null,
                'status' => $trainer->status,
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
