@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Trainer</h1>
            <p class="text-gray-600">Kelola data trainer dan instruktur</p>
        </div>
        <a href="{{ route('admin.trainer.create') }}" class="px-6 py-2 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition shadow-sm">
            <i class="fas fa-plus mr-2"></i> Tambah Trainer
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.trainer.index') }}" method="GET">
            <div class="flex gap-3 mb-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, email, atau keahlian..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]">
                </div>
                <button type="submit" class="px-6 py-2 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                @if(request('search') || request('status'))
                <a href="{{ route('admin.trainer.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
                @endif
            </div>
            
            <!-- Status Filter -->
            <div class="flex gap-2">
                <a href="{{ route('admin.trainer.index') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request('status') ? 'bg-[#7b2cbf] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-list mr-1"></i> Semua
                </a>
                <a href="{{ route('admin.trainer.index', ['status' => 'active']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-circle mr-1"></i> Active
                </a>
                <a href="{{ route('admin.trainer.index', ['status' => 'inactive']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') == 'inactive' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-times-circle mr-1"></i> Inactive
                </a>
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Trainers List -->
    @if($trainers->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-chalkboard-teacher text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Trainer</h3>
        <p class="text-gray-500 mb-4">Tambahkan trainer untuk mulai mengelola instruktur</p>
        <a href="{{ route('admin.trainer.create') }}" class="inline-flex items-center px-6 py-2 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition">
            <i class="fas fa-plus mr-2"></i> Tambah Trainer
        </a>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keahlian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($trainers as $trainer)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-lg font-bold shadow">
                                {{ strtoupper(substr($trainer->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $trainer->name }}
                                </div>
                                @if($trainer->bio)
                                <div class="text-xs text-gray-500 mt-1 max-w-md truncate">
                                    {{ Str::limit($trainer->bio, 60) }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 flex items-center gap-2 mb-1">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            <a href="mailto:{{ $trainer->email }}" class="hover:text-[#7b2cbf]">{{ $trainer->email }}</a>
                        </div>
                        @if($trainer->phone)
                        <div class="text-sm text-gray-900 flex items-center gap-2">
                            <i class="fab fa-whatsapp text-green-500 text-xs"></i>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trainer->phone) }}" 
                               target="_blank" 
                               class="hover:text-green-600">
                                {{ $trainer->phone }}
                            </a>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                            <i class="fas fa-code mr-1"></i>
                            {{ $trainer->expertise }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($trainer->status === 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle text-xs mr-1"></i>
                            Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.trainer.show', $trainer) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium"
                               title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.trainer.edit', $trainer) }}" 
                               class="text-yellow-600 hover:text-yellow-800 font-medium"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.trainer.destroy', $trainer) }}" 
                                  method="POST" 
                                  class="inline-block"
                                  onsubmit="return confirm('Yakin ingin menghapus trainer ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 font-medium"
                                        title="Hapus">
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
    @endif

    <!-- Pagination -->
    @if($trainers->hasPages())
    <div class="mt-6">
        {{ $trainers->links() }}
    </div>
    @endif
</div>
@endsection