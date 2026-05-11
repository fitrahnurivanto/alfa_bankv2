@props(['project', 'stats', 'deadline'])

<!-- Project Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Budget</p>
                <p class="text-2xl font-bold text-gray-900">{{ $project->presenter->formatCurrency($project->budget) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-wallet text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Actual Cost</p>
                <p class="text-2xl font-bold text-gray-900">{{ $project->presenter->formatCurrency($project->actual_cost) }}</p>
                <p class="text-xs {{ $project->presenter->getBudgetColorClass($stats['budget_used_percentage']) }} font-semibold">
                    {{ number_format($stats['budget_used_percentage'], 1) }}% used
                </p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-money-bill text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Deadline</p>
                @if($deadline)
                    @if($project->status === 'completed')
                        <p class="text-lg font-bold text-green-600">Selesai</p>
                        <p class="text-xs text-gray-600">{{ $deadline['formatted_date'] }}</p>
                    @elseif($deadline['is_overdue'])
                        <p class="text-lg font-bold text-red-600">Terlambat</p>
                        <p class="text-xs text-red-600">{{ abs($deadline['days_left']) }} hari lewat</p>
                    @elseif($deadline['is_today'])
                        <p class="text-lg font-bold text-orange-600">Hari Ini!</p>
                        <p class="text-xs text-gray-600">{{ $deadline['formatted_date'] }}</p>
                    @else
                        <p class="text-lg font-bold {{ $deadline['is_urgent'] ? 'text-orange-600' : 'text-blue-600' }}">{{ $deadline['days_left'] }} hari</p>
                        <p class="text-xs text-gray-600">{{ $deadline['formatted_date'] }}</p>
                    @endif
                @else
                    <p class="text-lg font-bold text-gray-400">-</p>
                    <p class="text-xs text-gray-500">Belum ditentukan</p>
                @endif
            </div>
            <div class="w-12 h-12 {{ !$deadline ? 'bg-gray-100' : ($deadline['is_overdue'] ? 'bg-red-100' : ($deadline['is_urgent'] ? 'bg-orange-100' : 'bg-purple-100')) }} rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt {{ !$deadline ? 'text-gray-400' : ($deadline['is_overdue'] ? 'text-red-600' : ($deadline['is_urgent'] ? 'text-orange-600' : 'text-purple-600')) }} text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Tasks</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</p>
                <p class="text-xs text-green-600">{{ $stats['completed_tasks'] }} completed</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tasks text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Profit Calculation Card -->
<div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-sm p-6 mb-6 border border-green-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <p class="text-sm text-gray-600 mb-1">Budget Project</p>
            <p class="text-xl font-bold text-gray-900">{{ $project->presenter->formatCurrency($project->budget) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600 mb-1">Total Expenses</p>
            <p class="text-xl font-bold text-orange-600">{{ $project->presenter->formatCurrency($stats['total_expenses']) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600 mb-1">Sisa Budget</p>
            @php
                $remaining = $project->budget - $stats['total_expenses'];
            @endphp
            <p class="text-xl font-bold {{ $remaining >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                {{ $project->presenter->formatCurrency($remaining) }}
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-600 mb-1">Profit/Omset</p>
            @php
                $profit = $project->budget - $stats['total_expenses'];
            @endphp
            <p class="text-2xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $project->presenter->formatCurrency($profit) }}
            </p>
            <p class="text-xs {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                <i class="fas {{ $profit >= 0 ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                {{ $profit >= 0 ? 'Profit' : 'Rugi' }}
            </p>
        </div>
    </div>
</div>
