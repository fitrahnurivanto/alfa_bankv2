@extends('layouts.app')

@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola akun pengguna sistem (Admin, Marketing, Akademik, Finance, Trainer)</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#7b2cbf] to-[#9d4edd] text-white rounded-xl shadow-md hover:shadow-lg transition-all text-sm font-medium whitespace-nowrap">
            <i class="fas fa-user-plus"></i>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-5 flex items-center gap-2">
        <i class="fas fa-check-circle text-green-500"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-5 flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-red-500"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf] text-sm">
                <option value="">Semua Role</option>
                <option value="admin"     {{ request('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                <option value="marketing" {{ request('role') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                <option value="akademik"  {{ request('role') === 'akademik'  ? 'selected' : '' }}>Akademik</option>
                <option value="finance"   {{ request('role') === 'finance'   ? 'selected' : '' }}>Finance</option>
                <option value="trainer"   {{ request('role') === 'trainer'   ? 'selected' : '' }}>Trainer/Pengajar</option>
            </select>
            <button type="submit" class="px-5 py-2 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a24a6] text-sm transition">
                <i class="fas fa-search mr-1"></i>Cari
            </button>
            @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm transition">
                <i class="fas fa-times mr-1"></i>Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($users->isEmpty())
        <div class="text-center py-16">
            <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada pengguna ditemukan</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Dibuat</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-sm
                                    {{ $user->role === 'admin' ? 'bg-red-500' :
                                       ($user->role === 'marketing' ? 'bg-orange-500' :
                                       ($user->role === 'akademik' ? 'bg-indigo-500' :
                                       ($user->role === 'finance' ? 'bg-green-500' : 'bg-blue-500'))) }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ $user->name }}</p>
                                    @if($user->phone)
                                    <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            @php
                                $roleColors = [
                                    'admin'     => 'bg-red-100 text-red-700',
                                    'marketing' => 'bg-orange-100 text-orange-700',
                                    'akademik'  => 'bg-indigo-100 text-indigo-700',
                                    'finance'   => 'bg-green-100 text-green-700',
                                    'trainer'   => 'bg-blue-100 text-blue-700',
                                ];
                                $roleLabels = [
                                    'admin'     => 'Admin',
                                    'marketing' => 'Marketing',
                                    'akademik'  => 'Akademik',
                                    'finance'   => 'Finance',
                                    'trainer'   => 'Trainer',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 text-xs font-medium transition">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                @if($user->id !== \Illuminate\Support\Facades\Auth::id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-xs font-medium transition">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t">{{ $users->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
