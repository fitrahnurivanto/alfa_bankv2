@extends('layouts.app')

@section('title', 'Dashboard Alfa Bank')

@section('page-title', 'Dashboard Alfa Bank')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    .chart-container { position: relative; height: 320px; }
    .chart-container-lg { position: relative; height: 380px; }
    #calendar { max-width: 100%; margin: 0 auto; height: 600px; }
    .fc-event { cursor: pointer; }
    .fc-event:hover { opacity: 0.8; }
    .calendar-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
    .calendar-content.active { max-height: 700px; overflow-y: auto; }
    .filter-select { background:#fff; border:1px solid #e5e5e5; color:#6b7280; font-size:12px; padding:6px 10px; border-radius:6px; }
    .filter-select:focus { outline:none; border-color:#fe0000; box-shadow:0 0 0 2px rgba(254,0,0,.15); }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border-l-4 border-[#43bf21] p-4 rounded-lg shadow-sm">
    <div class="flex items-center"><i class="fas fa-check-circle text-[#43bf21] text-xl mr-3"></i><p class="text-green-800 font-medium">{{ session('success') }}</p></div>
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border-l-4 border-[#fe0000] p-4 rounded-lg shadow-sm">
    <div class="flex items-center"><i class="fas fa-exclamation-circle text-[#fe0000] text-xl mr-3"></i><p class="text-red-800 font-medium">{{ session('error') }}</p></div>
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-50 border-l-4 border-[#fe0000] p-4 rounded-lg shadow-sm">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-[#fe0000] text-xl mr-3 mt-0.5"></i>
        <div>
            <p class="text-red-800 font-medium mb-2">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<!-- Tab Dashboard -->
@if(\Illuminate\Support\Facades\Auth::user()?->isAdmin())
<div class="flex justify-center mb-6">
    <div class="inline-flex items-center gap-1 px-2 py-1.5 rounded-full bg-white border-2 border-[#fe0000]">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-5 py-1.5 text-xs font-semibold rounded-full bg-[#fe0000] text-white"><i class="fas fa-chart-simple"></i>Admin</a>
        <a href="{{ route('admin.dashboard.akademik') }}" class="inline-flex items-center gap-1.5 px-5 py-1.5 text-xs font-medium rounded-full text-gray-800 hover:bg-[#fed0d0]"><i class="fas fa-graduation-cap"></i>Akademik</a>
        <a href="{{ route('admin.dashboard.finance') }}" class="inline-flex items-center gap-1.5 px-5 py-1.5 text-xs font-medium rounded-full text-gray-800 hover:bg-[#fed0d0]"><i class="far fa-credit-card"></i>Finance</a>
    </div>
</div>
@endif

<!-- Judul + Filter -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-5 gap-3">
    <h4 class="text-xl md:text-2xl font-bold text-gray-900">Dashboard <span class="text-[#fe0000]">Alfabank</span></h4>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2 w-full lg:w-auto">
        <select name="period" class="filter-select flex-1 sm:flex-none sm:w-36" onchange="this.form.submit()">
            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>Semua Periode</option>
            @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $mk => $mn)
            <option value="month_{{ $mk }}" {{ $period == 'month_'.$mk ? 'selected' : '' }}>{{ $mn }}</option>
            @endforeach
        </select>
        <select name="year" class="filter-select flex-1 sm:flex-none sm:w-32" onchange="this.form.submit()">
            <option value="all" {{ request('year', 'all') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
            @for($y = now()->year; $y >= 2023; $y--)
            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <select name="status" class="filter-select flex-1 sm:flex-none sm:w-36" onchange="this.form.submit()">
            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif</option>
        </select>
    </form>
</div>

<!-- Target Omset Bulanan -->
<div id="target-omset" class="card-figma rounded-lg overflow-hidden mb-5 scroll-mt-6">
    <div class="px-5 py-3 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h5 class="text-base font-bold text-gray-900 leading-tight">Target Omset Bulanan</h5>
            <p class="text-xs text-gray-800">{{ now()->translatedFormat('F Y') }} (Real-time)</p>
        </div>
        @if($targetAmount > 0)
        <button type="button" onclick="openTargetModal()" class="border border-[#e28100] text-[#e28100] bg-[#fff4e5] px-4 py-1.5 rounded-md hover:bg-[#ffe9cc] transition text-xs font-medium flex items-center gap-2"><i class="fas fa-pen-to-square"></i><span>Edit Target</span></button>
        @else
        <button type="button" onclick="openTargetModal()" class="bg-[#fe0000] text-white px-4 py-1.5 rounded-md hover:bg-red-700 transition text-xs font-medium flex items-center gap-2"><i class="fas fa-plus"></i><span>Set Target</span></button>
        @endif
    </div>
    @if($targetAmount > 0)
    <div class="px-5 pt-6 pb-5">
        <div class="text-center mb-3">
            <div class="text-5xl md:text-6xl font-bold text-gray-900 leading-none">{{ number_format($targetPercentage, 1) }}%</div>
            <p class="text-sm text-gray-800 mt-1">dari target tercapai</p>
        </div>
        <div class="w-full bg-[#d9d9d9] rounded-full h-3 overflow-hidden mb-5">
            <div class="bg-[#fed0d0] h-full transition-all duration-500" style="width: {{ min($targetPercentage, 100) }}%"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="card-figma rounded-md p-3"><p class="text-xs text-gray-800">Omset Masuk</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($targetRevenue, 0, ',', '.') }}</p></div>
            <div class="card-figma rounded-md p-3"><p class="text-xs text-gray-800">Target Bulan Ini</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($targetAmount, 0, ',', '.') }}</p></div>
        </div>
    </div>
    @else
    <div class="p-12 text-center">
        <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-bullseye text-4xl text-[#fe0000]"></i></div>
        <h6 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Target</h6>
        <p class="text-gray-500 mb-6">Silakan set target omset bulanan terlebih dahulu untuk mulai tracking</p>
        <button onclick="openTargetModal()" class="bg-[#fe0000] text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition font-semibold"><i class="fas fa-plus mr-2"></i>Set Target Sekarang</button>
    </div>
    @endif
</div>

<!-- Rekap Omset + Rekap Honor -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <section class="card-figma rounded-lg p-4 xl:col-span-2">
        <h5 class="text-base font-bold text-gray-900 mb-3">Rekap Omset</h5>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-book-open text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Omset Reguler</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($regularRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total Omset Masuk kategori reguler</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-certificate text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Data Sertifikasi</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">{{ number_format($certificationStudentCount, 0, ',', '.') }} siswa</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total peserta program sertifikasi</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-building text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Omset Corporate</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($corporateRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total Omset Masuk kategori corporate</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Omset Sertifikasi</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($certificationRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Dari {{ $certificationClassCount }} kelas sertifikasi</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-user text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Omset Private</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($privateRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total Omset Masuk kategori private</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-layer-group text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Nilai Kelas</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($classValueRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total nilai harga kelas periode ini</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-money-bill-transfer text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Omset Masuk</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($classRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total Omset Masuk semua kategori</p>
                </div>
            </div>
        </div>
    </section>
    <section class="card-figma rounded-lg p-4">
        <h5 class="text-base font-bold text-gray-900 mb-3">Rekap Honor</h5>
        <div class="grid grid-cols-1 gap-3">
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-book-open text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Honor Reguler</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($regularHonorPayment, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Pembayaran honor kategori reguler</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-building text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Honor Corporate</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($trainingHonorPayment, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Pembayaran honor kategori corporate</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-user text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Honor Private</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($privateHonorPayment, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Pembayaran honor kategori private</p>
                </div>
            </div>
            <div class="card-figma rounded-lg p-3 flex items-center gap-3 ">
                <div class="w-12 h-12 shrink-0 rounded-lg bg-[#fed0d0] flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] text-gray-800 leading-tight">Total Honor</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($totalHonorPayment, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-gray-500 leading-tight mt-0.5">Total honor dari seluruh kategori</p>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Rekap Kelas -->
<section class="card-figma rounded-lg p-4 mb-5">
    <h5 class="text-base font-bold text-gray-900 mb-3">Rekap Kelas</h5>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="card-figma rounded-lg overflow-hidden">
            <div class="px-5 py-3.5 bg-gradient-to-r from-[#fed0d0] to-[#fff1f1]"><h6 class="text-base font-bold text-gray-900">Reguler</h6></div>
            <div class="divide-y divide-gray-100 text-sm">
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Total Kelas</span><span class="text-xl font-bold text-[#fe0000]">{{ $regularClassesCount }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelas Berjalan</span><span class="text-xl font-bold text-[#fe0000]">{{ $runningRegularClasses }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Peserta</span><span class="text-xl font-bold text-[#fe0000]">{{ number_format($regularParticipants, 0, ',', '.') }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelulusan</span><span class="font-semibold"><span class="text-[#43bf21]">{{ number_format($regularPassedParticipants, 0, ',', '.') }} lulus</span> <span class="text-gray-300">/</span> <span class="text-[#fe0000]">{{ number_format($regularFailedParticipants, 0, ',', '.') }} gagal</span></span></div>
            </div>
        </div>
        <div class="card-figma rounded-lg overflow-hidden">
            <div class="px-5 py-3.5 bg-gradient-to-r from-[#fed0d0] to-[#fff1f1]"><h6 class="text-base font-bold text-gray-900">Corporate</h6></div>
            <div class="divide-y divide-gray-100 text-sm">
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Total Kelas</span><span class="text-xl font-bold text-[#fe0000]">{{ $corporateClassesCount }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelas Berjalan</span><span class="text-xl font-bold text-[#fe0000]">{{ $runningCorporateClasses }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Peserta</span><span class="text-xl font-bold text-[#fe0000]">{{ number_format($corporateParticipants, 0, ',', '.') }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelulusan</span><span class="font-semibold"><span class="text-[#43bf21]">{{ number_format($corporatePassedParticipants, 0, ',', '.') }} lulus</span> <span class="text-gray-300">/</span> <span class="text-[#fe0000]">{{ number_format($corporateFailedParticipants, 0, ',', '.') }} gagal</span></span></div>
            </div>
        </div>
        <div class="card-figma rounded-lg overflow-hidden">
            <div class="px-5 py-3.5 bg-gradient-to-r from-[#fed0d0] to-[#fff1f1]"><h6 class="text-base font-bold text-gray-900">Private</h6></div>
            <div class="divide-y divide-gray-100 text-sm">
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Total Kelas</span><span class="text-xl font-bold text-[#fe0000]">{{ $privateClassesCount }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelas Berjalan</span><span class="text-xl font-bold text-[#fe0000]">{{ $runningPrivateClasses }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Peserta</span><span class="text-xl font-bold text-[#fe0000]">{{ number_format($privateParticipants, 0, ',', '.') }}</span></div>
                <div class="px-5 py-4 flex justify-between items-center"><span class="text-gray-600">Kelulusan</span><span class="font-semibold"><span class="text-[#43bf21]">{{ number_format($privatePassedParticipants, 0, ',', '.') }} lulus</span> <span class="text-gray-300">/</span> <span class="text-[#fe0000]">{{ number_format($privateFailedParticipants, 0, ',', '.') }} gagal</span></span></div>
            </div>
        </div>
    </div>
</section>

<!-- Grafik Keuangan Kelas -->
<div class="card-figma rounded-lg mb-5">
    <div class="px-5 pt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h5 class="text-base font-bold text-gray-900">Grafik Keuangan Kelas per Bulan ({{ $selectedYear }})</h5>
        <div class="flex gap-2 shrink-0">
                <button id="classRevenueChartTypeBar" onclick="switchChartType('classRevenueChart', 'bar')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-[#fe0000] text-white border-[#fe0000]"><i class="fas fa-chart-simple"></i> Bar</button>
                <button id="classRevenueChartTypeLine" onclick="switchChartType('classRevenueChart', 'line')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-white text-gray-800 border-gray-800"><i class="fas fa-chart-line"></i> Line</button>
            </div>
    </div>
    <p class="px-5 pt-2 text-[11px] text-gray-600 flex flex-wrap justify-center gap-4">
        <span><span class="inline-block w-2.5 h-2.5 bg-[#43bf21] rounded-full mr-1"></span>Omset Masuk</span>
        <span><span class="inline-block w-2.5 h-2.5 bg-[#fe0000] rounded-full mr-1"></span>Sisa Pembayaran (Unpaid)</span>
        <span><span class="inline-block w-2.5 h-2.5 bg-[#344bfd] rounded-full mr-1"></span>Biaya Operasional</span>
    </p>
    <div class="p-5">
        @if(is_array($monthlyClassRevenue) && (array_sum($monthlyClassRevenue) > 0 || array_sum($monthlyRemainingPayment) > 0 || array_sum($monthlyClassCost) > 0))
        <div class="chart-container"><canvas id="classRevenueChart"></canvas></div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-chart-line text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Keuangan</h6>
                <p class="text-sm text-gray-500">@if($period !== 'all' || $year !== 'all')Tidak ada data keuangan kelas untuk filter yang dipilih @else Belum ada data keuangan kelas tersedia @endif</p>
            </div>
        @endif
    </div>
</div>

<!-- Grafik Omset per Kelas -->
<div class="card-figma rounded-lg mb-5">
    <div class="px-5 pt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h5 class="text-base font-bold text-gray-900">Grafik Omset per Kelas/Pelatihan Bulan Berjalan ({{ $currentMonthLabel }})</h5>
            <p class="text-[11px] text-gray-500 mt-0.5">Menampilkan kelas aktif yang overlap bulan ini dan kelas selesai pada bulan ini. ({{ $currentMonthClassRevenueByClass->count() }} kelas)</p>
        </div>
        <div class="flex gap-2 shrink-0">
                <button id="currentMonthClassRevenueChartTypeBar" onclick="switchChartType('currentMonthClassRevenueChart', 'bar')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-[#fe0000] text-white border-[#fe0000]"><i class="fas fa-chart-simple"></i> Bar</button>
                <button id="currentMonthClassRevenueChartTypeLine" onclick="switchChartType('currentMonthClassRevenueChart', 'line')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-white text-gray-800 border-gray-800"><i class="fas fa-chart-line"></i> Line</button>
            </div>
    </div>
    <div class="p-5">
        @if($currentMonthClassRevenueByClass->count() > 0)
        <div class="chart-container-lg" style="height: {{ $currentMonthClassRevenueByClass->count() <= 3 ? '250px' : ($currentMonthClassRevenueByClass->count() <= 6 ? '310px' : '380px') }};"><canvas id="currentMonthClassRevenueChart"></canvas></div>
        @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-coins text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Omset Kelas Bulan Ini</h6>
                <p class="text-sm text-gray-500">Belum ada kelas aktif yang menghasilkan omset pada bulan berjalan.</p>
            </div>
        @endif
    </div>
</div>

<!-- Sertifikasi (2 kolom) -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
    <div class="card-figma rounded-lg">
        <div class="px-5 pt-4 flex items-center justify-between gap-3">
            <h5 class="text-base font-bold text-gray-900">Grafik Omset Sertifikasi per Bulan ({{ $selectedYear }})</h5>
            <div class="flex gap-2 shrink-0">
                <button id="certificationRevenueChartTypeBar" onclick="switchChartType('certificationRevenueChart', 'bar')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-[#fe0000] text-white border-[#fe0000]"><i class="fas fa-chart-simple"></i> Bar</button>
                <button id="certificationRevenueChartTypeLine" onclick="switchChartType('certificationRevenueChart', 'line')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-white text-gray-800 border-gray-800"><i class="fas fa-chart-line"></i> Line</button>
            </div>
        </div>
        <div class="p-5">
            @if(is_array($monthlyCertificationRevenue) && array_sum($monthlyCertificationRevenue) > 0)
            <div class="chart-container"><canvas id="certificationRevenueChart"></canvas></div>
            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-certificate text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Omset Sertifikasi</h6>
                <p class="text-sm text-gray-500">Belum ada omset sertifikasi pada periode yang dipilih.</p>
            </div>
            @endif
        </div>
    </div>
    <div class="card-figma rounded-lg">
        <div class="px-5 pt-4 flex items-center justify-between gap-3">
            <div>
                <h5 class="text-base font-bold text-gray-900">Grafik Jumlah Kelas dan Siswa Sertifikasi per Bulan ({{ $selectedYear }})</h5>
                <p class="text-[11px] text-gray-500 mt-0.5">Siswa sertifikasi dihitung dari peserta yang benar-benar ikut sertifikasi.</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="classCountChartTypeBar" onclick="switchChartType('classCountChart', 'bar')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-[#fe0000] text-white border-[#fe0000]"><i class="fas fa-chart-simple"></i> Bar</button>
                <button id="classCountChartTypeLine" onclick="switchChartType('classCountChart', 'line')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-semibold text-xs transition border bg-white text-gray-800 border-gray-800"><i class="fas fa-chart-line"></i> Line</button>
            </div>
        </div>
        <div class="p-5">
            @if(is_array($monthlyCertificationClassCount) && (array_sum($monthlyCertificationClassCount) > 0 || array_sum($monthlyCertificationCount) > 0))
            <div class="chart-container"><canvas id="classCountChart"></canvas></div>
            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-chart-bar text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Kelas</h6>
                <p class="text-sm text-gray-500">@if($period !== 'all' || $year !== 'all')Tidak ada kelas untuk filter yang dipilih @else Belum ada kelas tersedia @endif</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Donut (2 kolom) -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
    <div class="card-figma rounded-lg">
        <div class="px-5 pt-4">
            <h5 class="text-base font-bold text-gray-900">Grafik Jumlah Siswa per Pelatihan ({{ $currentMonthLabel }})</h5>
            <p class="text-[11px] text-gray-500 mt-0.5">Total siswa dari kelas berjalan (status approved) yang mulai pada bulan ini.</p>
        </div>
        <div class="p-5">
            @if($currentMonthStudentsByTraining->count() > 0)
            <div class="chart-container"><canvas id="currentMonthStudentsByTrainingChart"></canvas></div>
            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-chart-pie text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Siswa Bulan Ini</h6>
                <p class="text-sm text-gray-500">Coba ubah periode atau tunggu kelas aktif berjalan.</p>
            </div>
            @endif
        </div>
    </div>
    <div class="card-figma rounded-lg">
        <div class="px-5 pt-4">
            <h5 class="text-base font-bold text-gray-900">Grafik Siswa Sertifikasi per Pelatihan @if($status === 'completed')(Selesai)@elseif($status === 'active')(Aktif)@else(Total)@endif</h5>
            <p class="text-[11px] text-gray-500 mt-0.5">Pelatihan yang memiliki peserta sertifikasi pada filter yang dipilih.</p>
        </div>
        <div class="p-5">
            @if($certificationByTraining->count() > 0)
            <div class="chart-container"><canvas id="certificationByTrainingChart"></canvas></div>
            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-[#fed0d0] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-certificate text-4xl text-[#fe0000]"></i></div>
                <h6 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Sertifikasi</h6>
                <p class="text-sm text-gray-500">Belum ada siswa sertifikasi pada filter yang dipilih</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Kelas Selesai | Kelas Berjalan -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
<div class="card-figma rounded-lg overflow-hidden">
    <div class="px-5 py-3 bg-[#fed0d0] flex justify-between items-center">
        <div><h5 class="text-base font-bold text-gray-900 leading-tight">Kelas Selesai</h5><p class="text-xs text-gray-800">Kelas yang telah diselesaikan</p></div>
        <span class="text-sm font-medium text-gray-900">{{ $completedClassesList->count() }} Kelas</span>
    </div>
    @if($completedClassesList->count() > 0)
    <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200"><tr><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Nama Kelas</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Trainer</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Periode</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Siswa</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Pertemuan</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Income</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($completedClassesList as $class)

                <tr class="hover:bg-[#f9f0f1] transition">
                    <td class="px-3 py-3"><a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-[#fe0000]">{{ $class->name }}</a>
                        @if(!empty($class->instansi))<div class="text-[11px] text-gray-500">{{ $class->instansi }}</div>@endif</td>
                    <td class="px-3 py-3 text-xs text-gray-900">@if($class->trainers->isNotEmpty()){{ $class->trainers->pluck('name')->join(', ') }}@else - @endif</td>
                    <td class="px-3 py-3 text-xs text-gray-600 whitespace-nowrap">@if($class->end_date){{ $class->end_date->format('d/m/Y') }}@else - @endif</td>
                    <td class="px-3 py-3"><span class="font-semibold text-gray-900 text-xs">{{ $class->amount }}</span></td>
                    <td class="px-3 py-3"><span class="font-semibold text-gray-900 text-xs">{{ $class->meet }}x</span></td>
                    <td class="px-3 py-3"><span class="font-semibold text-[#43bf21] text-xs whitespace-nowrap">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-10 text-center text-gray-500 min-h-[220px] flex items-center justify-center">Tidak ada kelas selesai</div>
    @endif
</div>

<div class="card-figma rounded-lg overflow-hidden">
    <div class="px-5 py-3 bg-[#fed0d0] flex justify-between items-center">
        <div><h5 class="text-base font-bold text-gray-900 leading-tight">Kelas Berjalan</h5><p class="text-xs text-gray-800">Kelas yang sedang aktif berlangsung</p></div>
        <span class="text-sm font-medium text-gray-900">{{ $activeClassesList->count() }} Kelas</span>
    </div>
    @if($activeClassesList->count() > 0)
    <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200"><tr><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Nama Kelas</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Trainer</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Periode</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Siswa</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Pertemuan</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Income</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($activeClassesList as $class)
                @php
                    $totalDays = $class->start_date && $class->end_date ? $class->start_date->diffInDays($class->end_date) + 1 : 0;
                    $daysElapsed = $class->start_date ? $class->start_date->diffInDays(now()) + 1 : 0;
                    $progressPercent = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100)) : 0;
                @endphp
                <tr class="hover:bg-[#f9f0f1] transition">
                    <td class="px-3 py-3"><a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-[#fe0000]">{{ $class->name }}</a>
                        @if(!empty($class->instansi))<div class="text-[11px] text-gray-500">{{ $class->instansi }}</div>@endif
                        @if(($class->method ?? null) == 'online')<span class="inline-block mt-0.5 px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-semibold rounded-full"><i class="fas fa-laptop mr-1"></i>Online</span>@elseif(isset($class->method))<span class="inline-block mt-0.5 px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-semibold rounded-full"><i class="fas fa-building mr-1"></i>Offline</span>@endif</td>
                    <td class="px-3 py-3 text-xs text-gray-900">@if($class->trainers->isNotEmpty()){{ $class->trainers->pluck('name')->join(', ') }}@else - @endif</td>
                    <td class="px-3 py-3 text-xs whitespace-nowrap">@if($class->start_date && $class->end_date)<div class="text-gray-900 font-medium">{{ $class->start_date->format('d M Y') }}</div><div class="text-gray-600">s/d {{ $class->end_date->format('d M Y') }}</div>
                        <div class="mt-1 flex items-center gap-1.5"><div class="w-14 bg-gray-200 rounded-full h-1.5"><div class="bg-[#fe0000] h-1.5 rounded-full" style="width: {{ $progressPercent }}%"></div></div><span class="text-[10px] font-semibold text-gray-700">{{ $progressPercent }}%</span></div>@else<div class="text-gray-500">-</div>@endif</td>
                    <td class="px-3 py-3"><span class="font-semibold text-gray-900 text-xs">{{ $class->amount }}</span></td>
                    <td class="px-3 py-3"><span class="font-semibold text-gray-900 text-xs">{{ $class->meet }}x</span></td>
                    <td class="px-3 py-3"><span class="font-semibold text-[#43bf21] text-xs whitespace-nowrap">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-10 text-center text-gray-500 min-h-[220px] flex items-center justify-center">Tidak ada kelas berjalan</div>
    @endif
</div>
</div>

<!-- Kelas Pending | Aktivitas Terbaru -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
<div class="card-figma rounded-lg overflow-hidden">
    <div class="px-5 py-3 bg-[#fed0d0] flex justify-between items-center">
        <div><h5 class="text-base font-bold text-gray-900 leading-tight">Kelas Pending</h5><p class="text-xs text-gray-800">Kelas yang belum di-approve</p></div>
        <span class="text-sm font-medium text-gray-900">{{ $pendingClassesList->count() }} Kelas</span>
    </div>
    @if($pendingClassesList->count() > 0)
    <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200"><tr><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Nama Kelas</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Trainer</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Periode</th><th class="px-3 py-2.5 text-left text-[11px] font-medium text-gray-800 uppercase tracking-wide">Income</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($pendingClassesList as $class)

                <tr class="hover:bg-[#f9f0f1] transition">
                    <td class="px-3 py-3"><a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-gray-900 hover:text-[#fe0000]">{{ $class->name }}</a>
                        @if(!empty($class->instansi))<div class="text-[11px] text-gray-500">{{ $class->instansi }}</div>@endif</td>
                    <td class="px-3 py-3 text-xs text-gray-900">@if($class->trainers->isNotEmpty()){{ $class->trainers->pluck('name')->join(', ') }}@else - @endif</td>
                    <td class="px-3 py-3 text-xs text-gray-600 whitespace-nowrap">@if($class->start_date && $class->end_date){{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}@else - @endif<div class="text-[10px] text-gray-400">Dibuat {{ $class->created_at->format('d/m/Y') }}</div></td>
                    <td class="px-3 py-3"><span class="font-semibold text-[#43bf21] text-xs whitespace-nowrap">Rp {{ number_format($class->income, 0, ',', '.') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-10 text-center text-gray-500 min-h-[220px] flex items-center justify-center">Tidak ada kelas pending</div>
    @endif
</div>

<div class="card-figma rounded-lg overflow-hidden">
    <div class="px-5 py-3 bg-[#fed0d0]"><h5 class="text-base font-bold text-gray-900">Aktivitas Terbaru</h5></div>
    <div class="p-5 max-h-[420px] overflow-y-auto">
        <div data-activities="container">
            @if($recentActivities->count() > 0)
            <div class="space-y-1">
                @foreach($recentActivities as $activity)
                <div class="flex justify-between items-start py-2.5 border-b border-gray-100 last:border-0">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-circle text-[#43bf21] text-[9px] mt-1.5"></i>
                        <p class="text-sm text-gray-800"><strong>{{ $activity->user ? $activity->user->name : 'System' }}</strong> <span class="text-gray-600">- {{ $activity->description }}</span></p>
                    </div>
                    <small class="text-gray-500 text-xs whitespace-nowrap ml-4">{{ $activity->created_at->diffForHumans() }}</small>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Belum ada aktivitas</p>
            @endif
        </div>
    </div>
</div>
</div>

<!-- Modal Set/Edit Target -->
<div id="targetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[100] flex items-center justify-center p-4" onclick="event.target === this && closeTargetModal()">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="px-6 py-4 bg-[#fed0d0] rounded-t-xl flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-bullseye text-[#fe0000]"></i><span>Set Target Omset Bulanan</span></h3>
            <button type="button" onclick="closeTargetModal()" class="text-gray-700 hover:text-[#fe0000] transition"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="targetForm" action="{{ route('admin.dashboard.save-target') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div class="bg-[#f9f0f1] border border-[#fed0d0] rounded-lg p-3">
                    <p class="text-sm text-gray-800"><i class="fas fa-info-circle mr-2 text-[#fe0000]"></i>Target untuk: <span class="font-bold">{{ now()->format('F Y') }}</span> - <span class="font-bold">Alfa Bank</span></p>
                </div>
                <input type="hidden" name="division" value="academy">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Omset (Rp) <span class="text-[#fe0000]">*</span></label>
                    <input type="text" name="target_amount_display" id="target_amount_display" required placeholder="Contoh: 50.000.000"
                        value="{{ $targetAmount > 0 ? number_format($targetAmount, 0, ',', '.') : '' }}" oninput="formatCurrencyDashboard(this)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#fe0000]/30 focus:border-[#fe0000] focus:outline-none transition">
                    <input type="hidden" name="target_amount" id="target_amount" value="{{ $targetAmount > 0 ? $targetAmount : '' }}">
                    <p class="text-xs text-gray-500 mt-1.5">Gunakan format ribuan dengan titik (contoh: 50.000.000)</p>
                </div>
                <div id="modalError" class="hidden bg-red-50 border-l-4 border-[#fe0000] p-3 rounded"><p class="text-red-700 text-sm"></p></div>
            </div>
            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeTargetModal()" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium"><i class="fas fa-times mr-2"></i>Batal</button>
                <button type="submit" id="submitTargetBtn" class="flex-1 px-4 py-2.5 bg-[#fe0000] text-white rounded-lg hover:bg-red-700 transition shadow-md font-medium"><i class="fas fa-save mr-2"></i>Simpan Target</button>
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

        const colors = ['#344bfd','#43bf21','#e28100','#fe0000','#5b9bff','#a855f7','#e879f9','#86efac','#1e3a8a','#14b8a6'];

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
                        position: 'right',
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
            barBtn.classList.remove('bg-white', 'text-gray-800', 'border-gray-800');
            barBtn.classList.add('bg-[#fe0000]', 'text-white', 'border-[#fe0000]');
            lineBtn.classList.remove('bg-[#fe0000]', 'text-white', 'border-[#fe0000]');
            lineBtn.classList.add('bg-white', 'text-gray-800', 'border-gray-800');
        } else {
            lineBtn.classList.remove('bg-white', 'text-gray-800', 'border-gray-800');
            lineBtn.classList.add('bg-[#fe0000]', 'text-white', 'border-[#fe0000]');
            barBtn.classList.remove('bg-[#fe0000]', 'text-white', 'border-[#fe0000]');
            barBtn.classList.add('bg-white', 'text-gray-800', 'border-gray-800');
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
                        label: 'Omzet Masuk',
                        data: monthlyClassRevenue,
                        backgroundColor: type === 'bar' ? 'rgba(67, 191, 33, 0.8)' : 'rgba(67, 191, 33, 0.2)',
                        borderColor: 'rgba(67, 191, 33, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Sisa Pembayaran (Unpaid)',
                        data: monthlyRemainingPayment,
                        backgroundColor: type === 'bar' ? 'rgba(254, 0, 0, 0.8)' : 'rgba(254, 0, 0, 0.1)',
                        borderColor: 'rgba(254, 0, 0, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: false,
                        tension: 0.4,
                        borderDash: [5, 5],
                        borderRadius: 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Biaya Operasional',
                        data: monthlyClassCost,
                        backgroundColor: type === 'bar' ? 'rgba(52, 75, 253, 0.8)' : 'rgba(52, 75, 253, 0.1)',
                        borderColor: 'rgba(52, 75, 253, 1)',
                        borderWidth: type === 'bar' ? 2 : 3,
                        fill: false,
                        tension: 0.4,
                        borderRadius: 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    }
                ]
            };
            options.plugins.tooltip = { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } };
            options.scales.y.ticks = { callback: v => 'Rp ' + (v / 1000000).toLocaleString('id-ID') + ' jt' };
        } else if (chartName === 'classCountChart') {
            const monthlyClassCount = {!! json_encode($monthlyCertificationClassCount) !!};
            const monthlyCert = {!! json_encode($monthlyCertificationCount) !!};
            
            data = {
                labels: months,
                datasets: [
                    {
                        label: 'Jumlah Kelas',
                        data: monthlyClassCount,
                        backgroundColor: type === 'bar' ? 'rgba(52, 75, 253, 0.8)' : 'rgba(52, 75, 253, 0.2)',
                        borderColor: 'rgba(52, 75, 253, 1)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: 0,
                        pointRadius: type === 'line' ? 5 : 0,
                        pointHoverRadius: type === 'line' ? 7 : 0
                    },
                    {
                        label: 'Jumlah Siswa Sertifikasi',
                        data: monthlyCert,
                        backgroundColor: type === 'bar' ? 'rgba(67, 191, 33, 0.75)' : 'rgba(67, 191, 33, 0.2)',
                        borderColor: 'rgba(67, 191, 33, 1)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        borderRadius: 0,
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
                    backgroundColor: type === 'bar' ? 'rgba(52, 75, 253, 0.8)' : 'rgba(52, 75, 253, 0.2)',
                    borderColor: 'rgba(52, 75, 253, 1)',
                    borderWidth: type === 'bar' ? 2 : 3,
                    fill: type === 'line',
                    tension: 0.35,
                    borderRadius: 0,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                }]
            };
            options.plugins.tooltip = { callbacks: { label: c => 'Omset: Rp ' + c.parsed.y.toLocaleString('id-ID') } };
            options.scales.y.ticks = { callback: v => 'Rp ' + (v / 1000000).toLocaleString('id-ID') + ' jt' };
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
                    backgroundColor: type === 'bar' ? 'rgba(226, 129, 0, 0.8)' : 'rgba(226, 129, 0, 0.15)',
                    borderColor: 'rgba(226, 129, 0, 1)',
                    borderWidth: type === 'bar' ? 1 : 3,
                    fill: type === 'line',
                    tension: 0.35,
                    borderRadius: 0,
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
        const studentColors = ['#344bfd','#43bf21','#e28100','#fe0000','#5b9bff','#a855f7','#e879f9','#86efac','#1e3a8a','#14b8a6'];

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
                        position: 'right',
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