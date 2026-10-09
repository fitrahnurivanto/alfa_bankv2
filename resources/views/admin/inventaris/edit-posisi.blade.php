@extends('layouts.app')

@section('title', 'Ubah Posisi - ' . $inventaris->nama_barang)

@section('content')
<div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-start gap-4">
        <a href="{{ route('admin.inventaris.show', $inventaris->id) }}" class="text-gray-900 hover:text-[#fe0000] text-2xl mt-1.5">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Ubah Posisi Barang</h1>
            <p class="text-gray-800">{{ $inventaris->nama_barang }} ({{ $inventaris->kode_barang }})</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6 sm:p-8 max-w-3xl">
        <form action="{{ route('admin.inventaris.update-posisi', $inventaris->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <!-- Posisi Saat Ini -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Posisi Saat Ini
                    </label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-semibold">
                        {{ ucfirst($inventaris->posisi) }}
                    </div>
                </div>

                <!-- Posisi Baru -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Posisi Baru <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="posisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('posisi') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Posisi Baru --</option>
                        <option value="gudang" {{ old('posisi') === 'gudang' ? 'selected' : '' }}>Gudang</option>
                        <option value="kantor" {{ old('posisi') === 'kantor' ? 'selected' : '' }}>Kantor</option>
                        <option value="cabang" {{ old('posisi') === 'cabang' ? 'selected' : '' }}>Cabang</option>
                        <option value="dipinjam" {{ old('posisi') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="dijual" {{ old('posisi') === 'dijual' ? 'selected' : '' }}>Dijual</option>
                    </select>
                    @error('posisi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alasan -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Alasan Perubahan
                    </label>
                    <textarea 
                        name="alasan" 
                        rows="4"
                        placeholder="Jelaskan alasan mengubah posisi barang ini..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    >{{ old('alasan') }}</textarea>
                </div>

                <!-- Info -->
                <div class="bg-[#ffeed9] border border-[#8a4b00] rounded-lg p-4">
                    <p class="text-sm text-[#8a4b00]">
                        <i class="fas fa-info-circle mr-2"></i>
                        Perubahan posisi ini akan dicatat dalam riwayat audit dan dapat dilihat oleh user lain.
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
                    Simpan Perubahan Posisi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection