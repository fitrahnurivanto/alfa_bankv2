@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="max-w-7xl mx-auto">
        @php
            $activeGradeFile = $clas->activeGradeFile;
            $gradeFileStatusFilter = request('grade_file_status', 'all');
            $filteredGradeFiles = $clas->gradeFiles->when($gradeFileStatusFilter !== 'all', function ($files) use ($gradeFileStatusFilter) {
                return $files->where('status', $gradeFileStatusFilter);
            });
        @endphp
        <!-- Header -->
        <div class="mb-6 space-y-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <a href="{{ route('admin.classes.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $clas->name }}</h1>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <p class="text-gray-600">Detail kelas</p>
                            @if($clas->status === 'done')
                                <span class="px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Selesai</span>
                            @elseif($clas->status === 'approved')
                                <span class="px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Approved</span>
                            @elseif($clas->status === 'pending')
                                <span class="px-2.5 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 xl:justify-end">
                          @if($clas->status === 'pending' && \Illuminate\Support\Facades\Auth::user()->canApproveClass())
                        <!-- Approve Button -->
                        <form action="{{ route('admin.classes.approve', $clas) }}" 
                              method="POST" 
                              onsubmit="return confirm('Approve kelas ini?');">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg transition shadow-sm hover:shadow-md">
                                <i class="fas fa-check mr-2"></i>Approve
                            </button>
                        </form>
                        <!-- Reject Button -->
                        <button type="button"
                                onclick="showRejectModal({{ $clas->id }}, '{{ addslashes($clas->name) }}')"
                                class="px-4 py-2 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white rounded-lg transition shadow-sm hover:shadow-md">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                    @endif
                          @if($clas->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                        @if($activeGradeFile && $activeGradeFile->status === 'approved' && (float)($clas->price ?? 0) > 0)
                            <form action="{{ route('admin.classes.mark-as-done', $clas) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kelas ini?');">
                                @csrf
                                <button type="submit" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                    <i class="fas fa-check-circle mr-2"></i>Selesaikan Kelas
                                </button>
                            </form>
                        @elseif($activeGradeFile && $activeGradeFile->status === 'approved')
                            <button type="button" disabled
                                    class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed"
                                    title="Isi pendapatan kelas terlebih dahulu sebelum menyelesaikan kelas">
                                <i class="fas fa-lock mr-2"></i>Selesaikan Kelas
                            </button>
                        @else
                            <button type="button" disabled
                                    class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed"
                                    title="Trainer harus upload file nilai dan admin harus menyetujui file tersebut sebelum kelas diselesaikan">
                                <i class="fas fa-lock mr-2"></i>Selesaikan Kelas
                            </button>
                        @endif
                    @endif
                    @if(\Illuminate\Support\Facades\Auth::user()->canCreateEditClass())
                    <a href="{{ route('admin.classes.edit', $clas) }}" 
                       class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @endif
                    @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
                    <form action="{{ route('admin.classes.destroy', $clas) }}" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-trash mr-2"></i>Hapus
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Periode Kelas</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $clas->start_date ? $clas->start_date->format('d M Y') : '-' }} - {{ $clas->end_date ? $clas->end_date->format('d M Y') : '-' }}
                    </p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Total Peserta</p>
                    <p class="text-sm font-semibold text-gray-900">{{ number_format((int) ($clas->amount ?? 0), 0, ',', '.') }} orang</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Pertemuan</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $clas->meet ?? 0 }}x • {{ $clas->duration ?? 0 }} JPL</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500 mb-1">Pendapatan Kelas</p>
                    <p class="text-sm font-semibold text-emerald-700">Rp {{ number_format((float) ($clas->price ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="sticky top-4 z-20">
                <div class="bg-white/95 backdrop-blur border border-gray-200 rounded-xl p-3 shadow-sm">
                    <div class="flex flex-wrap gap-2">
                        <a href="#info-umum" class="px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">Informasi Umum</a>
                        <a href="#jadwal-kelas" class="px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">Jadwal</a>
                        <a href="#finansial-kelas" class="px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">Finansial</a>
                        <a href="#penutupan-kelas" class="px-3 py-1.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition">Penutupan</a>
                        <a href="#absensi-pengajar" class="px-3 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">Absensi</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column -->
            <div class="space-y-6 lg:col-span-8">
                <!-- Informasi Umum -->
                <div id="info-umum" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-circle-info text-sky-600"></i>Informasi Umum
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Kelas</p>
                            <p class="font-medium text-gray-900">{{ $clas->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kategori</p>
                            <p class="font-medium text-gray-900">{{ $clas->kategori->nama_kategori ?? '-' }}</p>
                        </div>
                        
                        @php
                            $kategoriName = strtolower($clas->kategori->nama_kategori ?? '');
                            $isCorporate = str_contains($kategoriName, 'corporate');
                            $isPrivate = str_contains($kategoriName, 'private');
                            $isRegular = str_contains($kategoriName, 'regular');
                            $usesTerminFlow = $isCorporate || $isPrivate;
                        @endphp
                        
                        @if($usesTerminFlow)
                            <!-- Field untuk Corporate Training & Private -->
                            @if($isCorporate)
                            <div>
                                <p class="text-sm text-gray-600">Instansi</p>
                                <p class="font-medium text-gray-900">{{ $clas->instansi ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Alamat</p>
                                <p class="font-medium text-gray-900">{{ $clas->alamat ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nama PIC</p>
                                <p class="font-medium text-gray-900">{{ $clas->no_pic ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">No. Kontak</p>
                                <p class="font-medium text-gray-900">{{ $clas->no_kontak ?? '-' }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-sm text-gray-600">Type Pembayaran</p>
                                <p class="font-medium text-gray-900">
                                    @if($clas->payment_type == 'termin_2x')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                            <i class="fas fa-file-invoice-dollar mr-1"></i> Termin 2x
                                        </span>
                                    @elseif($clas->payment_type == 'full')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                            <i class="fas fa-money-check-alt mr-1"></i> Full Payment
                                        </span>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>

                            @if($isPrivate)
                            <div>
                                <p class="text-sm text-gray-600">Nama Siswa</p>
                                <p class="font-medium text-gray-900">{{ $clas->private_student_name ?: '-' }}</p>
                            </div>
                            @endif
                            
                            @if($clas->payment_type == 'termin_2x')
                            <!-- Payment Status untuk Termin 2x -->
                            <div class="col-span-2 border-t border-gray-200 pt-4 mt-2">
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-wallet mr-1"></i> Status Pembayaran
                                    </p>
                                    @php
                                        // Price is now total contract/revenue for all categories
                                        $totalContract = $clas->price;
                                        $remainingSisa = max(0, $totalContract - ($clas->paid_amount ?? 0));
                                        $totalLabel = $isCorporate ? 'Total Kontrak' : 'Pendapatan/Nilai Kelas';
                                    @endphp
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">{{ $totalLabel }}</p>
                                            <p class="font-bold text-gray-900">Rp {{ number_format($totalContract, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">DP (Termin 1)</p>
                                            <p class="font-bold text-green-600">
                                                @if($clas->paid_amount)
                                                    Rp {{ number_format($clas->paid_amount, 0, ',', '.') }}
                                                @else
                                                    <span class="text-red-600">Belum dibayar</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Sisa (Termin 2)</p>
                                            <p class="font-bold {{ $remainingSisa > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                Rp {{ number_format($remainingSisa, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($remainingSisa > 0 && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                                    <button type="button" 
                                            onclick="openPaymentModal()"
                                            class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition">
                                        <i class="fas fa-money-bill-wave mr-2"></i>Input Pelunasan (Termin 2)
                                    </button>
                                    @elseif($remainingSisa > 0)
                                    <div class="text-center py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">
                                        <i class="fas fa-lock mr-2"></i>Pelunasan hanya dapat diinput Akademik/Admin
                                    </div>
                                    @else
                                    <div class="text-center py-2 bg-green-100 text-green-800 rounded-lg">
                                        <i class="fas fa-check-circle mr-2"></i>Pembayaran Sudah Lunas
                                    </div>
                                    @endif
                                    
                                    @if($clas->payment_notes)
                                    <div class="mt-3 pt-3 border-t border-blue-200">
                                        <p class="text-xs text-gray-600 mb-1">Catatan Pembayaran:</p>
                                        <p class="text-sm text-gray-700">{{ $clas->payment_notes }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Peserta</p>
                                <p class="font-medium text-gray-900">
                                    @if($isPrivate)
                                        <i class="fas fa-user text-gray-500 mr-1"></i>{{ $clas->private_student_name ?: '1 peserta (Private)' }}
                                    @else
                                        <i class="fas fa-users text-gray-500 mr-1"></i>{{ $clas->amount }} peserta
                                    @endif
                                </p>
                            </div>
                        @elseif($isRegular)
                            <!-- Field untuk Regular Class -->
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Peserta</p>
                                <p class="font-medium text-gray-900">
                                    <i class="fas fa-users text-gray-500 mr-1"></i>{{ $clas->amount }} peserta
                                </p>
                            </div>
                            @if($clas->jenis_reguler)
                            <div>
                                <p class="text-sm text-gray-600">Jenis</p>
                                <p class="font-medium text-gray-900">
                                    @if($clas->jenis_reguler === 'mandiri')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                            <i class="fas fa-user mr-1"></i> Mandiri
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">
                                            <i class="fas fa-list mr-1"></i> Lain-lain
                                        </span>
                                    @endif
                                </p>
                            </div>
                            @endif
                        @else
                            <!-- Default - tampilkan jumlah siswa -->
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Peserta</p>
                                <p class="font-medium text-gray-900">
                                    <i class="fas fa-users text-gray-500 mr-1"></i>{{ $clas->amount }} peserta
                                </p>
                            </div>
                        @endif
                        
                        <div>
                            <p class="text-sm text-gray-600">Metode</p>
                            <p class="font-medium text-gray-900">
                                @if($clas->method === 'online')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        <i class="fas fa-laptop mr-1"></i> Online
                                    </span>
                                @elseif($clas->method === 'offline')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        <i class="fas fa-building mr-1"></i> Offline
                                    </span>
                                @elseif($clas->method === 'mix')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        <i class="fas fa-exchange-alt mr-1"></i> Mix (Online & Offline)
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium text-gray-900">
                                @if($clas->status === 'done')
                                    <span class="px-3 py-1.5 inline-flex items-center bg-blue-100 text-blue-800 rounded-lg text-xs font-semibold">
                                        <i class="fas fa-check-double mr-1.5"></i> Selesai
                                    </span>
                                @elseif($clas->status === 'approved')
                                    <span class="px-3 py-1.5 inline-flex items-center bg-green-100 text-green-800 rounded-lg text-xs font-semibold">
                                        <i class="fas fa-check-circle mr-1.5"></i> Approved
                                    </span>
                                @elseif($clas->status === 'pending')
                                    <span class="px-3 py-1.5 inline-flex items-center bg-yellow-100 text-yellow-800 rounded-lg text-xs font-semibold">
                                        <i class="fas fa-clock mr-1.5"></i> Pending
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 inline-flex items-center bg-red-100 text-red-800 rounded-lg text-xs font-semibold">
                                        <i class="fas fa-times-circle mr-1.5"></i> Rejected
                                    </span>
                                @endif
                            </p>
                            @if($clas->status === 'rejected' && $clas->rejection_reason)
                                <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-xs text-red-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <span class="font-semibold">Alasan:</span> {{ $clas->rejection_reason }}
                                    </p>
                                </div>
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <p class="text-xs text-gray-600 italic text-center">
                                        <i class="fas fa-lock mr-1"></i>
                                        Kelas yang ditolak tidak dapat di-approve kembali. Silakan buat kelas baru.
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600 mb-2">Sertifikasi BNSP</p>
                            @if($clas->sertifikasi_bnsp)
                                <div class="space-y-3">
                                    <p class="font-medium text-gray-900">
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                            <i class="fas fa-certificate mr-1"></i> Ya
                                        </span>
                                    </p>
                                    
                                    <!-- Detail BNSP -->
                                    <div class="pl-4 border-l-4 border-purple-200 space-y-2">
                                        @if($clas->bnsp_tanggal_sertifikasi)
                                        <div class="flex items-start">
                                            <span class="text-sm text-gray-600 w-32">Tanggal:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $clas->bnsp_tanggal_sertifikasi->format('d F Y') }}</span>
                                        </div>
                                        @endif
                                        
                                        <div class="flex items-start">
                                            <span class="text-sm text-gray-600 w-32">Jumlah Siswa:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($clas->bnsp_student_count ?? 0, 0, ',', '.') }} siswa</span>
                                        </div>

                                        <div class="flex items-start">
                                            <span class="text-sm text-gray-600 w-32">Biaya / Siswa:</span>
                                            <span class="text-sm font-medium text-gray-900">Rp {{ number_format($clas->bnsp_fee_per_student ?? 0, 0, ',', '.') }}</span>
                                        </div>

                                        <div class="flex items-start">
                                            <span class="text-sm text-gray-600 w-32">Omset Sertifikasi:</span>
                                            <span class="text-sm font-semibold text-emerald-700">Rp {{ number_format(($clas->bnsp_student_count ?? 0) * ($clas->bnsp_fee_per_student ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="font-medium text-gray-900">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                        <i class="fas fa-times mr-1"></i> Tidak
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    @if($clas->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Deskripsi</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $clas->description }}</p>
                    </div>
                    @endif
                </div>

                <!-- Expenses -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">    
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-receipt text-orange-600 mr-2"></i>Biaya Tambahan / Expenses
                        </h2>
                        @if(in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin', 'superadmin', 'marketing', 'akademik']))
                        <button type="button" 
                                onclick="document.getElementById('addExpenseModal').classList.remove('hidden')"
                                class="px-4 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition shadow-md">
                            <i class="fas fa-plus mr-2"></i>Tambah Expense
                        </button>
                        @endif
                    </div>

                    @if($clas->expenses()->count() > 0)
                    <div class="space-y-3">
                        @foreach($clas->expenses()->with('user', 'approvedBy')->latest()->get() as $expense)
                        <div class="flex justify-between items-start pb-3 border-b hover:bg-orange-50 transition px-2 py-2 rounded">
                        <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $expense->description }}</p>
                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span><i class="fas fa-calendar text-orange-600"></i> {{ $expense->expense_date->format('d M Y') }}</span>
                                @if($expense->category)
                                <span class="px-2 py-0.5 rounded {{ in_array($expense->category, ['honor', 'trainer_honor']) ? 'bg-purple-100 text-purple-700' : ($expense->category === 'operational_cost' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                    <i class="fas fa-tag mr-1"></i>{{ $expense->category_label }}
                                </span>
                                @endif
                                <span><i class="fas fa-user text-orange-600"></i> {{ $expense->user?->name ?? 'Unknown' }}</span>
                                
                                <!-- Approval Status Badge -->
                                @if($expense->approval_status === 'approved')
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-semibold">
                                        <i class="fas fa-check-circle mr-1"></i>Approved
                                    </span>
                                @elseif($expense->approval_status === 'rejected')
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-semibold">
                                        <i class="fas fa-times-circle mr-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-semibold">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Rejection Reason -->
                            @if($expense->approval_status === 'rejected' && $expense->rejection_reason)
                            <p class="text-xs text-red-600 mt-2 italic">
                                <i class="fas fa-info-circle mr-1"></i>Ditolak: {{ $expense->rejection_reason }}
                            </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                                <span class="font-bold text-orange-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                            @php
                                $isHonorExpense = in_array($expense->category, ['trainer_honor', 'honor']);
                                $canEditDeleteExpense = \Illuminate\Support\Facades\Auth::user()->isAdmin()
                                    || (\Illuminate\Support\Facades\Auth::user()->role === 'marketing' && !$isHonorExpense)
                                    || (\Illuminate\Support\Facades\Auth::user()->role === 'akademik' && $isHonorExpense);
                                
                                // Non-honor dapat edit/delete kapan saja, honor hanya saat pending
                                $canPerformAction = $canEditDeleteExpense && (!$isHonorExpense || $expense->approval_status === 'pending');
                            @endphp
                            @if($canPerformAction)
                            <a href="{{ route('admin.class-expenses.edit', $expense) }}" class="text-blue-600 hover:text-blue-800 transition">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <form action="{{ route('admin.class-expenses.destroy', $expense) }}" method="POST" 
                                  onsubmit="return confirm('Yakin ingin menghapus expense ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-gray-300 bg-orange-50 px-3 py-2 rounded-lg">
                            <span class="text-sm font-bold text-gray-900">TOTAL EXPENSES (Approved)</span>
                            <span class="font-bold text-orange-600 text-lg">Rp {{ number_format($clas->expenses()->where('approval_status', 'approved')->sum('amount'), 0, ',', '.') }}</span>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-200 space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                                <span class="text-gray-600">Income Budget</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($clas->income, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Actual Income (Profit)</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($clas->income - $clas->expenses()->where('approval_status', 'approved')->sum('amount'), 0, ',', '.') }}</span>
                        </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-receipt text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 mb-4">Belum ada expense tercatat</p>
                        @if(in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin', 'superadmin', 'marketing', 'akademik']))
                        <button type="button" 
                                onclick="document.getElementById('addExpenseModal').classList.remove('hidden')"
                                class="text-orange-600 hover:text-orange-800 font-semibold">
                            <i class="fas fa-plus mr-1"></i>Tambah Expense Pertama
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6 lg:col-span-4">
                <!-- Jadwal -->
            <div id="jadwal-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-days text-violet-600"></i>Jadwal
                </h2>
                   <div class="mb-4 flex justify-end">
                       @if(\Illuminate\Support\Facades\Auth::user()->canCreateEditClass())
                       <button
                           type="button"
                           id="editScheduleBtn"
                           data-class-id="{{ $clas->id }}"
                           data-start-date="{{ $clas->start_date->format('Y-m-d') }}"
                           data-end-date="{{ $clas->end_date->format('Y-m-d') }}"
                           data-start-time="{{ optional($clas->start_time)->format('H:i') ?? '' }}"
                           data-end-time="{{ optional($clas->end_time)->format('H:i') ?? '' }}"
                           data-meet="{{ $clas->meet }}"
                           data-duration="{{ $clas->duration }}"
                           class="relative z-20 pointer-events-auto px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition shadow-sm hover:shadow-md">
                           <i class="fas fa-edit mr-2"></i>Edit Jadwal
                       </button>
                       @endif
                   </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Mulai</p>
                        <p class="font-medium text-gray-900">{{ $clas->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Selesai</p>
                        <p class="font-medium text-gray-900">{{ $clas->end_date->format('d M Y') }}</p>
                    </div>
                    @if($clas->start_time)
                    <div>
                        <p class="text-sm text-gray-600">Jam Mulai</p>
                        <p class="font-medium text-gray-900">{{ $clas->start_time->format('H:i') }}</p>
                    </div>
                    @endif
                    @if($clas->end_time)
                    <div>
                        <p class="text-sm text-gray-600">Jam Selesai</p>
                        <p class="font-medium text-gray-900">{{ $clas->end_time->format('H:i') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-600">Jumlah Pertemuan</p>
                        <p class="font-medium text-gray-900">{{ $clas->meet }}x</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">JPL per Pertemuan</p>
                        <p class="font-medium text-gray-900">{{ $clas->duration }} JPL</p>
                    </div>
                </div>

                <!-- Progress Status -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">
                        <i class="fas fa-tasks mr-2 text-purple-600"></i>Progress Kelas
                    </h3>
                    
                    @php
                        // Hitung progress berdasarkan status
                        $progress = 0;
                        $progressText = 'Belum Dimulai';
                        $progressColor = 'bg-gray-400';
                        $iconClass = 'fa-clock';
                        $statusBadgeColor = 'bg-gray-100 text-gray-800';
                        
                        if($clas->status === 'pending') {
                            $progress = 0;
                            $progressText = 'Menunggu Approval';
                            $progressColor = 'bg-gray-400';
                            $iconClass = 'fa-clock';
                            $statusBadgeColor = 'bg-yellow-100 text-yellow-800';
                        } elseif($clas->status === 'approved') {
                            $progress = 50;
                            $progressText = 'Sedang Berjalan';
                            $progressColor = 'bg-gradient-to-r from-yellow-400 to-amber-500';
                            $iconClass = 'fa-spinner fa-pulse';
                            $statusBadgeColor = 'bg-green-100 text-green-800';
                        } elseif($clas->status === 'done') {
                            $progress = 100;
                            $progressText = 'Selesai';
                            $progressColor = 'bg-gradient-to-r from-green-400 to-emerald-500';
                            $iconClass = 'fa-check-circle';
                            $statusBadgeColor = 'bg-blue-100 text-blue-800';
                        } elseif($clas->status === 'rejected') {
                            $progress = 0;
                            $progressText = 'Ditolak';
                            $progressColor = 'bg-red-400';
                            $iconClass = 'fa-times-circle';
                            $statusBadgeColor = 'bg-red-100 text-red-800';
                        }
                    @endphp
                    
                    <div class="space-y-3">
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="px-3 py-1.5 {{ $statusBadgeColor }} rounded-full text-xs font-semibold">
                                <i class="fas {{ $iconClass }} mr-1"></i>{{ $progressText }}
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-semibold text-gray-700">Progress</span>
                                <span class="font-bold text-lg {{ $progress >= 100 ? 'text-green-600' : ($progress >= 50 ? 'text-amber-600' : 'text-gray-500') }}">
                                    {{ $progress }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finansial -->
            <div id="finansial-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 h-full">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-sack-dollar mr-2 text-green-600"></i>Finansial</h2>
                @if($clas->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                <form action="{{ route('admin.classes.update-revenue', $clas) }}" method="POST" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    @csrf
                    @method('PATCH')
                    <label for="price_display" class="block text-sm font-medium text-blue-900 mb-2">Pendapatan Kelas</label>
                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" name="price_display" id="price_display" value="{{ old('price', $clas->price) ? number_format(old('price', $clas->price), 0, ',', '.') : '' }}" class="w-full pl-10 pr-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Isi pendapatan kelas">
                            <input type="hidden" name="price" id="price" value="{{ old('price', $clas->price) }}">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-1"></i>Simpan
                        </button>
                    </div>
                    <p class="text-xs text-blue-700 mt-2">Isi nilai pendapatan saat kelas akan diselesaikan.</p>
                </form>
                @endif
                <div class="space-y-3">
                    @if($isCorporate)
                        <!-- Corporate: Harga kontrak total -->
                        <div class="flex justify-between items-center pb-3 border-b">
                            <span class="text-sm text-gray-600">Harga Kontrak (Total)</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($clas->price, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <!-- Reguler/Private: Pendapatan kelas -->
                        <div class="flex justify-between items-center pb-3 border-b bg-blue-50 px-3 py-2 rounded-lg">
                            <span class="text-sm font-medium text-blue-900">Pendapatan Kelas</span>
                            <span class="font-semibold text-blue-600">Rp {{ number_format($clas->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b">
                            <span class="text-sm text-gray-600">Jumlah Siswa</span>
                            <span class="font-medium text-gray-900">{{ $clas->amount }} orang</span>
                        </div>
                    @endif
                    
                    @php
                        // Hitung biaya dari expenses yang sudah APPROVED saja
                        $approvedOperationalCost = $clas->expenses()
                            ->where('approval_status', 'approved')
                            ->where(function($q) {
                                $q->where('category', 'operational_cost')
                                  ->orWhere('description', 'Biaya Operasional (auto-generated)');
                            })
                            ->sum('amount');
                        
                        $approvedTrainerHonor = $clas->expenses()
                            ->where('approval_status', 'approved')
                            ->where(function($q) {
                                $q->where('category', 'trainer_honor')
                                  ->orWhere('category', 'honor')
                                  ->orWhere('description', 'Honor Trainer (auto-generated)');
                            })
                            ->sum('amount');
                        
                        // Hitung revenue
                        // Price is now total revenue for all categories (includes all students with discounts)
                        $totalRevenue = $clas->price;
                        
                        // Income Bersih = Revenue - Biaya Operasional (Approved) - Honor Trainer (Approved)
                        $incomeBersih = $totalRevenue - $approvedOperationalCost - $approvedTrainerHonor;
                        
                        // Total expenses lain (selain operational_cost dan trainer_honor) yang approved
                        $otherApprovedExpenses = $clas->expenses()
                            ->where('approval_status', 'approved')
                            ->whereNotIn('category', ['operational_cost', 'trainer_honor', 'honor'])
                            ->where('description', 'not like', '%(auto-generated)%')
                            ->sum('amount');
                        
                        // Profit Akhir = Income Bersih - Other Expenses
                        $finalProfit = $incomeBersih - $otherApprovedExpenses;
                        
                        // Info pending
                        $pendingOperationalCost = $clas->expenses()
                            ->where('approval_status', 'pending')
                            ->where(function($q) {
                                $q->where('category', 'operational_cost')
                                  ->orWhere('description', 'Biaya Operasional (auto-generated)');
                            })
                            ->sum('amount');
                        
                        $pendingTrainerHonor = $clas->expenses()
                            ->where('approval_status', 'pending')
                            ->where(function($q) {
                                $q->where('category', 'trainer_honor')
                                  ->orWhere('category', 'honor')
                                  ->orWhere('description', 'Honor Trainer (auto-generated)');
                            })
                            ->sum('amount');
                    @endphp
                    
                    <div class="flex justify-between items-center pb-3 border-b">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">Biaya Operasional</span>
                            @if($pendingOperationalCost > 0)
                                <span class="text-xs text-yellow-600 mt-1">
                                    <i class="fas fa-clock"></i> Pending: Rp {{ number_format($pendingOperationalCost, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        @if($approvedOperationalCost > 0)
                            <span class="font-medium text-red-600">- Rp {{ number_format($approvedOperationalCost, 0, ',', '.') }}</span>
                        @else
                            <span class="font-medium text-gray-400">Rp 0</span>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center pb-3 border-b">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-600">Honor Per Trainer</span>
                            @if($pendingTrainerHonor > 0)
                                <span class="text-xs text-yellow-600 mt-1">
                                    <i class="fas fa-clock"></i> Pending: Rp {{ number_format($pendingTrainerHonor, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        @if($approvedTrainerHonor > 0)
                            <span class="font-medium text-red-600">- Rp {{ number_format($approvedTrainerHonor, 0, ',', '.') }}</span>
                        @else
                            <span class="font-medium text-gray-400">Rp 0</span>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center pt-2 bg-gradient-to-r from-green-50 to-emerald-50 px-3 py-3 rounded-lg">
                        <span class="text-sm font-bold text-gray-900">Income Bersih (Setelah Biaya Approved)</span>
                        <span class="font-bold text-green-600 text-lg">Rp {{ number_format($incomeBersih, 0, ',', '.') }}</span>
                    </div>
                    
                    @if($otherApprovedExpenses > 0)
                    <div class="flex justify-between items-center pb-3 border-b">
                        <span class="text-sm text-gray-600">Expenses Lainnya (Approved)</span>
                        <span class="font-medium text-red-600">- Rp {{ number_format($otherApprovedExpenses, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center pt-2 px-3 py-3 rounded-lg {{ $finalProfit >= 0 ? 'bg-gradient-to-r from-emerald-50 to-green-50' : 'bg-gradient-to-r from-red-50 to-orange-50' }}">
                        <span class="text-sm font-bold text-gray-900">Profit Akhir</span>
                        <span class="font-bold {{ $finalProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }} text-lg">Rp {{ number_format($finalProfit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Team Trainer -->
            <div id="team-trainer" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 h-full">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chalkboard-teacher text-green-600 mr-2"></i>Team Trainer
                    </h2>
                    @if($clas->trainers->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-user-slash text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">Belum ada trainer yang ditugaskan</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($clas->trainers as $trainer)
                            <div class="flex items-start gap-3 p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-200 hover:shadow-md transition">
                                <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-green-700 text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 truncate">{{ $trainer->name }}</h4>
                                    <p class="text-sm text-gray-600 truncate">{{ $trainer->email }}</p>
                                    @if($trainer->phone)
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-phone"></i> {{ $trainer->phone }}
                                        </p>
                                    @endif
                                    <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Total: {{ $clas->trainers->count() }} trainer</strong> ditugaskan untuk kelas ini
                            </p>
                        </div>
                        
                        <!-- Honor Payment Status -->
                        @if(($clas->status === 'done' || $clas->status === 'approved') && in_array(auth()->user()->role, ['admin', 'akademik', 'marketing', 'finance']))
                        <div class="mt-4 p-4 rounded-lg border-2 border-green-200 bg-white">
                            <p class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-credit-card text-green-600"></i>Status Pembayaran Honor
                            </p>
                            @php
                                $honorStatus = $clas->getHonorStatus();
                                $statusLabel = [
                                    'pending' => 'Belum Diajukan',
                                    'submitted' => 'Pengajuan',
                                    'paid' => 'Sudah Dibayar'
                                ][$honorStatus] ?? 'Unknown';
                                
                                $statusColor = [
                                    'pending' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-circle'],
                                    'submitted' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-hourglass-half'],
                                    'paid' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle']
                                ][$honorStatus] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question-circle'];
                            @endphp
                            <div class="flex items-center justify-center p-3 rounded-lg {{ $statusColor['bg'] }}">
                                <div class="text-center">
                                    <i class="fas {{ $statusColor['icon'] }} {{ $statusColor['text'] }} text-2xl mb-2"></i>
                                    <p class="text-sm font-semibold {{ $statusColor['text'] }}">{{ $statusLabel }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>


        </div>

            </div>

        <!-- Penutupan Kelas -->
        <div id="penutupan-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-flag-checkered text-emerald-600 mr-2"></i>Penutupan Kelas
                </h2>
                <p class="text-xs text-gray-500">Lengkapi rekap kelulusan dan validasi file nilai sebelum kelas diselesaikan.</p>
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-graduate text-emerald-600 mr-2"></i>Rekap Kelulusan Siswa
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <p class="text-xs text-slate-500">Jumlah Siswa</p>
                        <p class="text-xl font-bold text-slate-700">{{ number_format((int) ($clas->amount ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200">
                        <p class="text-xs text-emerald-600">Lulus</p>
                        <p class="text-xl font-bold text-emerald-700">{{ number_format((int) ($clas->passed_students ?? 0), 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-rose-50 border border-rose-200">
                        <p class="text-xs text-rose-600">Tidak Lulus</p>
                        <p class="text-xl font-bold text-rose-700">{{ number_format((int) ($clas->failed_students ?? 0), 0, ',', '.') }}</p>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-3">Setelah nilai disetujui, isi tabel jumlah peserta lulus dan tidak lulus di sini.</p>

                @if(in_array($clas->status, ['approved', 'done']))
                    <form action="{{ route('admin.classes.update-graduation-summary', $clas) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Siswa</label>
                                <input type="number" min="0" name="total_students" value="{{ old('total_students', $clas->amount ?? 0) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                @error('total_students')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lulus</label>
                                <input type="number" min="0" name="passed_students" value="{{ old('passed_students', $clas->passed_students ?? 0) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                @error('passed_students')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tidak Lulus</label>
                                <input type="number" min="0" name="failed_students" value="{{ old('failed_students', $clas->failed_students ?? 0) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                @error('failed_students')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <p class="text-xs text-gray-500">Catatan: lulus + tidak lulus harus sama dengan jumlah siswa.</p>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm">
                                <i class="fas fa-save mr-1"></i>Simpan Rekap
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                        Rekap kelulusan hanya bisa diinput saat kelas berstatus approved atau done.
                    </div>
                @endif

                <div class="mt-3 text-xs text-gray-500">
                    Update terakhir: {{ $clas->pass_fail_updated_at ? $clas->pass_fail_updated_at->format('d M Y H:i') : '-' }}
                    @if($clas->passFailUpdatedBy)
                        oleh {{ $clas->passFailUpdatedBy->name }}
                    @endif
                </div>
            </div>

            <!-- File Nilai Siswa -->
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-signature text-indigo-600 mr-2"></i>File Nilai Siswa
                </h3>

                @if(!$activeGradeFile)
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Trainer belum mengupload file nilai. Kelas belum bisa diselesaikan sebelum file nilai tersedia dan disetujui.
                    </div>
                @else
                    <div class="p-4 rounded-lg border mb-4
                        {{ $activeGradeFile->status === 'approved' ? 'bg-green-50 border-green-200' : '' }}
                        {{ $activeGradeFile->status === 'pending_review' ? 'bg-yellow-50 border-yellow-200' : '' }}
                        {{ $activeGradeFile->status === 'rejected' ? 'bg-red-50 border-red-200' : '' }}">
                        <p class="font-semibold text-gray-900">{{ $activeGradeFile->file_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Status: {{ $activeGradeFile->status === 'approved' ? 'Disetujui' : ($activeGradeFile->status === 'pending_review' ? 'Menunggu Review' : 'Ditolak') }}
                        </p>
                        @if($activeGradeFile->uploaded_at)
                        <p class="text-xs text-gray-600">Diupload: {{ $activeGradeFile->uploaded_at->format('d M Y H:i') }}</p>
                        @endif
                        @if($activeGradeFile->review_notes)
                        <p class="text-xs mt-2 {{ $activeGradeFile->status === 'rejected' ? 'text-red-700' : 'text-gray-700' }}">
                            Catatan Review: {{ $activeGradeFile->review_notes }}
                        </p>
                        @endif
                    </div>

                    <div class="flex gap-2 mb-4">
                        <a href="{{ route('admin.classes.download-grade-file', ['clas' => $clas->id, 'gradeFile' => $activeGradeFile->id]) }}"
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                            <i class="fas fa-download mr-1"></i>Download File Nilai
                        </a>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                            <p class="text-sm font-semibold text-gray-800">Daftar File Nilai</p>
                            <form method="GET" action="{{ route('admin.classes.show', $clas) }}" class="flex items-center gap-2">
                                <label for="grade_file_status" class="text-xs text-gray-500">Filter status</label>
                                <select id="grade_file_status" name="grade_file_status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded-lg px-2 py-1 bg-white">
                                    <option value="all" {{ $gradeFileStatusFilter === 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="pending_review" {{ $gradeFileStatusFilter === 'pending_review' ? 'selected' : '' }}>Menunggu Review</option>
                                    <option value="approved" {{ $gradeFileStatusFilter === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="rejected" {{ $gradeFileStatusFilter === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </form>
                        </div>
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                            @forelse($filteredGradeFiles as $gradeFile)
                            <div class="border rounded-lg p-3 {{ $loop->first ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 bg-white' }}">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $gradeFile->file_name }}
                                            @if($loop->first)
                                                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700">Terbaru</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ $gradeFile->uploaded_at ? $gradeFile->uploaded_at->format('d M Y H:i') : '-' }} | Status: {{ $gradeFile->status === 'approved' ? 'Disetujui' : ($gradeFile->status === 'pending_review' ? 'Menunggu Review' : 'Ditolak') }}
                                        </p>
                                        @if($gradeFile->review_notes)
                                        <p class="text-xs mt-1 {{ $gradeFile->status === 'rejected' ? 'text-red-700' : 'text-gray-700' }}">Catatan: {{ $gradeFile->review_notes }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.classes.download-grade-file', ['clas' => $clas->id, 'gradeFile' => $gradeFile->id]) }}" class="inline-flex items-center justify-center px-3 py-2 bg-gray-800 text-white rounded text-xs hover:bg-black transition sm:w-auto w-full">
                                        Download
                                    </a>
                                </div>

                                @if($clas->status === 'approved' && $gradeFile->status !== 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                                <form action="{{ route('admin.classes.review-grade-file', ['clas' => $clas->id, 'gradeFile' => $gradeFile->id]) }}" method="POST" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                                    @csrf
                                    <textarea name="review_notes" rows="2" class="md:col-span-1 w-full border border-gray-300 rounded-lg px-2 py-2 text-xs" placeholder="Catatan (wajib jika tolak)"></textarea>
                                    <button type="submit" name="action" value="approve" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs">
                                        <i class="fas fa-check mr-1"></i>Setujui
                                    </button>
                                    <button type="submit" name="action" value="reject" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-xs">
                                        <i class="fas fa-times mr-1"></i>Tolak
                                    </button>
                                </form>
                                @endif
                            </div>
                            @empty
                            <div class="p-3 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500">
                                Tidak ada file nilai untuk filter status ini.
                            </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>

        <div id="absensi-pengajar" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user-check text-green-600 mr-2"></i>Absensi Pengajar (Read Only)
                </h2>
                <span class="text-xs text-gray-500">Real-time server time, tidak bisa input manual jam/tanggal</span>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-700">
                            <th class="text-left py-2">Tanggal</th>
                            <th class="text-left py-2">Trainer</th>
                            <th class="text-left py-2">Jam Berangkat</th>
                            <th class="text-left py-2">Lokasi Berangkat</th>
                            <th class="text-left py-2">Akurasi Berangkat (m)</th>
                            <th class="text-left py-2">Jam Pulang</th>
                            <th class="text-left py-2">Lokasi Pulang</th>
                            <th class="text-left py-2">Akurasi Pulang (m)</th>
                            <th class="text-left py-2">Siswa Hadir</th>
                            <th class="text-left py-2">Materi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clas->trainerAttendances->take(30) as $attendance)
                            <tr class="border-b border-gray-100 align-top">
                                <td class="py-2">{{ $attendance->attendance_date ? $attendance->attendance_date->format('d M Y') : '-' }}</td>
                                <td class="py-2">{{ $attendance->trainer->name ?? '-' }}</td>
                                <td class="py-2">{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : '-' }}</td>
                                <td class="py-2 text-xs">
                                    @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                        <a class="text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $attendance->check_in_latitude }},{{ $attendance->check_in_longitude }}">Lihat Maps</a>
                                        <div class="text-gray-500 mt-1">{{ $attendance->check_in_latitude }}, {{ $attendance->check_in_longitude }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2">{{ $attendance->check_in_accuracy !== null ? number_format((float)$attendance->check_in_accuracy, 0, ',', '.') : '-' }}</td>
                                <td class="py-2">{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : '-' }}</td>
                                <td class="py-2 text-xs">
                                    @if($attendance->check_out_latitude && $attendance->check_out_longitude)
                                        <a class="text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $attendance->check_out_latitude }},{{ $attendance->check_out_longitude }}">Lihat Maps</a>
                                        <div class="text-gray-500 mt-1">{{ $attendance->check_out_latitude }}, {{ $attendance->check_out_longitude }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2">{{ $attendance->check_out_accuracy !== null ? number_format((float)$attendance->check_out_accuracy, 0, ',', '.') : '-' }}</td>
                                <td class="py-2">{{ $attendance->students_present ?? '-' }}</td>
                                <td class="py-2 max-w-sm">{{ $attendance->material_covered ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-4 text-center text-gray-500">Belum ada data absensi pengajar untuk kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




<!-- Expenses Modal -->
@include('admin.classes.partials.expenses', ['class' => $clas])

<!-- Payment Modal for Pelunasan (Termin 2) -->
@php
    // Calculate total payment based on category
    // Price is now total payment for all categories
    $totalPayment = $clas->price;
    $remainingPayment = max(0, $totalPayment - ($clas->paid_amount ?? 0));
@endphp
@if($clas->payment_type == 'termin_2x' && $remainingPayment > 0 && \Illuminate\Support\Facades\Auth::user()->canManageClass())
<div id="paymentModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>Input Pelunasan (Termin 2)
                </h3>
                <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Info Pembayaran -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-600">Total Pembayaran</p>
                        <p class="font-bold text-blue-600">Rp {{ number_format($totalPayment, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">DP (Sudah Dibayar)</p>
                        <p class="font-bold text-green-600">Rp {{ number_format($clas->paid_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-span-2 pt-2 border-t border-blue-200">
                        <p class="text-gray-600">Sisa yang Harus Dibayar</p>
                        <p class="font-bold text-red-600 text-lg">Rp {{ number_format($remainingPayment, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.classes.update-payment', $clas) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="space-y-4">
                    <!-- Jumlah Pelunasan -->
                    <div>
                        <label for="settlement_amount_display" class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Pelunasan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" 
                                   name="settlement_amount_display" 
                                   id="settlement_amount_display" 
                                   value="{{ number_format($remainingPayment, 0, ',', '.') }}"
                                   placeholder="0" 
                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <input type="hidden" name="settlement_amount" id="settlement_amount" value="{{ $remainingPayment }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Masukkan jumlah yang akan dibayarkan (Termin 2)</p>
                    </div>

                    <!-- Tanggal Pelunasan -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Pelunasan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="payment_date" 
                               id="payment_date" 
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>

                    <!-- Catatan Pembayaran -->
                    <div>
                        <label for="payment_note" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Pembayaran
                        </label>
                        <textarea name="payment_note" 
                                  id="payment_note" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Contoh: Transfer via BCA tanggal ...">{{ $clas->payment_notes ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 mt-6">
                    <button type="button" 
                            onclick="closePaymentModal()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Simpan Pelunasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif

    <!-- Edit Schedule Modal -->
    <div id="editScheduleModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-calendar-edit text-blue-600"></i>Edit Jadwal Kelas
                    </h3>
                    <button type="button" onclick="closeEditScheduleModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="editScheduleForm" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" id="start_date" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Format: YYYY-MM-DD</p>
                        </div>
                        <!-- Tanggal Selesai -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" id="end_date" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Harus >= Tanggal Mulai</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Jam Mulai -->
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1.5">Jam Mulai</label>
                            <input type="time" name="start_time" id="start_time" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Format: HH:MM (24 jam)</p>
                        </div>
                        <!-- Jam Selesai -->
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1.5">Jam Selesai</label>
                            <input type="time" name="end_time" id="end_time" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Harus > Jam Mulai (jika diisi)</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Jumlah Pertemuan -->
                        <div>
                            <label for="meet" class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Pertemuan <span class="text-red-500">*</span></label>
                            <input type="number" name="meet" id="meet" required min="1" max="999" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Minimal 1 pertemuan</p>
                        </div>
                        <!-- JPL per Pertemuan -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-1.5">JPL per Pertemuan <span class="text-red-500">*</span></label>
                            <input type="number" name="duration" id="duration" required min="1" max="999" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <p class="text-xs text-gray-500 mt-1">Jam Pelajaran Lomba per pertemuan</p>
                        </div>
                    </div>
                </div>
                <!-- Buttons -->
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="button" 
                            onclick="closeEditScheduleModal()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
function openPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Format Rupiah
function formatRupiah(angka, prefix = '') {
    if (!angka) return '';
    
    let number_string = angka.replace(/[^,\d]/g, '').toString();
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
}

function unformatRupiah(rupiah) {
    return rupiah.replace(/\./g, '').replace(/,/g, '');
}

// Format settlement amount
document.addEventListener('DOMContentLoaded', function() {
    const settlementDisplay = document.getElementById('settlement_amount_display');
    const settlementHidden = document.getElementById('settlement_amount');
    
    if (settlementDisplay && settlementHidden) {
        settlementDisplay.addEventListener('keyup', function(e) {
            let value = this.value;
            this.value = formatRupiah(value);
            settlementHidden.value = unformatRupiah(value);
        });
    }
});

// Close modal on outside click
document.getElementById('paymentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});
</script>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Tolak Kelas
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Info Kelas -->
            <div class="mb-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-info-circle mr-1 text-yellow-600"></i>
                    Anda akan menolak kelas: <span id="rejectClassName" class="font-bold"></span>
                </p>
            </div>

            <!-- Form -->
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" 
                              id="rejection_reason" 
                              rows="4" 
                              required
                              placeholder="Jelaskan alasan penolakan kelas ini..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white rounded-lg transition shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i>Tolak Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentClassId = null;

function showRejectModal(classId, className) {
    currentClassId = classId;
    document.getElementById('rejectClassName').textContent = className;
    document.getElementById('rejectForm').action = `/admin/classes/${classId}/reject`;
    document.getElementById('rejection_reason').value = '';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    currentClassId = null;
}

// Close modal on outside click
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('rejectModal').classList.contains('hidden')) {
        closeRejectModal();
    }
});

// Form validation
document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    const reason = document.getElementById('rejection_reason').value.trim();
    if (reason.length < 10) {
        e.preventDefault();
        alert('Alasan penolakan minimal 10 karakter!');
        return false;
    }
});

function openEditScheduleModal(classId, startDate, endDate, startTime, endTime, meet, duration) {
    // Set form action
    document.getElementById('editScheduleForm').action = `/admin/classes/${classId}/update-schedule`;
    
    // Populate form fields
    document.getElementById('start_date').value = startDate;
    document.getElementById('end_date').value = endDate;
    document.getElementById('start_time').value = startTime;
    document.getElementById('end_time').value = endTime;
    document.getElementById('meet').value = meet;
    document.getElementById('duration').value = duration;
    
    // Show modal
    document.getElementById('editScheduleModal').classList.remove('hidden');
}

function closeEditScheduleModal() {
    document.getElementById('editScheduleModal').classList.add('hidden');
    document.getElementById('editScheduleModal').style.display = '';
}

const editScheduleButton = document.getElementById('editScheduleBtn');
if (editScheduleButton) {
    editScheduleButton.addEventListener('click', function() {
        openEditScheduleModal(
            this.dataset.classId,
            this.dataset.startDate,
            this.dataset.endDate,
            this.dataset.startTime,
            this.dataset.endTime,
            this.dataset.meet,
            this.dataset.duration
        );
        syncScheduleDateLimits();
    });
}

const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');

function syncScheduleDateLimits() {
    if (!startDateInput || !endDateInput) {
        return;
    }

    // Enforce start_date <= end_date while still allowing same day.
    if (endDateInput.value) {
        startDateInput.max = endDateInput.value;
    } else {
        startDateInput.removeAttribute('max');
    }

    if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
    } else {
        endDateInput.removeAttribute('min');
    }

    if (startDateInput.value && endDateInput.value && startDateInput.value > endDateInput.value) {
        startDateInput.setCustomValidity('Tanggal Mulai tidak boleh melebihi Tanggal Selesai.');
    } else {
        startDateInput.setCustomValidity('');
    }
}

startDateInput?.addEventListener('change', syncScheduleDateLimits);
endDateInput?.addEventListener('change', syncScheduleDateLimits);
syncScheduleDateLimits();

// Close modal on outside click
document.getElementById('editScheduleModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditScheduleModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('editScheduleModal').classList.contains('hidden')) {
        closeEditScheduleModal();
    }
});

// Form validation
document.getElementById('editScheduleForm')?.addEventListener('submit', function(e) {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const meet = parseInt(document.getElementById('meet').value);
    const duration = parseInt(document.getElementById('duration').value);
    
    // Validation: start_date <= end_date (equal date is allowed)
    if (endDate < startDate) {
        e.preventDefault();
        alert('Tanggal Mulai tidak boleh melebihi Tanggal Selesai!');
        return false;
    }
    
    // Validation: if both times are provided, end_time > start_time
    if (startTime && endTime && endTime <= startTime) {
        e.preventDefault();
        alert('Jam Selesai harus lebih besar dari Jam Mulai!');
        return false;
    }
    
    // Validation: meet and duration should be positive
    if (meet < 1) {
        e.preventDefault();
        alert('Jumlah Pertemuan minimal 1!');
        return false;
    }
    
    if (duration < 1) {
        e.preventDefault();
        alert('JPL per Pertemuan minimal 1!');
        return false;
    }
});
</script>
@endsection
