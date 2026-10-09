@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-start gap-4">
        <a href="{{ route('admin.trainers.index') }}" class="text-gray-900 hover:text-[#fe0000] text-2xl mt-1.5">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detail Trainer</h1>
            <p class="text-gray-800">Informasi lengkap terkait trainer di Alfa Bank</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Profil -->
        <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6">
            <div class="text-center mb-6">
                <div class="w-36 h-36 mx-auto rounded-2xl bg-[#bfd7ff] flex items-center justify-center text-5xl font-bold text-[#344bfd]">
                    {{ strtoupper(substr($trainer->name, 0, 2)) }}
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 mt-4">{{ $trainer->name }}</h2>
                <p class="text-sm text-gray-900">Trainer Alfa Bank</p>
                @if($trainer->expertise)
                <p class="text-sm font-medium text-gray-900">{{ $trainer->expertise }}</p>
                @endif
                <div class="mt-2">
                    @if($trainer->status === 'active')
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-[#c8f7b4] text-[#2e8b12]"><i class="fas fa-check mr-1"></i> Active</span>
                    @else
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-[#fed0d0] text-[#fe0000]"><i class="fas fa-times mr-1"></i> Inactive</span>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-gray-300 border-t border-gray-300 text-sm">
                <div class="py-3 flex items-start gap-3">
                    <i class="far fa-envelope w-5 text-center text-gray-700 mt-1"></i>
                    <div class="min-w-0">
                        <div class="text-gray-600">Email</div>
                        <a href="mailto:{{ $trainer->email }}" class="text-gray-900 hover:text-[#fe0000] break-all">{{ $trainer->email }}</a>
                    </div>
                </div>
                <div class="py-3 flex items-start gap-3">
                    <i class="fas fa-phone-volume w-5 text-center text-gray-700 mt-1"></i>
                    <div>
                        <div class="text-gray-600">No. Telepon</div>
                        @if($trainer->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trainer->phone) }}" target="_blank" class="text-gray-900 hover:text-[#43bf21]">{{ $trainer->phone }}</a>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>
                <div class="py-3 flex items-start gap-3">
                    <i class="far fa-calendar w-5 text-center text-gray-700 mt-1"></i>
                    <div>
                        <div class="text-gray-600">Terdaftar Sejak</div>
                        <div class="text-gray-900">{{ $trainer->created_at->translatedFormat('d F Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('admin.trainers.edit', $trainer) }}"
                   class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-[#fe0000] text-white text-lg font-semibold rounded-md hover:bg-[#cc0000] transition">
                    <i class="far fa-pen-to-square"></i> Edit Trainer
                </a>
                <form action="{{ route('admin.trainers.destroy', $trainer) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus trainer ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-white border border-[#fe0000] text-[#fe0000] font-semibold rounded-md hover:bg-[#fed0d0]/50 transition">
                        <i class="far fa-trash-can"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Detail -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Profil Singkat</h3>
                @if($trainer->bio)
                <div class="bg-[#f9f0f1] rounded-lg p-4">
                    <p class="text-gray-800 leading-relaxed">{{ $trainer->bio }}</p>
                </div>
                @else
                <p class="text-gray-500">Belum ada profil singkat.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Informasi Akun</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="bg-[#fed0d0] rounded-lg p-4">
                        <div class="text-gray-800">Dibuat pada</div>
                        <div class="text-base font-semibold text-gray-900">{{ $trainer->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="bg-[#fed0d0] rounded-lg p-4">
                        <div class="text-gray-800">Terakhir diupdate</div>
                        <div class="text-base font-semibold text-gray-900">{{ $trainer->updated_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection