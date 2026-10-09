@extends('layouts.app')

@section('page-title', 'Payment Requests')

@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Permintaan Pembayaran</h1>
        <p class="text-gray-800">Kelola payment request dari karyawan</p>
    </div>
    @if($pendingCount > 0)
    <div class="inline-flex items-center gap-2 bg-[#fff4e5] border border-[#e28100] px-5 py-2.5 rounded-lg">
        <i class="fas fa-hourglass-half text-[#e28100]"></i>
        <span class="text-[#e28100] font-semibold">{{ $pendingCount }} Pending Review</span>
    </div>
    @endif
</div>

@if(session('success'))
<div class="bg-green-50 border border-[#43bf21] text-green-800 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="far fa-clock text-2xl text-[#fe0000]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-900">Pending Review</p>
            <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $stats['pending'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="fas fa-check text-2xl text-[#fe0000]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-900">Admin Approved</p>
            <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $stats['admin_approved'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="fas fa-check-double text-2xl text-[#fe0000]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-900">Finance Approved</p>
            <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $stats['finance_approved'] ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="fas fa-money-bill-transfer text-2xl text-[#fe0000]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-900">Paid</p>
            <p class="text-2xl font-bold text-gray-900 leading-tight">{{ $stats['paid'] ?? 0 }}</p>
            <p class="text-xs text-gray-700">Rp {{ number_format($stats['total_paid'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-3 flex items-center gap-3">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="far fa-circle-xmark text-2xl text-[#fe0000]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-900">Rejected</p>
            <p class="text-2xl font-bold text-gray-900 leading-tight">{{ ($stats['admin_rejected'] ?? 0) + ($stats['finance_rejected'] ?? 0) }}</p>
        </div>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
        <select name="status" class="sm:w-52 px-4 py-2.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="admin_approved" {{ request('status') == 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
            <option value="finance_approved" {{ request('status') == 'finance_approved' ? 'selected' : '' }}>Finance Approved</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
            <option value="admin_rejected" {{ request('status') == 'admin_rejected' ? 'selected' : '' }}>Ditolak Admin</option>
            <option value="finance_rejected" {{ request('status') == 'finance_rejected' ? 'selected' : '' }}>Ditolak Finance</option>
        </select>
        <select name="period" class="sm:w-52 px-4 py-2.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
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
        <select name="year" class="sm:w-52 px-4 py-2.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 focus:outline-none focus:border-[#fe0000]">
            <option value="all">Semua Tahun</option>
            @php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    $selected = $year == $y ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
            @endphp
        </select>
        <button type="submit" class="px-8 py-2.5 bg-[#fe0000] text-white rounded-md font-semibold shadow-[0_3px_6px_rgba(0,0,0,0.25)] hover:bg-[#cc0000] transition">
            Filter
        </button>
        @if(request('status') || $period != 'month_' . date('m') || $year != date('Y'))
        <a href="{{ route('admin.payment-requests.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-center text-sm font-medium flex items-center justify-center">
            <i class="fas fa-times mr-1"></i> Reset
        </a>
        @endif
    </div>
</form>

<!-- Table -->
<div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 sm:p-6">
    <div class="overflow-x-auto rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
        <table class="w-full">
            <thead class="border-b border-gray-300">
                <tr class="text-xs font-semibold text-gray-900 uppercase">
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-left">Employee</th>
                    <th class="px-6 py-4 text-center">Project</th>
                    <th class="px-6 py-4 text-center">Diajukan &amp; Disetujui</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse($requests as $request)
                <tr class="hover:bg-[#f9f0f1] transition">
                    <td class="px-6 py-5 text-sm text-gray-900 whitespace-nowrap">
                        {{ $request->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 shrink-0 rounded-lg bg-[#bfd7ff] flex items-center justify-center text-[#344bfd] text-sm font-bold">
                                {{ strtoupper(substr($request->user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-900">{{ $request->user->name }}</p>
                                <p class="text-xs text-gray-700">{{ $request->user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($request->project)
                        <p class="text-sm text-gray-900">{{ $request->project->project_name }}</p>
                        <p class="text-sm text-gray-900">{{ $request->project->project_code }}</p>
                        @elseif($request->clas)
                        <p class="text-sm text-gray-900">{{ $request->clas->name }}</p>
                        <p class="text-sm text-gray-900">
                            Kelas {{ $request->clas->kategori ? $request->clas->kategori->name : '' }}
                            @if($request->clas->instansi)
                            <span class="text-xs text-gray-600">• {{ Str::limit($request->clas->instansi, 20) }}</span>
                            @endif
                        </p>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center whitespace-nowrap">
                        <p class="text-sm text-gray-900">Rp {{ number_format($request->requested_amount, 0, ',', '.') }}</p>
                        @if($request->approved_amount)
                        <p class="text-sm font-bold text-gray-900">Acc: {{ number_format($request->approved_amount, 0, ',', '.') }}</p>
                        @else
                        <p class="text-sm text-gray-400">Acc: -</p>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($request->status === 'pending')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#fff08a] text-[#8a6d00] text-sm rounded-full">Pending</span>
                        @elseif($request->status === 'admin_approved')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#c8f7b4] text-[#2e8b12] text-sm rounded-full">Admin Approved</span>
                        @elseif($request->status === 'finance_approved')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#cfdcff] text-[#344bfd] text-sm rounded-full">Finance Approved</span>
                        @elseif($request->status === 'paid')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#c8f7b4] text-[#2e8b12] text-sm rounded-full">Paid</span>
                        @elseif($request->status === 'admin_rejected')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#fed0d0] text-[#fe0000] text-sm rounded-full">Ditolak Admin</span>
                        @elseif($request->status === 'finance_rejected')
                        <span class="inline-flex items-center gap-1 px-4 py-0.5 bg-[#fed0d0] text-[#fe0000] text-sm rounded-full">Ditolak Finance</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        <a href="{{ route('admin.payment-requests.show', $request) }}"
                           class="inline-flex items-center gap-2 bg-white border border-[#fe0000] text-[#fe0000] px-5 py-1.5 rounded-md hover:bg-[#fed0d0]/40 transition text-sm font-semibold">
                            <i class="far fa-eye"></i> Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-[#fed0d0] text-4xl mb-3"></i>
                        <p class="text-gray-500">Belum ada permintaan pembayaran</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($requests->hasPages())
<div class="mt-6">
    {{ $requests->links() }}
</div>
@endif
@endsection