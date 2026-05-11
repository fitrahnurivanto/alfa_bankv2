@extends('layouts.app')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 16px;
        color: #374151;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 10px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #7b2cbf;
        box-shadow: 0 0 0 1px #7b2cbf;
    }
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 8px 12px;
    }
    .select2-results__option--highlighted {
        background-color: #7b2cbf !important;
    }
</style>
@endpush

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.classes.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ isset($clas) ? 'Edit Kelas' : 'Tambah Kelas' }}
                    </h1>
                    <p class="text-gray-600">Lengkapi informasi kelas di bawah ini</p>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-red-800">
                        Terdapat {{ $errors->count() }} kesalahan pada form:
                    </h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="classForm" 
                  action="{{ isset($clas) ? route('admin.classes.update', $clas) : route('admin.classes.store') }}" 
                  method="POST">
                @csrf
                @if(isset($clas))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kategori -->
                    <div class="md:col-span-2">
                        <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select name="kategori_id" 
                                id="kategori_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('kategori_id') border-red-500 @enderror"
                                required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" 
                                        data-type="{{ $kategori->id == 1 ? 'corporate' : ($kategori->id == 2 ? 'reguler' : 'private') }}"
                                        {{ old('kategori_id', $clas->kategori_id ?? '') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih kategori untuk memfilter pelatihan yang tersedia</p>
                        @error('kategori_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Pelatihan -->
                    <div class="md:col-span-2">
                        <label for="training_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Pelatihan/Skema Sertirfikasi <span class="text-red-500">*</span>
                            @if(isset($trainings))
                                <span class="text-xs text-gray-500">({{ $trainings->count() }} pelatihan tersedia)</span>
                            @endif
                        </label>
                        <select name="training_id" 
                                id="training_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('training_id') border-red-500 @enderror"
                                required
                                disabled>
                            <option value="">-- Pilih Kategori Terlebih Dahulu --</option>
                            @if(isset($trainings) && $trainings->count() > 0)
                                @foreach($trainings as $training)
                                    <option value="{{ $training->id }}" 
                                            data-type="{{ $training->type ?? 'reguler' }}"
                                            {{ old('training_id', $clas->training_id ?? '') == $training->id ? 'selected' : '' }}>
                                        {{ $training->name }}
                                    </option>
                                @endforeach
                                <!-- Option untuk tambah pelatihan baru -->
                                <option value="add_new" class="text-green-600 font-semibold" data-type="add_new">
                                    ➕ Tambah Pelatihan Baru
                                </option>
                            @endif
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle"></i>
                            Untuk corporate training, Anda bisa menambahkan pelatihan baru dengan cepat
                        </p>
                        @error('training_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Kelas -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kelas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $clas->name ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('name') border-red-500 @enderror"
                               placeholder="Contoh: Laravel Advanced Development"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <input type="hidden" name="private_student_name" id="private_student_name" value="{{ old('private_student_name', $clas->private_student_name ?? '') }}">

                    <!-- Instansi (hanya muncul untuk Corporate Training) -->
                    <div class="md:col-span-2" id="instansi-wrapper" style="display: none;">
                        <label for="instansi" class="block text-sm font-medium text-gray-700 mb-2">
                            Instansi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="instansi" 
                               id="instansi" 
                               value="{{ old('instansi', $clas->instansi ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('instansi') border-red-500 @enderror"
                               placeholder="Contoh: PT. ABC atau Universitas XYZ">
                        @error('instansi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat (hanya muncul untuk Corporate Training) -->
                    <div class="md:col-span-2" id="alamat-wrapper" style="display: none;">
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alamat" 
                                  id="alamat" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('alamat') border-red-500 @enderror"
                                  placeholder="Contoh: Jl. Sudirman No. 123, Jakarta Pusat">{{ old('alamat', $clas->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No PIC (hanya muncul untuk Corporate Training) -->
                    <div class="md:col-span-1" id="no-pic-wrapper" style="display: none;">
                        <label for="no_pic" class="block text-sm font-medium text-gray-700 mb-2">
                            No. PIC <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="no_pic" 
                               id="no_pic" 
                               value="{{ old('no_pic', $clas->no_pic ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('no_pic') border-red-500 @enderror"
                               placeholder="Contoh: John Doe">
                        @error('no_pic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No Kontak (hanya muncul untuk Corporate Training) -->
                    <div class="md:col-span-1" id="no-kontak-wrapper" style="display: none;">
                        <label for="no_kontak" class="block text-sm font-medium text-gray-700 mb-2">
                            No. Kontak <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="no_kontak" 
                               id="no_kontak" 
                               value="{{ old('no_kontak', $clas->no_kontak ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('no_kontak') border-red-500 @enderror"
                               placeholder="Contoh: 081234567890">
                        @error('no_kontak')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type Pembayaran (muncul untuk Corporate Training & Private) -->
                    <div class="md:col-span-2" id="payment-type-wrapper" style="display: none;">
                        <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_type" 
                                id="payment_type" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('payment_type') border-red-500 @enderror">
                            <option value="full" {{ old('payment_type', $clas->payment_type ?? 'full') == 'full' ? 'selected' : '' }}>Full Payment</option>
                            <option value="termin_2x" {{ old('payment_type', $clas->payment_type ?? 'full') == 'termin_2x' ? 'selected' : '' }}>Termin 2x</option>
                        </select>
                        @error('payment_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pembayaran DP / Termin 1 (hanya muncul untuk Termin 2x) -->
                    <div class="md:col-span-2" id="paid-amount-wrapper" style="display: none;">
                        <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Pembayaran DP (Termin 1) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" 
                                   name="paid_amount_display" 
                                   id="paid_amount_display" 
                                   value="{{ old('paid_amount', $clas->paid_amount ?? '') ? number_format(old('paid_amount', $clas->paid_amount ?? ''), 0, ',', '.') : '' }}"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('paid_amount') border-red-500 @enderror"
                                   placeholder="0">
                            <input type="hidden" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $clas->paid_amount ?? '') }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>Masukkan jumlah pembayaran pertama (DP). Pelunasan bisa diinput di detail kelas setelah approve.
                        </p>
                        @error('paid_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sertifikasi BNSP -->
                  

                    <!-- Jenis Reguler (hanya muncul untuk Regular) -->
                    <div class="md:col-span-2" id="jenis-reguler-wrapper" style="display: none;">
                        <label for="jenis_reguler" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_reguler" 
                                id="jenis_reguler" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('jenis_reguler') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="mandiri" {{ old('jenis_reguler', $clas->jenis_reguler ?? '') == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                            <option value="lain_lain" {{ old('jenis_reguler', $clas->jenis_reguler ?? '') == 'lain_lain' ? 'selected' : '' }}>Lain-lain</option>
                        </select>
                        @error('jenis_reguler')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pilih Trainer dari Database -->
                    {{-- <div class="md:col-span-2">
                        <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Trainer Utama
                        </label>
                        <select name="trainer_id" 
                                id="trainer_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('trainer_id') border-red-500 @enderror">
                            <option value="">-- Pilih Trainer (Opsional) --</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" 
                                        {{ old('trainer_id', $clas->trainer_id ?? '') == $trainer->id ? 'selected' : '' }}
                                        {{ in_array($trainer->id, $usedTrainerIds) ? 'disabled' : '' }}>
                                    {{ $trainer->name }}{{ $trainer->phone ? ' - ' . $trainer->phone : '' }}
                                    {{ in_array($trainer->id, $usedTrainerIds) ? ' (Sudah digunakan)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih trainer utama dari database trainer</p>
                        @error('trainer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div> --}}

                    <!-- Trainer Section -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Pilih / Tambah Trainer <span class="text-red-500">*</span>
                        </label>

                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div id="trainers-container" class="space-y-4">
                                @php
                                    $oldTrainerIds = old('trainer_ids', []);
                                    $oldNewTrainers = old('new_trainers', []);
                                    $hasOldData = !empty($oldTrainerIds) || !empty($oldNewTrainers);
                                @endphp
                                
                                @if($hasOldData)
                                    {{-- Display selected trainers from old data --}}
                                    @foreach($oldTrainerIds as $index => $trainerId)
                                    <div class="trainer-dropdown-item p-3 bg-white border-2 border-purple-200 rounded-lg">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                    <i class="fas fa-user-circle text-purple-600 mr-1"></i> Pilih Trainer yang Sudah Terdaftar
                                                </label>
                                                <select name="trainer_ids[]" 
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] bg-white">
                                                    <option value="">-- Pilih Trainer --</option>
                                                    @foreach($trainers as $trainer)
                                                        <option value="{{ $trainer->id }}" {{ $trainerId == $trainer->id ? 'selected' : '' }}>
                                                            {{ $trainer->name }} - {{ $trainer->email }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                    <i class="fas fa-user-plus text-green-600 mr-1"></i> Atau Ketik Nama Trainer Baru
                                                </label>
                                                <input type="text" 
                                                       name="new_trainers[]" 
                                                       value=""
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]"
                                                       placeholder="Contoh: Ivan Pratama (auto-create account)">
                                            </div>
                                        </div>
                                        @if($index > 0)
                                        <button type="button" 
                                                onclick="removeTrainerRow(this)"
                                                class="mt-3 w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                            <i class="fas fa-trash mr-2"></i>Hapus Trainer Ini
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                    
                                    {{-- Display new trainers from old data --}}
                                    @foreach($oldNewTrainers as $index => $trainerName)
                                        @if(!empty($trainerName))
                                        <div class="trainer-dropdown-item p-3 bg-white border-2 border-purple-200 rounded-lg">
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                        <i class="fas fa-user-circle text-purple-600 mr-1"></i> Pilih Trainer yang Sudah Terdaftar
                                                    </label>
                                                    <select name="trainer_ids[]" 
                                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] bg-white">
                                                        <option value="">-- Pilih Trainer --</option>
                                                        @foreach($trainers as $trainer)
                                                            <option value="{{ $trainer->id }}">
                                                                {{ $trainer->name }} - {{ $trainer->email }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                        <i class="fas fa-user-plus text-green-600 mr-1"></i> Atau Ketik Nama Trainer Baru
                                                    </label>
                                                    <input type="text" 
                                                           name="new_trainers[]" 
                                                           value="{{ $trainerName }}"
                                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]"
                                                           placeholder="Contoh: Ivan Pratama (auto-create account)">
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    onclick="removeTrainerRow(this)"
                                                    class="mt-3 w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                                <i class="fas fa-trash mr-2"></i>Hapus Trainer Ini
                                            </button>
                                        </div>
                                        @endif
                                    @endforeach
                                @else
                                    {{-- Default first row --}}
                                    <div class="trainer-dropdown-item p-3 bg-white border-2 border-purple-200 rounded-lg">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                    <i class="fas fa-user-circle text-purple-600 mr-1"></i> Pilih Trainer yang Sudah Terdaftar
                                                </label>
                                                <select name="trainer_ids[]" 
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] bg-white">
                                                    <option value="">-- Pilih Trainer --</option>
                                                    @forelse($trainers as $trainer)
                                                        <option value="{{ $trainer->id }}">
                                                            {{ $trainer->name }} - {{ $trainer->email }}
                                                        </option>
                                                    @empty
                                                        <option disabled>Belum ada trainer terdaftar</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                                    <i class="fas fa-user-plus text-green-600 mr-1"></i> Atau Ketik Nama Trainer Baru
                                                </label>
                                                <input type="text" 
                                                       name="new_trainers[]" 
                                                       value=""
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]"
                                                       placeholder="Contoh: Ivan Pratama (auto-create account)">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <button type="button" 
                                    onclick="addTrainerRow()"
                                    class="mt-3 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-plus mr-2"></i>Tambah Trainer Lainnya
                            </button>
                            
                            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-xs text-green-800">
                                    <i class="fas fa-info-circle mr-1"></i> <strong>Cara Pakai:</strong><br>
                                    • Pilih dari dropdown untuk trainer yang sudah terdaftar<br>
                                    • Atau isi kolom "ketik nama" untuk membuat akun trainer baru otomatis<br>
                                    • Email auto-generated dari nama (contoh: "Ivan Pratama" → ivanpratama@gmail.com)<br>
                                    • Password default: <strong>password</strong>
                                </p>
                            </div>
                        </div>
                        
                        @error('trainer_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('new_trainers')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pendapatan/Nilai Kelas / Nilai Kontrak -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="price-label">Pendapatan/Nilai Kelas</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" 
                                   name="price_display" 
                                   id="price_display" 
                                   value="{{ old('price', $clas->price ?? '') ? number_format(old('price', $clas->price ?? ''), 0, ',', '.') : '' }}"
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('price') border-red-500 @enderror"
                                   placeholder="5.000.000"
                                   >
                            <input type="hidden" name="price" id="price" value="{{ old('price', $clas->price ?? '') }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Boleh dikosongkan dulu, isi saat kelas akan diselesaikan. Contoh: 5.000.000</p>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Siswa (tidak muncul untuk Private) -->
                    <div id="amount-wrapper">
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Siswa <span class="text-gray-500 text-xs">(Opsional, bisa diisi di detail kelas)</span>
                        </label>
                        <input type="number" 
                               name="amount" 
                               id="amount" 
                               value="{{ old('amount', $clas->amount ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('amount') border-red-500 @enderror"
                               placeholder="0"
                               min="0">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Pertemuan -->
                    <div>
                        <label for="meet" class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Pertemuan (hari) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="meet" 
                               id="meet" 
                               value="{{ old('meet', $clas->meet ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('meet') border-red-500 @enderror"
                               placeholder="0"
                               min="1"
                               required>
                        @error('meet')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                            JPL dalam satu pertemuan <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="duration" 
                               id="duration" 
                               value="{{ old('duration', $clas->duration ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('duration') border-red-500 @enderror"
                               placeholder="0"
                               min="1"
                               required>
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Metode -->
                    <div>
                        <label for="method" class="block text-sm font-medium text-gray-700 mb-2">
                            Metode Pembelajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="method" 
                                id="method" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('method') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Metode</option>
                            <option value="online" {{ old('method', $clas->method ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ old('method', $clas->method ?? '') == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="mix" {{ old('method', $clas->method ?? '') == 'mix' ? 'selected' : '' }}>Mix (Online & Offline)</option>
                        </select>
                        @error('method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               value="{{ old('start_date', isset($clas) ? $clas->start_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('start_date') border-red-500 @enderror"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               value="{{ old('end_date', isset($clas) ? $clas->end_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('end_date') border-red-500 @enderror"
                               required>
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jam Mulai -->
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Jam Mulai
                        </label>
                        <input type="time" 
                               name="start_time" 
                               id="start_time" 
                               value="{{ old('start_time', isset($clas) && $clas->start_time ? $clas->start_time->format('H:i') : '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('start_time') border-red-500 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jam Selesai -->
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Jam Selesai
                        </label>
                        <input type="time" 
                               name="end_time" 
                               id="end_time" 
                               value="{{ old('end_time', isset($clas) && $clas->end_time ? $clas->end_time->format('H:i') : '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('end_time') border-red-500 @enderror">
                        @error('end_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] @error('description') border-red-500 @enderror"
                                  placeholder="Deskripsi kelas...">{{ old('description', $clas->description ?? '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                      <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="sertifikasi_bnsp" 
                                   id="sertifikasi_bnsp" 
                                   value="1"
                                   {{ old('sertifikasi_bnsp', $clas->sertifikasi_bnsp ?? false) ? 'checked' : '' }}
                                   class="w-4 h-4 text-[#7b2cbf] bg-gray-100 border-gray-300 rounded focus:ring-[#7b2cbf] focus:ring-2">
                            <label for="sertifikasi_bnsp" class="ml-2 text-sm font-medium text-gray-700">
                                Sertifikasi BNSP
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Centang jika kelas ini termasuk sertifikasi BNSP</p>
                        @error('sertifikasi_bnsp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Field BNSP Detail (tampil jika sertifikasi_bnsp dicentang) -->
                    <div id="bnsp-details" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                        <!-- Tanggal Sertifikasi -->
                        <div>
                            <label for="bnsp_tanggal_sertifikasi" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Sertifikasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="bnsp_tanggal_sertifikasi" 
                                   id="bnsp_tanggal_sertifikasi"
                                   value="{{ old('bnsp_tanggal_sertifikasi', $clas->bnsp_tanggal_sertifikasi ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#7b2cbf] focus:ring-[#7b2cbf] sm:text-sm">
                            @error('bnsp_tanggal_sertifikasi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bnsp_student_count" class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah Siswa Sertifikasi <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="bnsp_student_count"
                                   id="bnsp_student_count"
                                   min="1"
                                   value="{{ old('bnsp_student_count', $clas->bnsp_student_count ?? '') }}"
                                   placeholder="Contoh: 25"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#7b2cbf] focus:ring-[#7b2cbf] sm:text-sm">
                            @error('bnsp_student_count')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bnsp_fee_per_student" class="block text-sm font-medium text-gray-700 mb-2">
                                Biaya per Siswa <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="text"
                                       name="bnsp_fee_per_student_display"
                                       id="bnsp_fee_per_student_display"
                                       value="{{ old('bnsp_fee_per_student', $clas->bnsp_fee_per_student ?? '') ? number_format(old('bnsp_fee_per_student', $clas->bnsp_fee_per_student ?? ''), 0, ',', '.') : '' }}"
                                       placeholder="150.000"
                                       class="mt-1 block w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-[#7b2cbf] focus:ring-[#7b2cbf] sm:text-sm">
                                <input type="hidden" name="bnsp_fee_per_student" id="bnsp_fee_per_student" value="{{ old('bnsp_fee_per_student', $clas->bnsp_fee_per_student ?? '') }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Gunakan titik sebagai pemisah ribuan. Contoh: 150.000</p>
                            @error('bnsp_fee_per_student')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.classes.index') }}" 
                       class="flex-1 px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-center">
                        Batal
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-2.5 bg-gradient-to-r from-[#7b2cbf] to-[#9d4edd] text-white rounded-lg hover:shadow-lg transition">
                        {{ isset($clas) ? 'Update Kelas' : 'Simpan Kelas' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quick Add Training -->
<div id="quickAddTrainingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-xl">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-plus-circle mr-2"></i>Tambah Pelatihan Baru
            </h3>
            <p class="text-green-50 text-sm mt-1">Tambahkan pelatihan baru dengan cepat</p>
        </div>

        <!-- Body -->
        <form id="quickAddTrainingForm" class="p-6">
            <div class="mb-4">
                <label for="quick_training_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Pelatihan <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="quick_training_name" 
                       name="name"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: Public Speaking for Leaders"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    <i class="fas fa-info-circle"></i>
                    Kategori akan otomatis sesuai dengan pilihan kategori kelas
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-blue-700">
                    <i class="fas fa-lightbulb mr-1"></i>
                    <strong>Tips:</strong> Pelatihan akan langsung aktif dan bisa digunakan setelah dibuat.
                </p>
            </div>

            <!-- Alert Error -->
            <div id="quick_add_error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <span id="quick_add_error_message"></span>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeQuickAddModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="submit" 
                        id="quick_add_submit_btn"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-save mr-1"></i>Simpan & Gunakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle instansi field based on kategori selection
document.addEventListener('DOMContentLoaded', function() {
    const kategoriSelect = document.getElementById('kategori_id');
    const instansiWrapper = document.getElementById('instansi-wrapper');
    const instansiInput = document.getElementById('instansi');
    const alamatWrapper = document.getElementById('alamat-wrapper');
    const alamatInput = document.getElementById('alamat');
    const noPicWrapper = document.getElementById('no-pic-wrapper');
    const noPicInput = document.getElementById('no_pic');
    const noKontakWrapper = document.getElementById('no-kontak-wrapper');
    const noKontakInput = document.getElementById('no_kontak');
    const paymentTypeWrapper = document.getElementById('payment-type-wrapper');
    const paymentTypeInput = document.getElementById('payment_type');
    const paidAmountWrapper = document.getElementById('paid-amount-wrapper');
    const paidAmountDisplay = document.getElementById('paid_amount_display');
    const jenisRegulerWrapper = document.getElementById('jenis-reguler-wrapper');
    const jenisRegulerInput = document.getElementById('jenis_reguler');
    const priceLabel = document.getElementById('price-label');
    const amountWrapper = document.getElementById('amount-wrapper');
    const amountInput = document.getElementById('amount');
    const privateStudentInput = document.getElementById('private_student_name');
    
    function toggleFields() {
        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
        const kategoriText = selectedOption.text.toLowerCase();
        
        const isCorporate = kategoriText.includes('corporate');
        const isPrivate = kategoriText.includes('private');
        const useTerminFlow = isCorporate || isPrivate;

        // Toggle instansi khusus Corporate Training
        if (isCorporate) {
            instansiWrapper.style.display = 'block';
            instansiInput.required = true;
            alamatWrapper.style.display = 'block';
            alamatInput.required = true;
            noPicWrapper.style.display = 'block';
            noPicInput.required = true;
            noKontakWrapper.style.display = 'block';
            noKontakInput.required = true;
        } else {
            instansiWrapper.style.display = 'none';
            instansiInput.required = false;
            instansiInput.value = '';
            alamatWrapper.style.display = 'none';
            alamatInput.required = false;
            alamatInput.value = '';
            noPicWrapper.style.display = 'none';
            noPicInput.required = false;
            noPicInput.value = '';
            noKontakWrapper.style.display = 'none';
            noKontakInput.required = false;
            noKontakInput.value = '';
        }

        // Toggle payment type untuk Corporate + Private
        if (useTerminFlow) {
            paymentTypeWrapper.style.display = 'block';
            paymentTypeInput.required = true;
            priceLabel.textContent = isCorporate ? 'Nilai Kontrak' : 'Pendapatan/Nilai Kelas';
            togglePaidAmount();
        } else {
            paymentTypeWrapper.style.display = 'none';
            paymentTypeInput.required = false;
            paymentTypeInput.value = 'full';
            paidAmountWrapper.style.display = 'none';
            paidAmountDisplay.required = false;
            paidAmountDisplay.value = '';
            document.getElementById('paid_amount').value = '';
            priceLabel.textContent = 'Pendapatan/Nilai Kelas';
        }
        
        // Toggle jenis_reguler for Regular
        if (kategoriText.includes('regular') || kategoriText.includes('reguler')) {
            jenisRegulerWrapper.style.display = 'block';
            jenisRegulerInput.required = true;
        } else {
            jenisRegulerWrapper.style.display = 'none';
            jenisRegulerInput.required = false;
            jenisRegulerInput.value = '';
        }
        
        // Toggle amount for Private
        if (kategoriText.includes('private')) {
            amountWrapper.style.display = 'block';
            amountInput.required = false;
            amountInput.value = '1'; // Set default 1 untuk private
            privateStudentInput.required = false;
        } else {
            amountWrapper.style.display = 'block';
            amountInput.required = false;
            privateStudentInput.required = false;
            privateStudentInput.value = '';
        }
    }
    
    function togglePaidAmount() {
        const paymentType = paymentTypeInput.value;
        if (paymentType === 'termin_2x') {
            paidAmountWrapper.style.display = 'block';
            paidAmountDisplay.required = true;
        } else {
            paidAmountWrapper.style.display = 'none';
            paidAmountDisplay.required = false;
            paidAmountDisplay.value = '';
            document.getElementById('paid_amount').value = '';
        }
    }
    
    // Check on page load
    toggleFields();
    
    // Listen for changes
    kategoriSelect.addEventListener('change', toggleFields);
    paymentTypeInput.addEventListener('change', togglePaidAmount);
});

// Format Rupiah
function formatRupiah(angka, prefix = '') {
    if (!angka) return '';
    
    let number_string = angka.replace(/[^,\d]/g, '').toString();
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
}

function unformatRupiah(rupiah) {
    return rupiah.replace(/\./g, '').replace(/,/g, '');
}

document.addEventListener('DOMContentLoaded', function() {
    const priceDisplay = document.getElementById('price_display');
    const priceHidden = document.getElementById('price');
    const paidAmountDisplay = document.getElementById('paid_amount_display');
    const paidAmountHidden = document.getElementById('paid_amount');
    const bnspFeeDisplay = document.getElementById('bnsp_fee_per_student_display');
    const bnspFeeHidden = document.getElementById('bnsp_fee_per_student');
    
    // Format Price
    priceDisplay.addEventListener('keyup', function(e) {
        let value = this.value;
        this.value = formatRupiah(value);
        priceHidden.value = unformatRupiah(value);
    });
    
    // Format Paid Amount
    if (paidAmountDisplay) {
        paidAmountDisplay.addEventListener('keyup', function(e) {
            let value = this.value;
            this.value = formatRupiah(value);
            paidAmountHidden.value = unformatRupiah(value);
        });
    }

    // Format BNSP Fee per Student
    if (bnspFeeDisplay && bnspFeeHidden) {
        bnspFeeDisplay.addEventListener('keyup', function(e) {
            let value = this.value;
            this.value = formatRupiah(value);
            bnspFeeHidden.value = unformatRupiah(value);
        });
    }
});

function addTrainerRow() {
    const container = document.getElementById('trainers-container');
    const trainerItem = document.createElement('div');
    trainerItem.className = 'trainer-dropdown-item p-3 bg-white border-2 border-purple-200 rounded-lg';
    
    const trainersOptions = `
        <option value="">-- Pilih Trainer --</option>
        @foreach($trainers as $trainer)
            <option value="{{ $trainer->id }}">{{ $trainer->name }} - {{ $trainer->email }}</option>
        @endforeach
    `;
    
    trainerItem.innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    <i class="fas fa-user-circle text-purple-600 mr-1"></i> Pilih Trainer yang Sudah Terdaftar
                </label>
                <select name="trainer_ids[]" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] bg-white">
                    ${trainersOptions}
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    <i class="fas fa-user-plus text-green-600 mr-1"></i> Atau Ketik Nama Trainer Baru
                </label>
                <input type="text" 
                       name="new_trainers[]" 
                       value=""
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]"
                       placeholder="Contoh: Ivan Pratama (auto-create account)">
            </div>
        </div>
        <button type="button" 
                onclick="removeTrainerRow(this)"
                class="mt-3 w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
            <i class="fas fa-trash mr-2"></i>Hapus Trainer Ini
        </button>
    `;
    container.appendChild(trainerItem);
}

function removeTrainerRow(button) {
    const trainerItem = button.closest('.trainer-dropdown-item');
    trainerItem.remove();
}

// Toggle BNSP Details
document.addEventListener('DOMContentLoaded', function() {
    const bnspCheckbox = document.getElementById('sertifikasi_bnsp');
    const bnspDetails = document.getElementById('bnsp-details');
    
    function toggleBnspDetails() {
        if (bnspCheckbox.checked) {
            bnspDetails.style.display = 'grid';
        } else {
            bnspDetails.style.display = 'none';
        }
    }
    
    // Initial check
    toggleBnspDetails();
    
    // Listen for changes
    bnspCheckbox.addEventListener('change', toggleBnspDetails);
});

// Check nama kelas yang duplikat
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const nameError = document.createElement('p');
    nameError.className = 'mt-1 text-sm text-yellow-600 hidden';
    nameError.id = 'name-check-message';
    nameInput.parentElement.appendChild(nameError);
    
    let checkTimeout;
    
    nameInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        const value = this.value.trim();
        
        if (value.length < 3) {
            nameError.classList.add('hidden');
            return;
        }
        
        // Debounce untuk menghindari terlalu banyak request
        checkTimeout = setTimeout(function() {
            // Simple slug generation (sesuai dengan Str::slug di Laravel)
            const slug = value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            fetch(`{{ route('admin.classes.index') }}?check_name=${encodeURIComponent(slug)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    nameInput.classList.add('border-yellow-500');
                    nameError.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Nama kelas ini sudah digunakan. Silakan gunakan nama yang berbeda.';
                    nameError.classList.remove('hidden');
                } else {
                    nameInput.classList.remove('border-yellow-500');
                    nameError.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error checking name:', error);
            });
        }, 500);
    });
});

// Filter training berdasarkan kategori yang dipilih
function filterTrainingByKategori() {
    const kategoriSelect = document.getElementById('kategori_id');
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    const selectedType = selectedOption.getAttribute('data-type');
    const trainingSelect = document.getElementById('training_id');
    const allOptions = trainingSelect.querySelectorAll('option');
    
    // Reset dropdown jika tidak ada kategori
    if (!kategoriSelect.value || !selectedType) {
        // Jika belum pilih kategori, disable training dropdown
        trainingSelect.disabled = true;
        allOptions.forEach(option => {
            if (option.value === '') {
                option.textContent = '-- Pilih Kategori Terlebih Dahulu --';
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        return;
    }
    
    // Enable dropdown dan filter options berdasarkan type
    trainingSelect.disabled = false;
    let visibleCount = 0;
    
    const kategoriName = kategoriSelect.options[kategoriSelect.selectedIndex].text;
    
    allOptions.forEach(option => {
        if (option.value === '') {
            option.textContent = `-- Pilih Pelatihan ${kategoriName} --`;
            option.style.display = 'block';
        } else if (option.value === 'add_new') {
            // Option "Tambah Pelatihan Baru" selalu tampil
            option.style.display = 'block';
        } else {
            const optionType = option.getAttribute('data-type');
            if (optionType === selectedType) {
                option.style.display = 'block';
                visibleCount++;
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Update placeholder dengan jumlah
    allOptions[0].textContent = `-- Pilih Pelatihan (${visibleCount} tersedia) --`;
    
    // Trigger Select2 update jika sudah diinisialisasi
    if (typeof $.fn.select2 !== 'undefined' && $('#training_id').hasClass('select2-hidden-accessible')) {
        $('#training_id').select2('destroy');
        $('#training_id').select2({
            placeholder: `-- Pilih Pelatihan (${visibleCount} tersedia) --`,
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Tidak ada pelatihan tersedia untuk kategori ini";
                },
                searching: function() {
                    return "Mencari...";
                }
            },
            templateResult: function(option) {
                if (!option.id || option.element.style.display === 'none') {
                    return null;
                }
                return option.text;
            }
        });
        
        // Re-attach event handler setelah re-init
        attachSelect2QuickAddHandler();
    }
}

// Event listener untuk kategori
document.getElementById('kategori_id').addEventListener('change', function() {
    // Reset training selection saat kategori berubah
    document.getElementById('training_id').value = '';
    filterTrainingByKategori();
});

// Pastikan training dropdown ter-enable sebelum form submit
document.getElementById('classForm').addEventListener('submit', function(e) {
    const trainingSelect = document.getElementById('training_id');
    const kategoriSelect = document.getElementById('kategori_id');
    
    // Jika training dropdown disabled tapi kategori sudah dipilih, enable dulu
    if (trainingSelect.disabled && kategoriSelect.value) {
        trainingSelect.disabled = false;
    }
    
    // Validasi: pastikan training sudah dipilih
    if (!trainingSelect.value) {
        e.preventDefault();
        alert('Silakan pilih Jenis Pelatihan terlebih dahulu!');
        trainingSelect.focus();
        return false;
    }
});
</script>

<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

// Function to attach Select2 quick-add handler
function attachSelect2QuickAddHandler() {
    // Remove old handler first to avoid duplicate
    $('#training_id').off('select2:select');
    
    // Attach new handler
    $('#training_id').on('select2:select', function(e) {
        const selectedValue = e.params.data.id;
        const selectedText = e.params.data.text;
        
        // Check if user selected "add_new"
        if (selectedValue === 'add_new') {
            e.preventDefault();
            openQuickAddModal();
            // Reset dropdown ke empty
            setTimeout(() => {
                $('#training_id').val('').trigger('change');
            }, 100);
            return;
        }
        
        // Auto-fill nama kelas dengan format: "Kategori - Nama Training"
        const nameInput = document.getElementById('name');
        if (!nameInput.value) {
            const kategoriSelect = document.getElementById('kategori_id');
            const kategoriText = kategoriSelect.options[kategoriSelect.selectedIndex].text;
            
            if (kategoriText && selectedText && kategoriText !== '-- Pilih Kategori --') {
                nameInput.value = `${kategoriText} - ${selectedText}`;
            }
        }
    });
}


$(document).ready(function() {
    // Initialize Select2 untuk dropdown Nama Pelatihan
    $('#training_id').select2({
        placeholder: '-- Pilih Nama Pelatihan --',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Tidak ada hasil ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });
    
    // Set placeholder untuk search bar di dalam dropdown
    $('#training_id').on('select2:open', function() {
        $('.select2-search__field').attr('placeholder', 'Cari pelatihan...');
    });
    
    // Attach quick-add handler
    attachSelect2QuickAddHandler();
    
    // Trigger filter saat page load jika kategori sudah ada value (dari old() atau edit mode)
    const kategoriSelect = document.getElementById('kategori_id');
    if (kategoriSelect && kategoriSelect.value) {
        filterTrainingByKategori();
    }
});
</script>

<!-- Quick Add Training Modal Scripts -->
<script>
// Open modal
function openQuickAddModal() {
    const modal = document.getElementById('quickAddTrainingModal');
    const input = document.getElementById('quick_training_name');
    const errorDiv = document.getElementById('quick_add_error');
    
    // Reset form
    document.getElementById('quickAddTrainingForm').reset();
    errorDiv.classList.add('hidden');
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Focus input
    setTimeout(() => input.focus(), 100);
}

// Close modal
function closeQuickAddModal() {
    const modal = document.getElementById('quickAddTrainingModal');
    modal.classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickAddModal();
    }
});

// Close modal on background click
document.getElementById('quickAddTrainingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQuickAddModal();
    }
});

// Handle form submit via AJAX
document.getElementById('quickAddTrainingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('quick_add_submit_btn');
    const errorDiv = document.getElementById('quick_add_error');
    const errorMsg = document.getElementById('quick_add_error_message');
    const trainingName = document.getElementById('quick_training_name').value.trim();
    const kategoriSelect = document.getElementById('kategori_id');
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    const selectedType = selectedOption.getAttribute('data-type');
    
    // Validasi
    if (!trainingName) {
        errorMsg.textContent = 'Nama pelatihan wajib diisi!';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (!kategoriSelect.value || !selectedType) {
        errorMsg.textContent = 'Silakan pilih kategori kelas terlebih dahulu!';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Disable button & show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
    errorDiv.classList.add('hidden');
    
    // AJAX request
    fetch('{{ route("admin.api.trainings.quick-create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: trainingName,
            type: selectedType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new training to dropdown
            const trainingSelect = document.getElementById('training_id');
            const newOption = new Option(data.training.name, data.training.id, true, true);
            newOption.setAttribute('data-type', data.training.type);
            
            // Insert before "add_new" option
            const addNewOption = trainingSelect.querySelector('option[value="add_new"]');
            if (addNewOption) {
                trainingSelect.insertBefore(newOption, addNewOption);
            } else {
                trainingSelect.add(newOption);
            }
            
            // Update Select2
            $('#training_id').val(data.training.id).trigger('change');
            
            // Close modal
            closeQuickAddModal();
            
            // Show success message
            showNotification('success', 'Pelatihan berhasil ditambahkan!');
            
        } else {
            // Show error
            errorMsg.textContent = data.message || 'Terjadi kesalahan saat menyimpan';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorMsg.textContent = 'Terjadi kesalahan pada server. Silakan coba lagi.';
        errorDiv.classList.remove('hidden');
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>Simpan & Gunakan';
    });
});

// Helper function to show notification
function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-xl"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
@endsection
