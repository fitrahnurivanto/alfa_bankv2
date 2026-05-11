@extends('layouts.auth')

@section('title', 'Register - Management Project')

@section('content')
<div class="w-full max-w-2xl">
    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden backdrop-blur-sm bg-opacity-95">
        <!-- Header -->
        <div class="bg-white px-8 py-10 text-center border-b border-gray-200">
            <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-5xl text-indigo-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
            <p class="text-gray-600 text-sm">Mulai kelola proyek bersama kami</p>
        </div>
        
        <!-- Body -->
        <div class="p-8">
            @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                        <div class="text-red-700 text-sm">
                            <strong class="font-semibold">Terjadi kesalahan:</strong>
                            <ul class="mt-2 space-y-1 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Personal Information -->
                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-indigo-500 mr-2"></i>Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama lengkap"
                               required 
                               autofocus>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-indigo-500 mr-2"></i>Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   required>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-phone text-indigo-500 mr-2"></i>No. Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('phone') border-red-500 @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock text-indigo-500 mr-2"></i>Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Min. 8 karakter"
                                   required>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock text-indigo-500 mr-2"></i>Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ulangi password"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 pt-6">
                    <h6 class="text-base font-semibold text-gray-700 mb-4">
                        <i class="fas fa-building text-indigo-500 mr-2"></i>Informasi Perusahaan <span class="text-gray-400 text-sm font-normal">(Opsional)</span>
                    </h6>

                    <div class="space-y-5">
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-briefcase text-gray-400 mr-2"></i>Nama Perusahaan
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="{{ old('company_name') }}"
                                   placeholder="PT. Nama Perusahaan">
                        </div>

                        <div>
                            <label for="company_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>Alamat Perusahaan
                            </label>
                            <textarea class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none" 
                                      id="company_address" 
                                      name="company_address" 
                                      rows="3"
                                      placeholder="Alamat lengkap perusahaan">{{ old('company_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg hover:-translate-y-0.5 transform transition-all duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">OR</span>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-gray-600 text-sm mb-2">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-semibold text-sm transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Disini
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-6">
        <p class="text-gray-600 text-sm">© 2025 Management Project. All rights reserved.</p>
    </div>
</div>
@endsection
