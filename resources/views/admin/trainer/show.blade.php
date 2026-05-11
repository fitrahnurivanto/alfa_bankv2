@extends('layouts.app')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.trainer.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail Trainer</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.trainer.edit', $trainer) }}" 
                   class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <form action="{{ route('admin.trainer.destroy', $trainer) }}" 
                      method="POST" 
                      class="inline-block"
                      onsubmit="return confirm('Yakin ingin menghapus trainer ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-8 text-white">
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold shadow-lg">
                    {{ strtoupper(substr($trainer->name, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-2">{{ $trainer->name }}</h2>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm">
                            <i class="fas fa-code mr-2"></i> {{ $trainer->expertise }}
                        </span>
                        @if($trainer->status === 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-500">
                            <i class="fas fa-circle text-xs mr-1"></i> Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-500">
                            <i class="fas fa-times-circle text-xs mr-1"></i> Inactive
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-address-card mr-2 text-purple-600"></i> Informasi Kontak
            </h3>
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Email -->
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-envelope text-purple-600"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-1">Email</div>
                        <a href="mailto:{{ $trainer->email }}" 
                           class="text-gray-900 font-medium hover:text-purple-600">
                            {{ $trainer->email }}
                        </a>
                    </div>
                </div>

                <!-- Phone -->
                @if($trainer->phone)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fab fa-whatsapp text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-1">WhatsApp</div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trainer->phone) }}" 
                           target="_blank"
                           class="text-gray-900 font-medium hover:text-green-600">
                            {{ $trainer->phone }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Bio -->
        @if($trainer->bio)
        <div class="p-6 border-t">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-user mr-2 text-purple-600"></i> Profil Singkat
            </h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700 leading-relaxed">{{ $trainer->bio }}</p>
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        <div class="p-6 border-t bg-gray-50">
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Dibuat pada:</span>
                    <span class="text-gray-900 font-medium ml-2">{{ $trainer->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Terakhir diupdate:</span>
                    <span class="text-gray-900 font-medium ml-2">{{ $trainer->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection