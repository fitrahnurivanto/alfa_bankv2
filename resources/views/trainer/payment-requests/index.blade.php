@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payment Request</h1>
            <p class="text-gray-600">Riwayat pengajuan honor mengajar</p>
        </div>
        <a href="{{ route('trainer.payment-requests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm font-medium">
            <i class="fas fa-plus"></i>
            Buat Payment Request
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Request</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Approved</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-600">Rejected</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Paid</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['paid'] }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('trainer.payment-requests.index') }}" method="GET" class="flex gap-3 flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama kelas..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="admin_approved" {{ request('status') == 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="admin_rejected" {{ request('status') == 'admin_rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('trainer.payment-requests.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-times mr-2"></i>Reset
            </a>
            @endif
        </form>
    </div>

    <!-- success message -->
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

    <!-- Payment Requests Table -->
    @if($paymentRequests->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada payment request</h3>
        <p class="text-gray-500 mb-4">Buat payment request pertama Anda untuk klaim honor mengajar.</p>
        <a href="{{ route('trainer.payment-requests.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-plus"></i>
            Buat Payment Request
        </a>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Kelas</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Payment</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tanggal</th>
                        <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($paymentRequests as $request)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ $request->class->name ?? 'Honor Training' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $request->class->kategori->nama_kategori ?? '-' }}
                                </p>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-semibold text-gray-900">
                                Rp {{ number_format($request->amount, 0, ',', '.') }}
                            </p>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 text-xs rounded-full font-semibold
                                {{ $request->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $request->status === 'admin_approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $request->status === 'finance_approved' ? 'bg-teal-100 text-teal-700' : '' }}
                                {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ in_array($request->status, ['admin_rejected', 'finance_rejected']) ? 'bg-red-100 text-red-700' : '' }}">
                                @if($request->status === 'paid')
                                    <i class="fas fa-check-circle mr-1"></i>Paid
                                @elseif($request->status === 'admin_approved')
                                    <i class="fas fa-clock mr-1"></i>Admin Approved
                                @elseif($request->status === 'finance_approved')
                                    Finance Approved
                                @elseif($request->status === 'admin_rejected')
                                    <i class="fas fa-times-circle mr-1"></i>Ditolak Admin
                                @elseif($request->status === 'finance_rejected')
                                    <i class="fas fa-times-circle mr-1"></i>Ditolak Finance
                                @else
                                    {{ ucfirst($request->status) }}
                                @endif
                            </span>
                            @if(in_array($request->status, ['admin_rejected', 'finance_rejected']))
                                <p class="text-xs text-gray-500 mt-1 italic">
                                    <i class="fas fa-info-circle"></i> Anda bisa mengajukan request baru untuk kelas ini
                                </p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($request->status === 'paid')
                                <span class="px-3 py-1 text-xs rounded-full font-semibold bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle mr-1"></i>Paid
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs rounded-full font-semibold bg-orange-100 text-orange-700">
                                    <i class="fas fa-clock mr-1"></i>Unpaid
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $request->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('trainer.payment-requests.show', $request) }}" class="text-green-600 hover:text-green-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $paymentRequests->links() }}
    </div>
    @endif
</div>
@endsection
