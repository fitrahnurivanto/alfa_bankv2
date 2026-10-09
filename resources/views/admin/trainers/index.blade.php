@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Kelola Team Trainer</h1>
            <p class="text-gray-800">Daftar trainer yang terdaftar di Alfa Bank</p>
        </div>
        <a href="{{ route('admin.trainers.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-[#fe0000] text-white font-semibold rounded-lg shadow-[0_3px_6px_rgba(0,0,0,0.25)] hover:bg-[#cc0000] transition">
            <i class="fas fa-plus"></i> Tambah Trainer
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-[#43bf21] text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-[#fe0000] text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Total Trainer -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 mb-6 flex items-center gap-5">
        <div class="w-16 h-16 shrink-0 rounded-lg bg-[#fed0d0] border border-[#fe0000] flex items-center justify-center">
            <i class="fas fa-chalkboard-user text-3xl text-[#fe0000]"></i>
        </div>
        <div>
            <p class="text-lg text-gray-900">Total Trainer Terdaftar</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ method_exists($trainers, 'total') ? $trainers->total() : $trainers->count() }}</p>
        </div>
    </div>

    <!-- Daftar Trainer -->
    <div class="bg-white rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)] p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Daftar Trainer</h2>

            <form action="{{ route('admin.trainers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, email, atau keahlian..."
                       class="w-full sm:w-72 px-4 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:border-[#fe0000]">
                <button type="submit" class="px-5 py-2 bg-[#fe0000] text-white text-sm font-semibold rounded-md shadow hover:bg-[#cc0000] transition">
                    <i class="fas fa-magnifying-glass mr-1"></i> Cari
                </button>
                @if(request('search') || request('status'))
                <a href="{{ route('admin.trainers.index') }}" class="px-4 py-2 text-sm text-center bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
                @endif
            </form>
        </div>

        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('admin.trainers.index', array_filter(['search' => request('search')])) }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium transition {{ !request('status') ? 'bg-[#fe0000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Semua</a>
            <a href="{{ route('admin.trainers.index', array_filter(['status' => 'active', 'search' => request('search')])) }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium transition {{ request('status') == 'active' ? 'bg-[#c8f7b4] text-[#2e8b12]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Active</a>
            <a href="{{ route('admin.trainers.index', array_filter(['status' => 'inactive', 'search' => request('search')])) }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium transition {{ request('status') == 'inactive' ? 'bg-[#fed0d0] text-[#fe0000]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Inactive</a>
        </div>

        @if($trainers->isEmpty())
        <div class="p-12 text-center">
            <i class="fas fa-chalkboard-user text-6xl text-[#fed0d0] mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Trainer</h3>
            <p class="text-gray-500 mb-4">Tambahkan trainer untuk mulai mengelola instruktur</p>
            <a href="{{ route('admin.trainers.create') }}" class="inline-flex items-center px-6 py-2 bg-[#fe0000] text-white rounded-lg hover:bg-[#cc0000] transition">
                <i class="fas fa-plus mr-2"></i> Tambah Trainer
            </a>
        </div>
        @else
        <div class="overflow-x-auto rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
            <table class="min-w-full">
                <thead class="border-b border-gray-300">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">No. Telepon</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-900 uppercase tracking-wider">Jumlah Kelas</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-900 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($trainers as $trainer)
                    <tr class="hover:bg-[#f9f0f1] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 shrink-0 rounded-lg bg-[#bfd7ff] border border-[#344bfd]/40 flex items-center justify-center text-[#344bfd] text-xl font-bold">
                                    {{ strtoupper(substr($trainer->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-base text-gray-900 flex items-center gap-2">
                                        {{ $trainer->name }}
                                        @if($trainer->status !== 'active')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#fed0d0] text-[#fe0000]">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-700">ID: {{ $trainer->id }}</div>
                                    @if($trainer->expertise)
                                    <div class="text-xs text-gray-500 truncate max-w-[220px]">{{ $trainer->expertise }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center gap-2">
                                <i class="far fa-envelope text-gray-800"></i>
                                <a href="mailto:{{ $trainer->email }}" class="hover:text-[#fe0000]">{{ $trainer->email }}</a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                            @if($trainer->phone)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-phone-volume text-gray-800"></i>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trainer->phone) }}" target="_blank" class="hover:text-[#43bf21]">{{ $trainer->phone }}</a>
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if(isset($trainer->classes_count))
                            <span class="inline-flex px-4 py-0.5 rounded-full text-sm bg-[#c8f7b4] text-[#2e5a1a]">{{ $trainer->classes_count }} Kelas</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-3 text-lg">
                                <a href="{{ route('admin.trainers.show', $trainer) }}" class="text-[#344bfd] hover:opacity-70" title="Detail">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.trainers.edit', $trainer) }}" class="text-[#e28100] hover:opacity-70" title="Edit">
                                    <i class="far fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.trainers.destroy', $trainer) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Yakin ingin menghapus trainer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[#fe0000] hover:opacity-70" title="Hapus">
                                        <i class="far fa-trash-can"></i>
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
    </div>

    <!-- Pagination -->
    @if($trainers->hasPages())
    <div class="mt-6">
        {{ $trainers->links() }}
    </div>
    @endif
</div>
@endsection