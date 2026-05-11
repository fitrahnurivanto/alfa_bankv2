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
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelas Berjalan</h1>
                <p class="text-gray-600">Daftar semua kelas (diurutkan berdasarkan pembuatan terbaru)</p>
                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold">
                    <i class="fas fa-calendar-check"></i>
                    Menampilkan Data: {{ $periodLabel }}
                </div>
            </div>
            <a href="{{ route('admin.classes.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Semua Kelas
            </a>
        </div>
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
    
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Semua Kelas</p>
                        <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-gray-900 break-words">{{ $totalClassesCount }}</h4>
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
                        <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-blue-700 break-words">{{ $regularClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1 truncate">Kategori reguler</p>
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
                        <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-indigo-700 break-words">{{ $corporateClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1 truncate">Kategori corporate</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-xl">
                        <i class="fas fa-building text-3xl text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm hover:-translate-y-1 transition-transform duration-300">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-500 text-xs md:text-sm mb-1 truncate">Total Kelas Private</p>
                        <h4 class="text-lg md:text-xl xl:text-2xl font-bold text-amber-700 break-words">{{ $privateClassesCount }}</h4>
                        <p class="text-gray-400 text-xs mt-1 truncate">Kategori private</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-xl">
                        <i class="fas fa-user text-3xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 space-y-3">
        <form method="GET" action="{{ route('admin.classes.showclas') }}" class="flex flex-wrap gap-2 w-full justify-end">
            <div class="flex-1 sm:flex-none min-w-[170px]">
                <label for="filter-status" class="sr-only">Status</label>
                <select id="filter-status" name="status" class="w-full px-3 md:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Active</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="flex-1 sm:flex-none min-w-[190px]">
                <label for="filter-kategori" class="sr-only">Kategori</label>
                <select id="filter-kategori" name="kategori" class="w-full px-3 md:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
                    <option value="" {{ !request('kategori') ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('kategori') === (string) $category->id ? 'selected' : '' }}>{{ $category->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 sm:flex-none min-w-[190px]">
                <label for="filter-period" class="sr-only">Waktu</label>
                <select id="filter-period" name="period" class="w-full px-3 md:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white" onchange="this.form.submit()">
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
        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
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
               class="text-sm px-3 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition">
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
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all border border-gray-100">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 line-clamp-2">{{ $class->name }}</h3>
                            @if($class->instansi)
                            <p class="text-xs text-gray-500 mt-1"><i class="fas fa-building mr-1"></i>{{ $class->instansi }}</p>
                            @endif
                            @if(str_contains(strtolower($class->kategori->nama_kategori ?? ''), 'private') && !empty($class->private_student_name))
                            <p class="text-xs text-purple-700 mt-1"><i class="fas fa-user mr-1"></i>{{ $class->private_student_name }}</p>
                            @endif
                        </div>
                        @if($class->status === 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold whitespace-nowrap ml-2">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @elseif($class->status === 'done')
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold whitespace-nowrap ml-2">
                                <i class="fas fa-check-double mr-1"></i>Selesai
                            </span>
                        @elseif($class->status === 'rejected')
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold whitespace-nowrap ml-2">
                                <i class="fas fa-times-circle mr-1"></i>Rejected
                            </span>
                        @else
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold whitespace-nowrap ml-2">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @endif
                    </div>

                    <!-- Kategori -->
                    @if($class->kategori)
                    <div class="mb-3">
                        @php
                            $kategoriLower = strtolower($class->kategori->nama_kategori);
                        @endphp
                        @if(str_contains($kategoriLower, 'private'))
                            <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 rounded-lg text-xs font-semibold">
                                <i class="fas fa-user mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @elseif(str_contains($kategoriLower, 'reguler'))
                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-semibold">
                                <i class="fas fa-users mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @elseif(str_contains($kategoriLower, 'corporate'))
                            <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 rounded-lg text-xs font-semibold">
                                <i class="fas fa-building mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-lg text-xs font-semibold">
                                <i class="fas fa-tag mr-1"></i>{{ $class->kategori->nama_kategori }}
                            </span>
                        @endif
                    </div>
                    @endif

                    <!-- Trainer -->
                    <div class="mb-3">
                        <div class="flex flex-wrap gap-1">
                            @if($class->trainers && $class->trainers->count() > 0)
                                @foreach($class->trainers as $trainerUser)
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        <i class="fas fa-user-tie mr-1"></i>{{ $trainerUser->name }}
                                    </span>
                                @endforeach
                            @elseif(is_array($class->trainer))
                                @foreach($class->trainer as $trainer)
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        <i class="fas fa-user-tie mr-1"></i>{{ $trainer }}
                                    </span>
                                @endforeach
                            @elseif(!empty($class->trainer))
                                <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                    <i class="fas fa-user-tie mr-1"></i>{{ $class->trainer }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                    <i class="fas fa-user-tie mr-1"></i>Trainer belum ditentukan
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div class="mb-3 text-sm text-gray-600">
                        <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                        {{ $class->start_date->format('d M Y') }} - {{ $class->end_date->format('d M Y') }}
                    </div>

                    <!-- Summary -->
                    <div class="flex justify-between text-sm text-gray-600 mb-4 pb-4 border-b border-gray-100">
                        @php
                            $kategoriName = strtolower($class->kategori->nama_kategori ?? '');
                            $isCorporate = str_contains($kategoriName, 'corporate');
                            $isPrivate = str_contains($kategoriName, 'private');
                        @endphp
                        
                        @if($isCorporate)
                            <span><i class="fas fa-building mr-1 text-gray-400"></i>{{ $class->instansi ?? 'Corporate' }}</span>
                        @elseif($isPrivate)
                            <span><i class="fas fa-user mr-1 text-gray-400"></i>{{ $class->private_student_name ?: '1 peserta' }}</span>
                        @else
                            <span><i class="fas fa-users mr-1 text-gray-400"></i>{{ $class->amount }} peserta</span>
                        @endif
                        
                        <span><i class="fas fa-clock mr-1 text-gray-400"></i>{{ $class->duration }} JPL</span>
                        <span><i class="fas fa-book mr-1 text-gray-400"></i>{{ $class->meet }}x</span>
                    </div>

                    <!-- Metode -->
                    <div class="mb-4">
                        @if($class->method === 'online')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                <i class="fas fa-laptop mr-1"></i>Online
                            </span>
                        @elseif($class->method === 'offline')
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">
                                <i class="fas fa-building mr-1"></i>Offline
                            </span>
                        @else
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                <i class="fas fa-exchange-alt mr-1"></i>Mix
                            </span>
                        @endif
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        @php
                            // Hitung progress berdasarkan status
                            $progress = 0;
                            $progressText = 'Belum Dimulai';
                            $progressColor = 'bg-gray-400';
                            
                            if($class->status === 'approved') {
                                $progress = 50;
                                $progressText = 'Sedang Berjalan';
                                $progressColor = 'bg-gradient-to-r from-yellow-400 to-amber-500';
                            } elseif($class->status === 'done') {
                                $progress = 100;
                                $progressText = 'Selesai';
                                $progressColor = 'bg-gradient-to-r from-green-400 to-emerald-500';
                            } elseif($class->status === 'rejected') {
                                $progress = 0;
                                $progressText = 'Ditolak';
                                $progressColor = 'bg-red-400';
                            }
                        @endphp
                        
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                            <span class="font-semibold"><i class="fas fa-tasks mr-1"></i>{{ $progressText }}</span>
                            <span class="font-bold {{ $progress >= 100 ? 'text-green-600' : ($progress >= 50 ? 'text-amber-600' : 'text-gray-500') }}">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                            <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500 shadow-sm flex items-center justify-end px-1"
                                 style="width: {{ $progress }}%">
                                @if($progress >= 20)
                                    <i class="fas fa-{{ $progress >= 100 ? 'check' : 'spinner fa-pulse' }} text-white text-[8px]"></i>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        @if($class->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                            <div class="grid grid-cols-2 gap-2">
                                @if($class->activeGradeFile && $class->activeGradeFile->status === 'approved')
                                    <form action="{{ route('admin.classes.mark-as-done', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kelas ini?');">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-xs">
                                            <i class="fas fa-check-double mr-1"></i>Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full px-3 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed text-xs" title="Butuh file nilai disetujui admin">
                                        <i class="fas fa-lock mr-1"></i>Selesaikan
                                    </button>
                                @endif
                                <form action="{{ route('admin.classes.reject', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin reject kelas ini?');">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-xs">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                        <a href="{{ route('admin.classes.show', $class) }}"
                           class="block w-full text-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-md transition">
                            <i class="fas fa-eye mr-2"></i>Detail Kelas
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <i class="fas fa-chalkboard-teacher text-6xl text-gray-300 mb-4"></i>
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
