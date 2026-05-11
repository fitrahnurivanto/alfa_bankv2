<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingCategory;
use Illuminate\Http\Request;

class TrainingCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TrainingCategory::withCount([
            'trainings',
            'trainings as active_trainings_count' => function($query) {
                $query->whereHas('classes', function($q) {
                    $q->whereIn('status', ['pending', 'approved']);
                });
            }
        ])->latest()->get();
        
        return view('admin.academy.training-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.academy.training-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        TrainingCategory::create($validated);

        return redirect()->route('admin.training-categories.index')
            ->with('success', 'Kategori pelatihan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TrainingCategory $trainingCategory)
    {
        $trainingCategory->load('trainings');
        return view('admin.academy.training-categories.show', compact('trainingCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TrainingCategory $trainingCategory)
    {
        return view('admin.academy.training-categories.edit', compact('trainingCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrainingCategory $trainingCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $trainingCategory->update($validated);

        return redirect()->route('admin.training-categories.index')
            ->with('success', 'Kategori pelatihan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingCategory $trainingCategory)
    {
        // Check if category has trainings that are used in active classes (pending or approved)
        $hasActiveClasses = $trainingCategory->trainings()
            ->whereHas('classes', function($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->exists();

        if ($hasActiveClasses) {
            return redirect()->route('admin.training-categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang memiliki pelatihan yang sedang digunakan di kelas aktif.');
        }

        $trainingCategory->delete();

        return redirect()->route('admin.training-categories.index')
            ->with('success', 'Kategori pelatihan berhasil dihapus.');
    }
}
