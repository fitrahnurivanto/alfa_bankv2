@extends('layouts.app')

@section('title', 'Tambah Inventaris Barang')

@section('content')
<div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-start gap-4">
        <a href="{{ route('admin.inventaris.index') }}" class="text-gray-900 hover:text-[#fe0000] text-2xl mt-1.5">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Tambah Inventaris Barang</h1>
            <p class="text-gray-800">Masukkan data barang baru ke dalam sistem inventaris</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-900 pb-3 mb-5 border-b border-gray-300 -mx-6 sm:-mx-8 px-6 sm:px-8">Informasi Barang</h2>
        <form action="{{ route('admin.inventaris.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Barang -->
                <div class="md:col-span-2">
                    <label class="block text-base text-gray-900 mb-1.5">
                        Nama Barang <span class="text-[#fe0000]">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama_barang" 
                        value="{{ old('nama_barang') }}"
                        placeholder="Masukkan nama barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('nama_barang') border-red-500 @enderror"
                    />
                    @error('nama_barang')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Kategori Barang <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="kategori_barang" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('kategori_barang') border-red-500 @enderror"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Merek
                    </label>
                    <input 
                        type="text" 
                        name="merek" 
                        value="{{ old('merek') }}"
                        placeholder="Masukkan merek barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    />
                </div>

                <!-- Model -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Model
                    </label>
                    <input 
                        type="text" 
                        name="model" 
                        value="{{ old('model') }}"
                        placeholder="Masukkan model barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    />
                </div>

                <!-- Nomor Seri -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Nomor Seri
                    </label>
                    <input 
                        type="text" 
                        name="nomor_seri" 
                        value="{{ old('nomor_seri') }}"
                        placeholder="Masukkan nomor seri..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    />
                </div>

                <!-- Tanggal Pengadaan -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Tanggal Pengadaan <span class="text-[#fe0000]">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="tanggal_pengadaan" 
                        value="{{ old('tanggal_pengadaan') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('tanggal_pengadaan') border-red-500 @enderror"
                    />
                    @error('tanggal_pengadaan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga Beli -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Harga Beli
                    </label>
                    <input 
                        type="number" 
                        name="harga_beli" 
                        step="0.01"
                        value="{{ old('harga_beli') }}"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    />
                </div>

                <!-- Jumlah -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Jumlah <span class="text-[#fe0000]">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="jumlah" 
                        value="{{ old('jumlah', 1) }}"
                        min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('jumlah') border-red-500 @enderror"
                    />
                    @error('jumlah')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Satuan -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Satuan <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="satuan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('satuan') border-red-500 @enderror"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Posisi <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="posisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('posisi') border-red-500 @enderror"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Kondisi <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="kondisi" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('kondisi') border-red-500 @enderror"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Status <span class="text-[#fe0000]">*</span>
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('status') border-red-500 @enderror"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Supplier
                    </label>
                    <input 
                        type="text" 
                        name="supplier" 
                        value="{{ old('supplier') }}"
                        placeholder="Masukkan nama supplier..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    />
                </div>

                <!-- Sumber Pengadaan -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Sumber Pengadaan
                    </label>
                    <select 
                        name="sumber_pengadaan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    >
                        <option value="">-- Pilih Sumber --</option>
                        <option value="beli" {{ old('sumber_pengadaan') === 'beli' ? 'selected' : '' }}>Beli</option>
                        <option value="hibah" {{ old('sumber_pengadaan') === 'hibah' ? 'selected' : '' }}>Hibah</option>
                        <option value="transfer" {{ old('sumber_pengadaan') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <!-- Penanggung Jawab -->
                <div>
                    <label class="block text-base text-gray-900 mb-1.5">
                        Penanggung Jawab
                    </label>
                    <select 
                        name="penanggung_jawab" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
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
                    <label class="block text-base text-gray-900 mb-1.5">
                        Catatan
                    </label>
                    <textarea 
                        name="catatan" 
                        rows="4"
                        placeholder="Catatan tambahan tentang barang..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000]"
                    >{{ old('catatan') }}</textarea>
                </div>

                <!-- Upload Foto -->
                <div class="md:col-span-2">
                    <label class="block text-base text-gray-900 mb-1.5">
                        Upload Foto Barang (Optional - Max 5MB per foto)
                    </label>
                    <div class="border border-gray-700 rounded-lg p-10 text-center cursor-pointer hover:border-[#fe0000] hover:bg-[#fed0d0]/30 transition"
                         onclick="document.getElementById('photos').click()">
                        <i class="fas fa-cloud-arrow-up text-6xl text-gray-500 mb-3"></i>
                        <p class="text-lg text-gray-600">Klik untuk memilih foto atau drag & drop di sini</p>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
                <a href="{{ route('admin.inventaris.index') }}" class="px-6 py-3 text-center bg-white border border-[#fe0000] rounded-lg text-[#fe0000] font-semibold shadow hover:bg-[#fed0d0]/40 transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-[#fe0000] hover:bg-[#cc0000] text-white px-6 py-3 rounded-lg font-semibold shadow-[0_3px_6px_rgba(0,0,0,0.25)] transition"
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