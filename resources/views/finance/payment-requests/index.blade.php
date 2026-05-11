@extends('layouts.app')

@section('page-title', 'Payment Requests - Finance')

@section('content')
<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Permintaan Pembayaran</h1>
        <p class="text-gray-600">Validasi dan proses pembayaran karyawan</p>
    </div>
    @if($stats['admin_approved'] > 0)
        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2 rounded-lg">
            <i class="fas fa-clock text-yellow-600"></i>
            <span class="text-yellow-800 font-semibold">{{ $stats['admin_approved'] }} Menunggu Validasi</span>
        </div>
    @endif
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
    </div>
@endif

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-5 border border-yellow-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-yellow-700 text-xs font-medium">Menunggu Validasi</p>
                <h3 class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['admin_approved'] }}</h3>
                <p class="text-xs text-yellow-600 mt-1">Rp {{ number_format($stats['total_pending'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-yellow-200 p-3 rounded-lg">
                <i class="fas fa-clock text-yellow-700 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-700 text-xs font-medium">Disetujui Finance</p>
                <h3 class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['finance_approved'] }}</h3>
            </div>
            <div class="bg-blue-200 p-3 rounded-lg">
                <i class="fas fa-check-double text-blue-700 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-emerald-700 text-xs font-medium">Sudah Dibayar</p>
                <h3 class="text-2xl font-bold text-emerald-900 mt-1">{{ $stats['paid'] }}</h3>
                <p class="text-xs text-emerald-600 mt-1">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-emerald-200 p-3 rounded-lg">
                <i class="fas fa-money-check-alt text-emerald-700 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-700 text-xs font-medium">Ditolak Finance</p>
                <h3 class="text-2xl font-bold text-red-900 mt-1">{{ $stats['finance_rejected'] }}</h3>
            </div>
            <div class="bg-red-200 p-3 rounded-lg">
                <i class="fas fa-times-circle text-red-700 text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl shadow-sm mb-6 p-4">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Status:</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" onchange="this.form.submit()">
                <option value="">Menunggu Validasi</option>
                <option value="admin_approved" {{ request('status') == 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
                <option value="finance_approved" {{ request('status') == 'finance_approved' ? 'selected' : '' }}>Finance Approved</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                <option value="finance_rejected" {{ request('status') == 'finance_rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Periode:</label>
            <select name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" onchange="this.form.submit()">
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

        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Tahun:</label>
            <select name="year" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" onchange="this.form.submit()">
                @foreach(($years ?? collect([date('Y')])) as $yearItem)
                    <option value="{{ $yearItem }}" {{ (int)($year ?? date('Y')) === (int)$yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- Payment Requests List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-indigo-50 to-blue-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project/Kelas</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nominal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal Mulai Kelas</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($requests as $request)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-indigo-600 font-semibold text-sm">{{ substr($request->user->name, 0, 2) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $request->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $request->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($request->project)
                                <span class="text-sm text-gray-900">{{ Str::limit($request->project->project_name, 30) }}</span>
                                <span class="block text-xs text-gray-500">Project</span>
                            @elseif($request->clas)
                                <span class="text-sm text-gray-900">{{ $request->clas->name }}</span>
                                <span class="block text-xs text-gray-500">Kelas Pelatihan</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-blue-600">Rp {{ number_format($request->approved_amount ?? $request->requested_amount, 0, ',', '.') }}</div>
                            @if($request->approved_amount && $request->approved_amount != $request->requested_amount)
                                <div class="text-xs text-gray-500 line-through">Rp {{ number_format($request->requested_amount, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($request->clas && $request->clas->start_date)
                                <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($request->clas->start_date)->format('d/m/Y') }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($request->status === 'admin_approved')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                    <i class="fas fa-clock mr-1"></i>Admin Approved
                                </span>
                            @elseif($request->status === 'finance_approved')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                    <i class="fas fa-check-double mr-1"></i>Finance Approved
                                </span>
                            @elseif($request->status === 'paid')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i>Dibayar
                                </span>
                            @elseif($request->status === 'finance_rejected')
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                    <i class="fas fa-times-circle mr-1"></i>Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $request->created_at->format('d/m/Y') }}
                            <span class="block text-xs text-gray-400">{{ $request->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('finance.payment-requests.show', $request) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada payment request</p>
                                <p class="text-gray-400 text-sm">Semua payment request sudah diproses</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
