@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-start gap-4">
        <a href="{{ route('admin.trainers.index') }}" class="text-gray-900 hover:text-[#fe0000] text-2xl mt-1.5">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Tambah Trainer Baru</h1>
            <p class="text-gray-800">Lengkapi data trainer baru</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-6 sm:p-8">
        <form action="{{ route('admin.trainers.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-5">
            <div class="">
                <label for="name" class="block text-base text-gray-900 mb-1.5">Nama Lengkap <span class="text-[#fe0000]">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('name') border-red-500 @enderror" placeholder="Masukkan nama lengkap trainer">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="">
                <label for="email" class="block text-base text-gray-900 mb-1.5">Email <span class="text-[#fe0000]">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('email') border-red-500 @enderror" placeholder="trainer@example.com">
                <p class="text-xs text-gray-600 mt-1">Email akan digunakan untuk login</p>
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="">
                <label for="phone" class="block text-base text-gray-900 mb-1.5">No. Telepon / WhatsApp</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('phone') border-red-500 @enderror" placeholder="08xxxxxxxxxx">
                @error('phone')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="">
                <label for="expertise" class="block text-base text-gray-900 mb-1.5">Spesialisasi <span class="text-[#fe0000]">*</span></label>
                <input type="text" name="expertise" id="expertise" value="{{ old('expertise') }}" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('expertise') border-red-500 @enderror" placeholder="Contoh: Laravel, UI/UX, Digital Marketing">
                @error('expertise')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="">
                <label for="bio" class="block text-base text-gray-900 mb-1.5">Bio / Profil Singkat</label>
                <textarea name="bio" id="bio" rows="3" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('bio') border-red-500 @enderror" placeholder="Profil dan pengalaman trainer">{{ old('bio') }}</textarea>
                @error('bio')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="status" class="block text-base text-gray-900 mb-1.5">Status <span class="text-[#fe0000]">*</span></label>
                <select name="status" id="status" class="w-full px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] focus:ring-1 focus:ring-[#fe0000] @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            </div>

        <div class="mt-6 bg-[#ffeed9] border border-[#8a4b00] rounded-lg p-5 text-[#8a4b00]">
            <p class="text-xl font-semibold flex items-center gap-2 mb-2"><i class="far fa-circle-info"></i> Informasi</p>
            <ul class="list-disc pl-8 space-y-1 text-base">
                <li>Trainer akan mendapat akun untuk login ke sistem</li>
                <li>Trainer dapat mengajukan payment request untuk kelas yang mereka ajar</li>
                <li>Role: <strong>Trainer</strong> | Division: <strong>Pelatihan</strong></li>
            </ul>
        </div>

        <div class="flex flex-wrap gap-4 mt-8">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-[#fe0000] text-white font-semibold rounded-lg shadow-[0_3px_6px_rgba(0,0,0,0.25)] hover:bg-[#cc0000] transition">
                <i class="far fa-floppy-disk"></i> Simpan Trainer
            </button>
            <a href="{{ route('admin.trainers.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-[#fe0000] text-[#fe0000] font-semibold rounded-lg hover:bg-[#fed0d0]/40 transition">
                Batal
            </a>
        </div>
        </form>
    </div>
</div>
@endsection