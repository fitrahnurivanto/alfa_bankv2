@extends('layouts.auth')

@section('title', 'Login - Management Project')

@section('content')
<div class="w-full max-w-md">
    <!-- Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden backdrop-blur-sm bg-opacity-95">
        <!-- Header -->
        <div class="bg-white px-8 py-4 text-center border-b border-gray-200">
            <div class="flex items-center justify-center mx-auto mb-3">
                <img src="{{ asset('images/alfabank-logo.png') }}" 
                     alt="Alfa Bank" 
                     class="h-26 w-auto object-contain" 
                     style="height: 112px;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center" style="display:none;">
                    <i class="fas fa-user-circle text-5xl text-indigo-600"></i>
                </div>
            </div>
            <h2 class="text-lg font-bold text-gray-800 mb-1">Welcome Back!</h2>
            <p class="text-gray-600 text-xs">Login untuk melanjutkan</p>
        </div>
        
        <!-- Body -->
        <div class="p-8">
            @if(session('success'))
                <div class="mb-3 bg-green-50 border-l-4 border-green-500 p-2 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span class="text-green-700 text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-3 bg-red-50 border-l-4 border-red-500 p-2 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                        <div class="text-red-700 text-sm">
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form id="loginForm" action="{{ route('login') }}" method="POST" class="space-y-4" autocomplete="off" spellcheck="false">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope text-indigo-500 mr-2"></i>Email Address
                    </label>
                    <input type="email" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                              autocomplete="username"
                              autocapitalize="none"
                              autocorrect="off"
                           required 
                           autofocus>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-indigo-500 mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               autocapitalize="none"
                               autocorrect="off"
                               required>
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute top-0 right-0 h-full flex items-center justify-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                                aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="remember" class="ml-2 text-sm text-gray-600">
                            Remember me
                        </label>
                    </div>
                    
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        Lupa Password?
                    </a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg hover:-translate-y-0.5 transform transition-all duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-2">
        <p class="text-gray-600 text-xs">© 2025 Management Project. All rights reserved.</p>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

const loginForm = document.getElementById('loginForm');

if (loginForm) {
    loginForm.addEventListener('submit', async function (event) {
        if (loginForm.dataset.csrfRefreshed === '1') {
            return;
        }

        event.preventDefault();

        try {
            const response = await fetch("{{ route('csrf.token') }}", {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (response.ok) {
                const data = await response.json();

                if (data.token) {
                    const tokenInput = loginForm.querySelector('input[name="_token"]');
                    if (tokenInput) {
                        tokenInput.value = data.token;
                    }

                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (csrfMeta) {
                        csrfMeta.setAttribute('content', data.token);
                    }
                }
            }
        } catch (error) {
            // Fallback: proceed with existing token if refresh fails.
        }

        loginForm.dataset.csrfRefreshed = '1';
        loginForm.submit();
    });
}
</script>
@endsection
