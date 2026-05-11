<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Training::withCount([
                'classes as active_classes_count' => function($query) {
                    $query->whereIn('status', ['pending', 'approved']);
                }
            ]);

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $trainings = $query->latest()->paginate(20);

        return view('admin.academy.trainings.index', compact('trainings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.academy.trainings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:reguler,private,corporate',
            'name' => 'required|string|max:255|unique:trainings,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'name.unique' => 'Program pelatihan dengan nama ini sudah ada. Gunakan nama yang berbeda.'
        ]);

        // Map form fields to database fields
        $data = [
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['status'] === 'active',
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ];

        Training::create($data);

        return redirect()->route('admin.trainings.index')
            ->with('success', 'Pelatihan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        $training->load('classes');
        return view('admin.academy.trainings.show', compact('training'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Training $training)
    {
        return view('admin.academy.trainings.edit', compact('training'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Training $training)
    {
        $validated = $request->validate([
            'type' => 'required|in:reguler,private,corporate',
            'name' => 'required|string|max:255|unique:trainings,name,' . $training->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'name.unique' => 'Program pelatihan dengan nama ini sudah ada. Gunakan nama yang berbeda.'
        ]);

        // Map form fields to database fields
        $data = [
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['status'] === 'active',
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ];

        $training->update($data);

        return redirect()->route('admin.trainings.index')
            ->with('success', 'Pelatihan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        // Check if training is used in active classes (pending or approved only)
        $activeClassCount = $training->classes()
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activeClassCount > 0) {
            return redirect()->route('admin.trainings.index')
                ->with('error', 'Tidak dapat menghapus pelatihan yang sedang digunakan di kelas aktif. Ada ' . $activeClassCount . ' kelas yang masih berjalan.');
        }

        $training->delete();

        return redirect()->route('admin.trainings.index')
            ->with('success', 'Pelatihan berhasil dihapus.');
    }

    /**
     * Get trainings by type (for AJAX)
     */
    public function getByCategory(Request $request)
    {
        $trainings = Training::where('type', $request->type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return response()->json($trainings);
    }

    /**
     * Quick create training (for AJAX from create class form)
     */
    public function quickCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:reguler,private,corporate',
            ]);

            // Create training
            $training = Training::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'slug' => \Illuminate\Support\Str::slug($validated['name']),
                'is_active' => true,
                'price' => 0,
                'duration' => 30,
                'description' => 'Auto-created from class creation form',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pelatihan berhasil ditambahkan',
                'training' => [
                    'id' => $training->id,
                    'name' => $training->name,
                    'type' => $training->type,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
