@extends('layouts.app')

@section('title', 'Detail Barang - ' . $inventaris->nama_barang)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.inventaris.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $inventaris->nama_barang }}</h1>
            <p class="text-gray-600 mt-1">Kode: <span class="font-mono text-blue-600">{{ $inventaris->kode_barang }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventaris.edit', $inventaris->id) }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Foto Gallery -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                @php
                    $photos = $inventaris->photos ?? collect();
                @endphp
                @if($photos->count() > 0)
                    <div class="relative">
                        <img id="mainPhoto" src="{{ app(\App\Services\SupabaseStorageService::class)->getPublicUrl($photos->first()->file_path) }}" alt="{{ $inventaris->nama_barang }}" class="w-full h-96 object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2214%22 fill=%22%23999%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                        <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                            {{ $photos->count() }} foto
                        </div>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <div class="p-4 border-t border-gray-200 grid grid-cols-5 gap-2">
                        @foreach($photos as $photo)
                            <img 
                                src="{{ app(\App\Services\SupabaseStorageService::class)->getPublicUrl($photo->file_path) }}" 
                                alt="Thumbnail" 
                                class="h-20 w-full object-cover rounded-lg cursor-pointer hover:opacity-70 transition"
                                onclick="document.getElementById('mainPhoto').src = this.src"
                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E'"
                            />
                        @endforeach
                    </div>

                    <!-- Upload More Photos -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        <button 
                            type="button"
                            onclick="document.getElementById('addPhotoInput').click()"
                            class="text-blue-600 hover:text-blue-900 flex items-center gap-2 text-sm font-medium"
                        >
                            <i class="fas fa-plus"></i> Tambah Foto
                        </button>
                        <input 
                            type="file" 
                            id="addPhotoInput" 
                            accept="image/*" 
                            style="display: none;"
                            onchange="uploadPhoto(event, {{ $inventaris->id }})"
                        />
                    </div>
                @else
                    <div class="bg-gray-100 h-96 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Tidak ada foto</p>
                            <button 
                                type="button"
                                onclick="document.getElementById('addPhotoInput').click()"
                                class="text-blue-600 hover:text-blue-900 mt-2 text-sm font-medium"
                            >
                                Tambah Foto Pertama
                            </button>
                            <input 
                                type="file" 
                                id="addPhotoInput" 
                                accept="image/*" 
                                style="display: none;"
                                onchange="uploadPhoto(event, {{ $inventaris->id }})"
                            />
                        </div>
                    </div>
                @endif
            </div>

            <!-- Informasi Barang -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Informasi Barang</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Kategori</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->kategori_barang }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Merek</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->merek ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Model</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->model ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Nomor Seri</p>
                        <p class="font-semibold text-gray-900 font-mono">{{ $inventaris->nomor_seri ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Pengadaan</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->tanggal_pengadaan->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Harga Beli</p>
                        <p class="font-semibold text-gray-900">
                            @if($inventaris->harga_beli)
                                Rp {{ number_format($inventaris->harga_beli, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jumlah</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->jumlah }} {{ $inventaris->satuan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Supplier</p>
                        <p class="font-semibold text-gray-900">{{ $inventaris->supplier ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Sumber Pengadaan</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($inventaris->sumber_pengadaan ?? '-') }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Barang -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Status Barang</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="border rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-2">Posisi</p>
                        <p class="text-lg font-bold text-gray-900 mb-2">{{ ucfirst($inventaris->posisi) }}</p>
                        <a href="{{ route('admin.inventaris.edit-posisi', $inventaris->id) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            Ubah Posisi
                        </a>
                    </div>
                    <div class="border rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-2">Kondisi</p>
                        @php
                            $kondisiColors = [
                                'baru' => 'text-green-600 bg-green-50',
                                'baik' => 'text-blue-600 bg-blue-50',
                                'perbaikan' => 'text-yellow-600 bg-yellow-50',
                                'rusak_ringan' => 'text-orange-600 bg-orange-50',
                                'rusak_berat' => 'text-red-600 bg-red-50',
                            ];
                        @endphp
                        <p class="text-lg font-bold mb-2 px-3 py-1 rounded {{ $kondisiColors[$inventaris->kondisi] ?? 'text-gray-600 bg-gray-50' }}">
                            {{ ucfirst(str_replace('_', ' ', $inventaris->kondisi)) }}
                        </p>
                        <a href="{{ route('admin.inventaris.edit-kondisi', $inventaris->id) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            Ubah Kondisi
                        </a>
                    </div>
                    <div class="border rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-2">Status</p>
                        <span class="text-lg font-bold px-3 py-1 rounded {{ $inventaris->status === 'aktif' ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50' }}">
                            {{ ucfirst($inventaris->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            @if($inventaris->catatan)
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Catatan</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $inventaris->catatan }}</p>
                </div>
            @endif

            <!-- Riwayat Perubahan -->
            @if($inventaris->movements->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Riwayat Perubahan</h2>
                    <div class="space-y-4">
                        @foreach($inventaris->movements->sortByDesc('waktu_perubahan') as $movement)
                            <div class="border-l-4 {{ $movement->tipe_perubahan === 'posisi_change' ? 'border-blue-500' : 'border-orange-500' }} pl-4 py-2">
                                <p class="text-sm font-semibold text-gray-900">
                                    @if($movement->tipe_perubahan === 'posisi_change')
                                        Perubahan Posisi
                                    @else
                                        Perubahan Kondisi
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $movement->dari }} → {{ $movement->ke }}
                                </p>
                                @if($movement->alasan)
                                    <p class="text-sm text-gray-600 italic">Alasan: {{ $movement->alasan }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">
                                    Diubah oleh: <strong>{{ $movement->user->name }}</strong> pada {{ $movement->waktu_perubahan->format('d M Y H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Info Card -->
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Tambahan</h3>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Penanggung Jawab</p>
                    @if($inventaris->penanggungJawab)
                        <p class="font-semibold text-gray-900">{{ $inventaris->penanggungJawab->name }}</p>
                        <p class="text-xs text-gray-600">{{ ucfirst($inventaris->penanggungJawab->role) }}</p>
                    @else
                        <p class="text-gray-500">-</p>
                    @endif
                </div>

                <div class="mb-4 pb-4 border-b">
                    <p class="text-sm text-gray-600">Dibuat pada</p>
                    <p class="font-semibold text-gray-900">{{ $inventaris->created_at->format('d M Y H:i') }}</p>
                </div>

                <div class="mb-4 pb-4 border-b">
                    <p class="text-sm text-gray-600">Terakhir diperbarui</p>
                    <p class="font-semibold text-gray-900">{{ $inventaris->updated_at->format('d M Y H:i') }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.inventaris.edit', $inventaris->id) }}" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-center text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <button 
                        onclick="confirmDelete({{ $inventaris->id }})" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium"
                    >
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
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

function uploadPhoto(event, itemId) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5242880) {
        alert('Ukuran file terlalu besar. Maksimal 5MB.');
        return;
    }

    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`{{ route('admin.inventaris.upload-photo', ':id') }}`.replace(':id', itemId), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Foto berhasil diupload!');
            location.reload();
        } else {
            alert('Gagal mengupload foto: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupload foto');
    });
}
</script>
@endsection
