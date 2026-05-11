@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('trainer.payment-requests.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Payment Request</h1>
            <p class="text-gray-600">Ajukan payment request honor mengajar</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <p class="font-semibold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Mohon perbaiki kesalahan berikut:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @if($availableClasses->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada kelas yang tersedia</h3>
        <p class="text-gray-500 mb-4">
            Tidak ada kelas yang bisa di-request untuk payment.<br>
            Kelas harus merupakan kelas yang Anda ajar, berstatus <strong>selesai (done)</strong>, file nilai sudah <strong>disetujui admin</strong>, honor trainer sudah <strong>diisi Akademik/Admin</strong>, dan belum pernah di-request sebelumnya.
        </p>
        <a href="{{ route('trainer.classes.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i class="fas fa-chalkboard-teacher"></i>
            Lihat Kelas Saya
        </a>
    </div>
    @else
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('trainer.payment-requests.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Cara kerja:</strong><br>
                            1. Pilih kelas training yang memang Anda ajar dan sudah selesai<br>
                            1a. Pastikan file nilai siswa sudah diupload dan disetujui admin<br>
                            1b. Pastikan Akademik/Admin sudah mengisi honor trainer pada detail kelas<br>
                            2. Nominal honor akan otomatis diambil dari honor yang sudah diisi Akademik/Admin<br>
                            3. Payment request akan diajukan ke <strong>Admin Alfa Bank</strong> untuk approval<br>
                            4. Setelah disetujui Admin, akan dilanjutkan ke <strong>Finance</strong> untuk proses pencairan dana
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Select Class -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Kelas <span class="text-red-500">*</span>
                    </label>
                    <select name="class_id" 
                            id="classSelect"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition @error('class_id') border-red-500 @enderror"
                            required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($availableClasses as $class)
                        <option value="{{ $class->id }}" 
                                data-honor="{{ $class->trainer_honor }}"
                                data-name="{{ $class->name }}"
                                data-instansi="{{ $class->instansi }}"
                                data-kategori="{{ $class->kategori->nama_kategori ?? '-' }}"
                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->kategori->nama_kategori ?? 'Umum' }} 
                            ({{ $class->instansi }})
                        </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>Hanya kelas yang Anda ajar, status done, file nilai disetujui admin, honor sudah diisi Akademik/Admin, dan belum pernah di-request yang ditampilkan
                    </p>
                </div>

                <!-- Class Preview -->
                <div id="classPreview" class="hidden">
                    <div class="p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <h3 class="font-semibold text-gray-900 mb-3">Detail Kelas:</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Nama Kelas:</span>
                                <p class="font-medium text-gray-900" id="previewName">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Kategori:</span>
                                <p class="font-medium text-gray-900" id="previewKategori">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Instansi:</span>
                                <p class="font-medium text-gray-900" id="previewInstansi">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Honor Trainer:</span>
                                <p class="font-semibold text-green-600 text-lg" id="previewHonor">Rp 0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description/Notes -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea name="description" 
                              rows="4"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition @error('description') border-red-500 @enderror"
                              placeholder="Tambahkan catatan atau keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Payment Request
                    </button>
                    <a href="{{ route('trainer.payment-requests.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>

@if(!$availableClasses->isEmpty())
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classSelect');
    const classPreview = document.getElementById('classPreview');
    
    classSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const honor = selectedOption.getAttribute('data-honor');
            const name = selectedOption.getAttribute('data-name');
            const instansi = selectedOption.getAttribute('data-instansi');
            const kategori = selectedOption.getAttribute('data-kategori');
            
            document.getElementById('previewName').textContent = name;
            document.getElementById('previewKategori').textContent = kategori;
            document.getElementById('previewInstansi').textContent = instansi;
            document.getElementById('previewHonor').textContent = 'Rp ' + parseInt(honor).toLocaleString('id-ID');
            
            classPreview.classList.remove('hidden');
        } else {
            classPreview.classList.add('hidden');
        }
    });
    
    // Trigger preview if there's old value
    if (classSelect.value) {
        classSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endif
@endsection
