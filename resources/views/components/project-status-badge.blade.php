@props(['status'])

@php
$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
];

$statusLabels = [
    'pending' => 'Pending',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];

$badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
$label = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
    {{ $label }}
</span>
