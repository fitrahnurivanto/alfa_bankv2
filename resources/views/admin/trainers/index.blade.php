@extends('layouts.app')

@section('page-title', 'Kelola Trainer')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Team Trainer</h1>
        <p class="text-gray-600">Daftar trainer yang terdaftar di Alfa Bank</p>
    </div>
    <a href="{{ route('admin.trainers.create') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Tambah Trainer</span>
    </a>
</div>

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

<!-- Stats Card -->
<div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 mb-6 border border-green-200">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-green-700 text-sm font-medium">Total Trainer Terdaftar</p>
            <h3 class="text-3xl font-bold text-green-900 mt-1">{{ $trainers->total() }}</h3>
        </div>
        <div class="w-16 h-16 bg-green-200 rounded-full flex items-center justify-center">
            <i class="fas fa-chalkboard-teacher text-green-700 text-2xl"></i>
        </div>
    </div>
</div>

<!-- Trainers Table -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Telp</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Jumlah Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($trainers as $trainer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $trainer->name }}</p>
                                    <p class="text-xs text-gray-500">ID: {{ $trainer->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm text-gray-900">
                                <i class="fas fa-envelope text-gray-400"></i>
                                {{ $trainer->email }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($trainer->phone)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-phone text-gray-400"></i>
                                    {{ $trainer->phone }}
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                {{ $trainer->classes()->count() }} Kelas
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.trainers.show', $trainer) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.trainers.edit', $trainer) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.trainers.destroy', $trainer) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus trainer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-users text-5xl mb-4"></i>
                                <p class="text-lg font-medium">Belum ada trainer terdaftar</p>
                                <p class="text-sm mt-1">Klik tombol "Tambah Trainer" untuk menambahkan trainer baru</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($trainers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $trainers->links() }}
        </div>
    @endif
</div>
@endsection
