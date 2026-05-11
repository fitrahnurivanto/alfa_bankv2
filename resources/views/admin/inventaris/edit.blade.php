@extends('layouts.app')

@section('title', 'Edit Barang - ' . $inventaris->nama_barang)

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
        <h1 class="text-3xl font-bold text-gray-900">Edit Barang</h1>
        <p class="text-gray-600 mt-1">Perbarui informasi barang: {{ $inventaris->nama_barang }}</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl">
        <form action="{{ route('admin.inventaris.update', $inventaris->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Barang (Read-only) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kode Barang (Auto-Generated)
                    </label>
                    <input 
                        type="text" 
                        value="{{ $inventaris->kode_barang }}"
                        disabled
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-mono"
                    />
                </div>

                <!-- Nama Barang -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama_barang" 
                        value="{{ old('nama_barang', $inventaris->nama_barang) }}"
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
                        <option value="elektronik" {{ old('kategori_barang', $inventaris->kategori_barang) === 'elektronik' ? 'selected' : '' }}>Elektronik</option>
                        <option value="furniture" {{ old('kategori_barang', $inventaris->kategori_barang) === 'furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="alat_tulis" {{ old('kategori_barang', $inventaris->kategori_barang) === 'alat_tulis' ? 'selected' : '' }}>Alat Tulis</option>
                        <option value="kendaraan" {{ old('kategori_barang', $inventaris->kategori_barang) === 'kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                        <option value="lainnya" {{ old('kategori_barang', $inventaris->kategori_barang) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                        value="{{ old('merek', $inventaris->merek) }}"
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
                        value="{{ old('model', $inventaris->model) }}"
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
                        value="{{ old('nomor_seri', $inventaris->nomor_seri) }}"
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
                        value="{{ old('tanggal_pengadaan', $inventaris->tanggal_pengadaan->format('Y-m-d')) }}"
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
                        value="{{ old('harga_beli', $inventaris->harga_beli) }}"
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
                        value="{{ old('jumlah', $inventaris->jumlah) }}"
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
                        <option value="pcs" {{ old('satuan', $inventaris->satuan) === 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="set" {{ old('satuan', $inventaris->satuan) === 'set' ? 'selected' : '' }}>Set</option>
                        <option value="unit" {{ old('satuan', $inventaris->satuan) === 'unit' ? 'selected' : '' }}>Unit</option>
                        <option value="box" {{ old('satuan', $inventaris->satuan) === 'box' ? 'selected' : '' }}>Box</option>
                        <option value="buah" {{ old('satuan', $inventaris->satuan) === 'buah' ? 'selected' : '' }}>Buah</option>
                    </select>
                    @error('satuan')
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
                        value="{{ old('supplier', $inventaris->supplier) }}"
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
                        <option value="beli" {{ old('sumber_pengadaan', $inventaris->sumber_pengadaan) === 'beli' ? 'selected' : '' }}>Beli</option>
                        <option value="hibah" {{ old('sumber_pengadaan', $inventaris->sumber_pengadaan) === 'hibah' ? 'selected' : '' }}>Hibah</option>
                        <option value="transfer" {{ old('sumber_pengadaan', $inventaris->sumber_pengadaan) === 'transfer' ? 'selected' : '' }}>Transfer</option>
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
                            <option value="{{ $user->id }}" {{ old('penanggung_jawab', $inventaris->penanggung_jawab) == $user->id ? 'selected' : '' }}>
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
                    >{{ old('catatan', $inventaris->catatan) }}</textarea>
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
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
