<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataApiController extends Controller
{
    public function trainers(Request $request): JsonResponse
    {
        $trainers = User::query()
            ->where('role', 'trainer')
            ->where('status', 'active')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'address', 'specialization', 'bio', 'photo_path', 'cv_path', 'status']);

        return response()->json([
            'data' => $trainers->map(fn (User $trainer) => $this->trainerData($trainer))->values(),
            'meta' => [
                'total' => $trainers->count(),
            ],
        ]);
    }

    public function trainings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['reguler', 'private', 'corporate'])],
        ]);

        $trainings = Training::query()
            ->active()
            ->when(isset($validated['type']), function ($query) use ($validated) {
                $query->where('type', $validated['type']);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type', 'description', 'price', 'duration']);

        return response()->json([
            'data' => $trainings->map(fn (Training $training) => [
                'id' => $training->id,
                'name' => $training->name,
                'slug' => $training->slug,
                'type' => $training->type,
                'status' => $training->is_active ? 'active' : 'inactive',
                'is_active' => (bool) $training->is_active,
                'description' => $training->description,
                'price' => $training->price,
                'duration' => $training->duration,
            ])->values(),
            'meta' => [
                'total' => $trainings->count(),
            ],
        ]);
    }

    public function showTrainer(User $trainer): JsonResponse
    {
        if ($trainer->role !== 'trainer' || $trainer->status !== 'active') {
            return response()->json([
                'message' => 'Trainer tidak tersedia.',
            ], 404);
        }

        $trainer->load(['classes' => function ($query) {
            $query->with('training:id,name,type')
                ->whereIn('status', ['approved', 'done'])
                ->orderByDesc('start_date');
        }]);

        return response()->json([
            'data' => array_merge($this->trainerData($trainer), [
                'classes' => $trainer->classes->map(fn ($class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'status' => $class->status,
                    'training' => $class->training ? [
                        'id' => $class->training->id,
                        'name' => $class->training->name,
                        'type' => $class->training->type,
                    ] : null,
                    'start_date' => $class->start_date?->toDateString(),
                    'end_date' => $class->end_date?->toDateString(),
                ])->values()->all(),
            ]),
        ]);
    }

    private function trainerData(User $trainer): array
    {
        return [
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
        ];
    }
}
