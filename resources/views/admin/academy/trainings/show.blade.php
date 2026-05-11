@extends('layouts.app')

@section('title', 'Detail Pelatihan: ' . $training->name)

@section('page-title', 'Detail Pelatihan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.trainings.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Pelatihan
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Training Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">{{ $training->name }}</h2>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $training->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ $training->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @if($training->type === 'corporate')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-building mr-1"></i>
                                Corporate Training
                            </span>
                        @elseif($training->type === 'reguler')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-users mr-1"></i>
                                Regular Training
                            </span>
                        @elseif($training->type === 'private')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-user mr-1"></i>
                                Private Training
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi:</h3>
                        @if($training->description)
                            <p class="text-sm text-gray-600">{{ $training->description }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada deskripsi</p>
                        @endif
                    </div>

                    <!-- Type -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Tipe Pelatihan:</h3>
                        @if($training->type === 'corporate')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-building mr-2"></i>
                                Corporate Training
                            </span>
                        @elseif($training->type === 'reguler')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-users mr-2"></i>
                                Regular Training
                            </span>
                        @elseif($training->type === 'private')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-user mr-2"></i>
                                Private Training
                            </span>
                        @else
                            <p class="text-sm text-gray-400">Tidak ada tipe</p>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistik:</h3>
                        <div class="space-y-3">
                            <div class="bg-green-50 rounded-lg p-3 flex justify-between items-center">
                                <span class="text-sm text-gray-700">Total Kelas</span>
                                <span class="text-xl font-bold text-green-600">{{ $training->classes->count() }}</span>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 flex justify-between items-center">
                                <span class="text-sm text-gray-700">Kelas Aktif</span>
                                <span class="text-xl font-bold text-blue-600">
                                    {{ $training->classes->whereIn('status', ['pending', 'approved'])->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="text-xs text-gray-500 space-y-1 border-t border-gray-200 pt-4">
                        <div class="flex justify-between">
                            <span>Dibuat:</span>
                            <span>{{ $training->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Terakhir diubah:</span>
                            <span>{{ $training->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 mt-6">
                        <a href="{{ route('admin.trainings.edit', $training) }}" 
                           class="flex-1 text-center px-4 py-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition font-medium text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.trainings.destroy', $training) }}" 
                              method="POST" 
                              class="flex-1"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition font-medium text-sm"
                                    {{ $training->classes->whereIn('status', ['pending', 'approved'])->count() > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Classes List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-chalkboard-teacher mr-2 text-green-600"></i>Daftar Kelas ({{ $training->classes->count() }})
                    </h3>
                </div>

                <!-- Classes List -->
                <div class="p-6">
                    @if($training->classes->count() > 0)
                        <div class="space-y-3">
                            @foreach($training->classes as $class)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800 mb-1">{{ $class->name }}</h4>
                                            @if($class->instansi)
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <i class="fas fa-building mr-1"></i>{{ $class->instansi }}
                                                </p>
                                            @endif
                                            <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                                                <span><i class="fas fa-users mr-1"></i>{{ $class->amount }} peserta</span>
                                                @if($class->start_date && $class->end_date)
                                                    <span><i class="fas fa-calendar mr-1"></i>{{ $class->start_date->format('d M Y') }} - {{ $class->end_date->format('d M Y') }}</span>
                                                @endif
                                                @if($class->method)
                                                    <span>
                                                        <i class="fas {{ $class->method === 'online' ? 'fa-laptop' : 'fa-building' }} mr-1"></i>
                                                        {{ ucfirst($class->method) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-gray-100 text-gray-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                ];
                                                $statusColor = $statusColors[$class->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ ucfirst($class->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('admin.classes.show', $class) }}" 
                                           class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                            <i class="fas fa-eye mr-1"></i>Detail Kelas
                                        </a>
                                        @if($class->status !== 'completed' && $class->status !== 'cancelled')
                                            <a href="{{ route('admin.classes.edit', $class) }}" 
                                               class="text-xs px-3 py-1.5 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-100 transition">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-chalkboard-teacher text-4xl text-gray-400"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Kelas</h4>
                            <p class="text-sm text-gray-500 mb-4">Pelatihan ini belum digunakan dalam kelas manapun</p>
                            <a href="{{ route('admin.classes.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                <i class="fas fa-plus mr-2"></i>Buat Kelas dengan Pelatihan Ini
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
