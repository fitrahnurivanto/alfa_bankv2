@extends('layouts.app')

@section('page-title', 'Detail Trainer')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.trainers.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Trainer Profile -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center mb-6">
                @if($trainer->photo_path)
                    <img src="{{ asset('storage/' . $trainer->photo_path) }}"
                         alt="Foto {{ $trainer->name }}"
                         class="w-24 h-24 object-cover rounded-full mx-auto mb-4 border-4 border-green-100">
                @else
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-green-600 text-4xl"></i>
                    </div>
                @endif
                <h3 class="text-xl font-bold text-gray-900">{{ $trainer->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">Trainer Alfa Bank</p>
                <p class="text-sm text-green-700 mt-2">{{ $trainer->specialization ?: 'Spesialisasi belum diisi' }}</p>
            </div>

            <div class="space-y-3 border-t border-gray-100 pt-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-envelope text-gray-400 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm text-gray-900 break-words">{{ $trainer->email }}</p>
                    </div>
                </div>

                @if($trainer->phone)
                <div class="flex items-start gap-3">
                    <i class="fas fa-phone text-gray-400 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">No. Telepon</p>
                        <p class="text-sm text-gray-900">{{ $trainer->phone }}</p>
                    </div>
                </div>
                @endif

                @if($trainer->address)
                <div class="flex items-start gap-3">
                    <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Alamat</p>
                        <p class="text-sm text-gray-900">{{ $trainer->address }}</p>
                    </div>
                </div>
                @endif

                <div class="flex items-start gap-3">
                    <i class="fas fa-calendar text-gray-400 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Terdaftar Sejak</p>
                        <p class="text-sm text-gray-900">{{ $trainer->created_at->format('d F Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <span class="inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold {{ $trainer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($trainer->status ?: 'active') }}
                    </span>
                </div>
                @if($trainer->bio)
                    <div>
                        <p class="text-xs text-gray-500">Bio</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $trainer->bio }}</p>
                    </div>
                @endif
                @if($trainer->cv_path)
                    <a href="{{ route('admin.trainers.cv', $trainer) }}"
                       class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-file-pdf"></i> Lihat / Download CV
                    </a>
                @else
                    <p class="text-sm text-gray-500">CV belum diupload.</p>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.trainers.edit', $trainer) }}" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-edit"></i> Edit Trainer
                </a>
            </div>
        </div>
    </div>

    <!-- Trainer Classes -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelas yang Diajar
                </h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    {{ $trainer->classes->count() }} Kelas
                </span>
            </div>

            @if($trainer->classes->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-chalkboard text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">Trainer belum mengajar kelas manapun</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($trainer->classes as $class)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">{{ $class->name }}</h4>
                                    @if($class->instansi)
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-building text-gray-400"></i> {{ $class->instansi }}
                                        </p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    @if($class->status === 'approved') bg-green-100 text-green-800
                                    @elseif($class->status === 'done') bg-gray-100 text-gray-800
                                    @elseif($class->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($class->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Kategori</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $class->kategori ? $class->kategori->name : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Peserta</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $class->amount }} orang
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Pertemuan</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $class->meet }}x ({{ $class->duration }} JPL/pertemuan)
                                    </p>
                                </div>
                                @if($class->trainer_honor)
                                <div>
                                    <p class="text-gray-500">Honor Trainer</p>
                                    <p class="font-medium text-green-600">
                                        Rp {{ number_format($class->trainer_honor, 0, ',', '.') }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            @if($class->start_date && $class->end_date)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    {{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif

                            <div class="mt-3">
                                <a href="{{ route('admin.classes.show', $class) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Lihat Detail Kelas <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
