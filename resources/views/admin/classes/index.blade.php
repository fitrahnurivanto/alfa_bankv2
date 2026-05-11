@extends('layouts.app')

@section('page-title', 'Manajemen Kelas')

@section('content')
<style>
    .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        padding: 6px 12px;
        background-color: #1f2937;
        color: white;
        font-size: 12px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s, transform 0.2s;
        z-index: 50;
    }
    
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #1f2937;
    }
    
    .group:hover .tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(-4px);
    }
</style>

<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Kelas</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola data kelas pelatihan dan sertifikasi</p>
        </div>
            @if(\Illuminate\Support\Facades\Auth::user()->canCreateEditClass())
            <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#7b2cbf] to-[#9d4edd] text-white rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm font-medium whitespace-nowrap">
                <i class="fas fa-plus"></i>
                <span>Tambah Kelas</span>
            </a>
            @endif
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.classes.index') }}" method="GET" class="flex flex-col gap-3">
            <!-- Search Input -->
            <div class="w-full">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama kelas atau trainer..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
            </div>
            
            <!-- Filter Group -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Active</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Selesai</option>
                </select>
                <select name="method" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
                    <option value="">Semua Metode</option>
                    <option value="online" {{ request('method') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('method') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="mix" {{ request('method') == 'mix' ? 'selected' : '' }}>Mix (Online & Offline)</option>
                </select>
                <select name="period" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
                    <option value="all">Semua Bulan</option>
                    @php
                        $currentMonth = date('m');
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
                <select name="year" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
                    <option value="all">Semua Tahun</option>
                    @php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $selected = $year == $y ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                    @endphp
                </select>
                
                <!-- Action Buttons -->
                <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition text-sm font-medium">
                        <i class="fas fa-search mr-2"></i> Cari
                    </button>
                    @if(request('search') || request('status') || request('method') || request('period', 'month_' . date('m')) != 'month_' . date('m') || request('year', date('Y')) != date('Y'))
                    <a href="{{ route('admin.classes.index') }}" class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium text-center">
                        <i class="fas fa-times mr-2"></i> Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Classes List -->
    @if($classes->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-chalkboard-teacher text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg mb-4">Belum ada data kelas</p>
        @if(\Illuminate\Support\Facades\Auth::user()->canCreateEditClass())
        <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition">
            <i class="fas fa-plus"></i> Tambah Kelas Pertama
        </a>
        @endif
    </div>
    @else
    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($classes as $class)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <!-- Nama Kelas -->
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $class->name }}</div>
                                @if($class->instansi)
                                <div class="text-xs text-gray-500"><i class="fas fa-building mr-1"></i>{{ $class->instansi }}</div>
                                @endif
                                @if(str_contains(strtolower($class->kategori->nama_kategori ?? ''), 'private') && !empty($class->private_student_name))
                                <div class="text-xs text-purple-700 mt-1"><i class="fas fa-user mr-1"></i>{{ $class->private_student_name }}</div>
                                @endif
                                <div class="text-sm text-gray-500">{{ $class->meet }}x pertemuan • {{ $class->duration }} JPL</div>
                                
                                <!-- Pendapatan Kelas (Gross Income) -->
                                @php
                                    // Price is now total class revenue for all categories
                                    $grossIncome = $class->price;
                                @endphp
                                <div class="mt-2 text-sm">
                                    <span class="text-gray-600">Pendapatan:</span>
                                    <span class="font-bold text-green-600">Rp {{ number_format($grossIncome, 0, ',', '.') }}</span>
                                </div>
                                
                                <!-- Progress Bar -->
                                @php
                                    $progress = 0;
                                    $progressColor = 'bg-gray-400';
                                    
                                    if($class->status === 'approved') {
                                        $progress = 50;
                                        $progressColor = 'bg-amber-500';
                                    } elseif($class->status === 'done') {
                                        $progress = 100;
                                        $progressColor = 'bg-green-500';
                                    }
                                @endphp
                                
                                @if($progress > 0)
                                <div class="mt-2 w-48">
                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span class="font-bold">{{ $progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center space-y-2 max-w-[180px] mx-auto">
                            <!-- Status Badge -->
                            @if($class->status === 'done')
                                <div class="w-full px-3 py-1.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg bg-blue-100 text-blue-800">
                                    <i class="fas fa-check-double mr-1.5"></i> Selesai
                                </div>
                            @elseif($class->status === 'approved')
                                <div class="w-full px-3 py-1.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1.5"></i> Active
                                </div>
                            @elseif($class->status === 'pending')
                                <div class="w-full px-3 py-1.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1.5"></i> Waiting
                                </div>
                            @else
                                <div class="w-full px-3 py-1.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1.5"></i> Rejected
                                </div>
                            @endif
                            
                            <!-- Action Buttons untuk Pending -->
                            @if($class->status === 'pending' && \Illuminate\Support\Facades\Auth::user()->canApproveClass())
                                <div class="flex flex-col gap-1.5 w-full">
                                    <form action="{{ route('admin.classes.approve', $class) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Approve kelas ini?')"
                                                class="w-full px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-1">
                                            <i class="fas fa-check"></i>
                                            <span>Approve</span>
                                        </button>
                                    </form>
                                    <button type="button" 
                                            onclick="showRejectModal({{ $class->id }}, '{{ addslashes($class->name) }}')"
                                            class="w-full px-3 py-1.5 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-1">
                                        <i class="fas fa-times"></i>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            @endif
                            
                            <!-- Info untuk Rejected -->
                            @if($class->status === 'rejected')
                                <!-- Rejection Reason -->
                                @if($class->rejection_reason)
                                <div class="p-2 bg-red-50 border border-red-200 rounded-lg w-full">
                                    <p class="text-xs text-red-700 text-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <span class="font-semibold">Ditolak:</span> {{ Str::limit($class->rejection_reason, 40) }}
                                    </p>
                                </div>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1.5">
                            @if($class->trainers && $class->trainers->count() > 0)
                                @foreach($class->trainers as $trainer)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-user-tie"></i>
                                        {{ $trainer->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ada trainer</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($class->method === 'online')
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-laptop mr-1"></i> Online
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-building mr-1"></i> Offline
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $class->start_date->format('d M Y') }}</div>
                        <div class="text-sm text-gray-500">{{ $class->end_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.classes.show', $class) }}" 
                                   class="group relative p-2 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 rounded-lg transition">
                                    <i class="fas fa-eye text-sm"></i>
                                    <span class="tooltip">Lihat Detail</span>
                                </a>
                                @if(\Illuminate\Support\Facades\Auth::user()->canCreateEditClass())
                                <a href="{{ route('admin.classes.edit', $class) }}" 
                                   class="group relative p-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 hover:text-yellow-700 rounded-lg transition">
                                    <i class="fas fa-edit text-sm"></i>
                                    <span class="tooltip">Edit</span>
                                </a>
                                @endif
                                @if(\Illuminate\Support\Facades\Auth::user()->isAdmin() || \Illuminate\Support\Facades\Auth::user()->role === 'marketing')
                                <form action="{{ route('admin.classes.destroy', $class) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="group relative p-2 bg-red-100 hover:bg-red-200 text-red-700 hover:text-red-800 rounded-lg transition">
                                        <i class="fas fa-trash text-sm"></i>
                                        <span class="tooltip">Hapus</span>
                                    </button>
                                </form>
                                @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
        @foreach($classes as $class)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <!-- Card Header -->
            <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900 text-base mb-1">{{ $class->name }}</h3>
                @if($class->instansi)
                <p class="text-xs text-gray-600"><i class="fas fa-building mr-1"></i>{{ $class->instansi }}</p>
                @endif
                @if(str_contains(strtolower($class->kategori->nama_kategori ?? ''), 'private') && !empty($class->private_student_name))
                <p class="text-xs text-purple-700 mt-1"><i class="fas fa-user mr-1"></i>{{ $class->private_student_name }}</p>
                @endif
            </div>

            <!-- Card Body -->
            <div class="p-4 space-y-3">
                <!-- Status -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600 font-medium">Status:</span>
                    <div class="flex flex-col items-end gap-2">
                        @if($class->status === 'done')
                            <span class="px-3 py-1 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800">
                                <i class="fas fa-check-double mr-1"></i> Selesai
                            </span>
                        @elseif($class->status === 'approved')
                            <span class="px-3 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Active
                            </span>
                        @elseif($class->status === 'pending')
                            <span class="px-3 py-1 text-xs font-semibold rounded-lg bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Waiting
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Rejected
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Trainer -->
                <div>
                    <p class="text-xs text-gray-600 font-medium mb-1">Trainer:</p>
                    <div class="flex flex-wrap gap-1.5">
                        @if($class->trainers && $class->trainers->count() > 0)
                            @foreach($class->trainers as $trainer)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                    <i class="fas fa-user-tie"></i>
                                    {{ $trainer->name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-xs text-gray-400 italic">Belum ada trainer</span>
                        @endif
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Metode:</p>
                        @if($class->method === 'online')
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-laptop mr-1"></i> Online
                            </span>
                        @else
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-building mr-1"></i> Offline
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Pertemuan:</p>
                        <p class="text-sm font-medium text-gray-900">{{ $class->meet }}x • {{ $class->duration }} JPL</p>
                    </div>
                </div>

                <!-- Tanggal -->
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-600 mb-1">Periode:</p>
                    <p class="text-sm text-gray-900">
                        <i class="fas fa-calendar-alt mr-1 text-purple-600"></i>
                        {{ $class->start_date->format('d M Y') }} - {{ $class->end_date->format('d M Y') }}
                    </p>
                </div>

                <!-- Pendapatan -->
                @php
                    // Price is now total class revenue for all categories
                    $grossIncome = $class->price;
                @endphp
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-600 mb-1">Pendapatan:</p>
                    <p class="text-base font-bold text-green-600">Rp {{ number_format($grossIncome, 0, ',', '.') }}</p>
                </div>

                <!-- Progress Bar -->
                @php
                    $progress = 0;
                    $progressColor = 'bg-gray-400';
                    
                    if($class->status === 'approved') {
                        $progress = 50;
                        $progressColor = 'bg-amber-500';
                    } elseif($class->status === 'done') {
                        $progress = 100;
                        $progressColor = 'bg-green-500';
                    }
                @endphp
                
                @if($progress > 0)
                <div class="pt-2">
                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                        <span>Progress</span>
                        <span class="font-bold">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons Pending -->
                @if($class->status === 'pending')
                <div class="flex flex-col gap-2 pt-3 border-t border-gray-200">
                    <form action="{{ route('admin.classes.approve', $class) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Approve kelas ini?')"
                                class="w-full px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Approve</span>
                        </button>
                    </form>
                    <button type="button" 
                            onclick="showRejectModal({{ $class->id }}, '{{ addslashes($class->name) }}')"
                            class="w-full px-4 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i>
                        <span>Tolak</span>
                    </button>
                </div>
                @endif

                <!-- Rejection Reason -->
                @if($class->status === 'rejected' && $class->rejection_reason)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span class="font-semibold">Ditolak:</span> {{ $class->rejection_reason }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Card Footer - Action Buttons -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex gap-2">
                <a href="{{ route('admin.classes.show', $class) }}" 
                   class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition text-center">
                    <i class="fas fa-eye mr-1"></i> Detail
                </a>
                <a href="{{ route('admin.classes.edit', $class) }}" 
                   class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition text-center">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('admin.classes.destroy', $class) }}" 
                      method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');"
                      class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $classes->links() }}
    </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Tolak Kelas
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Info Kelas -->
            <div class="mb-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-info-circle mr-1 text-yellow-600"></i>
                    Anda akan menolak kelas: <span id="rejectClassName" class="font-bold"></span>
                </p>
            </div>

            <!-- Form -->
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" 
                              id="rejection_reason" 
                              rows="4" 
                              required
                              placeholder="Jelaskan alasan penolakan kelas ini..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white rounded-lg transition shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i>Tolak Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentClassId = null;

function showRejectModal(classId, className) {
    currentClassId = classId;
    document.getElementById('rejectClassName').textContent = className;
    document.getElementById('rejectForm').action = `/admin/classes/${classId}/reject`;
    document.getElementById('rejection_reason').value = '';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    currentClassId = null;
}

// Close modal on outside click
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('rejectModal').classList.contains('hidden')) {
        closeRejectModal();
    }
});

// Form validation
document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    const reason = document.getElementById('rejection_reason').value.trim();
    if (reason.length < 10) {
        e.preventDefault();
        alert('Alasan penolakan minimal 10 karakter!');
        return false;
    }
});
</script>
@endsection
