@extends('layouts.app')

@section('title', 'Ubah Kondisi - ' . $inventaris->nama_barang)

@section('content')
<div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-start gap-4">
        <a href="{{ route('admin.inventaris.show', $inventaris->id) }}" class="text-gray-900 hover:text-[#fe0000] text-2xl mt-1.5">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Ubah Kondisi Barang</h1>
            <p class="text-gray-800">{{ $inventaris->nama_barang }} ({{ $inventaris->kode_barang }})</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6 sm:p-8 max-w-3xl">
        <form action="{{ route('admin.inventaris.update-kondisi', $inventaris->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <!-- Kondisi Saat Ini -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Kondisi Saat Ini
                    </label>
                    @php
                        $kondisiColors = [
                            'baru' => 'bg-green-50 text-green-700 border-green-200',
                            'baik' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'perbaikan' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'rusak_ringan' => 'bg-orange-50 text-orange-700 border-orange-200',
                            'rusak_berat' => 'bg-red-50 text-red-700 border-red-200',
                        ];
                        $colorClass = $kondisiColors[$inventaris->kondisi] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                    @endphp
                    <div class="px-4 py-2 border {{ $colorClass }} rounded-lg font-semibold">
                        {{ ucfirst(str_replace('_', ' ', $inventaris->kondisi)) }}
                    </div>
                </div>

                <!-- Kondisi Baru -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Kondisi Baru <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="kondisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('kondisi') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Kondisi Baru --</option>
                        <option value="baru" {{ old('kondisi') === 'baru' ? 'selected' : '' }}>Baru</option>
                        <option value="baik" {{ old('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="perbaikan" {{ old('kondisi') === 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                        <option value="rusak_ringan" {{ old('kondisi') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alasan -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Alasan Perubahan Kondisi <span class="text-[#fe0000]">*</span>
                    </label>
                    <textarea 
                        name="alasan" 
                        rows="4"
                        placeholder="Jelaskan alasan mengubah kondisi barang ini (misalnya: maintenance, rusak karena jatuh, dll)..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('alasan') border-red-500 @enderror"
                    >{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info -->
                <div class="bg-[#ffeed9] border border-[#8a4b00] rounded-lg p-4">
                    <p class="text-sm text-[#8a4b00]">
                        <i class="fas fa-info-circle mr-2"></i>
                        Perubahan kondisi ini akan dicatat dalam riwayat audit dan dapat dilihat oleh user lain.
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
                <a href="{{ route('admin.inventaris.show', $inventaris->id) }}" class="px-6 py-3 text-center bg-white border border-[#fe0000] rounded-lg text-[#fe0000] font-semibold shadow hover:bg-[#fed0d0]/40 transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-[#fe0000] hover:bg-[#cc0000] text-white px-6 py-3 rounded-lg font-semibold shadow-[0_3px_6px_rgba(0,0,0,0.25)] transition"
                >
                    Simpan Perubahan Kondisi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection