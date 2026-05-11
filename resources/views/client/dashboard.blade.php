@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div class="p-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang, {{ \Illuminate\Support\Facades\Auth::user()->name }}!</h2>
        <p class="text-gray-600 mt-1">Monitor project dan aktivitas Anda</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-3 text-green-600"></i>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Total Orders</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</h3>
                </div>
                <div class="bg-blue-100 p-4 rounded-lg">
                    <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Total Spent</p>
                    <h4 class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h4>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <i class="fas fa-wallet text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Active Projects</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['active_projects'] }}</h3>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <i class="fas fa-tasks text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Completed</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['completed_projects'] }}</h3>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-shopping-cart mr-2 text-blue-600"></i>Recent Orders
                </h5>
            </div>
            <div class="p-6">
                @if($orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($orders as $order)
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <div class="flex justify-between items-center mb-2">
                                    <strong class="text-gray-900">{{ $order->order_number }}</strong>
                                    @if($order->payment_status == 'paid')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Terkonfirmasi</span>
                                    @elseif($order->payment_status == 'pending_review')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending Review</span>
                                    @elseif($order->payment_status == 'rejected')
                                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Ditolak</span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">{{ ucfirst($order->payment_status) }}</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center">
                                    <small class="text-gray-500">{{ $order->created_at->format('d M Y') }}</small>
                                    <strong class="text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">Belum ada order</p>
                        <small class="text-gray-400">Hubungi admin untuk membuat order baru</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-project-diagram mr-2 text-indigo-600"></i>Recent Projects
                </h5>
            </div>
            <div class="p-6">
                @if($projects->count() > 0)
                    <div class="space-y-4">
                        @foreach($projects as $project)
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <div class="flex justify-between items-center mb-2">
                                    <strong class="text-gray-900">{{ $project->project_name }}</strong>
                                    @if($project->status == 'completed')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Completed</span>
                                    @elseif($project->status == 'in_progress')
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">In Progress</span>
                                    @elseif($project->status == 'pending')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending</span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">Pending</span>
                                    @endif
                                </div>
                                <small class="text-gray-500">{{ $project->project_code }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada project</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
