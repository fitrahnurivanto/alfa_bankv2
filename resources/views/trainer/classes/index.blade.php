@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Kelas Saya</h1>
        <p class="text-gray-600">Daftar kelas yang Anda ajar</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Kelas</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Sedang Berjalan</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['ongoing'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Selesai</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['done'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('trainer.classes.index') }}" method="GET" class="flex flex-col gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama kelas atau instansi..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Semua Status</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Selesai</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                </select>
                <select name="period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
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
                <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="all">Semua Tahun</option>
                    @php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $selected = $year == $y ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                    @endphp
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    @if(request('search') || request('status') || request('sort') || request('period', 'month_' . date('m')) != 'month_' . date('m') || request('year', date('Y')) != date('Y'))
                    <a href="{{ route('trainer.classes.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center text-sm font-medium">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Classes Grid -->
    @if($classes->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada kelas</h3>
        <p class="text-gray-500">Anda belum ditugaskan untuk mengajar kelas apapun.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($classes as $class)
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded">
                        {{ $class->kategori->nama_kategori ?? 'Umum' }}
                    </span>
                </div>
                <span class="px-2 py-1 text-xs rounded-full
                    {{ $class->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $class->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $class->status === 'done' ? 'bg-blue-100 text-blue-700' : '' }}">
                    {{ $class->status === 'done' ? 'Selesai' : ucfirst($class->status) }}
                </span>
            </div>
            
            <a href="{{ route('trainer.classes.show', $class) }}" class="block mb-3">
                <h3 class="text-lg font-bold text-gray-900 hover:text-purple-600 transition line-clamp-2">
                    {{ $class->name }}
                </h3>
            </a>
            
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-building w-5 text-gray-400"></i>
                    <span class="truncate">{{ $class->instansi ?? '-' }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span>{{ $class->start_date ? \Carbon\Carbon::parse($class->start_date)->format('d M Y') : '-' }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-users w-5 text-gray-400"></i>
                    <span>{{ $class->amount ?? 0 }} Peserta</span>
                </div>
                <div class="flex items-center font-semibold text-green-600">
                    <i class="fas fa-money-bill-wave w-5"></i>
                    <span>Rp {{ number_format($class->trainer_honor ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100">
                <a href="{{ route('trainer.classes.show', $class) }}" class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-eye mr-2"></i>Lihat Detail
                </a>
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
@endsection
