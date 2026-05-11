<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    /**
     * Display a listing of trainers.
     */
    public function index()
    {
        // Show trainer role only
        $trainers = User::where('role', 'trainer')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.trainers.index', compact('trainers'));
    }

    /**
     * Show the form for creating a new trainer.
     */
    public function create()
    {
        return view('admin.trainers.create');
    }

    /**
     * Store a newly created trainer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['role'] = 'trainer';
        $validated['division'] = 'academy'; // Trainer belongs to academy division
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer berhasil ditambahkan!');
    }

    /**
     * Display the specified trainer.
     */
    public function show(User $trainer)
    {
        // Ensure the requested user is a trainer
        if ($trainer->role !== 'trainer') {
            abort(404);
        }

        // Load trainer's classes
        $trainer->load(['classes' => function($query) {
            $query->orderBy('start_date', 'desc');
        }]);

        return view('admin.trainers.show', compact('trainer'));
    }

    /**
     * Show the form for editing the specified trainer.
     */
    public function edit(User $trainer)
    {
        // Ensure we're editing a trainer
        if ($trainer->role !== 'trainer') {
            abort(404);
        }

        return view('admin.trainers.edit', compact('trainer'));
    }

    /**
     * Update the specified trainer in storage.
     */
    public function update(Request $request, User $trainer)
    {
        // Ensure we're updating a trainer
        if ($trainer->role !== 'trainer') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($trainer->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $trainer->update($validated);

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Data trainer berhasil diperbarui!');
    }

    /**
     * Remove the specified trainer from storage.
     */
    public function destroy(User $trainer)
    {
        // Ensure we're deleting a trainer
        if ($trainer->role !== 'trainer') {
            abort(404);
        }

        // Check if trainer has classes
        $classCount = $trainer->classes()->count();
        
        if ($classCount > 0) {
            return redirect()->route('admin.trainers.index')
                ->with('error', "Trainer tidak dapat dihapus karena masih memiliki {$classCount} kelas yang terkait.");
        }

        $trainer->delete();

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer berhasil dihapus!');
    }
}
