@extends('layouts.app')

@section('page-title', 'Payment Requests')

@section('content')
<!-- Header -->
<div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permintaan Pembayaran</h1>
            <p class="text-gray-600">Kelola payment request dari karyawan</p>
        </div>
        @if($pendingCount > 0)
            <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2 rounded-lg">
                <i class="fas fa-clock text-yellow-600"></i>
                <span class="text-yellow-800 font-semibold">{{ $pendingCount }} Pending Review</span>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-5 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-700 text-xs font-medium">Pending Review</p>
                    <h3 class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
                <div class="bg-yellow-200 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-700 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-700 text-xs font-medium">Admin Approved</p>
                    <h3 class="text-2xl font-bold text-green-900 mt-1">{{ $stats['admin_approved'] ?? 0 }}</h3>
                </div>
                <div class="bg-green-200 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-700 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 text-xs font-medium">Finance Approved</p>
                    <h3 class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['finance_approved'] ?? 0 }}</h3>
                </div>
                <div class="bg-blue-200 p-3 rounded-lg">
                    <i class="fas fa-check-double text-blue-700 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-700 text-xs font-medium">Paid</p>
                    <h3 class="text-2xl font-bold text-emerald-900 mt-1">{{ $stats['paid'] ?? 0 }}</h3>
                    <p class="text-xs text-emerald-600 mt-1">Rp {{ number_format($stats['total_paid'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-emerald-200 p-3 rounded-lg">
                    <i class="fas fa-money-check-alt text-emerald-700 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-700 text-xs font-medium">Rejected</p>
                    <h3 class="text-2xl font-bold text-red-900 mt-1">{{ ($stats['admin_rejected'] ?? 0) + ($stats['finance_rejected'] ?? 0) }}</h3>
                </div>
                <div class="bg-red-200 p-3 rounded-lg">
                    <i class="fas fa-times-circle text-red-700 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm mb-6 p-4">
        <form method="GET" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="admin_approved" {{ request('status') == 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
                    <option value="finance_approved" {{ request('status') == 'finance_approved' ? 'selected' : '' }}>Finance Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                    <option value="admin_rejected" {{ request('status') == 'admin_rejected' ? 'selected' : '' }}>Ditolak Admin</option>
                    <option value="finance_rejected" {{ request('status') == 'finance_rejected' ? 'selected' : '' }}>Ditolak Finance</option>
                </select>
                <select name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
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
                <select name="year" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
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
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    @if(request('status') || $period != 'month_' . date('m') || $year != date('Y'))
                    <a href="{{ route('admin.payment-requests.index') }}" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition text-center text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Diajukan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Disetujui</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    {{ $request->created_at->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-indigo-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    @if($request->project)
                                        <p class="text-sm font-medium text-gray-900">{{ $request->project->project_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->project->project_code }}</p>
                                    @elseif($request->clas)
                                        <p class="text-sm font-medium text-gray-900">{{ $request->clas->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-graduation-cap"></i>
                                                Kelas {{ $request->clas->kategori ? $request->clas->kategori->name : 'Pelatihan' }}
                                            </span>
                                            @if($request->clas->instansi)
                                                • {{ Str::limit($request->clas->instansi, 20) }}
                                            @endif
                                        </p>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($request->requested_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($request->approved_amount)
                                    <span class="text-sm font-semibold text-green-600">
                                        Rp {{ number_format($request->approved_amount, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($request->status === 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @elseif($request->status === 'admin_approved')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-check"></i> Admin Approved
                                    </span>
                                @elseif($request->status === 'finance_approved')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-check-double"></i> Finance Approved
                                    </span>
                                @elseif($request->status === 'paid')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-money-check-alt"></i> Paid
                                    </span>
                                @elseif($request->status === 'admin_rejected')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-times"></i> Ditolak Admin
                                    </span>
                                @elseif($request->status === 'finance_rejected')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full flex items-center gap-1 w-fit">
                                        <i class="fas fa-times"></i> Ditolak Finance
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.payment-requests.show', $request) }}" 
                                   class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">Belum ada permintaan pembayaran</p>
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
