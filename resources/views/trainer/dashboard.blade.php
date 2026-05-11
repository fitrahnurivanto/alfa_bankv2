@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
    <!-- Header -->
    <div class="mb-2">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dashboard Trainer</h1>
        <p class="text-sm md:text-base text-gray-600 mt-1">Selamat datang kembali, <span class="font-semibold text-gray-900">{{ $trainer->name }}</span></p>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 md:p-5 mb-6">
        <form method="GET" action="{{ route('trainer.dashboard') }}" class="flex flex-col lg:flex-row gap-3 lg:items-end">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label for="period" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select name="period" id="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="this.form.submit()">
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua Periode</option>
                        <option value="month_01" {{ $period === 'month_01' ? 'selected' : '' }}>Januari</option>
                        <option value="month_02" {{ $period === 'month_02' ? 'selected' : '' }}>Februari</option>
                        <option value="month_03" {{ $period === 'month_03' ? 'selected' : '' }}>Maret</option>
                        <option value="month_04" {{ $period === 'month_04' ? 'selected' : '' }}>April</option>
                        <option value="month_05" {{ $period === 'month_05' ? 'selected' : '' }}>Mei</option>
                        <option value="month_06" {{ $period === 'month_06' ? 'selected' : '' }}>Juni</option>
                        <option value="month_07" {{ $period === 'month_07' ? 'selected' : '' }}>Juli</option>
                        <option value="month_08" {{ $period === 'month_08' ? 'selected' : '' }}>Agustus</option>
                        <option value="month_09" {{ $period === 'month_09' ? 'selected' : '' }}>September</option>
                        <option value="month_10" {{ $period === 'month_10' ? 'selected' : '' }}>Oktober</option>
                        <option value="month_11" {{ $period === 'month_11' ? 'selected' : '' }}>November</option>
                        <option value="month_12" {{ $period === 'month_12' ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="year" id="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="this.form.submit()">
                        <option value="all" {{ $year === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($availableYears as $filterYear)
                        <option value="{{ $filterYear }}" {{ (string) $year === (string) $filterYear ? 'selected' : '' }}>{{ $filterYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Status Kelas</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="this.form.submit()">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Selesai</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>
            <div>
                <a href="{{ route('trainer.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-rotate-left mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-5 mb-6">
        <!-- Total Kelas -->
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-all duration-300 border border-gray-200 border-l-4 border-l-purple-500">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1">Total Kelas</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-purple-600">{{ $totalClasses }}</h3>
                        <p class="text-gray-400 text-xs mt-1">Semua kelas</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-chalkboard-teacher text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas Bulan Ini -->
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-all duration-300 border border-gray-200 border-l-4 border-l-blue-500">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1">Kelas Bulan Ini</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-blue-600">{{ $classesThisMonth }}</h3>
                        <p class="text-gray-400 text-xs mt-1">Bulan berjalan</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Honor -->
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-all duration-300 border border-gray-200 border-l-4 border-l-green-500">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1">Total Honor</p>
                        <h3 class="text-lg md:text-xl font-bold text-green-600">Rp {{ number_format($totalHonor, 0, ',', '.') }}</h3>
                        <p class="text-gray-400 text-xs mt-1">Akumulasi honor</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Honor Diterima -->
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-all duration-300 border border-gray-200 border-l-4 border-l-teal-500">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1">Honor Diterima</p>
                        <h3 class="text-lg md:text-xl font-bold text-teal-600">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
                        <p class="text-gray-400 text-xs mt-1">Sudah dibayar</p>
                    </div>
                    <div class="bg-teal-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-wallet text-2xl text-teal-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payment -->
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-all duration-300 border border-gray-200 border-l-4 border-l-orange-500">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1">Pending Payment</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-orange-600">{{ $pendingPayments }}</h3>
                        <p class="text-gray-400 text-xs mt-1">Menunggu approval</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Chart & Kelas by Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
        <!-- Bar Chart Income (Jan-Des) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-4 md:p-6 flex flex-col">
            <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex-1">
                    <h3 class="text-base md:text-lg font-bold text-gray-900">
                        <i class="fas fa-chart-bar text-teal-600 mr-2"></i>Tren Honor Trainer
                    </h3>
                    <p class="text-xs md:text-sm text-gray-600">Payment request berstatus paid dari Januari sampai Desember ({{ $chartDescription }})</p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="button" id="chartTypeBar" onclick="switchChartType('bar')" class="px-3 py-2 bg-teal-100 text-teal-700 rounded-lg font-semibold text-xs md:text-sm hover:bg-teal-200 transition border-2 border-teal-400">
                        <i class="fas fa-chart-bar mr-1"></i>Bar
                    </button>
                    <button type="button" id="chartTypeLine" onclick="switchChartType('line')" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg font-semibold text-xs md:text-sm hover:bg-gray-200 transition border-2 border-gray-200">
                        <i class="fas fa-chart-line mr-1"></i>Line
                    </button>
                </div>
            </div>
            <div class="w-full flex-1" style="min-height: 360px;">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        <!-- Kelas by Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="mb-4">
                <h3 class="text-base md:text-lg font-bold text-gray-900">
                    <i class="fas fa-chart-pie text-purple-600 mr-2"></i>Status Kelas
                </h3>
                <p class="text-xs md:text-sm text-gray-600">Breakdown status</p>
            </div>
            <div class="space-y-3">
                <!-- Approved -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-lg p-3 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="w-9 h-9 bg-gray-500 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-hourglass-half text-white text-sm"></i>
                        </div>
                        <span class="text-2xl font-bold text-gray-600">{{ $klasByStatus['pending'] }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Pending</p>
                    <p class="text-xs text-gray-600">Menunggu approval</p>
                </div>

                <!-- Approved -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-lg p-3 border border-yellow-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="w-9 h-9 bg-yellow-500 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                        <span class="text-2xl font-bold text-yellow-600">{{ $klasByStatus['approved'] }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Approved</p>
                    <p class="text-xs text-gray-600">Siap dimulai</p>
                </div>

                <!-- Rejected -->
                <div class="bg-gradient-to-br from-red-50 to-red-100/50 rounded-lg p-3 border border-red-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="w-9 h-9 bg-red-500 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-times text-white text-sm"></i>
                        </div>
                        <span class="text-2xl font-bold text-red-600">{{ $klasByStatus['rejected'] }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Rejected</p>
                    <p class="text-xs text-gray-600">Tidak disetujui</p>
                </div>

                <!-- Done -->
                <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-lg p-3 border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="w-9 h-9 bg-green-500 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                        <span class="text-2xl font-bold text-green-600">{{ $klasByStatus['done'] }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Done</p>
                    <p class="text-xs text-gray-600">Selesai</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Payment Request Monitoring -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
        <div class="mb-4">
            <h3 class="text-base md:text-lg font-bold text-gray-900">
                <i class="fas fa-file-invoice-dollar text-emerald-600 mr-2"></i>Monitoring Payment Request
            </h3>
            <p class="text-xs md:text-sm text-gray-600">Pantau jumlah dan nominal payment request berdasarkan status.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-yellow-800">Pending</span>
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <p class="text-2xl font-bold text-yellow-700">{{ $paymentRequestStats['pending']['count'] }}</p>
                <p class="text-xs text-yellow-700 mt-2">Nominal: Rp {{ number_format($paymentRequestStats['pending']['amount'], 0, ',', '.') }}</p>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-blue-800">Waiting Approval</span>
                    <i class="fas fa-hourglass-half text-blue-600"></i>
                </div>
                <p class="text-2xl font-bold text-blue-700">{{ $paymentRequestStats['waiting']['count'] }}</p>
                <p class="text-xs text-blue-700 mt-2">Nominal: Rp {{ number_format($paymentRequestStats['waiting']['amount'], 0, ',', '.') }}</p>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-red-800">Rejected</span>
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <p class="text-2xl font-bold text-red-700">{{ $paymentRequestStats['rejected']['count'] }}</p>
                <p class="text-xs text-red-700 mt-2">Nominal: Rp {{ number_format($paymentRequestStats['rejected']['amount'], 0, ',', '.') }}</p>
            </div>

            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-green-800">Paid</span>
                    <i class="fas fa-circle-check text-green-600"></i>
                </div>
                <p class="text-2xl font-bold text-green-700">{{ $paymentRequestStats['paid']['count'] }}</p>
                <p class="text-xs text-green-700 mt-2">Nominal: Rp {{ number_format($paymentRequestStats['paid']['amount'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Upcoming Classes -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base md:text-lg font-bold text-gray-900">
                            <i class="fas fa-calendar-check text-purple-600 mr-2"></i>Kelas Akan Datang
                        </h2>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Kelas yang sudah approved</p>
                    </div>
                    <a href="{{ route('trainer.classes.index') }}" class="text-xs md:text-sm text-purple-600 hover:text-purple-700 font-semibold hover:underline">
                        Lihat Semua →
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($upcomingClasses as $class)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-purple-600 text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('trainer.classes.show', $class) }}" class="font-semibold text-sm md:text-base text-gray-900 hover:text-purple-600 block truncate">
                                {{ $class->name }}
                            </a>
                            <p class="text-xs md:text-sm text-gray-600 truncate">
                                <i class="fas fa-building mr-1 text-gray-400"></i>{{ $class->instansi ?? '-' }}
                            </p>
                            <div class="flex items-center gap-3 mt-2 flex-wrap">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1 text-gray-400"></i>{{ $class->start_date ? \Carbon\Carbon::parse($class->start_date)->format('d M Y') : '-' }}
                                </span>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                    {{ ucfirst($class->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-calendar-times text-5xl mb-3 text-gray-300"></i>
                    <p class="text-sm font-medium">Tidak ada kelas mendatang</p>
                    <p class="text-xs mt-1">Kelas akan muncul setelah approved</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payment Requests -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base md:text-lg font-bold text-gray-900">
                            <i class="fas fa-file-invoice text-green-600 mr-2"></i>Payment Request Terbaru
                        </h2>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Request honor yang sudah dibuat</p>
                    </div>
                    <a href="{{ route('trainer.payment-requests.index') }}" class="text-xs md:text-sm text-green-600 hover:text-green-700 font-semibold hover:underline">
                        Lihat Semua →
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentPayments as $payment)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-green-600 text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('trainer.payment-requests.show', $payment) }}" class="font-semibold text-sm md:text-base text-gray-900 hover:text-green-600 block truncate">
                                {{ $payment->class->name ?? 'Honor Training' }}
                            </a>
                            <p class="text-sm md:text-base font-bold text-gray-900 mt-1">
                                Rp {{ number_format($payment->approved_amount ?? $payment->requested_amount ?? 0, 0, ',', '.') }}
                            </p>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                                    {{ $payment->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $payment->status === 'admin_approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $payment->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                    @if($payment->status === 'paid')
                                        <i class="fas fa-check-circle mr-1"></i>Paid
                                    @elseif($payment->status === 'admin_approved')
                                        <i class="fas fa-hourglass-half mr-1"></i>Admin Approved
                                    @elseif($payment->status === 'pending')
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    @else
                                        <i class="fas fa-times-circle mr-1"></i>{{ ucfirst($payment->status) }}
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1 text-gray-400"></i>{{ $payment->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-file-invoice text-5xl mb-3 text-gray-300"></i>
                    <p class="text-sm font-medium">Belum ada payment request</p>
                    <a href="{{ route('trainer.payment-requests.create') }}" class="inline-block mt-3 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition shadow-sm">
                        <i class="fas fa-plus mr-2"></i>Buat Payment Request
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Classes -->
    <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 md:p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base md:text-lg font-bold text-gray-900">
                        <i class="fas fa-history text-blue-600 mr-2"></i>Kelas Terbaru
                    </h2>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">5 kelas terakhir yang Anda ajar</p>
                </div>
                <a href="{{ route('trainer.classes.index') }}" class="text-xs md:text-sm text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                    Lihat Semua →
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Nama Kelas</th>
                        <th class="text-left py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Kategori</th>
                        <th class="text-left py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Instansi</th>
                        <th class="text-left py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Honor</th>
                        <th class="text-left py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-center py-3 px-4 text-xs md:text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentClasses as $class)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <a href="{{ route('trainer.classes.show', $class) }}" class="font-medium text-sm md:text-base text-gray-900 hover:text-purple-600">
                                {{ $class->name }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-xs md:text-sm text-gray-600">
                            {{ $class->kategori->nama_kategori ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-xs md:text-sm text-gray-600">
                            {{ Str::limit($class->instansi ?? '-', 30) }}
                        </td>
                        <td class="py-3 px-4 text-xs md:text-sm font-semibold text-gray-900">
                            Rp {{ number_format($class->trainer_honor ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                {{ $class->status === 'approved' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $class->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $class->status === 'done' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $class->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $class->status === 'done' ? 'Selesai' : ucfirst($class->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('trainer.classes.show', $class) }}" class="inline-flex items-center justify-center w-8 h-8 text-purple-600 hover:bg-purple-100 rounded-lg transition">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <i class="fas fa-inbox text-5xl mb-3 text-gray-300"></i>
                            <p class="text-sm font-medium">Belum ada kelas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let incomeChartInstance = null;
    let currentChartType = 'bar';

    function getChartOptions(type) {
        const baseConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Honor: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        };

        if (type === 'line') {
            baseConfig.interaction = {
                intersect: false,
                mode: 'index'
            };
        }

        return baseConfig;
    }

    function getDatasetConfig(type) {
        const baseDataset = {
            label: 'Honor Diterima',
            borderColor: 'rgba(20, 184, 166, 1)',
            borderWidth: 2,
        };

        if (type === 'bar') {
            return {
                ...baseDataset,
                backgroundColor: 'rgba(20, 184, 166, 0.8)',
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(20, 184, 166, 1)',
            };
        } else {
            return {
                ...baseDataset,
                backgroundColor: 'rgba(20, 184, 166, 0.15)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(20, 184, 166, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            };
        }
    }

    function createIncomeChart(type = 'bar') {
        const incomeCtx = document.getElementById('incomeChart').getContext('2d');
        const chartData = @json($chartData);
        
        const labels = chartData.map(item => item.month);
        const incomeData = chartData.map(item => item.income);

        if (incomeChartInstance) {
            incomeChartInstance.destroy();
        }

        incomeChartInstance = new Chart(incomeCtx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    ...getDatasetConfig(type),
                    data: incomeData,
                }]
            },
            options: getChartOptions(type)
        });

        currentChartType = type;
    }

    function switchChartType(type) {
        if (currentChartType === type) return;

        createIncomeChart(type);

        // Update button styles
        const barBtn = document.getElementById('chartTypeBar');
        const lineBtn = document.getElementById('chartTypeLine');

        if (type === 'bar') {
            barBtn.classList.add('bg-teal-100', 'text-teal-700', 'border-teal-400');
            barBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            lineBtn.classList.remove('bg-teal-100', 'text-teal-700', 'border-teal-400');
            lineBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        } else {
            lineBtn.classList.add('bg-teal-100', 'text-teal-700', 'border-teal-400');
            lineBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            barBtn.classList.remove('bg-teal-100', 'text-teal-700', 'border-teal-400');
            barBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        createIncomeChart('bar');
    });
</script>

@endsection