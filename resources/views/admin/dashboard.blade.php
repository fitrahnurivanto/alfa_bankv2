@extends('layouts.app')

@section('title', 'Dashboard Alfa Bank')

@section('page-title', 'Dashboard Alfa Bank')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }

    .chart-container-lg {
        position: relative;
        height: 380px;
    }

    .recap-card-grid > div {
        min-height: 100%;
    }

    .recap-card-grid .recap-card {
        min-height: 100%;
    }

    .recap-card-grid .recap-card-body {
        padding: 0.875rem;
    }

    @media (min-width: 768px) {
        .recap-card-grid .recap-card-body {
            padding: 1rem 1.1rem;
        }
    }

    .recap-card-grid .recap-card-title {
        font-size: 0.72rem;
        line-height: 1rem;
    }

    .recap-card-grid .recap-card-value {
        font-size: 1.05rem;
        line-height: 1.35rem;
    }

    @media (min-width: 768px) {
        .recap-card-grid .recap-card-title {
            font-size: 0.78rem;
        }

        .recap-card-grid .recap-card-value {
            font-size: 1.2rem;
        }
    }

    .recap-card-grid .recap-card-icon {
        width: 2.5rem;
        height: 2.5rem;
        padding: 0.55rem;
        border-radius: 0.75rem;
    }

    .recap-card-grid .recap-card-icon i {
        font-size: 1.1rem;
    }

    @media (min-width: 768px) {
        .recap-card-grid .recap-card-icon {
            width: 2.75rem;
            height: 2.75rem;
            padding: 0.6rem;
        }

        .recap-card-grid .recap-card-icon i {
            font-size: 1.15rem;
        }
    }

    #calendar {
        max-width: 100%;
        margin: 0 auto;
        height: 600px;
    }
    
    .fc-event {
        cursor: pointer;
    }
    
    .fc-event:hover {
        opacity: 0.8;
    }
    
    .calendar-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    
    .calendar-content.active {
        max-height: 700px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm animate-fade-in">
    <div class="flex items-center">
        <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm animate-fade-in">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3"></i>
        <p class="text-red-800 font-medium">{{ session('error') }}</p>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm animate-fade-in">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3 mt-0.5"></i>
        <div>
            <p class="text-red-800 font-medium mb-2">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<!-- Filter Form -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 md:mb-6 gap-3">
    <div>
        <h4 class="text-xl md:text-2xl font-bold text-gray-800">
            <i class="fas fa-chart-line mr-2 md:mr-3"></i>Dashboard <span class="text-green-600">Alfa Bank</span>
        </h4>
        @if(\Illuminate\Support\Facades\Auth::user()?->isAdmin())
            <div class="inline-flex items-center gap-1 p-1 mt-2 rounded-full bg-gray-100 border border-gray-200">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-green-600 text-white shadow-md hover:shadow-lg"><i class="fas fa-chart-bar mr-1"></i>Admin</a>
                <a href="{{ route('admin.dashboard.akademik') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-graduation-cap mr-1"></i>Akademik</a>
                <a href="{{ route('admin.dashboard.finance') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-money-bill-wave mr-1"></i>Finance</a>
            </div>
        @endif
    </div>
    
    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 w-full lg:w-auto">
        <select name="period" class="flex-1 sm:flex-none px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Semua Periode</option>
            <option value="month_01" {{ $period == 'month_01' ? 'selected' : '' }}>Januari</option>
            <option value="month_02" {{ $period == 'month_02' ? 'selected' : '' }}>Februari</option>
            <option value="month_03" {{ $period == 'month_03' ? 'selected' : '' }}>Maret</option>
            <option value="month_04" {{ $period == 'month_04' ? 'selected' : '' }}>April</option>
            <option value="month_05" {{ $period == 'month_05' ? 'selected' : '' }}>Mei</option>
            <option value="month_06" {{ $period == 'month_06' ? 'selected' : '' }}>Juni</option>
            <option value="month_07" {{ $period == 'month_07' ? 'selected' : '' }}>Juli</option>
            <option value="month_08" {{ $period == 'month_08' ? 'selected' : '' }}>Agustus</option>
            <option value="month_09" {{ $period == 'month_09' ? 'selected' : '' }}>September</option>
            <option value="month_10" {{ $period == 'month_10' ? 'selected' : '' }}>Oktober</option>
            <option value="month_11" {{ $period == 'month_11' ? 'selected' : '' }}>November</option>
            <option value="month_12" {{ $period == 'month_12' ? 'selected' : '' }}>Desember</option>
        </select>
        
        <select name="year" class="flex-1 sm:flex-none px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
            <option value="all" {{ request('year', 'all') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
            @for($y = now()->year; $y >= 2023; $y--)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        
        <select name="status" class="flex-1 sm:flex-none px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif</option>
        </select>
    </form>
</div>

<!-- Stats Cards -->
<div class="space-y-3 md:space-y-4 mb-4 md:mb-5">
    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-3 md:p-4 space-y-3">
        <div class="flex items-start justify-start px-1">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900 text-white text-xs md:text-sm font-semibold tracking-wide uppercase shadow-sm">
                Rekap Omset
            </span>
        </div>
        <div class="grid recap-card-grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 items-stretch">
            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Omset Reguler</p>
                            <h4 class="recap-card-value font-bold text-blue-600 break-words">Rp {{ number_format($regularRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori reguler</p>
                        </div>
                        <div class="recap-card-icon bg-blue-100 shrink-0 flex items-center justify-center">
                            <i class="fas fa-sack-dollar text-3xl text-blue-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Omset Corporate</p>
                            <h4 class="recap-card-value font-bold text-indigo-600 break-words">Rp {{ number_format($corporateRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori corporate</p>
                        </div>
                        <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                            <i class="fas fa-building text-3xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Omset Private</p>
                            <h4 class="recap-card-value font-bold text-amber-600 break-words">Rp {{ number_format($privateRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori private</p>
                        </div>
                        <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                            <i class="fas fa-user text-3xl text-amber-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Pendapatan Kotor (Bruto)</p>
                            <h4 class="recap-card-value font-bold text-gray-800 break-words">Rp {{ number_format($classRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor semua kategori</p>
                        </div>
                        <div class="recap-card-icon bg-gray-200 shrink-0 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-3xl text-gray-700"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Omset Sertifikasi</p>
                            <h4 class="recap-card-value font-bold text-emerald-700 break-words">Rp {{ number_format($certificationRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Dari {{ $certificationClassCount }} kelas sertifikasi</p>
                        </div>
                        <div class="recap-card-icon bg-emerald-100 shrink-0 flex items-center justify-center">
                            <i class="fas fa-certificate text-3xl text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 h-full recap-card">
                <div class="recap-card-body h-full flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="recap-card-title text-gray-500 font-medium mb-1 truncate">Data Sertifikasi</p>
                            <h4 class="recap-card-value font-bold text-cyan-700 break-words">{{ number_format($certificationStudentCount, 0, ',', '.') }} siswa</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total peserta program sertifikasi</p>
                        </div>
                        <div class="recap-card-icon bg-cyan-100 shrink-0 flex items-center justify-center">
                            <i class="fas fa-user-check text-3xl text-cyan-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-3 md:p-4 space-y-3">
        <div class="flex items-start justify-start px-1">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-800 text-white text-xs md:text-sm font-semibold tracking-wide uppercase shadow-sm">
                Rekap Kelas
            </span>
        </div>
        <div class="grid recap-card-grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Reguler</p>
                        <h4 class="recap-card-value font-bold text-blue-700">{{ $regularClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas approved + selesai kategori reguler</p>
                    </div>
                    <div class="recap-card-icon bg-blue-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Corporate</p>
                        <h4 class="recap-card-value font-bold text-indigo-700">{{ $corporateClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas approved + selesai kategori corporate</p>
                    </div>
                    <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Private</p>
                        <h4 class="recap-card-value font-bold text-amber-700">{{ $privateClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas approved + selesai kategori private</p>
                    </div>
                    <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Berjalan Reguler</p>
                        <h4 class="recap-card-value font-bold text-blue-700">{{ $runningRegularClasses }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas berjalan kategori reguler</p>
                    </div>
                    <div class="recap-card-icon bg-blue-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Berjalan Corporate</p>
                        <h4 class="recap-card-value font-bold text-indigo-700">{{ $runningCorporateClasses }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas berjalan kategori corporate</p>
                    </div>
                    <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelas Berjalan Private</p>
                        <h4 class="recap-card-value font-bold text-amber-700">{{ $runningPrivateClasses }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Jumlah kelas berjalan kategori private</p>
                    </div>
                    <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Peserta Reguler</p>
                        <h4 class="recap-card-value font-bold text-blue-700">{{ number_format($regularParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Peserta reguler dari kelas berjalan yang mulai bulan ini</p>
                    </div>
                    <div class="recap-card-icon bg-blue-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Peserta Corporate</p>
                        <h4 class="recap-card-value font-bold text-indigo-700">{{ number_format($corporateParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Peserta corporate dari kelas berjalan yang mulai bulan ini</p>
                    </div>
                    <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Peserta Private</p>
                        <h4 class="recap-card-value font-bold text-amber-700">{{ number_format($privateParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Peserta private dari kelas berjalan yang mulai bulan ini</p>
                    </div>
                    <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelulusan Reguler</p>
                        <h4 class="text-base md:text-lg font-bold text-emerald-700">Lulus: {{ number_format($regularPassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-base md:text-lg font-bold text-rose-700">Tidak Lulus: {{ number_format($regularFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="recap-card-icon bg-emerald-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-emerald-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelulusan Corporate</p>
                        <h4 class="text-base md:text-lg font-bold text-emerald-700">Lulus: {{ number_format($corporatePassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-base md:text-lg font-bold text-rose-700">Tidak Lulus: {{ number_format($corporateFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between recap-card">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Kelulusan Private</p>
                        <h4 class="text-base md:text-lg font-bold text-emerald-700">Lulus: {{ number_format($privatePassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-base md:text-lg font-bold text-rose-700">Tidak Lulus: {{ number_format($privateFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-user-xmark text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-3 md:p-4 space-y-3">
        <div class="flex items-start justify-start px-1">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-700 text-white text-xs md:text-sm font-semibold tracking-wide uppercase shadow-sm">
                Rekap Honor
            </span>
        </div>
        <div class="grid recap-card-grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4 items-stretch">
            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between md:order-1 recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Honor Reguler</p>
                        <h4 class="recap-card-value font-bold text-blue-700">Rp {{ number_format($regularHonorPayment, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Pembayaran honor kategori reguler</p>
                    </div>
                    <div class="recap-card-icon bg-blue-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between md:order-4 xl:order-last recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Total Honor</p>
                        <h4 class="recap-card-value font-bold text-emerald-700">Rp {{ number_format($totalHonorPayment, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Total honor dari seluruh kategori</p>
                    </div>
                    <div class="recap-card-icon bg-emerald-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-coins text-2xl text-emerald-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between md:order-2 recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Honor Corporate</p>
                        <h4 class="recap-card-value font-bold text-indigo-700">Rp {{ number_format($trainingHonorPayment, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Pembayaran honor kategori corporate</p>
                    </div>
                    <div class="recap-card-icon bg-indigo-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 h-full flex flex-col justify-between md:order-3 recap-card">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="recap-card-title text-gray-500 font-medium mb-1">Honor Private</p>
                        <h4 class="recap-card-value font-bold text-amber-700">Rp {{ number_format($privateHonorPayment, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs mt-1">Pembayaran honor kategori private</p>
                    </div>
                    <div class="recap-card-icon bg-amber-100 shrink-0 flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Class Revenue Chart -->
<div class="bg-white rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-line mr-2 text-green-600"></i>Grafik Keuangan Kelas per Bulan ({{ $selectedYear }})
                </h5>
                <p class="text-xs text-gray-500 mt-1">
            <span class="inline-block mr-3">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span>Pendapatan Kotor (Bruto)
            </span>
            <span class="inline-block mr-3">
                <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1"></span>Sisa Pembayaran (Unpaid)
            </span>
            <span class="inline-block">
                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-1"></span>Biaya Operasional
            </span>
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="classRevenueChartTypeBar" onclick="switchChartType('classRevenueChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-emerald-100 text-emerald-700 border-emerald-400 hover:bg-emerald-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="classRevenueChartTypeLine" onclick="switchChartType('classRevenueChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if(is_array($monthlyClassRevenue) && (array_sum($monthlyClassRevenue) > 0 || array_sum($monthlyRemainingPayment) > 0 || array_sum($monthlyClassCost) > 0))
            <div class="chart-container">
                <canvas id="classRevenueChart"></canvas>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-4xl text-gray-400"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Keuangan</h6>
                <p class="text-sm text-gray-500">
                    @if($period !== 'all' ||$year !== 'all')
                        Tidak ada data keuangan kelas untuk filter yang dipilih
                    @else
                        Belum ada data keuangan kelas tersedia
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Monthly Class Revenue by Class Chart -->
<div class="bg-white rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
            <div class="flex-1 min-w-0">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-coins mr-2 text-amber-600"></i>Grafik Omset per Kelas/Pelatihan Bulan Berjalan ({{ $currentMonthLabel }})
                </h5>
                <p class="text-xs text-gray-500 mt-1">Menampilkan kelas aktif yang overlap bulan ini dan kelas selesai pada bulan ini.</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                    <i class="fas fa-layer-group"></i>
                    {{ $currentMonthClassRevenueByClass->count() }} kelas aktif
                </div>
                <button id="currentMonthClassRevenueChartTypeBar" onclick="switchChartType('currentMonthClassRevenueChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-emerald-100 text-emerald-700 border-emerald-400 hover:bg-emerald-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="currentMonthClassRevenueChartTypeLine" onclick="switchChartType('currentMonthClassRevenueChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if($currentMonthClassRevenueByClass->count() > 0)
            <div class="chart-container-lg" style="height: {{ $currentMonthClassRevenueByClass->count() <= 3 ? '250px' : ($currentMonthClassRevenueByClass->count() <= 6 ? '310px' : '380px') }};">
                <canvas id="currentMonthClassRevenueChart"></canvas>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-coins text-4xl text-gray-400"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Omset Kelas Bulan Ini</h6>
                <p class="text-sm text-gray-500">Belum ada kelas aktif yang menghasilkan omset pada bulan berjalan.</p>
            </div>
        @endif
    </div>
</div>

<!-- Monthly Certification Revenue Chart -->
<div class="bg-white rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-certificate mr-2 text-emerald-600"></i>Grafik Omset Sertifikasi per Bulan ({{ $selectedYear }})
            </h5>
            <div class="flex gap-2 shrink-0">
                <button id="certificationRevenueChartTypeBar" onclick="switchChartType('certificationRevenueChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-emerald-100 text-emerald-700 border-emerald-400 hover:bg-emerald-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="certificationRevenueChartTypeLine" onclick="switchChartType('certificationRevenueChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if(is_array($monthlyCertificationRevenue) && array_sum($monthlyCertificationRevenue) > 0)
            <div class="chart-container">
                <canvas id="certificationRevenueChart"></canvas>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-4xl text-gray-400"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Omset Sertifikasi</h6>
                <p class="text-sm text-gray-500">Belum ada omset sertifikasi pada periode yang dipilih.</p>
            </div>
        @endif
    </div>
</div>

<!-- Monthly Class Count Chart -->
<div class="bg-white rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>Grafik Jumlah Kelas dan Siswa Sertifikasi per Bulan ({{ $selectedYear }})
                </h5>
                <p class="text-xs text-gray-500 mt-1">Siswa sertifikasi dihitung dari peserta yang benar-benar ikut sertifikasi, bukan total siswa kelas.</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="classCountChartTypeBar" onclick="switchChartType('classCountChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-purple-100 text-purple-700 border-purple-400 hover:bg-purple-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="classCountChartTypeLine" onclick="switchChartType('classCountChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if(is_array($monthlyClassCount) && (array_sum($monthlyClassCount) > 0 || array_sum($monthlyCertificationCount) > 0))
            <div class="chart-container">
                <canvas id="classCountChart"></canvas>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-bar text-4xl text-gray-400"></i>
                </div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Kelas</h6>
                <p class="text-sm text-gray-500">
                    @if($period !== 'all' || $year !== 'all')
                        Tidak ada kelas untuk filter yang dipilih
                    @else
                        Belum ada kelas tersedia
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Training Charts -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>Grafik Jumlah Siswa per Pelatihan ({{ $currentMonthLabel }})
            </h5>
            <p class="text-sm text-gray-500 mt-1">Total siswa dari kelas berjalan (status approved) yang mulai pada bulan ini.</p>
        </div>
        <div class="p-6">
            @if($currentMonthStudentsByTraining->count() > 0)
                <div class="chart-container">
                    <canvas id="currentMonthStudentsByTrainingChart"></canvas>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-bar text-5xl text-gray-300"></i>
                    </div>
                    <h6 class="text-lg font-semibold text-gray-600 mb-2">Belum Ada Data Siswa Bulan Ini</h6>
                    <p class="text-gray-400 text-sm mb-1">Coba ubah periode atau tunggu kelas aktif berjalan.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-certificate mr-2 text-emerald-600"></i>Grafik Siswa Sertifikasi per Pelatihan
                @if($status === 'completed')
                    (Selesai)
                @elseif($status === 'active')
                    (Aktif)
                @else
                    (Total)
                @endif
            </h5>
            <p class="text-sm text-gray-500 mt-1">Pelatihan yang memiliki peserta sertifikasi pada filter yang dipilih.</p>
        </div>
        <div class="p-6">
            @if($certificationByTraining->count() > 0)
                <div class="chart-container">
                    <canvas id="certificationByTrainingChart"></canvas>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-certificate text-5xl text-gray-300"></i>
                    </div>
                    <h6 class="text-lg font-semibold text-gray-600 mb-2">Belum Ada Data Sertifikasi</h6>
                    <p class="text-gray-400 text-sm mb-1">Belum ada siswa sertifikasi pada filter yang dipilih</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Real-time Target Omset -->
<div class="mb-6">
    <div id="target-omset" class="bg-white rounded-xl shadow-sm scroll-mt-6 overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h5 class="text-lg font-bold text-gray-900">Target Omset Bulanan</h5>
                    <p class="text-sm text-gray-500">{{ now()->translatedFormat('F Y') }} (Real-time)</p>
                </div>
                @if($targetAmount > 0)
                    <button type="button" onclick="openTargetModal()" class="border-2 border-green-500 text-green-700 bg-green-50 px-4 py-2 rounded-lg hover:bg-green-100 transition text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Target</span>
                    </button>
                @else
                    <button type="button" onclick="openTargetModal()" class="border-2 border-green-500 text-white bg-green-600 px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium flex items-center gap-2 shadow-md">
                        <i class="fas fa-plus"></i>
                        <span>Set Target</span>
                    </button>
                @endif
            </div>
        </div>

        @if($targetAmount > 0)
        <!-- Main Content -->
        <div class="p-8 pb-24">
            <!-- Big Percentage -->
            <div class="text-center mb-6">
                <div class="text-7xl font-black text-gray-900 mb-2">
                    {{ number_format($targetPercentage, 1) }}%
                </div>
                <p class="text-sm text-gray-500">dari target tercapai</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-20">
                <div class="w-full bg-blue-50 rounded-full h-4 overflow-hidden">
                    <div class="bg-green-600 h-full transition-all duration-500" 
                         style="width: {{ min($targetPercentage, 100) }}%">
                    </div>
                </div>
            </div>

            <!-- Revenue Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">Pendapatan Kotor</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($targetRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">Target Bulan Ini</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($targetAmount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        @else
        <!-- No Target Set -->
        <div class="p-12 text-center">
            <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-bullseye text-4xl text-gray-400"></i>
            </div>
            <h6 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Target</h6>
            <p class="text-gray-500 mb-6">Silakan set target omset bulanan terlebih dahulu untuk mulai tracking</p>
            <button onclick="openTargetModal()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                <i class="fas fa-plus mr-2"></i>Set Target Sekarang
            </button>
        </div>
        @endif
    </div>
</div>



<!-- Kelas Selesai Table -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-lg font-bold text-gray-900"><i class="fas fa-check-circle mr-2 text-blue-600"></i>Kelas Selesai</h5>
                <p class="text-sm text-gray-600">Kelas yang telah diselesaikan</p>
            </div>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                {{ $completedClassesList->count() }} Kelas
            </span>
        </div>
    </div>
    @if($completedClassesList->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Instansi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Siswa</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Pertemuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Income</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($completedClassesList as $class)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $class->instansi ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($class->end_date)
                            {{ $class->end_date->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->amount }}</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->meet }}x</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-green-600">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-6 text-center text-gray-500">Tidak ada kelas selesai</div>
    @endif
</div>

<!-- Kelas Berjalan Table -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-lg font-bold text-gray-900"><i class="fas fa-chalkboard-teacher mr-2 text-green-600"></i>Kelas Berjalan</h5>
                <p class="text-sm text-gray-600">Kelas yang sedang aktif berlangsung</p>
            </div>
            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                {{ $activeClassesList->count() }} Kelas
            </span>
        </div>
    </div>
    @if($activeClassesList->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Instansi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Siswa</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Pertemuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Income</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($activeClassesList as $class)
                @php
                    $totalDays = $class->start_date && $class->end_date ? $class->start_date->diffInDays($class->end_date) + 1 : 0;
                    $daysElapsed = $class->start_date ? $class->start_date->diffInDays(now()) + 1 : 0;
                    $progressPercent = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100)) : 0;
                @endphp
                <tr class="hover:bg-green-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-green-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $class->instansi ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($class->start_date && $class->end_date)
                            <div class="text-gray-900 font-medium">{{ $class->start_date->format('d M Y') }}</div>
                            <div class="text-gray-600">s/d {{ $class->end_date->format('d M Y') }}</div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->amount }}</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->meet }}x</span></td>
                    <td class="px-6 py-4">
                        @if($class->method == 'online')
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full"><i class="fas fa-laptop mr-1"></i>Online</span>
                        @else
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full"><i class="fas fa-building mr-1"></i>Offline</span>
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-green-600">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                    <td class="px-6 py-4">
                        <div class="w-20">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-gray-700">{{ $progressPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full transition-all" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-6 text-center text-gray-500">Tidak ada kelas berjalan</div>
    @endif
</div>

<!-- Kelas Pending Table -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-yellow-100">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-lg font-bold text-gray-900"><i class="fas fa-hourglass-half mr-2 text-yellow-600"></i>Kelas Pending</h5>
                <p class="text-sm text-gray-600">Kelas yang belum di-approve</p>
            </div>
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                {{ $pendingClassesList->count() }} Kelas
            </span>
        </div>
    </div>
    @if($pendingClassesList->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Instansi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Income</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pendingClassesList as $class)
                <tr class="hover:bg-yellow-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-yellow-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $class->instansi ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $class->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($class->start_date && $class->end_date)
                            {{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-green-600">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-6 text-center text-gray-500">Tidak ada kelas pending</div>
    @endif
</div>

<!-- Recent Activities -->
<div class="bg-white rounded-2xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h5 class="text-lg font-semibold text-gray-800"><i class="fas fa-history mr-2"></i>Aktivitas Terbaru</h5>
    </div>
    <div class="p-6">
        <div data-activities="container">
            @if($recentActivities->count() > 0)
                <div class="space-y-4">
                    @foreach($recentActivities as $activity)
                        <div class="flex justify-between items-start py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle text-green-500 text-xs mt-2"></i>
                                <div>
                                    <p class="text-gray-800">
                                        <strong>{{ $activity->user ? $activity->user->name : 'System' }}</strong>
                                        <span class="text-gray-600">- {{ $activity->description }}</span>
                                    </p>
                                </div>
                            </div>
                            <small class="text-gray-500 text-sm whitespace-nowrap ml-4">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Belum ada aktivitas</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal Set/Edit Target -->
<div id="targetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[100] flex items-center justify-center p-4" onclick="event.target === this && closeTargetModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-t-2xl flex justify-between items-center">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i class="fas fa-bullseye"></i>
                <span>Set Target Omset Bulanan</span>
            </h3>
            <button type="button" onclick="closeTargetModal()" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="targetForm" action="{{ route('admin.dashboard.save-target') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <!-- Current Month Info -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-green-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Target untuk: <span class="font-bold">{{ now()->format('F Y') }}</span> - <span class="font-bold">Alfa Bank</span>
                    </p>
                </div>

                <!-- Hidden Division Input -->
                <input type="hidden" name="division" value="academy">

                <!-- Target Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Target Omset (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="target_amount_display" id="target_amount_display" required
                           placeholder="Contoh: 50.000.000" 
                           value="{{ $targetAmount > 0 ? number_format($targetAmount, 0, ',', '.') : '' }}"
                           oninput="formatCurrencyDashboard(this)"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                    <input type="hidden" name="target_amount" id="target_amount" value="{{ $targetAmount > 0 ? $targetAmount : '' }}">
                    <p class="text-xs text-gray-500 mt-1.5">Gunakan format ribuan dengan titik (contoh: 50.000.000)</p>
                </div>

                <!-- Error display dalam modal -->
                <div id="modalError" class="hidden bg-red-50 border-l-4 border-red-500 p-3 rounded">
                    <p class="text-red-700 text-sm"></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeTargetModal()" 
                        class="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="submit" id="submitTargetBtn"
                        class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition shadow-md hover:shadow-lg font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // ========================================
    // MODAL TARGET FUNCTIONS
    // ========================================
    function openTargetModal() {
        const modal = document.getElementById('targetModal');
        const inputDisplay = document.getElementById('target_amount_display');
        
        if (!modal) {
            console.error('Modal targetModal tidak ditemukan!');
            return;
        }
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Auto focus ke input setelah modal muncul
        setTimeout(() => {
            if (inputDisplay) {
                inputDisplay.focus();
                inputDisplay.select(); // Select text jika ada value
            }
        }, 100);
        
        console.log('Modal target dibuka'); // Debug log
    }
    
    function closeTargetModal() {
        const modal = document.getElementById('targetModal');
        
        if (!modal) {
            console.error('Modal targetModal tidak ditemukan!');
            return;
        }
        
        // Hide modal
        modal.classList.add('hidden');
        
        // Restore body scroll
        document.body.style.overflow = 'auto';
        
        console.log('Modal target ditutup'); // Debug log
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('targetModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeTargetModal();
            }
        }
    });

    // Format currency untuk dashboard
    function formatCurrencyDashboard(input) {
        let value = input.value.replace(/\D/g, '');
        const hiddenInput = document.getElementById('target_amount');
        
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
            if (hiddenInput) {
                hiddenInput.value = value;
                console.log('✅ Hidden input diset:', value);
            }
        } else {
            input.value = '';
            if (hiddenInput) {
                hiddenInput.value = '';
                console.log('⚠️ Hidden input dikosongkan');
            }
        }
    }

    // Form submission handler dengan validation dan logging
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('targetForm');
        const modalError = document.getElementById('modalError');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('🔄 Form submit triggered');
                
                const displayInput = document.getElementById('target_amount_display');
                const hiddenInput = document.getElementById('target_amount');
                const divisionInput = document.querySelector('input[name="division"]');
                
                // Log semua values
                console.log('📊 Form values:');
                console.log('- Display input:', displayInput?.value);
                console.log('- Hidden input:', hiddenInput?.value);
                console.log('- Division:', divisionInput?.value);
                console.log('- Form action:', form.action);
                console.log('- Form method:', form.method);
                
                // Validasi hidden input harus terisi
                if (!hiddenInput || !hiddenInput.value || hiddenInput.value === '0') {
                    e.preventDefault();
                    console.error('❌ Validasi gagal: Hidden input kosong atau 0');
                    
                    // Show error dalam modal
                    if (modalError) {
                        const errorText = modalError.querySelector('p');
                        errorText.textContent = 'Masukkan target omset yang valid (minimal Rp 1)';
                        modalError.classList.remove('hidden');
                        
                        // Hide error after 5 seconds
                        setTimeout(() => {
                            modalError.classList.add('hidden');
                        }, 5000);
                    }
                    
                    // Focus ke input
                    if (displayInput) {
                        displayInput.focus();
                    }
                    return false;
                }
                
                // Disable submit button untuk prevent double submit
                const submitBtn = document.getElementById('submitTargetBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                    console.log('⏳ Submit button disabled, menunggu response...');
                }
                
                console.log('✅ Validasi berhasil, form akan disubmit');
                // Form akan otomatis submit karena tidak ada e.preventDefault()
            });
        } else {
            console.error('❌ Form targetForm tidak ditemukan!');
        }
    });

    // ========================================
    // CHART INITIALIZATION
    // ========================================
    
    // Chart.js default config
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.color = '#6b7280';

    let dashboardCharts = {}; // Store chart instances

    // ========================================
    // TRAINING CLASS REVENUE CHART (Handled by Toggle System)
    // ========================================
    // Chart initialization now handled by switchChartType toggle system below
    
    // ========================================
    // TRAINING CLASS COUNT CHART (Handled by Toggle System)
    // ========================================
    // Chart initialization now handled by switchChartType toggle system below

    // ========================================
    // CERTIFICATION REVENUE CHART (Handled by Toggle System)
    // ========================================
    // Chart initialization now handled by switchChartType toggle system below

    const certificationByTrainingCtx = document.getElementById('certificationByTrainingChart');
    if (certificationByTrainingCtx) {
        const certificationByTraining = {!! json_encode($certificationByTraining) !!};
        const certificationTrainingNames = certificationByTraining.map(item => {
            const name = item.training ? item.training.name : 'Unknown';
            return name.length > 30 ? name.substring(0, 30) + '...' : name;
        });
        const certificationStudentTotals = certificationByTraining.map(item => item.total_students);

        const colors = [
            'rgba(16, 185, 129, 0.85)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(6, 182, 212, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(132, 204, 22, 0.8)',
            'rgba(20, 184, 166, 0.8)'
        ];

        new Chart(certificationByTrainingCtx, {
            type: 'doughnut',
            data: {
                labels: certificationTrainingNames,
                datasets: [{
                    label: 'Jumlah Siswa Sertifikasi',
                    data: certificationStudentTotals,
                    backgroundColor: colors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 11
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value} siswa`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const fullName = certificationByTraining[context.dataIndex].training
                                    ? certificationByTraining[context.dataIndex].training.name
                                    : 'Unknown';
                                return fullName + ': ' + context.parsed + ' siswa';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // ========================================
    // CHART TOGGLE SYSTEM
    // ========================================
    
    let adminChartInstances = {};
    let adminChartTypes = {
        'classRevenueChart': 'line',
        'classCountChart': 'bar',
        'certificationRevenueChart': 'line',
        'currentMonthClassRevenueChart': 'bar'
    };
    
    function switchChartType(chartName, newType) {
        if (adminChartTypes[chartName] === newType) return;
        if (!adminChartInstances[chartName]) return;
        
        // Destroy old chart
        adminChartInstances[chartName].destroy();
        
        // Update type
        adminChartTypes[chartName] = newType;
        
        // Recreate chart
        recreateAdminChart(chartName, newType);
        
        // Update button styles
        updateAdminChartButtons(chartName, newType);
    }
    
    function updateAdminChartButtons(chartName, currentType) {
        const barBtn = document.getElementById(chartName + 'TypeBar');
        const lineBtn = document.getElementById(chartName + 'TypeLine');
        
        if (!barBtn || !lineBtn) return;
        
        if (currentType === 'bar') {
            barBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            barBtn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            lineBtn.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            lineBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        } else {
            lineBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
            lineBtn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            barBtn.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-400');
            barBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
        }
    }
    
    function getAdminChartConfig(chartName, type) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { padding: 15, font: { size: 12, weight: 'bold' } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false } }
            }
        };
        
        let data, options = baseOptions;
        
        if (chartName === 'classRevenueChart') {
            const monthlyClassRevenue = {!! json_encode($monthlyClassRevenue) !!};
            const monthlyRemainingPayment = {!! json_encode($monthlyRemainingPayment) !!};
            const monthlyClassCost = {!! json_encode($monthlyClassCost) !!};
            
            data = {
                labels: months,
                datasets: [
                    {
                        label: 'Pendapatan Kotor (Bruto)',
                        data: monthlyClassRevenue,
                        backgroundColor: type === 'bar' ? 'rgba(34, 197, 94, 0.8)' : 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: type === 'bar' ? 6 : 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Sisa Pembayaran (Unpaid)',
                        data: monthlyRemainingPayment,
                        backgroundColor: type === 'bar' ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.1)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: false,
                        tension: 0.4,
                        borderDash: [5, 5],
                        borderRadius: type === 'bar' ? 6 : 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Biaya Operasional',
                        data: monthlyClassCost,
                        backgroundColor: type === 'bar' ? 'rgba(59, 130, 246, 0.8)' : 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: false,
                        tension: 0.4,
                        borderRadius: type === 'bar' ? 6 : 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    }
                ]
            };
            options.plugins.tooltip = { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } };
            options.scales.y.ticks = { callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' };
        } else if (chartName === 'classCountChart') {
            const monthlyClassCount = {!! json_encode($monthlyClassCount) !!};
            const monthlyCert = {!! json_encode($monthlyCertificationCount) !!};
            
            data = {
                labels: months,
                datasets: [
                    {
                        label: 'Jumlah Kelas',
                        data: monthlyClassCount,
                        backgroundColor: type === 'bar' ? 'rgba(139, 92, 246, 0.8)' : 'rgba(139, 92, 246, 0.2)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: type === 'bar' ? 8 : 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Jumlah Siswa Sertifikasi',
                        data: monthlyCert,
                        backgroundColor: type === 'bar' ? 'rgba(34, 197, 94, 0.75)' : 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: type === 'bar' ? 8 : 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    }
                ]
            };
            options.scales.y.ticks = { stepSize: 1, callback: v => Number.isInteger(v) ? v : '' };
            options.plugins.tooltip = { callbacks: { label: c => c.dataset.label === 'Jumlah Siswa Sertifikasi' ? 'Siswa: ' + c.parsed.y : 'Kelas: ' + c.parsed.y } };
        } else if (chartName === 'certificationRevenueChart') {
            const monthlyCertRev = {!! json_encode($monthlyCertificationRevenue) !!};
            
            data = {
                labels: months,
                datasets: [{
                    label: 'Omset Sertifikasi',
                    data: monthlyCertRev,
                    backgroundColor: type === 'bar' ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: type === 'bar' ? 2 : 3,
                    fill: type === 'line',
                    tension: 0.35,
                    borderRadius: type === 'bar' ? 6 : 0,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                }]
            };
            options.plugins.tooltip = { callbacks: { label: c => 'Omset: Rp ' + c.parsed.y.toLocaleString('id-ID') } };
            options.scales.y.ticks = { callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt' };
        } else if (chartName === 'currentMonthClassRevenueChart') {
            const currentMonthClassRevenueByClass = {!! json_encode($currentMonthClassRevenueByClass) !!};
            const currentMonthClassRevenueLabels = currentMonthClassRevenueByClass.map(item => {
                const label = item.class_name || 'Tanpa Nama Kelas';
                return label.length > 28 ? label.substring(0, 28) + '...' : label;
            });
            const currentMonthClassRevenueTotals = currentMonthClassRevenueByClass.map(item => item.total_revenue);

            data = {
                labels: currentMonthClassRevenueLabels,
                datasets: [{
                    label: 'Omset Kelas',
                    data: currentMonthClassRevenueTotals,
                    backgroundColor: type === 'bar' ? 'rgba(245, 158, 11, 0.8)' : 'rgba(245, 158, 11, 0.15)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: type === 'bar' ? 1 : 3,
                    fill: type === 'line',
                    tension: 0.35,
                    borderRadius: type === 'bar' ? 8 : 0,
                    categoryPercentage: 0.5,
                    barPercentage: 0.6,
                    maxBarThickness: 56,
                    pointRadius: type === 'line' ? 4 : 0,
                    pointHoverRadius: type === 'line' ? 6 : 0
                }]
            };

            options.plugins.legend = { display: false };
            options.plugins.tooltip = {
                callbacks: {
                    label: function(context) {
                        const item = currentMonthClassRevenueByClass[context.dataIndex];
                        const fullName = item.class_name + ' - ' + item.training_name;
                        const value = context.parsed.y ?? context.parsed;
                        return fullName + ': Rp ' + Number(value).toLocaleString('id-ID');
                    }
                }
            };
            options.scales.x.ticks = {
                font: { size: 11 },
                maxRotation: 40,
                minRotation: 0
            };
            options.scales.y.beginAtZero = true;
            options.scales.y.grace = '8%';
            options.scales.y.ticks = {
                callback: function(value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            };
        }
        
        return { type, data, options };
    }
    
    function recreateAdminChart(chartName, type) {
        const ctx = document.getElementById(chartName);
        if (!ctx) return;
        
        const config = getAdminChartConfig(chartName, type);
        adminChartInstances[chartName] = new Chart(ctx, config);
    }
    
    // Reinitialize charts with toggle support
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            ['classRevenueChart', 'classCountChart', 'certificationRevenueChart', 'currentMonthClassRevenueChart'].forEach(name => {
                const ctx = document.getElementById(name);
                if (ctx && !adminChartInstances[name]) {
                    recreateAdminChart(name, adminChartTypes[name]);
                    updateAdminChartButtons(name, adminChartTypes[name]);
                }
            });
        }, 100);
    });

    const currentMonthStudentsByTrainingCtx = document.getElementById('currentMonthStudentsByTrainingChart');
    if (currentMonthStudentsByTrainingCtx) {
        const currentMonthStudentsByTraining = {!! json_encode($currentMonthStudentsByTraining) !!};
        const currentMonthStudentLabels = currentMonthStudentsByTraining.map(item => {
            const name = item.training ? item.training.name : 'Unknown';
            return name.length > 30 ? name.substring(0, 30) + '...' : name;
        });
        const currentMonthStudentTotals = currentMonthStudentsByTraining.map(item => item.total_students);
        const studentColors = [
            'rgba(79, 70, 229, 0.85)',
            'rgba(16, 185, 129, 0.85)',
            'rgba(245, 158, 11, 0.85)',
            'rgba(239, 68, 68, 0.85)',
            'rgba(14, 165, 233, 0.85)',
            'rgba(168, 85, 247, 0.85)',
            'rgba(236, 72, 153, 0.85)',
            'rgba(34, 197, 94, 0.85)'
        ];

        new Chart(currentMonthStudentsByTrainingCtx, {
            type: 'doughnut',
            data: {
                labels: currentMonthStudentLabels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: currentMonthStudentTotals,
                    backgroundColor: currentMonthStudentLabels.map((_, index) => studentColors[index % studentColors.length]),
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 14,
                            boxWidth: 12,
                            boxHeight: 12,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (!data.labels.length || !data.datasets.length) {
                                    return [];
                                }

                                return data.labels.map((label, index) => {
                                    const value = data.datasets[0].data[index];
                                    return {
                                        text: `${label}: ${value.toLocaleString('id-ID')} siswa`,
                                        fillStyle: data.datasets[0].backgroundColor[index],
                                        hidden: false,
                                        index: index
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || 'Unknown';
                                return `${label}: ${context.parsed.toLocaleString('id-ID')} siswa`;
                            }
                        }
                    }
                }
            }
        });
    }

</script>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
@endpush

@endsection
