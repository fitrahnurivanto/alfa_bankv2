@extends('layouts.app')

@section('title', 'Detail Kategori: ' . $trainingCategory->name)

@section('page-title', 'Detail Kategori Pelatihan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.training-categories.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Kategori
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Category Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">{{ $trainingCategory->name }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2
                        {{ $trainingCategory->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas fa-circle text-xs mr-1"></i>
                        {{ $trainingCategory->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi:</h3>
                        @if($trainingCategory->description)
                            <p class="text-sm text-gray-600">{{ $trainingCategory->description }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">Tidak ada deskripsi</p>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistik:</h3>
                        <div class="space-y-3">
                            <div class="bg-blue-50 rounded-lg p-3 flex justify-between items-center">
                                <span class="text-sm text-gray-700">Total Pelatihan</span>
                                <span class="text-xl font-bold text-blue-600">{{ $trainingCategory->trainings->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="text-xs text-gray-500 space-y-1 border-t border-gray-200 pt-4">
                        <div class="flex justify-between">
                            <span>Dibuat:</span>
                            <span>{{ $trainingCategory->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Terakhir diubah:</span>
                            <span>{{ $trainingCategory->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 mt-6">
                        <a href="{{ route('admin.training-categories.edit', $trainingCategory) }}" 
                           class="flex-1 text-center px-4 py-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition font-medium text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.training-categories.destroy', $trainingCategory) }}" 
                              method="POST" 
                              class="flex-1"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition font-medium text-sm">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Trainings List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-graduation-cap mr-2 text-green-600"></i>Daftar Pelatihan ({{ $trainingCategory->trainings->count() }})
                    </h3>
                </div>

                <!-- Trainings List -->
                <div class="p-6">
                    @if($trainingCategory->trainings->count() > 0)
                        <div class="space-y-3">
                            @foreach($trainingCategory->trainings as $training)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800 mb-1">{{ $training->name }}</h4>
                                            @if($training->description)
                                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $training->description }}</p>
                                            @endif
                                            <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                                <span><i class="fas fa-clock mr-1"></i>{{ $training->duration ?? '-' }} menit</span>
                                                <span><i class="fas fa-calendar mr-1"></i>{{ $training->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $training->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $training->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('admin.trainings.show', $training) }}" 
                                           class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                        <a href="{{ route('admin.trainings.edit', $training) }}" 
                                           class="text-xs px-3 py-1.5 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-100 transition">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-graduation-cap text-4xl text-gray-400"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Pelatihan</h4>
                            <p class="text-sm text-gray-500 mb-4">Kategori ini belum memiliki pelatihan yang terdaftar</p>
                            <a href="{{ route('admin.trainings.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                <i class="fas fa-plus mr-2"></i>Tambah Pelatihan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
