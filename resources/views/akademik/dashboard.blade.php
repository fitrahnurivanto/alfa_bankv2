@extends('layouts.app')

@section('page-title', 'Dashboard Akademik')

@section('content')
@php
    $isAdminViewer = \Illuminate\Support\Facades\Auth::user()?->isAdmin();
    $dashboardRoute = $isAdminViewer ? route('admin.dashboard.akademik') : route('akademik.dashboard');
@endphp

<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Akademik</h1>
        <p class="text-sm text-gray-600 mt-1">Monitoring kelas, peserta, kelulusan, dan rekap absensi instruktur</p>
        @if($isAdminViewer)
            <div class="inline-flex items-center gap-1 p-1 mt-2 rounded-full bg-gray-100 border border-gray-200">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-chart-bar mr-1"></i>Admin</a>
                <a href="{{ route('admin.dashboard.akademik') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-purple-600 text-white shadow-md hover:shadow-lg"><i class="fas fa-graduation-cap mr-1"></i>Akademik</a>
                <a href="{{ route('admin.dashboard.finance') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-money-bill-wave mr-1"></i>Finance</a>
            </div>
        @endif
    </div>

    <form method="GET" action="{{ $dashboardRoute }}" class="w-full lg:w-auto flex flex-col sm:flex-row gap-2">
        <select name="period" onchange="this.form.submit()" class="w-full lg:w-auto px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
            <option value="all" {{ ($period ?? ('month_' . date('m'))) == 'all' ? 'selected' : '' }}>Semua Periode</option>
            <option value="month_01" {{ ($period ?? '') == 'month_01' ? 'selected' : '' }}>Januari</option>
            <option value="month_02" {{ ($period ?? '') == 'month_02' ? 'selected' : '' }}>Februari</option>
            <option value="month_03" {{ ($period ?? '') == 'month_03' ? 'selected' : '' }}>Maret</option>
            <option value="month_04" {{ ($period ?? '') == 'month_04' ? 'selected' : '' }}>April</option>
            <option value="month_05" {{ ($period ?? '') == 'month_05' ? 'selected' : '' }}>Mei</option>
            <option value="month_06" {{ ($period ?? '') == 'month_06' ? 'selected' : '' }}>Juni</option>
            <option value="month_07" {{ ($period ?? '') == 'month_07' ? 'selected' : '' }}>Juli</option>
            <option value="month_08" {{ ($period ?? '') == 'month_08' ? 'selected' : '' }}>Agustus</option>
            <option value="month_09" {{ ($period ?? '') == 'month_09' ? 'selected' : '' }}>September</option>
            <option value="month_10" {{ ($period ?? '') == 'month_10' ? 'selected' : '' }}>Oktober</option>
            <option value="month_11" {{ ($period ?? '') == 'month_11' ? 'selected' : '' }}>November</option>
            <option value="month_12" {{ ($period ?? '') == 'month_12' ? 'selected' : '' }}>Desember</option>
        </select>
        <select name="year" onchange="this.form.submit()" class="w-full lg:w-auto px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
            @foreach(($years ?? collect([date('Y')])) as $yearItem)
                <option value="{{ $yearItem }}" {{ (int)($year ?? date('Y')) === (int)$yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
            @endforeach
        </select>
    </form>

    @unless($isAdminViewer)
        <a href="{{ route('akademik.classes.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i class="fas fa-list mr-2"></i>Detail Kelas Akademik
        </a>
    @endunless
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4 mb-8">
    @php
        $runningRegularClasses = (int) $runningClassByCategory->filter(function ($item) {
            return stripos((string) $item->category_name, 'regular') !== false;
        })->sum('total_classes');

        $runningCorporateClasses = (int) $runningClassByCategory->filter(function ($item) {
            return stripos((string) $item->category_name, 'corporate') !== false;
        })->sum('total_classes');

        $runningPrivateClasses = (int) $runningClassByCategory->filter(function ($item) {
            return stripos((string) $item->category_name, 'private') !== false;
        })->sum('total_classes');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
        <div class="p-4 md:p-6">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Total Kelas Berjalan</p>
                    <h4 class="text-3xl font-bold text-blue-700 mb-1">{{ $runningClassesCount }}</h4>
                    <p class="text-gray-400 text-xs">Status approved</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-xl flex-shrink-0">
                    <i class="fas fa-play-circle text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
        <div class="p-4 md:p-6">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelas Reguler</p>
                    <h4 class="text-3xl font-bold text-blue-700 mb-1">{{ $runningRegularClasses }}</h4>
                    <p class="text-gray-400 text-xs">Jumlah kelas kategori reguler</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-xl flex-shrink-0">
                    <i class="fas fa-book-open text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
        <div class="p-4 md:p-6">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelas Corporate</p>
                    <h4 class="text-3xl font-bold text-indigo-700 mb-1">{{ $runningCorporateClasses }}</h4>
                    <p class="text-gray-400 text-xs">Jumlah kelas kategori corporate</p>
                </div>
                <div class="bg-indigo-100 p-3 rounded-xl flex-shrink-0">
                    <i class="fas fa-building text-2xl text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
        <div class="p-4 md:p-6">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelas Private</p>
                    <h4 class="text-3xl font-bold text-amber-700 mb-1">{{ $runningPrivateClasses }}</h4>
                    <p class="text-gray-400 text-xs">Jumlah kelas kategori private</p>
                </div>
                <div class="bg-amber-100 p-3 rounded-xl flex-shrink-0">
                    <i class="fas fa-user text-2xl text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 px-1">Peserta per Kategori</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Peserta Reguler</p>
                        <h4 class="text-3xl font-bold text-blue-700 mb-1">{{ number_format($regularParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs">Jumlah peserta kategori reguler</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Peserta Corporate</p>
                        <h4 class="text-3xl font-bold text-amber-700 mb-1">{{ number_format($corporateParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs">Jumlah peserta kategori corporate</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-building text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Peserta Private</p>
                        <h4 class="text-3xl font-bold text-purple-700 mb-1">{{ number_format($privateParticipants, 0, ',', '.') }}</h4>
                        <p class="text-gray-400 text-xs">Jumlah peserta kategori private</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-user text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 px-1">Lulus dan Tidak Lulus per Kategori</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelulusan Reguler</p>
                        <h4 class="text-lg md:text-xl font-bold text-emerald-700">Lulus: {{ number_format($regularPassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-lg md:text-xl font-bold text-rose-700">Tidak Lulus: {{ number_format($regularFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-book-open text-2xl text-emerald-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelulusan Corporate</p>
                        <h4 class="text-lg md:text-xl font-bold text-emerald-700">Lulus: {{ number_format($corporatePassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-lg md:text-xl font-bold text-rose-700">Tidak Lulus: {{ number_format($corporateFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-building text-2xl text-emerald-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
            <div class="p-4 md:p-5">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Kelulusan Private</p>
                        <h4 class="text-lg md:text-xl font-bold text-emerald-700">Lulus: {{ number_format($privatePassedParticipants, 0, ',', '.') }}</h4>
                        <h5 class="text-lg md:text-xl font-bold text-rose-700">Tidak Lulus: {{ number_format($privateFailedParticipants, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-xl flex-shrink-0">
                        <i class="fas fa-user-xmark text-2xl text-emerald-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 md:gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="mb-4 md:mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
                <h3 class="text-base md:text-lg font-bold text-gray-900 mb-1">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>Grafik Jumlah Kelas per Kategori ({{ $selectedYear }})
                </h3>
                <p class="text-xs md:text-sm text-gray-600">Jan-Des, 3 kategori per bulan: Regular, Corporate, Private</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="classByCategoryChartTypeBar" onclick="switchAkademikChartType('classByCategoryChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-purple-100 text-purple-700 border-purple-400 hover:bg-purple-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="classByCategoryChartTypeLine" onclick="switchAkademikChartType('classByCategoryChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
        <div class="w-full overflow-x-auto">
            <canvas id="classByCategoryChart" class="max-w-full" style="min-height: 320px; max-height: 440px;"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-chart-bar mr-2 text-emerald-600"></i>Peserta Lulus per Training (Per Kategori)
        </h3>
        <p class="text-xs text-gray-500 mb-4">Periode: {{ $currentMonthLabel ?? now()->translatedFormat('F Y') }}</p>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Regular Training Chart -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 md:p-5">
                <h4 class="font-semibold text-blue-900 mb-3 text-sm md:text-base">
                    <i class="fas fa-book-open text-blue-600 mr-2"></i>Regular
                </h4>
                <div class="w-full overflow-x-auto">
                    <canvas id="regularTrainingChart" style="min-height: 280px; max-height: 320px;"></canvas>
                </div>
            </div>

            <!-- Corporate Training Chart -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 md:p-5">
                <h4 class="font-semibold text-indigo-900 mb-3 text-sm md:text-base">
                    <i class="fas fa-building text-indigo-600 mr-2"></i>Corporate
                </h4>
                <div class="w-full overflow-x-auto">
                    <canvas id="corporateTrainingChart" style="min-height: 280px; max-height: 320px;"></canvas>
                </div>
            </div>

            <!-- Private Training Chart -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 md:p-5">
                <h4 class="font-semibold text-amber-900 mb-3 text-sm md:text-base">
                    <i class="fas fa-user text-amber-600 mr-2"></i>Private
                </h4>
                <div class="w-full overflow-x-auto">
                    <canvas id="privateTrainingChart" style="min-height: 280px; max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Original Chart (commented out, keeping for reference) -->
    <!--
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-6">
        <div class="mb-4 md:mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
                <h3 class="text-base md:text-lg font-bold text-gray-900 mb-1">
                    <i class="fas fa-user-graduate mr-2 text-emerald-600"></i>Grafik Peserta Lulus per Kategori
                </h3>
                <p class="text-xs md:text-sm text-gray-600">Periode: {{ $currentMonthLabel ?? now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="passedByProgramChartTypeBar" onclick="switchAkademikChartType('passedByProgramChart', 'bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-emerald-100 text-emerald-700 border-emerald-400 hover:bg-emerald-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="passedByProgramChartTypeLine" onclick="switchAkademikChartType('passedByProgramChart', 'line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
        <div class="w-full overflow-x-auto">
            <canvas id="passedByProgramChart" class="max-w-full" style="min-height: 320px; max-height: 440px;"></canvas>
        </div>
    </div>
    -->
</div>

<!-- Rekap Kelas by Status -->
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
                        <a href="{{ route('akademik.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
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
    <div class="p-6 text-center text-gray-500">Belum ada kelas selesai dalam periode ini</div>
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
                        <a href="{{ route('akademik.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-green-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($class->start_date && $class->end_date)
                            {{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->amount }}</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->meet }}x</span></td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        @if($class->training_type)
                            {{ ucfirst($class->training_type) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-green-600">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 w-48">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 w-12 text-right">{{ $progressPercent }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-6 text-center text-gray-500">Belum ada kelas berjalan dalam periode ini</div>
    @endif
</div>

<!-- Kelas Pending Table -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-amber-100">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-lg font-bold text-gray-900"><i class="fas fa-hourglass-half mr-2 text-amber-600"></i>Kelas Pending</h5>
                <p class="text-sm text-gray-600">Kelas menunggu persetujuan</p>
            </div>
            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-sm font-semibold rounded-full">
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
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Periode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Siswa</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Pertemuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Income</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pendingClassesList as $class)
                <tr class="hover:bg-amber-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('akademik.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-amber-600">{{ $class->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($class->trainers->isNotEmpty())
                            {{ $class->trainers->pluck('name')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($class->start_date && $class->end_date)
                            {{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->amount ?? '-' }}</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-gray-900">{{ $class->meet ?? '-' }}x</span></td>
                    <td class="px-6 py-4"><span class="font-semibold text-amber-600">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-6 text-center text-gray-500">Belum ada kelas pending dalam periode ini</div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="p-4 md:p-6 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-900">Tabel Peserta Lulus dan Tidak Lulus</h3>
        <p class="text-xs text-gray-500 mt-1">Data diisi akademik pada halaman detail kelas</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kelas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Program</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Peserta</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Lulus</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tidak Lulus</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($graduationTable as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <a href="{{ route('akademik.classes.show', $row['class_id']) }}" class="text-purple-700 hover:underline">{{ $row['class_name'] }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['category_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['program_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($row['participants'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-700">{{ number_format($row['passed'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-rose-700">{{ number_format($row['failed'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kelas dengan nilai approved.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-900">Presentase Rekap Absen Tiap Instruktur</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Instruktur</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Hadir</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Terlambat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Rata-rata Telat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">% Kehadiran</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">% Keterlambatan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($instructorAttendanceRecap as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['trainer_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($row['attended_sessions'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['late_sessions'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                            {{ number_format($row['late_sessions'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['avg_late_minutes'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                            {{ $row['avg_late_minutes'] }} mnt
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['attendance_percentage'] >= 80 ? 'text-emerald-700' : ($row['attendance_percentage'] >= 60 ? 'text-amber-700' : 'text-rose-700') }}">
                            {{ $row['attendance_percentage'] }}%
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['late_percentage'] <= 10 ? 'text-emerald-700' : ($row['late_percentage'] <= 20 ? 'text-amber-700' : 'text-rose-700') }}">
                            {{ $row['late_percentage'] }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data absensi instruktur.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthLabels = @json($monthLabels);
        const regularMonthlyClasses = @json($regularMonthlyClasses);
        const corporateMonthlyClasses = @json($corporateMonthlyClasses);
        const privateMonthlyClasses = @json($privateMonthlyClasses);
        const regularTrainingsData = @json($regularTrainingsPassData);
        const corporateTrainingsData = @json($corporateTrainingsPassData);
        const privateTrainingsData = @json($privateTrainingsPassData);

        const isMobile = () => window.innerWidth < 768;

        const classCanvas = document.getElementById('classByCategoryChart');
        const regularTrainingCanvas = document.getElementById('regularTrainingChart');
        const corporateTrainingCanvas = document.getElementById('corporateTrainingChart');
        const privateTrainingCanvas = document.getElementById('privateTrainingChart');

        function setCanvasHeight(canvas) {
            if (!canvas) return;
            canvas.style.height = isMobile() ? '280px' : '300px';
        }

        setCanvasHeight(classCanvas);
        setCanvasHeight(regularTrainingCanvas);
        setCanvasHeight(corporateTrainingCanvas);
        setCanvasHeight(privateTrainingCanvas);

        const akademikCharts = {};
        const akademikChartTypes = {
            classByCategoryChart: 'bar'
        };

        function updateAkademikButtons(chartKey, currentType) {
            const barBtn = document.getElementById(chartKey + 'TypeBar');
            const lineBtn = document.getElementById(chartKey + 'TypeLine');
            if (!barBtn || !lineBtn) return;

            const activeClasses = chartKey === 'passedByProgramChart'
                ? ['bg-emerald-100', 'text-emerald-700', 'border-emerald-400']
                : ['bg-purple-100', 'text-purple-700', 'border-purple-400'];
            const inactiveClasses = ['bg-gray-100', 'text-gray-600', 'border-gray-200'];

            if (currentType === 'bar') {
                barBtn.classList.remove(...inactiveClasses);
                barBtn.classList.add(...activeClasses);
                lineBtn.classList.remove(...activeClasses);
                lineBtn.classList.add(...inactiveClasses);
            } else {
                lineBtn.classList.remove(...inactiveClasses);
                lineBtn.classList.add(...activeClasses);
                barBtn.classList.remove(...activeClasses);
                barBtn.classList.add(...inactiveClasses);
            }
        }

        function switchAkademikChartType(chartKey, newType) {
            if (akademikChartTypes[chartKey] === newType) return;
            if (akademikCharts[chartKey]) {
                akademikCharts[chartKey].destroy();
            }
            akademikChartTypes[chartKey] = newType;
            createAkademikChart(chartKey, newType);
            updateAkademikButtons(chartKey, newType);
        }

        window.switchAkademikChartType = switchAkademikChartType;

        function getChartBaseOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: true,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: isMobile() ? 'bottom' : 'top',
                        labels: {
                            usePointStyle: true,
                            padding: isMobile() ? 10 : 15,
                            font: {
                                size: isMobile() ? 10 : 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: isMobile() ? 8 : 12,
                        titleFont: {
                            size: isMobile() ? 12 : 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: isMobile() ? 11 : 13
                        },
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#4b5563',
                            font: { size: isMobile() ? 9 : 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#4b5563',
                            font: { size: isMobile() ? 9 : 11 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                        }
                    }
                }
            };
        }

        function createMiniTrainingChart(canvasId, trainingData, categoryColor, categoryLabel) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const labels = trainingData.map(item => item.training_name || 'Unknown');
            const data = trainingData.map(item => parseInt(item.passed_total) || 0);

            const palette = [
                { bg: categoryColor === 'regular' ? 'rgba(59, 130, 246, 0.8)' : categoryColor === 'corporate' ? 'rgba(99, 102, 241, 0.8)' : 'rgba(245, 158, 11, 0.85)', 
                  border: categoryColor === 'regular' ? 'rgba(37, 99, 235, 1)' : categoryColor === 'corporate' ? 'rgba(79, 70, 229, 1)' : 'rgba(217, 119, 6, 1)' }
            ];

            const bgColors = data.map((_, idx) => palette[idx % palette.length].bg);
            const borderColors = data.map((_, idx) => palette[idx % palette.length].border);

            akademikCharts[canvasId] = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: categoryLabel + ' Peserta Lulus',
                        data: data,
                        backgroundColor: bgColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    ...getChartBaseOptions(),
                    plugins: {
                        ...getChartBaseOptions().plugins,
                        legend: {
                            display: false
                        },
                        tooltip: {
                            ...getChartBaseOptions().plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return Number(context.parsed.y || 0).toLocaleString('id-ID') + ' peserta';
                                }
                            }
                        }
                    }
                }
            });
        }

        function createAkademikChart(chartKey, type) {
            const baseOptions = getChartBaseOptions();

            if (chartKey === 'classByCategoryChart' && classCanvas) {
                const regularStyle = {
                    backgroundColor: type === 'bar' ? 'rgba(59, 130, 246, 0.75)' : 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: type === 'bar' ? 1 : 2,
                    borderRadius: type === 'bar' ? 6 : 0,
                    maxBarThickness: 24,
                    fill: type === 'line',
                    tension: type === 'line' ? 0.35 : 0,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                };
                const corporateStyle = {
                    backgroundColor: type === 'bar' ? 'rgba(99, 102, 241, 0.75)' : 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: type === 'bar' ? 1 : 2,
                    borderRadius: type === 'bar' ? 6 : 0,
                    maxBarThickness: 24,
                    fill: type === 'line',
                    tension: type === 'line' ? 0.35 : 0,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                };
                const privateStyle = {
                    backgroundColor: type === 'bar' ? 'rgba(245, 158, 11, 0.8)' : 'rgba(245, 158, 11, 0.2)',
                    borderColor: 'rgba(217, 119, 6, 1)',
                    borderWidth: type === 'bar' ? 1 : 2,
                    borderRadius: type === 'bar' ? 6 : 0,
                    maxBarThickness: 24,
                    fill: type === 'line',
                    tension: type === 'line' ? 0.35 : 0,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                };

                akademikCharts[chartKey] = new Chart(classCanvas.getContext('2d'), {
                    type: type,
                    data: {
                        labels: monthLabels,
                        datasets: [{ label: 'Regular', data: regularMonthlyClasses, ...regularStyle }, { label: 'Corporate', data: corporateMonthlyClasses, ...corporateStyle }, { label: 'Private', data: privateMonthlyClasses, ...privateStyle }]
                    },
                    options: {
                        ...baseOptions,
                        plugins: {
                            ...baseOptions.plugins,
                            tooltip: {
                                ...baseOptions.plugins.tooltip,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + Number(context.parsed.y || 0).toLocaleString('id-ID') + ' kelas';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Initialize charts
        createAkademikChart('classByCategoryChart', 'bar');
        updateAkademikButtons('classByCategoryChart', 'bar');
        
        // Create 3 mini training charts
        createMiniTrainingChart('regularTrainingChart', regularTrainingsData, 'regular', 'Regular');
        createMiniTrainingChart('corporateTrainingChart', corporateTrainingsData, 'corporate', 'Corporate');
        createMiniTrainingChart('privateTrainingChart', privateTrainingsData, 'private', 'Private');

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                setCanvasHeight(classCanvas);
                setCanvasHeight(regularTrainingCanvas);
                setCanvasHeight(corporateTrainingCanvas);
                setCanvasHeight(privateTrainingCanvas);

                if (akademikCharts.classByCategoryChart) {
                    akademikCharts.classByCategoryChart.options.plugins.legend.position = isMobile() ? 'bottom' : 'top';
                    akademikCharts.classByCategoryChart.options.plugins.legend.labels.padding = isMobile() ? 10 : 15;
                    akademikCharts.classByCategoryChart.options.plugins.legend.labels.font.size = isMobile() ? 10 : 12;
                    akademikCharts.classByCategoryChart.update();
                }

                ['regularTrainingChart', 'corporateTrainingChart', 'privateTrainingChart'].forEach(key => {
                    if (akademikCharts[key]) {
                        akademikCharts[key].resize();
                    }
                });
            }, 250);
        });
    });
</script>
@endsection
