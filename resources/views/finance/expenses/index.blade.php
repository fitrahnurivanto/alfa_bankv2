@extends('layouts.app')

@section('page-title', 'Expense Management')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Expense Management</h1>
                <p class="text-gray-600 mt-1">Kelola dan approve expense dari kelas training</p>
            </div>
            <a href="{{ route('finance.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('finance.expenses.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari deskripsi..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            
            <select name="class" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="">Semua Kelas</option>
                @foreach($classes as $clas)
                    <option value="{{ $clas->id }}" {{ request('class') == $clas->id ? 'selected' : '' }}>
                        {{ $clas->name }}
                    </option>
                @endforeach
            </select>

            <select name="period" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
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

            <select name="year" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                @foreach(($years ?? collect([date('Y')])) as $yearItem)
                    <option value="{{ $yearItem }}" {{ (int)($year ?? date('Y')) === (int)$yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
                @endforeach
            </select>
            
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            @if(request('search') || request('class') || ($period ?? ('month_' . date('m'))) !== ('month_' . date('m')) || (int)($year ?? date('Y')) !== (int)date('Y'))
            <a href="{{ route('finance.expenses.index', ['status' => request('status', 'pending')]) }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-times mr-2"></i>Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Expenses List -->
    @if($expenses->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">Belum ada expense</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat Oleh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Mulai Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($expenses as $expense)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $expense->clas?->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $expense->clas?->kategori?->nama_kategori ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ $expense->user?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-500">{{ $expense->expense_date->format('d M Y') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->description)
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $expense->description }}</p>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->clas && $expense->clas->trainers->isNotEmpty())
                            <p class="text-sm text-gray-900">{{ $expense->clas->trainers->pluck('name')->join(', ') }}</p>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->clas?->start_date)
                            <p class="text-sm text-gray-900">{{ $expense->clas->start_date->format('d M Y') }}</p>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->category)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ in_array($expense->category, ['honor', 'trainer_honor']) ? 'bg-purple-100 text-purple-700' : ($expense->category === 'operational_cost' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $expense->category_label }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ $expense->expense_date->format('d M Y') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->approval_status === 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Approved
                            </span>
                            @if($expense->approvedBy)
                                <p class="text-xs text-gray-500 mt-1">oleh {{ $expense->approvedBy?->name }}</p>
                            @endif
                        @elseif($expense->approval_status === 'rejected')
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                <i class="fas fa-times-circle mr-1"></i>Rejected
                            </span>
                            @if($expense->rejection_reason)
                                <p class="text-xs text-red-600 mt-1">{{ $expense->rejection_reason }}</p>
                            @endif
                        @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($expense->approval_status === 'pending')
                            <div class="flex gap-2">
                                <!-- Approve Button -->
                                <form action="{{ route('finance.class-expenses.approve', $expense) }}" 
                                      method="POST" onsubmit="return confirm('Approve expense ini?');">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                </form>
                                
                                <!-- Reject Button -->
                                <button onclick="openRejectModal({{ $expense->id }})" 
                                        class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </div>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($expenses->hasPages())
    <div class="mt-6">
        {{ $expenses->links() }}
    </div>
    @endif
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 flex justify-between items-center rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Reject Expense</h3>
            <button onclick="closeRejectModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Alasan Reject <span class="text-red-500">*</span>
                </label>
                <textarea name="rejection_reason" required rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                          placeholder="Jelaskan alasan reject expense ini..."></textarea>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <button type="button" onclick="closeRejectModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(expenseId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    
    form.action = `/finance/class-expenses/${expenseId}/reject`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.reset();
    modal.classList.add('hidden');
}
</script>
@endsection
