@extends('layouts.app')

@section('title', 'Ubah Kondisi - ' . $inventaris->nama_barang)

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
        <h1 class="text-3xl font-bold text-gray-900">Ubah Kondisi Barang</h1>
        <p class="text-gray-600 mt-1">{{ $inventaris->nama_barang }} ({{ $inventaris->kode_barang }})</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl">
        <form action="{{ route('admin.inventaris.update-kondisi', $inventaris->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <!-- Kondisi Saat Ini -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kondisi Baru <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="kondisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('kondisi') border-red-500 @enderror"
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Alasan Perubahan Kondisi <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="alasan" 
                        rows="4"
                        placeholder="Jelaskan alasan mengubah kondisi barang ini (misalnya: maintenance, rusak karena jatuh, dll)..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('alasan') border-red-500 @enderror"
                    >{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Perubahan kondisi ini akan dicatat dalam riwayat audit dan dapat dilihat oleh user lain.
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
                    Simpan Perubahan Kondisi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
