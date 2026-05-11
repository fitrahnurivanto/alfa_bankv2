@extends('layouts.app')

@section('title', 'Kategori Pelatihan')

@section('page-title', 'Kategori Pelatihan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header with Add Button -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-layer-group mr-2 text-green-600"></i>Kategori Pelatihan
            </h2>
            <p class="text-sm text-gray-600 mt-1">Kelola kategori untuk pelatihan di Alfa Bank</p>
        </div>
        <a href="{{ route('admin.training-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
            <i class="fas fa-plus mr-2"></i>Tambah Kategori
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Categories Grid -->
    @if($categories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition overflow-hidden">
                    <!-- Category Header -->
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $category->name }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $category->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas fa-circle text-xs mr-1"></i>
                                    {{ $category->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Body -->
                    <div class="p-6">
                        @if($category->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $category->description }}</p>
                        @else
                            <p class="text-sm text-gray-400 mb-4 italic">Tidak ada deskripsi</p>
                        @endif

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-blue-50 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $category->trainings_count }}</div>
                                <div class="text-xs text-gray-600 mt-1">Total Pelatihan</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $category->active_trainings_count }}</div>
                                <div class="text-xs text-gray-600 mt-1">Kelas Aktif</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="{{ route('admin.training-categories.show', $category) }}" 
                               class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                            <a href="{{ route('admin.training-categories.edit', $category) }}" 
                               class="flex-1 text-center px-3 py-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.training-categories.destroy', $category) }}" 
                                  method="POST" 
                                  class="flex-1"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm font-medium"
                                        {{ $category->active_trainings_count > 0 ? 'disabled title="Tidak dapat menghapus kategori dengan kelas aktif"' : '' }}>
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Footer with timestamp -->
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span><i class="fas fa-clock mr-1"></i>{{ $category->created_at->format('d M Y') }}</span>
                            @if($category->updated_at != $category->created_at)
                                <span><i class="fas fa-edit mr-1"></i>{{ $category->updated_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-layer-group text-5xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Kategori Pelatihan</h3>
            <p class="text-gray-500 mb-6">Mulai dengan menambahkan kategori pelatihan pertama Anda</p>
            <a href="{{ route('admin.training-categories.create') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
                <i class="fas fa-plus mr-2"></i>Tambah Kategori Pertama
            </a>
        </div>
    @endif
</div>
@endsection
