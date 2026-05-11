@extends('layouts.app')

@section('title', 'Ubah Posisi - ' . $inventaris->nama_barang)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.inventaris.show', $inventaris->id) }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Ubah Posisi Barang</h1>
        <p class="text-gray-600 mt-1">{{ $inventaris->nama_barang }} ({{ $inventaris->kode_barang }})</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl">
        <form action="{{ route('admin.inventaris.update-posisi', $inventaris->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <!-- Posisi Saat Ini -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Posisi Saat Ini
                    </label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-semibold">
                        {{ ucfirst($inventaris->posisi) }}
                    </div>
                </div>

                <!-- Posisi Baru -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Posisi Baru <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="posisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('posisi') border-red-500 @enderror"
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Alasan Perubahan
                    </label>
                    <textarea 
                        name="alasan" 
                        rows="4"
                        placeholder="Jelaskan alasan mengubah posisi barang ini..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >{{ old('alasan') }}</textarea>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Perubahan posisi ini akan dicatat dalam riwayat audit dan dapat dilihat oleh user lain.
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.inventaris.show', $inventaris->id) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
                >
                    Simpan Perubahan Posisi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
