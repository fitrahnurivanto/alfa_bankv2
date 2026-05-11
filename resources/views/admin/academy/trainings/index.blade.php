@extends('layouts.app')

@section('title', 'Pelatihan')

@section('page-title', 'Pelatihan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header with Add Button -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-graduation-cap mr-2 text-green-600"></i>Pelatihan
            </h2>
            <p class="text-sm text-gray-600 mt-1">Kelola pelatihan yang tersedia di Alfa Bank</p>
        </div>
        <a href="{{ route('admin.trainings.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
            <i class="fas fa-plus mr-2"></i>Tambah Pelatihan
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

    <!-- Filter & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.trainings.index') }}" class="flex flex-col md:flex-row gap-3">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari nama pelatihan..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            
            <!-- Type Filter -->
            <div class="w-full md:w-64">
                <select name="type" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Semua Tipe</option>
                    <option value="corporate" {{ request('type') == 'corporate' ? 'selected' : '' }}>Corporate Training</option>
                    <option value="reguler" {{ request('type') == 'reguler' ? 'selected' : '' }}>Regular Training</option>
                    <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>Private Training</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
                @if(request('search') || request('type'))
                    <a href="{{ route('admin.trainings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Trainings Table -->
    @if($trainings->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Pelatihan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Kelas Aktif</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($trainings as $training)
                            <tr class="hover:bg-green-50 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <a href="{{ route('admin.trainings.show', $training) }}" class="font-semibold text-gray-900 hover:text-green-600">
                                            {{ $training->name }}
                                        </a>
                                        @if($training->description)
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit($training->description, 80) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($training->type === 'corporate')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-building mr-1"></i>
                                            Corporate
                                        </span>
                                    @elseif($training->type === 'reguler')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-users mr-1"></i>
                                            Regular
                                        </span>
                                    @elseif($training->type === 'private')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-user mr-1"></i>
                                            Private
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $training->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        {{ $training->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="text-lg font-bold text-green-600">{{ $training->active_classes_count }}</span>
                                        <span class="text-sm text-gray-500 ml-1">kelas</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.trainings.show', $training) }}" 
                                           class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition text-sm"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.trainings.edit', $training) }}" 
                                           class="px-3 py-1.5 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-100 transition text-sm"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.trainings.destroy', $training) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-3 py-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition text-sm"
                                                    title="Hapus"
                                                    {{ $training->active_classes_count > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($trainings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $trainings->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-graduation-cap text-5xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                @if(request('search') || request('category'))
                    Tidak Ada Pelatihan Ditemukan
                @else
                    Belum Ada Pelatihan
                @endif
            </h3>
            <p class="text-gray-500 mb-6">
                @if(request('search') || request('category'))
                    Coba ubah filter atau kata kunci pencarian Anda
                @else
                    Mulai dengan menambahkan pelatihan pertama Anda
                @endif
            </p>
            @if(!request('search') && !request('category'))
                <a href="{{ route('admin.trainings.create') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah Pelatihan Pertama
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
