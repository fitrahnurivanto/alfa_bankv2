@extends('layouts.app')

@section('page-title', 'Maintenance Expense')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Maintenance Expense Finance</h1>
        <p class="text-sm text-gray-600">
            Gunakan halaman ini jika tidak memiliki akses terminal. Hanya kategori honor yang akan dipertahankan di workflow Finance.
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Mode Aman</h2>
        <p class="text-sm text-gray-600 mb-4">
            Semua expense non-honor akan otomatis di-approve. Data tetap tersimpan untuk histori.
        </p>
        <form method="POST" action="{{ route('admin.expense-maintenance.run') }}">
            @csrf
            <button
                type="submit"
                class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                onclick="return confirm('Jalankan normalisasi mode aman?')"
            >
                Jalankan Normalisasi
            </button>
        </form>
    </div>

    @if(session('command_output'))
        <div class="bg-gray-900 text-gray-100 rounded-2xl p-5 overflow-x-auto">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-300 mb-3">Output Proses</h3>
            <pre class="text-xs md:text-sm whitespace-pre-wrap">{{ session('command_output') }}</pre>
        </div>
    @endif
</div>
@endsection
