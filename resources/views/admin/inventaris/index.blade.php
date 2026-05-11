@extends('layouts.app')

@section('title', 'Inventaris Barang')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventaris Barang</h1>
            <p class="text-gray-600 mt-1">Kelola daftar barang dan perlengkapan perusahaan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventaris.exportPdf') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.inventaris.exportExcel') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.inventaris.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Barang
            </a>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <form class="md:col-span-2" method="GET" action="{{ route('admin.inventaris.index') }}">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Cari kode, nama, atau merek barang..." 
                        value="{{ request('search') }}"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Filter Kategori -->
            <form method="GET" action="{{ route('admin.inventaris.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select 
                    name="kategori" 
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Semua Kategori</option>
                    <option value="elektronik" {{ request('kategori') === 'elektronik' ? 'selected' : '' }}>Elektronik</option>
                    <option value="furniture" {{ request('kategori') === 'furniture' ? 'selected' : '' }}>Furniture</option>
                    <option value="alat_tulis" {{ request('kategori') === 'alat_tulis' ? 'selected' : '' }}>Alat Tulis</option>
                    <option value="kendaraan" {{ request('kategori') === 'kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                    <option value="lainnya" {{ request('kategori') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </form>

            <!-- Filter Kondisi -->
            <form method="GET" action="{{ route('admin.inventaris.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                <select 
                    name="kondisi" 
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Semua Kondisi</option>
                    <option value="baru" {{ request('kondisi') === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="baik" {{ request('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="perbaikan" {{ request('kondisi') === 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                    <option value="rusak_ringan" {{ request('kondisi') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('kondisi') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Inventaris Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($inventaris->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kode Barang</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nama Barang</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kategori</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Jumlah</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Posisi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tahun Pengadaan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kondisi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventaris as $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono text-blue-600">{{ $item->kode_barang }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama_barang }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-medium">{{ $item->kategori_barang }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->jumlah }} {{ $item->satuan }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->posisi }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $kondisiColors = [
                                        'baru' => 'bg-green-100 text-green-800',
                                        'baik' => 'bg-blue-100 text-blue-800',
                                        'perbaikan' => 'bg-yellow-100 text-yellow-800',
                                        'rusak_ringan' => 'bg-orange-100 text-orange-800',
                                        'rusak_berat' => 'bg-red-100 text-red-800',
                                        'hilang' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $kondisiColors[$item->kondisi] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->kondisi)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 {{ $item->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-xs font-medium">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.inventaris.show', $item->id) }}" class="text-blue-600 hover:text-blue-900 px-2 py-1" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inventaris.edit', $item->id) }}" class="text-amber-600 hover:text-amber-900 px-2 py-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        onclick="confirmDelete({{ $item->id }})" 
                                        class="text-red-600 hover:text-red-900 px-2 py-1" 
                                        title="Hapus"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $inventaris->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">Belum ada data inventaris barang</p>
                <a href="{{ route('admin.inventaris.create') }}" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Tambah Barang Pertama
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(itemId) {
    if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.inventaris.destroy', ':id') }}`.replace(':id', itemId);
        form.submit();
    }
}
</script>
@endsection
