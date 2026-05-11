@extends('layouts.app')

@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="p-4 sm:p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700 transition">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Pengguna Baru</h1>
            <p class="text-sm text-gray-500">Buat akun untuk admin, marketing, akademik, finance, atau trainer</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-5">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
            <li><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nama -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm @error('name') border-red-400 @enderror"
                           placeholder="Masukkan nama lengkap">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm @error('email') border-red-400 @enderror"
                           placeholder="email@example.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- No HP -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm"
                           placeholder="08xxxxxxxxxx">
                </div>

                <!-- Role -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm @error('role') border-red-400 @enderror">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin"     {{ old('role') === 'admin'     ? 'selected' : '' }}>Admin (akses penuh)</option>
                        <option value="marketing" {{ old('role') === 'marketing' ? 'selected' : '' }}>Marketing (buat kelas, laporan, pelatihan)</option>
                        <option value="akademik"  {{ old('role') === 'akademik'  ? 'selected' : '' }}>Akademik (approve kelas, pengajar, payment, expense)</option>
                        <option value="finance"   {{ old('role') === 'finance'   ? 'selected' : '' }}>Finance (expense & payment request)</option>
                        <option value="trainer"   {{ old('role') === 'trainer'   ? 'selected' : '' }}>Trainer/Pengajar</option>
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" required data-password-input
                               class="w-full px-4 pr-11 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm @error('password') border-red-400 @enderror"
                               placeholder="Min. 8 karakter">
                        <button type="button" data-toggle-password
                                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                                aria-label="Lihat password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" required data-password-input
                               class="w-full px-4 pr-11 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm"
                               placeholder="Ulangi password">
                        <button type="button" data-toggle-password
                                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                                aria-label="Lihat konfirmasi password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 sm:flex-none px-6 py-2.5 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a24a6] font-medium text-sm transition">
                    <i class="fas fa-user-plus mr-2"></i>Simpan Pengguna
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 font-medium text-sm transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            const wrapper = button.closest('.relative');
            const input = wrapper ? wrapper.querySelector('[data-password-input]') : null;
            const icon = button.querySelector('i');

            if (!input || !icon) {
                return;
            }

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !showPassword);
            icon.classList.toggle('fa-eye-slash', showPassword);
            button.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Lihat password');
        });
    });
});
</script>
@endsection
