@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div class="p-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang, {{ \Illuminate\Support\Facades\Auth::user()->name }}!</h2>
        <p class="text-gray-600 mt-1">Akses dashboard client Anda</p>
    </div>

    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-12 text-center">
            <div class="mb-6">
                <i class="fas fa-user-circle text-gray-300 text-8xl"></i>
            </div>
            <h4 class="text-2xl font-semibold text-gray-800 mb-3">Akun Client Belum Terhubung</h4>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                Akun Anda belum terhubung dengan data client.<br>
                Silakan hubungi administrator untuk mengaktifkan akses Anda.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-2xl mx-auto mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5 mr-3"></i>
                    <div class="text-left">
                        <p class="text-blue-800 font-semibold mb-1">Informasi</p>
                        <p class="text-blue-700 text-sm">
                            Data client biasanya dibuat otomatis saat order pertama Anda dikonfirmasi oleh admin.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="mt-8">
                <p class="text-gray-600 flex items-center justify-center gap-2">
                    <i class="fas fa-envelope text-gray-400"></i>
                    Butuh bantuan? Hubungi: 
                    <a href="mailto:admin@cmuchickens.com" class="text-indigo-600 hover:text-indigo-700 font-semibold">admin@cmuchickens.com</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
