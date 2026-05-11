@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.clients.show', $client) }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Client</h1>
            <p class="text-gray-600">Perbarui informasi client</p>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <div class="font-semibold mb-2">
            <i class="fas fa-exclamation-circle mr-2"></i> Terjadi Kesalahan:
        </div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('admin.clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <!-- Client Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Client <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $client->name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7b2cbf] focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Masukkan nama client"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Name -->
                <div>
                    <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Perusahaan
                    </label>
                    <input type="text" 
                           id="company_name" 
                           name="company_name" 
                           value="{{ old('company_name', $client->company_name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7b2cbf] focus:border-transparent @error('company_name') border-red-500 @enderror"
                           placeholder="Masukkan nama perusahaan (opsional)">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $client->email) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7b2cbf] focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="Masukkan email client"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fab fa-whatsapp text-green-500"></i>
                        </div>
                        <input type="text" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $client->phone) }}"
                               class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7b2cbf] focus:border-transparent @error('phone') border-red-500 @enderror"
                               placeholder="08xxxxxxxxxx"
                               required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> Format: 08xxx atau +628xxx
                    </p>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7b2cbf] focus:border-transparent @error('address') border-red-500 @enderror"
                              placeholder="Masukkan alamat lengkap (opsional)">{{ old('address', $client->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                <a href="{{ route('admin.clients.show', $client) }}" 
                   class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition font-medium shadow-sm">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Client Info Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 text-lg mt-0.5"></i>
            <div class="flex-1">
                <h3 class="font-semibold text-blue-900 mb-1">Informasi Penting</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Client ini memiliki {{ $client->orders()->count() }} order</li>
                    <li>• Client ini memiliki {{ $client->projects()->count() }} project 
                        @php
                            $activeProjects = $client->projects()->whereIn('status', ['pending', 'in_progress'])->count();
                            $completedProjects = $client->projects()->where('status', 'completed')->count();
                        @endphp
                        ({{ $activeProjects }} aktif, {{ $completedProjects }} selesai)
                    </li>
                    <li>• Bergabung sejak {{ $client->created_at->format('d M Y') }}</li>
                    @if($activeProjects > 0)
                    <li class="text-orange-700 font-semibold">⚠️ Client tidak dapat dihapus karena masih ada {{ $activeProjects }} project aktif</li>
                    @else
                    <li class="text-green-700 font-semibold">✓ Client dapat dihapus (semua project sudah selesai)</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
