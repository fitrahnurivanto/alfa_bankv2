@extends('layouts.app')

@section('page-title', 'Finance Dashboard')

@section('content')
@php
    $isAdminViewer = \Illuminate\Support\Facades\Auth::user()?->isAdmin();
    $dashboardRoute = $isAdminViewer ? route('admin.dashboard.finance') : route('finance.dashboard');
@endphp

<!-- Header -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 md:mb-6 gap-3">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900">Dashboard Finance</h1>
        <p class="text-xs md:text-sm text-gray-600 mt-1">Overview keuangan dan approval management</p>
        @if($isAdminViewer)
            <div class="inline-flex items-center gap-1 p-1 mt-2 rounded-full bg-gray-100 border border-gray-200">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-chart-bar mr-1"></i>Admin</a>
                <a href="{{ route('admin.dashboard.akademik') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full text-gray-700 hover:bg-gray-200 hover:text-gray-900"><i class="fas fa-graduation-cap mr-1"></i>Akademik</a>
                <a href="{{ route('admin.dashboard.finance') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-indigo-600 text-white shadow-md hover:shadow-lg"><i class="fas fa-money-bill-wave mr-1"></i>Finance</a>
            </div>
        @endif
    </div>
    
    <!-- Period Filter -->
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
</div>

<!-- Statistics Cards (Same as Admin Dashboard) -->
<div class="space-y-3 md:space-y-4 mb-4 md:mb-5">
    <div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Reguler</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-blue-600 break-words">Rp {{ number_format($adminRegularRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori reguler</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <i class="fas fa-book-open text-3xl text-blue-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Corporate</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-indigo-600 break-words">Rp {{ number_format($adminCorporateRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori corporate</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-xl">
                            <i class="fas fa-building text-3xl text-indigo-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Private</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-amber-600 break-words">Rp {{ number_format($adminPrivateRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor kategori private</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-xl">
                            <i class="fas fa-user text-3xl text-amber-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Pendapatan Kotor (Bruto)</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-gray-800 break-words">Rp {{ number_format($adminTotalRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total pendapatan kotor semua kategori</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <i class="fas fa-money-bill-wave text-3xl text-purple-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="mb-2 text-xs md:text-sm text-gray-500">
            Total kelas dihitung dari data yang lolos filter periode.
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-gray-900 break-words">{{ $adminTotalClasses }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Semua kelas sesuai filter</p>
                        </div>
                        <div class="bg-slate-100 p-3 rounded-xl">
                            <i class="fas fa-layer-group text-3xl text-slate-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas Reguler</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-blue-700 break-words">{{ $adminRegularClasses }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Jumlah kelas kategori reguler</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <i class="fas fa-book-open text-3xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas Corporate</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-indigo-700 break-words">{{ $adminCorporateClasses }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Jumlah kelas kategori corporate</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-xl">
                            <i class="fas fa-building text-3xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 sm:col-span-2 xl:col-span-1">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas Private</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-amber-700 break-words">{{ $adminPrivateClasses }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Jumlah kelas kategori private</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-xl">
                            <i class="fas fa-user text-3xl text-amber-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peserta per Kategori -->
    <div class="mb-4 md:mb-6">
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
                            <h4 class="text-3xl font-bold text-indigo-700 mb-1">{{ number_format($corporateParticipants, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs">Jumlah peserta kategori corporate</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-xl flex-shrink-0">
                            <i class="fas fa-building text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300 hover:shadow-md">
                <div class="p-4 md:p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-gray-500 text-xs md:text-sm mb-2 font-medium">Peserta Private</p>
                            <h4 class="text-3xl font-bold text-amber-700 mb-1">{{ number_format($privateParticipants, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs">Jumlah peserta kategori private</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-xl flex-shrink-0">
                            <i class="fas fa-user text-2xl text-amber-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Sertifikasi</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-emerald-700 break-words">Rp {{ number_format($adminCertificationRevenue, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Dari {{ $adminCertificationClassCount }} kelas sertifikasi</p>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-xl">
                            <i class="fas fa-certificate text-3xl text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Data Sertifikasi</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-cyan-700 break-words">{{ number_format($adminCertificationStudentCount, 0, ',', '.') }} siswa</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">Total peserta program sertifikasi</p>
                        </div>
                        <div class="bg-cyan-100 p-3 rounded-xl">
                            <i class="fas fa-user-check text-3xl text-cyan-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Rekap Pembayaran Honor -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-4 md:mb-6">
        <div class="mb-6">
            <h3 class="text-base md:text-lg font-bold text-gray-900 mb-1">
                <i class="fas fa-wallet mr-2 text-emerald-600"></i>Rekap Pembayaran Honor
            </h3>
            <p class="text-xs md:text-sm text-gray-600">Ringkasan pembayaran honor trainer/karyawan per kategori pelatihan</p>
        </div>

        @php
            $categoryCards = $honorRecapByCategory->take(3);
            $totalAllCategoriesAmount = $honorRecapByCategory->sum('total_amount');
            $totalAllCategoriesCount = $honorRecapByCategory->sum('total_count');
            $cardStyles = [
                ['value' => 'text-emerald-700', 'iconBg' => 'bg-emerald-100', 'icon' => 'text-emerald-600 fas fa-graduation-cap'],
                ['value' => 'text-blue-700', 'iconBg' => 'bg-blue-100', 'icon' => 'text-blue-600 fas fa-chalkboard-teacher'],
                ['value' => 'text-amber-700', 'iconBg' => 'bg-amber-100', 'icon' => 'text-amber-600 fas fa-user-tie'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            @forelse($categoryCards as $index => $category)
                @php $style = $cardStyles[$index] ?? $cardStyles[0]; @endphp
                <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                    <div class="p-4 md:p-6">
                        <div class="flex justify-between items-center">
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">{{ $category['name'] }}</p>
                                <h4 class="text-lg md:text-xl xl:text-2xl font-bold {{ $style['value'] }} break-words">Rp {{ number_format($category['total_amount'], 0, ',', '.') }}</h4>
                                <p class="text-gray-400 text-xs mt-1 truncate">{{ $category['total_count'] }} transaksi</p>
                            </div>
                            <div class="{{ $style['iconBg'] }} p-3 rounded-xl">
                                <i class="{{ $style['icon'] }} text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 lg:col-span-3">
                    <p class="text-sm text-gray-600">Tidak ada data kategori pada periode ini</p>
                </div>
            @endforelse

            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Seluruh Kategori</p>
                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-purple-700 break-words">Rp {{ number_format($totalAllCategoriesAmount, 0, ',', '.') }}</h4>
                            <p class="text-gray-400 text-xs mt-1 truncate">{{ $totalAllCategoriesCount }} transaksi</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <i class="fas fa-layer-group text-3xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Value Trend Chart -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-4 md:mb-6">
        <div class="mb-4 md:mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
                <h3 class="text-base md:text-lg font-bold text-gray-900 mb-1">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>Tren Nilai Kelas per Kategori ({{ $selectedYear }})
                </h3>
                <p class="text-xs md:text-sm text-gray-600">Perbandingan nilai kelas Reguler, Corporate, dan Private per bulan</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="revenueChartTypeBar" onclick="switchFinanceChartType('bar')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-purple-100 text-purple-700 border-purple-400 hover:bg-purple-200">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button id="revenueChartTypeLine" onclick="switchFinanceChartType('line')" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full font-semibold text-xs md:text-sm transition border-2 shadow-sm bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200">
                    <i class="fas fa-chart-line"></i> Line
                </button>
            </div>
        </div>
        <div class="w-full overflow-x-auto">
            <canvas id="revenueChart" class="max-w-full" style="min-height: 250px; max-height: 400px;"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let financeChartInstance = null;
        let financeChartType = 'bar';

        function updateFinanceButtons(type) {
            const barBtn = document.getElementById('revenueChartTypeBar');
            const lineBtn = document.getElementById('revenueChartTypeLine');

            if (!barBtn || !lineBtn) return;

            if (type === 'bar') {
                barBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
                barBtn.classList.add('bg-purple-100', 'text-purple-700', 'border-purple-400');
                lineBtn.classList.remove('bg-purple-100', 'text-purple-700', 'border-purple-400');
                lineBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
            } else {
                lineBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-gray-200');
                lineBtn.classList.add('bg-purple-100', 'text-purple-700', 'border-purple-400');
                barBtn.classList.remove('bg-purple-100', 'text-purple-700', 'border-purple-400');
                barBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-gray-200');
            }
        }

        function switchFinanceChartType(newType) {
            if (financeChartType === newType) return;
            if (financeChartInstance) {
                financeChartInstance.destroy();
            }
            financeChartType = newType;
            createFinanceChart(newType);
            updateFinanceButtons(newType);
        }

        window.switchFinanceChartType = switchFinanceChartType;

        function createFinanceChart(type) {
            const canvas = document.getElementById('revenueChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const monthlyRegularRevenue = @json($monthlyRegularRevenueArray);
            const monthlyCorporateRevenue = @json($monthlyCorporateRevenueArray);
            const monthlyPrivateRevenue = @json($monthlyPrivateRevenueArray);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const isLine = type === 'line';

            financeChartInstance = new Chart(ctx, {
                type: type,
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Reguler',
                        data: monthlyRegularRevenue,
                        backgroundColor: isLine ? 'rgba(59, 130, 246, 0.2)' : 'rgba(59, 130, 246, 0.75)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: isLine ? 2 : 1,
                        borderRadius: isLine ? 0 : 6,
                        maxBarThickness: 28,
                        fill: isLine,
                        tension: isLine ? 0.35 : 0,
                        pointRadius: isLine ? 5 : 0,
                        pointHoverRadius: isLine ? 7 : 0
                    }, {
                        label: 'Corporate',
                        data: monthlyCorporateRevenue,
                        backgroundColor: isLine ? 'rgba(99, 102, 241, 0.2)' : 'rgba(99, 102, 241, 0.75)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: isLine ? 2 : 1,
                        borderRadius: isLine ? 0 : 6,
                        maxBarThickness: 28,
                        fill: isLine,
                        tension: isLine ? 0.35 : 0,
                        pointRadius: isLine ? 5 : 0,
                        pointHoverRadius: isLine ? 7 : 0
                    }, {
                        label: 'Private',
                        data: monthlyPrivateRevenue,
                        backgroundColor: isLine ? 'rgba(245, 158, 11, 0.2)' : 'rgba(245, 158, 11, 0.8)',
                        borderColor: 'rgba(217, 119, 6, 1)',
                        borderWidth: isLine ? 2 : 1,
                        borderRadius: isLine ? 0 : 6,
                        maxBarThickness: 28,
                        fill: isLine,
                        tension: isLine ? 0.35 : 0,
                        pointRadius: isLine ? 5 : 0,
                        pointHoverRadius: isLine ? 7 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: true,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: window.innerWidth < 768 ? 'bottom' : 'top',
                            labels: {
                                usePointStyle: true,
                                padding: window.innerWidth < 768 ? 10 : 15,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: window.innerWidth < 768 ? 8 : 12,
                            titleFont: {
                                size: window.innerWidth < 768 ? 12 : 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: window.innerWidth < 768 ? 11 : 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 9 : 11
                                },
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                    }
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 9 : 11
                                }
                            },
                            grid: {
                                display: false
                            },
                            stacked: false
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            createFinanceChart('bar');
            updateFinanceButtons('bar');

            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (financeChartInstance) {
                        financeChartInstance.options.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'top';
                        financeChartInstance.options.plugins.legend.labels.padding = window.innerWidth < 768 ? 10 : 15;
                        financeChartInstance.options.plugins.legend.labels.font.size = window.innerWidth < 768 ? 10 : 12;
                        financeChartInstance.update();
                    }
                }, 250);
            });
        });
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Recent Expenses (Pending Approval) -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base md:text-lg font-bold text-gray-900">Expense Perlu Approval</h3>
                    <a href="{{ route('finance.expenses.index') }}" class="text-xs md:text-sm text-purple-600 hover:text-purple-700 font-semibold">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentExpenses as $expense)
                    <div class="p-3 md:p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between mb-2 gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="font-semibold text-sm md:text-base text-gray-900 truncate">{{ $expense->description }}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full flex-shrink-0">
                                        <i class="fas fa-graduation-cap mr-1"></i>Pelatihan
                                    </span>
                                </div>
                                <p class="text-xs md:text-sm text-gray-600 truncate">{{ $expense->clas ? $expense->clas->name : '-' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '-' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-sm md:text-base text-gray-900 whitespace-nowrap">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full mt-1">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 md:p-8 text-center text-gray-500">
                        <i class="fas fa-check-circle text-3xl md:text-4xl mb-2 text-gray-300"></i>
                        <p class="text-sm md:text-base">Tidak ada expense yang perlu diapprove</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payment Requests -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base md:text-lg font-bold text-gray-900">Payment Request Approved</h3>
                    <a href="{{ route('finance.payment-requests.index', ['status' => 'approved']) }}" class="text-xs md:text-sm text-purple-600 hover:text-purple-700 font-semibold">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentPaymentRequests as $request)
                    <div class="p-3 md:p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between mb-2 gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="font-semibold text-sm md:text-base text-gray-900 truncate">{{ $request->user->name }}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full flex-shrink-0">
                                        <i class="fas fa-graduation-cap mr-1"></i>Pelatihan
                                    </span>
                                </div>
                                <p class="text-xs md:text-sm text-gray-600 truncate">
                                    {{ $request->clas ? $request->clas->name : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $request->created_at->format('d M Y') }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-sm md:text-base text-gray-900 whitespace-nowrap">Rp {{ number_format($request->approved_amount, 0, ',', '.') }}</p>
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>Approved
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 md:p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-3xl md:text-4xl mb-2 text-gray-300"></i>
                        <p class="text-sm md:text-base">Tidak ada payment request</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
