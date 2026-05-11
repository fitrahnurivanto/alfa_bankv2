@extends('layouts.app')

@section('page-title', 'Kelas Akademik')

@section('content')
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Kelas Akademik</h1>
        <p class="text-sm text-gray-600 mt-1">Input jumlah siswa lulus/tidak lulus dan monitoring absensi trainer</p>
    </div>

    <a href="{{ route('akademik.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
        <i class="fas fa-chart-line mr-2"></i>Kembali ke Dashboard
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-6">
    <form method="GET" action="{{ route('akademik.classes.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Cari Kelas / Instansi</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Contoh: Inhouse BSI"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="period" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>Semua Periode</option>
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

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
            <select name="year" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                @foreach(($years ?? collect([date('Y')])) as $yearItem)
                    <option value="{{ $yearItem }}" {{ (int)($year ?? date('Y')) === (int)$yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="done" {{ ($status ?? '') === 'done' ? 'selected' : '' }}>Done</option>
            </select>
        </div>

        <div class="md:col-span-5 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-black transition">
                <i class="fas fa-search mr-1"></i>Terapkan Filter
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($classes as $class)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $class->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $class->kategori->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $class->training->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($class->amount ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-700">{{ number_format($class->passed_students ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-rose-700">{{ number_format($class->failed_students ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $class->status === 'done' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                {{ strtoupper($class->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <div class="flex gap-2 justify-end">
                                <a href="{{ route('akademik.classes.show', $class->id) }}" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                                <a href="{{ route('akademik.classes.edit', $class->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kelas untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($classes->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $classes->links() }}
        </div>
    @endif
</div>
@endsection
