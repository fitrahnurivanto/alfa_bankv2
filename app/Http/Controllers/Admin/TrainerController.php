<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            'specialization' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['role'] = 'trainer';
        $validated['division'] = 'academy'; // Trainer belongs to academy division
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';
        $validated = $this->storeProfileFiles($request, $validated);
        unset($validated['photo'], $validated['cv']);

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

    public function downloadCv(User $trainer)
    {
        if ($trainer->role !== 'trainer' || !$trainer->cv_path) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($trainer->cv_path)) {
            abort(404);
        }

        $extension = pathinfo($trainer->cv_path, PATHINFO_EXTENSION);

        return response()->download(
            storage_path('app/public/' . $trainer->cv_path),
            'cv-' . str($trainer->name)->slug() . ($extension ? '.' . $extension : '')
        );
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
            'specialization' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $oldPhotoPath = $trainer->photo_path;
        $oldCvPath = $trainer->cv_path;
        $validated = $this->storeProfileFiles($request, $validated);
        unset($validated['photo'], $validated['cv']);

        $trainer->update($validated);

        if ($oldPhotoPath && $trainer->photo_path !== $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }
        if ($oldCvPath && $trainer->cv_path !== $oldCvPath) {
            Storage::disk('public')->delete($oldCvPath);
        }

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

        if ($trainer->photo_path) {
            Storage::disk('public')->delete($trainer->photo_path);
        }
        if ($trainer->cv_path) {
            Storage::disk('public')->delete($trainer->cv_path);
        }

        $trainer->delete();

        return redirect()->route('admin.trainers.index')
            ->with('success', 'Trainer berhasil dihapus!');
    }

    private function storeProfileFiles(Request $request, array $data): array
    {
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('trainers/photos', 'public');
        }

        if ($request->hasFile('cv')) {
            $data['cv_path'] = $request->file('cv')->store('trainers/cv', 'public');
        }

        return $data;
    }
}
