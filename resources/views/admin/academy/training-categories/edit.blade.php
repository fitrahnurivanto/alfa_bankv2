@extends('layouts.app')

@section('title', 'Edit Kategori: ' . $trainingCategory->name)

@section('page-title', 'Edit Kategori Pelatihan')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.training-categories.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Kategori
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-edit mr-2 text-yellow-600"></i>Edit Kategori Pelatihan
            </h2>
            <p class="text-sm text-gray-600 mt-1">Perbarui informasi kategori {{ $trainingCategory->name }}</p>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.training-categories.update', $trainingCategory) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $trainingCategory->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="Contoh: Soft Skills, Technical Skills, Leadership"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Deskripsi
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Deskripsi singkat tentang kategori pelatihan ini...">{{ old('description', $trainingCategory->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Opsional - Jelaskan jenis pelatihan yang termasuk dalam kategori ini</p>
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select name="status" 
                        id="status"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('status') border-red-500 @enderror"
                        required>
                    <option value="active" {{ old('status', $trainingCategory->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $trainingCategory->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Kategori aktif akan tersedia untuk dipilih saat membuat pelatihan</p>
            </div>

            <!-- Warning if has active trainings -->
            @if($trainingCategory->trainings()->whereHas('classes', function($q) { $q->whereIn('status', ['pending', 'approved']); })->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3 mt-1"></i>
                        <div>
                            <h4 class="font-semibold text-yellow-800 mb-1">Perhatian!</h4>
                            <p class="text-sm text-yellow-700">
                                Kategori ini memiliki pelatihan yang sedang digunakan di kelas aktif. 
                                Perubahan status menjadi nonaktif akan mempengaruhi pembuatan kelas baru.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-semibold shadow-sm">
                    <i class="fas fa-save mr-2"></i>Update Kategori
                </button>
                <a href="{{ route('admin.training-categories.index') }}" 
                   class="flex-1 text-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Link to Detail -->
    <div class="mt-6">
        <a href="{{ route('admin.training-categories.show', $trainingCategory) }}" 
           class="inline-flex items-center text-blue-600 hover:text-blue-800 transition text-sm">
            <i class="fas fa-eye mr-2"></i>Lihat Detail Kategori
        </a>
    </div>
</div>
@endsection
