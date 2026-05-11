@extends('layouts.app')

@section('page-title', 'Edit Trainer')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.trainers.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Data Trainer</h2>

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <ul class="list-disc list-inside mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.trainers.update', $trainer) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   name="name" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   value="{{ old('name', $trainer->name) }}"
                   placeholder="Masukkan nama lengkap trainer"
                   required>
        </div>

        <!-- Email -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   value="{{ old('email', $trainer->email) }}"
                   placeholder="trainer@example.com"
                   required>
            <p class="text-sm text-gray-600 mt-1">Email akan digunakan untuk login</p>
        </div>

        <!-- Phone -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                No. Telepon
            </label>
            <input type="text" 
                   name="phone" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   value="{{ old('phone', $trainer->phone) }}"
                   placeholder="08xxxxxxxxxx">
        </div>

        <!-- Address -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Alamat
            </label>
            <textarea name="address" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                      rows="3"
                      placeholder="Masukkan alamat lengkap">{{ old('address', $trainer->address) }}</textarea>
        </div>

        <!-- Password (Optional) -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm font-semibold text-yellow-800 mb-3">
                <i class="fas fa-lock mr-1"></i> Ubah Password (Opsional)
            </p>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Password Baru
                </label>
                <input type="password" 
                       name="password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                       placeholder="Kosongkan jika tidak ingin mengubah password">
                <p class="text-xs text-gray-600 mt-1">Minimal 8 karakter</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Konfirmasi Password Baru
                </label>
                <input type="password" 
                       name="password_confirmation" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                       placeholder="Ulangi password baru">
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 text-lg mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Informasi:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Trainer ID: <strong>{{ $trainer->id }}</strong></li>
                        <li>Jumlah Kelas: <strong>{{ $trainer->classes()->count() }} kelas</strong></li>
                        <li>Terdaftar sejak: <strong>{{ $trainer->created_at->format('d/m/Y') }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold flex items-center gap-2">
                <i class="fas fa-save"></i> Update Trainer
            </button>
            <a href="{{ route('admin.trainers.index') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
