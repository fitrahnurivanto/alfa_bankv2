@extends('layouts.app')

@section('title', 'Tambah Inventaris Barang')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.inventaris.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Tambah Inventaris Barang</h1>
        <p class="text-gray-600 mt-1">Masukkan data barang baru ke dalam sistem inventaris</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl">
        <form action="{{ route('admin.inventaris.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Barang -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama_barang" 
                        value="{{ old('nama_barang') }}"
                        placeholder="Masukkan nama barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama_barang') border-red-500 @enderror"
                    />
                    @error('nama_barang')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kategori Barang <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="kategori_barang" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('kategori_barang') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Kategori --</option>
                        <option value="elektronik" {{ old('kategori_barang') === 'elektronik' ? 'selected' : '' }}>Elektronik</option>
                        <option value="furniture" {{ old('kategori_barang') === 'furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="alat_tulis" {{ old('kategori_barang') === 'alat_tulis' ? 'selected' : '' }}>Alat Tulis</option>
                        <option value="kendaraan" {{ old('kategori_barang') === 'kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                        <option value="lainnya" {{ old('kategori_barang') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('kategori_barang')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merek -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Merek
                    </label>
                    <input 
                        type="text" 
                        name="merek" 
                        value="{{ old('merek') }}"
                        placeholder="Masukkan merek barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Model -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Model
                    </label>
                    <input 
                        type="text" 
                        name="model" 
                        value="{{ old('model') }}"
                        placeholder="Masukkan model barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Nomor Seri -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nomor Seri
                    </label>
                    <input 
                        type="text" 
                        name="nomor_seri" 
                        value="{{ old('nomor_seri') }}"
                        placeholder="Masukkan nomor seri..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Tanggal Pengadaan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Pengadaan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="tanggal_pengadaan" 
                        value="{{ old('tanggal_pengadaan') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tanggal_pengadaan') border-red-500 @enderror"
                    />
                    @error('tanggal_pengadaan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga Beli -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Harga Beli
                    </label>
                    <input 
                        type="number" 
                        name="harga_beli" 
                        step="0.01"
                        value="{{ old('harga_beli') }}"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Jumlah -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="jumlah" 
                        value="{{ old('jumlah', 1) }}"
                        min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('jumlah') border-red-500 @enderror"
                    />
                    @error('jumlah')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Satuan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Satuan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="satuan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('satuan') border-red-500 @enderror"
                    >
                        <option value="pcs" {{ old('satuan') === 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="set" {{ old('satuan') === 'set' ? 'selected' : '' }}>Set</option>
                        <option value="unit" {{ old('satuan') === 'unit' ? 'selected' : '' }}>Unit</option>
                        <option value="box" {{ old('satuan') === 'box' ? 'selected' : '' }}>Box</option>
                        <option value="buah" {{ old('satuan') === 'buah' ? 'selected' : '' }}>Buah</option>
                    </select>
                    @error('satuan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Posisi -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Posisi <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="posisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('posisi') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Posisi --</option>
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

                <!-- Kondisi -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kondisi <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="kondisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('kondisi') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Kondisi --</option>
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

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
                    >
                        <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : true }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                        <option value="disposed" {{ old('status') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Supplier
                    </label>
                    <input 
                        type="text" 
                        name="supplier" 
                        value="{{ old('supplier') }}"
                        placeholder="Masukkan nama supplier..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Sumber Pengadaan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Sumber Pengadaan
                    </label>
                    <select 
                        name="sumber_pengadaan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">-- Pilih Sumber --</option>
                        <option value="beli" {{ old('sumber_pengadaan') === 'beli' ? 'selected' : '' }}>Beli</option>
                        <option value="hibah" {{ old('sumber_pengadaan') === 'hibah' ? 'selected' : '' }}>Hibah</option>
                        <option value="transfer" {{ old('sumber_pengadaan') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <!-- Penanggung Jawab -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Penanggung Jawab
                    </label>
                    <select 
                        name="penanggung_jawab" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">-- Pilih Penanggung Jawab --</option>
                        @foreach(\App\Models\User::where('role', '!=', 'client')->get() as $user)
                            <option value="{{ $user->id }}" {{ old('penanggung_jawab') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Catatan -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea 
                        name="catatan" 
                        rows="4"
                        placeholder="Catatan tambahan tentang barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >{{ old('catatan') }}</textarea>
                </div>

                <!-- Upload Foto -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Upload Foto Barang (Optional - Max 5MB per foto)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50"
                         onclick="document.getElementById('photos').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Klik untuk memilih foto atau drag & drop di sini</p>
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, GIF, WebP - Maksimal 5MB per foto</p>
                    </div>
                    <input 
                        type="file" 
                        id="photos" 
                        name="photos[]" 
                        multiple 
                        accept="image/*"
                        style="display: none;"
                        onchange="previewPhotos(event)"
                    />
                    @error('photos.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Preview Foto -->
                    <div id="photoPreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.inventaris.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
                >
                    Simpan Barang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewPhotos(event) {
    const files = event.target.files;
    const previewDiv = document.getElementById('photoPreview');
    previewDiv.innerHTML = '';

    for (let file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative bg-gray-100 rounded-lg overflow-hidden h-32';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                <div class="absolute top-0 right-0 bg-red-500 text-white px-2 py-1 text-xs rounded-bl-lg">
                    ${(file.size / 1024 / 1024).toFixed(2)} MB
                </div>
            `;
            previewDiv.appendChild(div);
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
