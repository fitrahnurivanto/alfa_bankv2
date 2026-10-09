@extends('layouts.app')

@section('content')
<div class="p-6">
    @php
        $activePeriod = $period ?? ('month_' . now()->format('m'));
        $monthNames   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        if (preg_match('/^month_(\d{2})$/', $activePeriod, $pm)) {
            $periodLabel = $monthNames[(int)$pm[1] - 1];
        } elseif ($activePeriod === 'all') {
            $periodLabel = 'Semua Waktu';
        } else {
            $periodLabel = $monthNames[now()->month - 1];
        }
    @endphp

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Kelas Berjalan</h1>
            <p class="text-gray-800">Daftar semua kelas (diurutkan berdasarkan pembuatan terbaru)</p>
            <div class="mt-3 inline-flex items-center gap-2 px-4 py-1.5 bg-[#fff4e5] text-[#e28100] border border-[#e28100] rounded-md text-sm font-medium">
                <i class="far fa-pen-to-square"></i>
                Menampilkan Data: {{ $periodLabel }}
            </div>
        </div>
        <a href="{{ route('admin.classes.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-[#fe0000] text-white font-semibold rounded-lg shadow-[0_3px_6px_rgba(0,0,0,0.25)] hover:bg-[#cc0000] transition">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Semua Kelas
        </a>
    </div>

    <!-- Statistics Cards -->
    @php
        use App\Models\Clas;
        // Apply period filter to statistics
        $statsQuery = Clas::query();
        
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $statsQuery->whereYear('start_date', now()->year)->whereMonth('start_date', $month);
        } elseif ($period !== 'all') {
            $statsQuery->whereYear('start_date', now()->year)->whereMonth('start_date', now()->month);
        }
        
        $totalClassesCount = (clone $statsQuery)->count();
        $regularClassesCount = (clone $statsQuery)->whereHas('training', function ($query) {
            $query->where('type', 'reguler');
        })->count();
        $corporateClassesCount = (clone $statsQuery)->whereHas('training', function ($query) {
            $query->where('type', 'corporate');
        })->count();
        $privateClassesCount = (clone $statsQuery)->whereHas('training', function ($query) {
            $query->where('type', 'private');
        })->count();
    @endphp
    
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="bg-white rounded-lg shadow-[0_2px_5px_rgba(0,0,0,0.2)] p-3 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-chalkboard-user text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Total Semua Kelas</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $totalClassesCount }}</p>
                    <p class="text-[11px] text-gray-700">Semua kelas sesuai filter</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_5px_rgba(0,0,0,0.2)] p-3 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-book-open text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Total Kelas Reguler</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $regularClassesCount }}</p>
                    <p class="text-[11px] text-gray-700">Kategori reguler</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_5px_rgba(0,0,0,0.2)] p-3 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-building text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Total Kelas Corporate</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $corporateClassesCount }}</p>
                    <p class="text-[11px] text-gray-700">Kategori corporate</p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-[0_2px_5px_rgba(0,0,0,0.2)] p-3 flex items-center gap-3">
                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
                    <i class="fas fa-user text-2xl text-[#fe0000]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Total Kelas Private</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $privateClassesCount }}</p>
                    <p class="text-[11px] text-gray-700">Kategori private</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6 space-y-3">
        <form method="GET" action="{{ route('admin.classes.showclas') }}" class="flex flex-wrap gap-2 w-full justify-end">
            <div class="flex-1 sm:flex-none min-w-[170px]">
                <label for="filter-status" class="sr-only">Status</label>
                <select id="filter-status" name="status" class="w-full px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] bg-white" onchange="this.form.submit()">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Active</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="flex-1 sm:flex-none min-w-[190px]">
                <label for="filter-kategori" class="sr-only">Kategori</label>
                <select id="filter-kategori" name="kategori" class="w-full px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] bg-white" onchange="this.form.submit()">
                    <option value="" {{ !request('kategori') ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('kategori') === (string) $category->id ? 'selected' : '' }}>{{ $category->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 sm:flex-none min-w-[190px]">
                <label for="filter-period" class="sr-only">Waktu</label>
                <select id="filter-period" name="period" class="w-full px-4 py-2.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000] bg-white" onchange="this.form.submit()">
                    <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="month_01" {{ ($period ?? '') === 'month_01' ? 'selected' : '' }}>Januari</option>
                    <option value="month_02" {{ ($period ?? '') === 'month_02' ? 'selected' : '' }}>Februari</option>
                    <option value="month_03" {{ ($period ?? '') === 'month_03' ? 'selected' : '' }}>Maret</option>
                    <option value="month_04" {{ ($period ?? '') === 'month_04' ? 'selected' : '' }}>April</option>
                    <option value="month_05" {{ ($period ?? '') === 'month_05' ? 'selected' : '' }}>Mei</option>
                    <option value="month_06" {{ ($period ?? '') === 'month_06' ? 'selected' : '' }}>Juni</option>
                    <option value="month_07" {{ ($period ?? '') === 'month_07' ? 'selected' : '' }}>Juli</option>
                    <option value="month_08" {{ ($period ?? '') === 'month_08' ? 'selected' : '' }}>Agustus</option>
                    <option value="month_09" {{ ($period ?? '') === 'month_09' ? 'selected' : '' }}>September</option>
                    <option value="month_10" {{ ($period ?? '') === 'month_10' ? 'selected' : '' }}>Oktober</option>
                    <option value="month_11" {{ ($period ?? '') === 'month_11' ? 'selected' : '' }}>November</option>
                    <option value="month_12" {{ ($period ?? '') === 'month_12' ? 'selected' : '' }}>Desember</option>
                </select>
            </div>
        </form>
        
        <!-- Active Filters Info -->
        @if(request('status') || request('kategori') || (($period ?? 'this_month') !== 'this_month'))
        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-info-circle"></i>
                <span>Filter aktif:
                    @if(request('status'))
                        <span class="font-semibold text-gray-800">{{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('status') && request('kategori'))
                        <span class="mx-1">&</span>
                    @endif
                    @if(request('kategori'))
                        <span class="font-semibold text-gray-800">{{ $categories->find(request('kategori'))->nama_kategori ?? 'Kategori' }}</span>
                    @endif
                    @if(request('status') || request('kategori'))
                        <span class="mx-1">&</span>
                    @endif
                    <span class="font-semibold text-gray-800">
                        @if(($period ?? 'this_month') === 'today')
                            Hari Ini
                        @elseif(($period ?? 'this_month') === 'this_week')
                            Minggu Ini
                        @elseif(($period ?? 'this_month') === 'this_year')
                            Tahun Ini
                        @elseif(($period ?? 'this_month') === 'all')
                            Semua Waktu
                        @else
                            Bulan Ini
                        @endif
                    </span>
                </span>
            </div>
            <a href="{{ route('admin.classes.showclas') }}" 
               class="text-sm px-3 py-1 bg-[#fed0d0] text-[#fe0000] hover:bg-[#fcb5b5] rounded-lg transition">
                <i class="fas fa-times mr-1"></i> Reset Filter
            </a>
        </div>
        @endif
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($approvedClasses as $class)
            <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] hover:shadow-lg transition-all overflow-hidden">
                <div class="p-5">
                    <!-- Header -->
                    <div class="flex justify-between items-start -mx-5 -mt-5 mb-3 px-6 py-4 border-b border-gray-300">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 line-clamp-2">{{ $class->name }}</h3>
                            @if($class->instansi)
                            <p class="text-xs text-gray-500 mt-1"><i class="fas fa-building mr-1"></i>{{ $class->instansi }}</p>
                            @endif
                            @if(str_contains(strtolower($class->kategori->nama_kategori ?? ''), 'private') && !empty($class->private_student_name))
                            <p class="text-xs text-gray-700 mt-1"><i class="fas fa-user mr-1"></i>{{ $class->private_student_name }}</p>
                            @endif
                        </div>
                        @if($class->status === 'pending')
                            <span class="px-3 py-1 bg-[#fff08a] text-[#8a6d00] rounded-full text-xs font-medium whitespace-nowrap ml-2">
                                <i class="fas fa-check mr-1"></i>Pending
                            </span>
                        @elseif($class->status === 'done')
                            <span class="px-3 py-1 bg-[#cfdcff] text-[#344bfd] rounded-full text-xs font-medium whitespace-nowrap ml-2">
                                <i class="fas fa-check mr-1"></i>Selesai
                            </span>
                        @elseif($class->status === 'rejected')
                            <span class="px-3 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium whitespace-nowrap ml-2">
                                <i class="fas fa-times-circle mr-1"></i>Rejected
                            </span>
                        @else
                            <span class="px-3 py-1 bg-[#c8f7b4] text-[#2e8b12] rounded-full text-xs font-medium whitespace-nowrap ml-2">
                                <i class="fas fa-check mr-1"></i>Aktif
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 mb-3">
                    <!-- Kategori -->
                    @if($class->kategori)
                    <div class="contents">
                        @php
                            $kategoriLower = strtolower($class->kategori->nama_kategori);
                        @endphp
                        @if(str_contains($kategoriLower, 'private'))
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium">
                                <i class="fas fa-user mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @elseif(str_contains($kategoriLower, 'reguler'))
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium">
                                <i class="fas fa-users"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @elseif(str_contains($kategoriLower, 'corporate'))
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium">
                                <i class="fas fa-building mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fed0d0] text-[#fe0000] rounded-full text-xs font-medium">
                                <i class="fas fa-tag mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @endif
                    </div>
                    @endif

                    <!-- Trainer -->
                    <div class="contents">
                        <div class="contents">
                            @if($class->trainers && $class->trainers->count() > 0)
                                @foreach($class->trainers as $trainerUser)
                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs">
                                        <i class="fas fa-user-group mr-1"></i>{{ $trainerUser->name }}
                                    </span>
                                @endforeach
                            @elseif(is_array($class->trainer))
                                @foreach($class->trainer as $trainer)
                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs">
                                        <i class="fas fa-user-group mr-1"></i>{{ $trainer }}
                                    </span>
                                @endforeach
                            @elseif(!empty($class->trainer))
                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs">
                                    <i class="fas fa-user-group mr-1"></i>{{ $class->trainer }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs">
                                    <i class="fas fa-user-group mr-1"></i>Trainer belum ditentukan
                                </span>
                            @endif
                        </div>
                    </div>

                    
                    <!-- Metode -->
                    <div class="contents">
                        @if($class->method === 'online')
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#cfdcff] text-[#344bfd] border border-[#344bfd]/30 rounded-full text-xs">
                                <i class="fas fa-laptop mr-1"></i>Online
                            </span>
                        @elseif($class->method === 'offline')
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fdebd0] text-[#8a4b00] border border-[#e28100]/50 rounded-full text-xs">
                                <i class="fas fa-building mr-1"></i>Offline
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-[#fed0d0] text-[#fe0000] border border-[#fe0000]/30 rounded-full text-xs">
                                <i class="fas fa-exchange-alt mr-1"></i>Mix
                            </span>
                        @endif
                    </div>
            
                    
                </div>

                <!-- Jadwal -->
                    <div class="flex items-center py-2.5 border-t border-gray-200 text-base text-gray-900">
                        <i class="far fa-calendar w-5 text-center text-gray-800 mr-3"></i>
                        {{ $class->start_date?->format('d M Y') ?? '-' }} - {{ $class->end_date?->format('d M Y') ?? '-' }}
                    </div>

                    <!-- Summary -->
                    <div class="flex flex-col text-base text-gray-900 mb-4 border-b border-gray-200">
                        @php
                            $kategoriName = strtolower($class->kategori->nama_kategori ?? '');
                            $isCorporate = str_contains($kategoriName, 'corporate');
                            $isPrivate = str_contains($kategoriName, 'private');
                        @endphp
                        
                        @if($isCorporate)
                            <span class="flex items-center py-2.5 border-t border-gray-200"><i class="fas fa-building w-5 text-center text-gray-800 mr-3"></i>{{ $class->instansi ?? 'Corporate' }}</span>
                        @elseif($isPrivate)
                            <span class="flex items-center py-2.5 border-t border-gray-200"><i class="fas fa-user w-5 text-center text-gray-800 mr-3"></i>{{ $class->private_student_name ?: '1 peserta' }}</span>
                        @else
                            <span class="flex items-center py-2.5 border-t border-gray-200"><i class="fas fa-users w-5 text-center text-gray-800 mr-3"></i>{{ $class->amount }} peserta</span>
                        @endif
                        
                        <span class="flex items-center py-2.5 border-t border-gray-200"><i class="fas fa-clock w-5 text-center text-gray-800 mr-3"></i>{{ $class->duration }} JPL</span>
                        <span class="flex items-center py-2.5 border-t border-gray-200"><i class="fas fa-book w-5 text-center text-gray-800 mr-3"></i>{{ $class->meet }}x</span>
                    </div>

                    <!-- Progress Bar: Target Pendapatan -->
                    <div class="mb-4">
                        @php
                            $targetRevenue = (float) ($class->target_revenue ?? 0);
                            $paidAmount    = (float) ($class->paid_amount ?? 0);
                    
                            // Jika ada target revenue → progress berdasarkan uang masuk vs target
                            if ($targetRevenue > 0) {
                                $revenueProgress     = min(100, round($paidAmount / $targetRevenue * 100));
                                $revenueProgressColor = $revenueProgress >= 100
                                    ? 'bg-[#43bf21]'
                                    : ($revenueProgress >= 60
                                        ? 'bg-[#344bfd]'
                                        : ($revenueProgress >= 30
                                            ? 'bg-[#e28100]'
                                            : 'bg-[#fe0000]'));
                            } else {
                                $revenueProgress      = null;
                                $revenueProgressColor = 'bg-gray-300';
                            }
                    
                            // Progress status kelas (existing logic)
                            $statusProgress = 0;
                            $progressText   = 'Belum Dimulai';
                            $progressColor  = 'bg-gray-400';
                    
                            if ($class->status === 'approved') {
                                $statusProgress = 50;
                                $progressText   = 'Sedang Berjalan';
                                $progressColor  = 'bg-[#e28100]';
                            } elseif ($class->status === 'done') {
                                $statusProgress = 100;
                                $progressText   = 'Selesai';
                                $progressColor  = 'bg-[#43bf21]';
                            } elseif ($class->status === 'rejected') {
                                $statusProgress = 0;
                                $progressText   = 'Ditolak';
                                $progressColor  = 'bg-red-400';
                            }
                        @endphp
                    
                        {{-- Progress status kelas --}}
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                            <span class="font-semibold"><i class="fas fa-tasks mr-1"></i>{{ $progressText }}</span>
                            <span class="font-bold {{ $statusProgress >= 100 ? 'text-green-600' : ($statusProgress >= 50 ? 'text-gray-900' : 'text-gray-500') }}">
                                {{ $statusProgress }}%
                            </span>
                        </div>
                        <div class="w-full bg-[#d9d9d9] rounded-full h-2.5 overflow-hidden mb-3">
                            <div class="{{ $progressColor }} h-2.5 rounded-full transition-all duration-500"
                                style="width: {{ $statusProgress }}%"></div>
                        </div>
                    
                        {{-- Progress target pendapatan --}}
                        @if($targetRevenue > 0)
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                <span class="font-semibold">
                                    <i class="fas fa-bullseye mr-1 text-emerald-600"></i>Target Pendapatan
                                </span>
                                <span class="font-bold {{ $revenueProgress >= 100 ? 'text-green-600' : ($revenueProgress >= 60 ? 'text-gray-900' : 'text-gray-900') }}">
                                    {{ $revenueProgress }}%
                                </span>
                            </div>
                            <div class="w-full bg-[#d9d9d9] rounded-full h-2.5 overflow-hidden mb-1">
                                <div class="{{ $revenueProgressColor }} h-2.5 rounded-full transition-all duration-500"
                                    style="width: {{ $revenueProgress }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-gray-400">
                                <span>Masuk: <strong class="text-gray-600">Rp {{ number_format($paidAmount, 0, ',', '.') }}</strong></span>
                                <span>Target: <strong class="text-gray-600">Rp {{ number_format($targetRevenue, 0, ',', '.') }}</strong></span>
                            </div>
                        @else
                            {{-- Belum ada target, tampilkan hint --}}
                            <div class="mt-1 text-[10px] text-gray-400 italic">
                                <i class="fas fa-info-circle mr-1"></i>Belum ada target pendapatan. Set target saat edit kelas.
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        @if($class->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                            <div class="grid grid-cols-2 gap-2">
                                @if($class->activeGradeFile && $class->activeGradeFile->status === 'approved')
                                    <form action="{{ route('admin.classes.mark-as-done', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kelas ini?');">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2.5 bg-[#43bf21] text-white rounded-md hover:bg-[#37a01b] transition text-sm font-semibold">
                                            <i class="fas fa-lock-open mr-1"></i>Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full px-3 py-2.5 bg-[#fed0d0] text-[#fe0000] rounded-md cursor-not-allowed text-sm font-semibold" title="Butuh file nilai disetujui admin">
                                        <i class="fas fa-lock mr-1"></i>Selesaikan
                                    </button>
                                @endif
                                <form action="{{ route('admin.classes.reject', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin reject kelas ini?');">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2.5 bg-white border border-[#fe0000] text-[#fe0000] rounded-md hover:bg-[#fed0d0]/50 transition text-sm font-semibold">
                                        <i class="far fa-circle-xmark mr-1"></i>Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                        <a href="{{ route('admin.classes.show', $class) }}"
                           class="block w-full text-center px-4 py-2.5 bg-[#fe0000] text-white font-semibold rounded-md hover:bg-[#cc0000] transition">
                            <i class="far fa-eye mr-2"></i>Detail Kelas
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-12 text-center">
                    <i class="fas fa-chalkboard-user text-6xl text-[#fed0d0] mb-4"></i>
                    @if(request('status'))
                        @if(request('status') == 'pending')
                            <p class="text-gray-500 text-lg mb-4">Tidak ada kelas dengan status Pending</p>
                            <p class="text-gray-400 text-sm">Kelas pending akan muncul di sini setelah dibuat</p>
                        @elseif(request('status') == 'approved')
                            <p class="text-gray-500 text-lg mb-4">Tidak ada kelas yang sedang aktif</p>
                            <p class="text-gray-400 text-sm">Approve kelas pending untuk menampilkannya di sini</p>
                        @elseif(request('status') == 'done')
                            <p class="text-gray-500 text-lg mb-4">Belum ada kelas yang selesai</p>
                            <p class="text-gray-400 text-sm">Tandai kelas aktif sebagai selesai untuk menampilkannya di sini</p>
                        @elseif(request('status') == 'rejected')
                            <p class="text-gray-500 text-lg mb-4">Tidak ada kelas yang rejected</p>
                            <p class="text-gray-400 text-sm">Kelas yang ditolak akan muncul di sini</p>
                        @endif
                    @elseif(request('kategori'))
                        <p class="text-gray-500 text-lg mb-4">Belum ada kelas untuk kategori ini</p>
                        <p class="text-gray-400 text-sm">Pilih kategori lain atau hapus filter</p>
                    @else
                        <p class="text-gray-500 text-lg mb-4">Belum ada kelas tersedia</p>
                        <p class="text-gray-400 text-sm">Buat kelas baru dari halaman manajemen kelas</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection