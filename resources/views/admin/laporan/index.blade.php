@extends('layouts.app')

@section('content')
<div class="p-1 sm:p-2">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Laporan {{ $activeDivision === 'training' ? 'Pelatihan' : 'Project' }}</h1>
            <p class="text-gray-800">Pilih filter untuk mengexport data {{ $activeDivision === 'training' ? 'kelas pelatihan' : 'project' }}</p>
        </div>
        @if($activeDivision === 'training')
        <span class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#fed0d0] border border-[#fe0000] text-[#fe0000] rounded-lg text-lg font-semibold">
            <i class="fas fa-graduation-cap"></i> Pelatihan
        </span>
        @else
        <span class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#fed0d0] border border-[#fe0000] text-[#fe0000] rounded-lg text-lg font-semibold">
            <i class="fas fa-briefcase"></i> Agency
        </span>
        @endif
    </div>

    <!-- Export Form Card -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-5 sm:p-6 mb-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 shrink-0 bg-[#13a100] rounded-lg flex items-center justify-center">
                <i class="far fa-file-lines text-white text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Export Data ke Excel</h2>
                <p class="text-gray-800">Pilih filter untuk mengexport data {{ $activeDivision === 'training' ? 'kelas pelatihan' : 'project' }}</p>
            </div>
        </div>

        <form action="{{ route('admin.laporan.index') }}" method="GET" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <!-- Period Filter -->
                <div>
                    <label for="period" class="block text-xs font-medium uppercase text-gray-900 mb-1">Periode</label>
                    <select name="period" id="period" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
                        <option value="all">Semua Periode</option>
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
                    <label for="year" class="block text-xs font-medium uppercase text-gray-900 mb-1">Tahun</label>
                    <select name="year" id="year" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
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
                    <label for="start_date" class="block text-xs font-medium uppercase text-gray-900 mb-1">Tanggal Mulai (Opsional)</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
                </div>

                <!-- End Date (Optional Detail Filter) -->
                <div>
                    <label for="end_date" class="block text-xs font-medium uppercase text-gray-900 mb-1">Tanggal Akhir (Opsional)</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.laporan.index') }}" class="px-6 py-2.5 bg-[#e5e5e5] text-gray-700 rounded-lg hover:bg-[#d4d4d4] transition font-medium">
                    Reset
                </a>
                <button type="submit" class="px-6 py-2.5 bg-white border border-[#fe0000] text-[#fe0000] rounded-lg hover:bg-[#fed0d0]/40 transition font-semibold flex items-center">
                    <i class="fas fa-filter mr-2"></i>Terapkan Filter
                </button>
                <button type="submit" formaction="{{ route('admin.laporan.export') }}" formmethod="GET" class="px-6 py-2.5 bg-[#13a100] text-white rounded-lg hover:bg-[#0f8000] transition font-semibold flex items-center shadow">
                    <i class="far fa-file-lines mr-2"></i>Export ke Excel
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    @if($activeDivision === 'training')
    @php
                    // Build query with filters
                    $query = \App\Models\Clas::query();
                    $hasDateRange = !empty($filters['start_date']) && !empty($filters['end_date']);
                    
                    if (!empty($filters['year'])) {
                        $query->whereYear('start_date', $filters['year']);
                    }
                    if (!empty($filters['start_date'])) {
                        $query->whereDate('start_date', '>=', $filters['start_date']);
                    }
                    if (!empty($filters['end_date'])) {
                        $query->whereDate('start_date', '<=', $filters['end_date']);
                    }
                    
                    // Revenue calculation - cash basis from confirmed payments
                    $classes = (clone $query)->whereIn('status', ['approved', 'done'])
                        ->with('kategori')
                        ->get();

                    $paymentRevenueQuery = \App\Models\InboundPayment::query()
                        ->where('status', 'verified')
                        ->whereNotNull('paid_at');

                    if ($hasDateRange) {
                        $paymentRevenueQuery->whereBetween('paid_at', [$filters['start_date'], $filters['end_date']]);
                    } else {
                        if (!empty($filters['year'])) {
                            $paymentRevenueQuery->whereYear('paid_at', $filters['year']);
                        }
                        if (!empty($filters['start_date'])) {
                            $paymentRevenueQuery->whereDate('paid_at', '>=', $filters['start_date']);
                        }
                        if (!empty($filters['end_date'])) {
                            $paymentRevenueQuery->whereDate('paid_at', '<=', $filters['end_date']);
                        }
                    }

                    $totalRevenue = (float) (clone $paymentRevenueQuery)->sum('amount');

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

                    $regularRevenue = (clone $paymentRevenueQuery)->whereHas('clas.training', function ($q) {
                        $q->where('type', 'reguler');
                    })->sum('amount');
                    $corporateRevenue = (clone $paymentRevenueQuery)->whereHas('clas.training', function ($q) {
                        $q->where('type', 'corporate');
                    })->sum('amount');
                    $privateRevenue = (clone $paymentRevenueQuery)->whereHas('clas.training', function ($q) {
                        $q->where('type', 'private');
                    })->sum('amount');

                    $certificationQuery = (clone $query)->where('sertifikasi_bnsp', true);
                    $certificationClassCount = (clone $certificationQuery)->count();
                    $certificationStudentCount = (int) (clone $certificationQuery)->sum('bnsp_student_count');
                    $certificationRevenue = (float) (clone $certificationQuery)
                        ->get(['bnsp_student_count', 'bnsp_fee_per_student'])
                        ->sum(function ($item) {
                            return ((int) ($item->bnsp_student_count ?? 0)) * ((float) ($item->bnsp_fee_per_student ?? 0));
                        });
                @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        <section class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Daftar Omset</h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Omset Reguler</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($regularRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Total pendapatan kotor kategori reguler</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Omset Corporate</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($corporateRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Total pendapatan kotor kategori corporate</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Omset Private</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($privateRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Total pendapatan kotor kategori private</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Pendapatan Kotor (Bruto)</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Total pendapatan kotor semua kategori</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Total Semua Kelas</h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Total Kelas Reguler</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">{{ $regularClasses }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Jumlah kelas kategori reguler</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-building text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Total Kelas Corporate</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">{{ $corporateClasses }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Jumlah kelas kategori corporate</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-user text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Total Kelas Private</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">{{ $privateClasses }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Jumlah kelas kategori private</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-layer-group text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Total Kelas</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">{{ $totalClasses }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Total kelas approved + selesai (reguler, corporate, private)</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Program Sertifikasi</h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-certificate text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Omset Sertifikasi</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">Rp {{ number_format($certificationRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-700 leading-tight">Dari {{ $certificationClassCount }} kelas sertifikasi</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
                    <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                        <i class="fas fa-id-card text-2xl text-[#fe0000]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base text-gray-900 leading-tight">Data Sertifikasi</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900 leading-tight break-words">{{ number_format($certificationStudentCount, 0, ',', '.') }} Siswa</p>
                        <p class="text-xs text-gray-700 leading-tight">Total peserta program sertifikasi</p>
                    </div>
                </div>
            </div>
        </section>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-diagram-project text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-base text-gray-900">Total Project</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProjects }}</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-circle-check text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-base text-gray-900">Project Selesai</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $completedProjects }}</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-spinner text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-base text-gray-900">Project Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeProjects }}</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-base text-gray-900">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
    </div>
    @endif
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