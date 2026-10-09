@extends('layouts.app')

@section('title', 'Inventaris Barang')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Inventaris Barang</h1>
            <p class="text-gray-800">Kelola daftar barang dan perlengkapan perusahaan</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.inventaris.exportPdf') }}" class="bg-white border border-[#fe0000] text-[#fe0000] hover:bg-[#fed0d0]/40 px-5 py-2.5 rounded-lg flex items-center gap-2 font-medium transition">
                <i class="far fa-file-lines"></i> Export PDF
            </a>
            <a href="{{ route('admin.inventaris.exportExcel') }}" class="bg-[#d9ffd0] border border-[#13a100] text-[#13a100] hover:bg-[#c4f7b8] px-5 py-2.5 rounded-lg flex items-center gap-2 font-medium transition">
                <i class="far fa-file"></i> Export Excel
            </a>
            <a href="{{ route('admin.inventaris.create') }}" class="bg-[#fe0000] hover:bg-[#cc0000] text-white px-5 py-2.5 rounded-lg flex items-center gap-2 font-semibold shadow-[0_3px_6px_rgba(0,0,0,0.25)] transition">
                <i class="fas fa-plus"></i> Tambah Barang
            </a>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row gap-3">
            <!-- Search -->
            <form class="flex-1" method="GET" action="{{ route('admin.inventaris.index') }}">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Cari kode, nama, atau merek barang..." 
                        value="{{ request('search') }}"
                        class="flex-1 px-4 py-2.5 bg-white border-2 border-gray-900 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000]"
                    />
                    <button type="submit" class="bg-[#fe0000] hover:bg-[#cc0000] text-white px-6 py-2.5 rounded-lg shadow transition">
                        <i class="fas fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>

            <!-- Filter Kategori -->
            <form class="lg:w-56" method="GET" action="{{ route('admin.inventaris.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select 
                    name="kategori" 
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
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
            <form class="lg:w-56" method="GET" action="{{ route('admin.inventaris.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                <select 
                    name="kondisi" 
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
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
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] overflow-hidden">
        @if($inventaris->count() > 0)
            <table class="w-full">
                <thead class="bg-white border-b-2 border-gray-300">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Kode Barang</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Nama Barang</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Jumlah</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Posisi</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Tahun Pengadaan</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Kondisi</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-medium uppercase tracking-wider text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventaris as $item)
                        <tr class="border-b border-gray-200 hover:bg-[#f9f0f1]">
                            <td class="px-6 py-4 text-sm font-mono text-[#344bfd]">{{ $item->kode_barang }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama_barang }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="px-3 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium">{{ $item->kategori_barang }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->jumlah }} {{ $item->satuan }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->posisi }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $kondisiColors = [
                                        'baru' => 'bg-[#c8f7b4] text-[#2e8b12]',
                                        'baik' => 'bg-[#cfdcff] text-[#344bfd]',
                                        'perbaikan' => 'bg-[#fff08a] text-[#8a6d00]',
                                        'rusak_ringan' => 'bg-[#fdebd0] text-[#8a4b00]',
                                        'rusak_berat' => 'bg-[#fed0d0] text-[#fe0000]',
                                        'hilang' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $kondisiColors[$item->kondisi] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->kondisi)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 {{ $item->status === 'aktif' ? 'bg-[#c8f7b4] text-[#2e8b12]' : 'bg-gray-100 text-gray-800' }} rounded-full text-xs font-medium">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.inventaris.show', $item->id) }}" class="text-[#344bfd] hover:opacity-70 px-2 py-1 text-lg" title="Lihat">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inventaris.edit', $item->id) }}" class="text-[#e28100] hover:opacity-70 px-2 py-1 text-lg" title="Edit">
                                        <i class="far fa-pen-to-square"></i>
                                    </a>
                                    <button 
                                        onclick="confirmDelete({{ $item->id }})" 
                                        class="text-[#fe0000] hover:opacity-70 px-2 py-1 text-lg" 
                                        title="Hapus"
                                    >
                                        <i class="far fa-trash-can"></i>
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
                <i class="fas fa-box-open text-5xl text-[#fed0d0] mb-4"></i>
                <p class="text-gray-600 text-lg">Belum ada data inventaris barang</p>
                <a href="{{ route('admin.inventaris.create') }}" class="inline-block mt-4 bg-[#fe0000] hover:bg-[#cc0000] text-white px-6 py-3 rounded-lg font-semibold shadow-[0_3px_6px_rgba(0,0,0,0.25)] transition">
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