@extends('layouts.app')

@section('title', 'Tambah Kategori Pelatihan')

@section('page-title', 'Tambah Kategori Pelatihan')

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
        <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-plus-circle mr-2 text-green-600"></i>Tambah Kategori Pelatihan Baru
            </h2>
            <p class="text-sm text-gray-600 mt-1">Isi formulir di bawah untuk menambah kategori pelatihan</p>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.training-categories.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror"
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
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Deskripsi singkat tentang kategori pelatihan ini...">{{ old('description') }}</textarea>
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
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('status') border-red-500 @enderror"
                        required>
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Kategori aktif akan tersedia untuk dipilih saat membuat pelatihan</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold shadow-sm">
                    <i class="fas fa-save mr-2"></i>Simpan Kategori
                </button>
                <a href="{{ route('admin.training-categories.index') }}" 
                   class="flex-1 text-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
            <div>
                <h4 class="font-semibold text-blue-800 mb-1">Tentang Kategori Pelatihan</h4>
                <p class="text-sm text-blue-700">
                    Kategori pelatihan digunakan untuk mengelompokkan pelatihan-pelatihan yang sejenis. 
                    Ini memudahkan pencarian dan organisasi pelatihan di sistem.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
