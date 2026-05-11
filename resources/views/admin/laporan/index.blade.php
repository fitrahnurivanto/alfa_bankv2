@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-chart-line mr-3 {{ $activeDivision === 'training' ? 'text-green-600' : 'text-indigo-600' }}"></i>
                        Laporan {{ $activeDivision === 'training' ? 'Pelatihan' : 'Project' }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Export data {{ $activeDivision === 'training' ? 'kelas pelatihan' : 'project' }} dalam format Excel
                    </p>
                </div>
                @if($activeDivision === 'training')
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold">
                    <i class="fas fa-graduation-cap mr-2"></i>Pelatihan
                </span>
                @else
                <span class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg font-semibold">
                    <i class="fas fa-briefcase mr-2"></i>Agency
                </span>
                @endif
            </div>
        </div>

        <!-- Export Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-file-excel text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Export Data ke Excel</h2>
                    <p class="text-sm text-gray-600">
                        Pilih filter untuk mengexport data {{ $activeDivision === 'training' ? 'kelas pelatihan' : 'project' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.laporan.index') }}" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Period Filter -->
                    <div>
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>Filter Bulan
                        </label>
                        <select name="period" id="period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="all">Semua Bulan</option>
                            @php
                                $months = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="month_{{ $num }}" {{ $period == 'month_' . $num ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>Filter Tahun
                        </label>
                        <select name="year" id="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Tahun</option>
                            @php
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                    $selected = $year == $y ? 'selected' : '';
                                    echo "<option value=\"$y\" $selected>$y</option>";
                                }
                            @endphp
                        </select>
                    </div>

                    <!-- Start Date (Optional Detail Filter) -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-2 text-indigo-600"></i>Tanggal Mulai (Opsional)
                        </label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- End Date (Optional Detail Filter) -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-times mr-2 text-indigo-600"></i>Tanggal Akhir (Opsional)
                        </label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-4">
                    <a href="{{ route('admin.laporan.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 flex items-center">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-filter mr-2"></i>Terapkan Filter
                    </button>
                    <button
                        type="submit"
                        formaction="{{ route('admin.laporan.export') }}"
                        formmethod="GET"
                        class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        <i class="fas fa-file-excel mr-2"></i>Export ke Excel
                    </button>
                </div>
            </form>
        </div>


        <!-- Statistics Cards -->
        <div class="mt-8">
            @if($activeDivision === 'training')
                @php
                    // Build query with filters
                    $query = \App\Models\Clas::query();
                    
                    if (!empty($filters['year'])) {
                        $query->whereYear('start_date', $filters['year']);
                    }
                    if (!empty($filters['start_date'])) {
                        $query->whereDate('start_date', '>=', $filters['start_date']);
                    }
                    if (!empty($filters['end_date'])) {
                        $query->whereDate('start_date', '<=', $filters['end_date']);
                    }
                    
                    // Revenue calculation - SAMA SEPERTI DASHBOARD
                    // Hanya hitung approved & done, dengan corporate tidak dikali amount
                    $classes = (clone $query)->whereIn('status', ['approved', 'done'])
                        ->with('kategori')
                        ->get();

                    $totalRevenue = 0;
                    foreach ($classes as $class) {
                        // Price is now total revenue for all categories
                        $totalRevenue += $class->price;
                    }

                    $regularClasses = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'reguler');
                    })->count();
                    $corporateClasses = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'corporate');
                    })->count();
                    $privateClasses = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'private');
                    })->count();

                    // Samakan cakupan Total Kelas dengan 3 card kategori agar sinkron.
                    $totalClasses = $regularClasses + $corporateClasses + $privateClasses;

                    $regularRevenue = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'reguler');
                    })->sum('price');
                    $corporateRevenue = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'corporate');
                    })->sum('price');
                    $privateRevenue = (clone $query)->whereIn('status', ['approved', 'done'])->whereHas('training', function ($q) {
                        $q->where('type', 'private');
                    })->sum('price');

                    $certificationQuery = (clone $query)->where('sertifikasi_bnsp', true);
                    $certificationClassCount = (clone $certificationQuery)->count();
                    $certificationStudentCount = (int) (clone $certificationQuery)->sum('bnsp_student_count');
                    $certificationRevenue = (float) (clone $certificationQuery)
                        ->get(['bnsp_student_count', 'bnsp_fee_per_student'])
                        ->sum(function ($item) {
                            return ((int) ($item->bnsp_student_count ?? 0)) * ((float) ($item->bnsp_fee_per_student ?? 0));
                        });
                @endphp
                <div class="space-y-3 md:space-y-4 mb-4 md:mb-5">
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
                            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                                <div class="p-4 md:p-6">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Reguler</p>
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-blue-600 break-words">Rp {{ number_format($regularRevenue, 0, ',', '.') }}</h4>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-indigo-600 break-words">Rp {{ number_format($corporateRevenue, 0, ',', '.') }}</h4>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-amber-600 break-words">Rp {{ number_format($privateRevenue, 0, ',', '.') }}</h4>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-gray-800 break-words">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
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
                            Total kelas dihitung dari data yang lolos filter bulan/tahun/tanggal.
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
                            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                                <div class="p-4 md:p-6">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas</p>
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-gray-900 break-words">{{ $totalClasses }}</h4>
                                            <p class="text-gray-400 text-xs mt-1 truncate">Total kelas approved + selesai (reguler, corporate, private)</p>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-blue-700 break-words">{{ $regularClasses }}</h4>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-indigo-700 break-words">{{ $corporateClasses }}</h4>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-amber-700 break-words">{{ $privateClasses }}</h4>
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

                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
                                <div class="p-4 md:p-6">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Omset Sertifikasi</p>
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-emerald-700 break-words">Rp {{ number_format($certificationRevenue, 0, ',', '.') }}</h4>
                                            <p class="text-gray-400 text-xs mt-1 truncate">Dari {{ $certificationClassCount }} kelas sertifikasi</p>
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
                                            <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-cyan-700 break-words">{{ number_format($certificationStudentCount, 0, ',', '.') }} siswa</h4>
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
            @else
                @php
                    // Agency stats - with filters (use project start_date if order_date is null)
                    $projectQuery = \App\Models\Project::whereHas('order.orderItems.service.category', function($q) {
                        $q->where('division', 'agency');
                    });
                    
                    // Apply filters - if start_date and end_date exist, ignore year filter
                    $hasDateRange = !empty($filters['start_date']) && !empty($filters['end_date']);
                    
                    if ($hasDateRange) {
                        // Use date range only
                        $projectQuery->where(function($q) use ($filters) {
                            $q->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                              ->orWhereHas('order', function($subQ) use ($filters) {
                                  $subQ->whereBetween('order_date', [$filters['start_date'], $filters['end_date']]);
                              });
                        });
                    } else {
                        // Apply year filter if no date range
                        if (!empty($filters['year'])) {
                            $projectQuery->where(function($q) use ($filters) {
                                $q->whereHas('order', function($subQ) use ($filters) {
                                    $subQ->whereYear('order_date', $filters['year']);
                                })->orWhereYear('start_date', $filters['year']);
                            });
                        }
                        // Apply individual date filters
                        if (!empty($filters['start_date'])) {
                            $projectQuery->where(function($q) use ($filters) {
                                $q->whereHas('order', function($subQ) use ($filters) {
                                    $subQ->whereDate('order_date', '>=', $filters['start_date']);
                                })->orWhereDate('start_date', '>=', $filters['start_date']);
                            });
                        }
                        if (!empty($filters['end_date'])) {
                            $projectQuery->where(function($q) use ($filters) {
                                $q->whereHas('order', function($subQ) use ($filters) {
                                    $subQ->whereDate('order_date', '<=', $filters['end_date']);
                                })->orWhereDate('start_date', '<=', $filters['end_date']);
                            });
                        }
                    }
                    
                    $totalProjects = (clone $projectQuery)->count();
                    $completedProjects = (clone $projectQuery)->where('status', 'completed')->count();
                    $activeProjects = (clone $projectQuery)->where('status', 'in_progress')->count();
                    
                    // Revenue with filters
                    $revenueQuery = \App\Models\Order::where('payment_status', 'paid')
                        ->whereHas('orderItems.service.category', function($q) {
                            $q->where('division', 'agency');
                        });
                    
                    if ($hasDateRange) {
                        $revenueQuery->whereBetween('order_date', [$filters['start_date'], $filters['end_date']]);
                    } else {
                        if (!empty($filters['year'])) {
                            $revenueQuery->whereYear('order_date', $filters['year']);
                        }
                        if (!empty($filters['start_date'])) {
                            $revenueQuery->whereDate('order_date', '>=', $filters['start_date']);
                        }
                        if (!empty($filters['end_date'])) {
                            $revenueQuery->whereDate('order_date', '<=', $filters['end_date']);
                        }
                    }
                    
                    $totalRevenue = $revenueQuery->sum('paid_amount');
                @endphp

                <!-- Total Projects -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Project</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalProjects }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Completed Projects -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Project Selesai</p>
                            <p class="text-2xl font-bold text-green-600">{{ $completedProjects }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Project Aktif</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $activeProjects }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-spinner text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Pendapatan</p>
                            <p class="text-xl font-bold text-purple-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const periodSelect = document.getElementById('period');
        const yearSelect = document.getElementById('year');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        if (!periodSelect || !yearSelect || !startDateInput || !endDateInput) {
            return;
        }

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function getSelectedYear() {
            const currentYear = new Date().getFullYear();
            const selectedYear = parseInt(yearSelect.value, 10);

            return Number.isFinite(selectedYear) ? selectedYear : currentYear;
        }

        function syncDatesFromPeriod() {
            const periodValue = periodSelect.value || 'all';
            const selectedYear = getSelectedYear();

            if (!periodValue.startsWith('month_')) {
                return;
            }

            const month = periodValue.split('_')[1];
            const year = selectedYear;
            const lastDay = new Date(year, parseInt(month, 10), 0).getDate();

            startDateInput.value = `${year}-${month}-01`;
            endDateInput.value = `${year}-${month}-${pad(lastDay)}`;
        }

        function syncDatesOnLoad() {
            const startHasValue = startDateInput.value.trim() !== '';
            const endHasValue = endDateInput.value.trim() !== '';

            if (!startHasValue || !endHasValue) {
                syncDatesFromPeriod();
            }
        }

        periodSelect.addEventListener('change', syncDatesFromPeriod);
        yearSelect.addEventListener('change', syncDatesFromPeriod);
        document.addEventListener('DOMContentLoaded', syncDatesOnLoad);

        syncDatesOnLoad();
    })();
</script>
@endpush
