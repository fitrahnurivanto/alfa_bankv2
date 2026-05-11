@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Client</h1>
            <p class="text-gray-600">Kelola data client dan pelanggan</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.clients.index') }}" method="GET">
            <div class="flex gap-3 mb-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, email, atau company..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#7b2cbf] focus:border-[#7b2cbf]">
                </div>
                <button type="submit" class="px-6 py-2 bg-[#7b2cbf] text-white rounded-lg hover:bg-[#6a25a8] transition">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                @if(request('search') || request('status') || request('division'))
                <a href="{{ route('admin.clients.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
                @endif
            </div>
            
            <!-- Status & Division Filter -->
            <div class="flex justify-between items-center gap-2">
                <div class="flex gap-2">
                    <a href="{{ route('admin.clients.index') }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !request('status') ? 'bg-[#7b2cbf] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-list mr-1"></i> Semua
                    </a>
                    <a href="{{ route('admin.clients.index', ['status' => 'active']) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-circle mr-1"></i> Active
                    </a>
                    <a href="{{ route('admin.clients.index', ['status' => 'past']) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') == 'past' ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        <i class="fas fa-history mr-1"></i> Past Client
                    </a>
                </div>
                
                @if($user->isSuperAdmin())
                <!-- Division Filter for Super Admin -->
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600">Divisi:</label>
                    <select name="division" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('division') == 'all' || !request('division') ? 'selected' : '' }}>Semua</option>
                        <option value="agency" {{ request('division') == 'agency' ? 'selected' : '' }}>Agency</option>
                        <option value="training" {{ request('division') == 'training' ? 'selected' : '' }}>Pelatihan</option>
                    </select>
                </div>
                @endif
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

    <!-- Clients List -->
    @if($clients->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Client</h3>
        <p class="text-gray-500">Client akan otomatis dibuat saat admin membuat order baru</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($clients as $client)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow">
                                {{ strtoupper(substr($client->name ?: ($client->user->name ?? 'C'), 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $client->name ?: ($client->user->name ?? 'N/A') }}
                                </div>
                                @if($client->company_name)
                                <div class="text-xs text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-building text-xs"></i>
                                    {{ $client->company_name }}
                                </div>
                                @endif
                                
                                @php
                                    // Check for registration orders
                                    $registrations = $client->orders->where('order_type', 'registration');
                                @endphp
                                
                                @if($registrations->count() > 0)
                                    @foreach($registrations as $reg)
                                        @if($reg->registration_type === 'magang')
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-700">
                                                <i class="fas fa-user-graduate mr-1 text-xs"></i> Peserta Magang
                                            </span>
                                            @if($reg->institution_name)
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <i class="fas fa-school mr-1"></i>{{ $reg->institution_name }}
                                                @if($reg->participant_age)
                                                    | {{ $reg->participant_age }} tahun
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        @break
                                        @elseif($reg->registration_type === 'sertifikasi')
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-700">
                                                <i class="fas fa-certificate mr-1 text-xs"></i> Peserta Sertifikasi
                                            </span>
                                            @if($reg->institution_name)
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <i class="fas fa-school mr-1"></i>{{ $reg->institution_name }}
                                                @if($reg->participant_age)
                                                    | {{ $reg->participant_age }} tahun
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        @break
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 flex items-center gap-2 mb-1">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            {{ $client->email ?: ($client->user->email ?? '-') }}
                        </div>
                        @if($client->phone || $client->contact_phone)
                        <div class="text-sm text-gray-900 flex items-center gap-2">
                            <i class="fab fa-whatsapp text-green-500 text-xs"></i>
                            {{ $client->phone ?: $client->contact_phone }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            <i class="fas fa-shopping-cart mr-1"></i>
                            {{ $client->total_orders }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                            <i class="fas fa-briefcase mr-1"></i>
                            {{ $client->total_projects }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900">
                            Rp {{ number_format($client->total_revenue, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($client->has_active_project)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            Active
                        </span>
                        @elseif($client->total_orders > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            <i class="fas fa-history text-xs mr-1"></i>
                            Past Client
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock text-xs mr-1"></i>
                            New
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.clients.show', $client) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium"
                               title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.clients.edit', $client) }}" 
                               class="text-green-600 hover:text-green-800 font-medium"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @php
                                $clientPhone = $client->phone ?: $client->contact_phone;
                            @endphp
                            @if($clientPhone)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $clientPhone);
                                // Convert 08xxx to 628xxx for international format
                                if (substr($cleanPhone, 0, 1) === '0') {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}" 
                               target="_blank"
                               class="text-green-600 hover:text-green-800 font-medium transition"
                               title="Chat WhatsApp">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </a>
                            @endif
                            <form action="{{ route('admin.clients.destroy', $client) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus client ini?\n\n⚠️ PERHATIAN:\n- Client dengan project aktif (pending/in progress) tidak dapat dihapus\n- Client dengan project completed dapat dihapus\n\nApakah Anda yakin?');">
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
    @if($clients->hasPages())
    <div class="mt-6">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
