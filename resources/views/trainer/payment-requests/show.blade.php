@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('trainer.payment-requests.index') }}" class="w-10 h-10 rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-gray-900 hover:border-gray-300 flex items-center justify-center transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Payment Request</h1>
                <p class="text-gray-600">Payment Request #{{ $paymentRequest->id }}</p>
            </div>
        </div>

        <!-- Status Badge -->
        <div>
            @if($paymentRequest->status === 'pending')
                <span class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-clock mr-2"></i>Pending Approval
                </span>
            @elseif($paymentRequest->status === 'admin_approved')
                <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-check mr-2"></i>Admin Approved
                </span>
            @elseif($paymentRequest->status === 'finance_approved')
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-check-circle mr-2"></i>Finance Approved
                </span>
            @elseif($paymentRequest->status === 'paid')
                <span class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-check-double mr-2"></i>Paid
                </span>
            @elseif($paymentRequest->status === 'admin_rejected')
                <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-times-circle mr-2"></i>Rejected by Admin
                </span>
            @elseif($paymentRequest->status === 'finance_rejected')
                <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full font-semibold text-sm">
                    <i class="fas fa-times-circle mr-2"></i>Rejected by Finance
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Bukti Transfer Pembayaran -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                    <i class="fas fa-file-invoice-dollar text-emerald-600 mr-2"></i>Bukti Transfer Pembayaran
                </h2>

                @if($paymentRequest->status === 'paid' && $paymentRequest->bukti_transfer_url)
                    @php
                        $proofUrl = $paymentRequest->bukti_transfer_url;
                        $proofPath = parse_url((string) $proofUrl, PHP_URL_PATH) ?? '';
                        $proofExt = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION));
                        $isPdfProof = $proofExt === 'pdf';
                        $isImageProof = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    @endphp

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Tanggal Bayar</p>
                                <p class="font-semibold text-gray-800">{{ $paymentRequest->paid_at ? \Carbon\Carbon::parse($paymentRequest->paid_at)->format('d M Y H:i') : '-' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Nominal</p>
                                <p class="font-semibold text-emerald-700">Rp {{ number_format($paymentRequest->approved_amount ?? $paymentRequest->requested_amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Metode</p>
                                <p class="font-semibold text-gray-800">{{ $paymentRequest->payment_method ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            @if($isPdfProof)
                                <iframe src="{{ $proofUrl }}" class="w-full h-72" title="Preview Bukti Pembayaran"></iframe>
                            @elseif($isImageProof)
                                <img src="{{ $proofUrl }}" alt="Preview Bukti Pembayaran" class="w-full h-72 object-contain bg-white">
                            @else
                                <div class="p-4 text-sm text-gray-600">
                                    Preview tidak tersedia untuk tipe file ini. Silakan download file bukti transfer.
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                                <i class="fas fa-eye mr-2"></i>Lihat di Tab Baru
                            </a>
                            <a href="{{ route('trainer.payment-requests.download-proof', $paymentRequest) }}" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                                <i class="fas fa-download mr-2"></i>Download Bukti Pembayaran
                            </a>
                        </div>
                    </div>
                @elseif($paymentRequest->status === 'paid')
                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pembayaran sudah berstatus paid, tetapi bukti transfer belum tersedia.
                    </div>
                @else
                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                        <i class="fas fa-info-circle mr-1"></i>
                        Bukti transfer akan tampil setelah Finance menandai payment request ini sebagai paid.
                    </div>
                @endif
            </div>

            <!-- Payment Request Details -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                    <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>Informasi Payment Request
                </h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Jumlah Honor</span>
                        <span class="sm:col-span-2 text-2xl font-bold text-green-600">
                            Rp {{ number_format($paymentRequest->amount, 0, ',', '.') }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Tanggal Request</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">
                            {{ $paymentRequest->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Dibuat Oleh</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">
                            {{ $paymentRequest->user->name }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Request ID</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">
                            #{{ $paymentRequest->id }}
                        </span>
                    </div>
                    
                    @if($paymentRequest->description)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3">
                        <span class="text-sm font-medium text-gray-600">Catatan</span>
                        <div class="sm:col-span-2 bg-gray-50 p-4 rounded-lg text-gray-700">
                            {{ $paymentRequest->description }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Class Information -->
            @if($paymentRequest->class)
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                    <i class="fas fa-chalkboard-teacher text-purple-600 mr-2"></i>Informasi Kelas
                </h2>
                
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Nama Kelas</span>
                        <span class="sm:col-span-2 font-semibold text-gray-900">{{ $paymentRequest->class->name }}</span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Kategori</span>
                        <span class="w-fit px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                            {{ $paymentRequest->class->kategori->nama_kategori ?? 'Umum' }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Instansi</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">{{ $paymentRequest->class->instansi }}</span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Jumlah Peserta</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">{{ $paymentRequest->class->jml_peserta ?? 0 }} orang</span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Tanggal Pelaksanaan</span>
                        <span class="sm:col-span-2 font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($paymentRequest->class->tgl_pelaksanaan)->format('d M Y') }}
                            -
                            {{ \Carbon\Carbon::parse($paymentRequest->class->tgl_selesai)->format('d M Y') }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 py-3">
                        <span class="text-sm font-medium text-gray-600">Status Kelas</span>
                        @if($paymentRequest->class->done)
                            <span class="w-fit px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>Selesai
                            </span>
                        @elseif($paymentRequest->class->status === 'approved')
                            <span class="w-fit px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-play-circle mr-1"></i>Berjalan
                            </span>
                        @else
                            <span class="w-fit px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('trainer.classes.show', $paymentRequest->class->id) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat Detail Kelas
                    </a>
                </div>
            </div>
            @endif

            <!-- Approval History/Notes -->
            @if(in_array($paymentRequest->status, ['admin_rejected', 'finance_rejected']))
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-red-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    Alasan Penolakan
                </h3>
                @if($paymentRequest->status === 'admin_rejected' && $paymentRequest->admin_notes)
                    <p class="text-red-800">{{ $paymentRequest->admin_notes }}</p>
                    <p class="text-sm text-red-600 mt-2">
                        Ditolak oleh Admin pada: {{ $paymentRequest->updated_at->format('d M Y, H:i') }}
                    </p>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anda dapat mengajukan payment request lagi untuk kelas ini.
                        </p>
                    </div>
                @elseif($paymentRequest->status === 'finance_rejected' && $paymentRequest->finance_notes)
                    <p class="text-red-800">{{ $paymentRequest->finance_notes }}</p>
                    <p class="text-sm text-red-600 mt-2">
                        Ditolak oleh Finance pada: {{ $paymentRequest->updated_at->format('d M Y, H:i') }}
                    </p>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anda dapat mengajukan payment request lagi untuk kelas ini.
                        </p>
                    </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6 lg:sticky lg:top-6 h-fit">
            <!-- Payment Status -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <h3 class="font-bold text-gray-900 mb-4">
                    <i class="fas fa-money-check-alt text-green-600 mr-2"></i>Status Pembayaran
                </h3>
                
                <div class="text-center">
                    @if($paymentRequest->status === 'paid')
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                            <i class="fas fa-check-double text-3xl text-green-600"></i>
                        </div>
                        <p class="text-lg font-bold text-green-600">Sudah Dibayar</p>
                        @if($paymentRequest->paid_at)
                        <p class="text-sm text-gray-500 mt-1">
                            {{ \Carbon\Carbon::parse($paymentRequest->paid_at)->format('d M Y, H:i') }}
                        </p>
                        @endif
                        @if($paymentRequest->paidBy)
                        <p class="text-xs text-gray-500 mt-1">
                            Dibayar oleh: {{ $paymentRequest->paidBy->name }}
                        </p>
                        @endif
                    @elseif($paymentRequest->status === 'admin_approved')
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-3">
                            <i class="fas fa-clock text-3xl text-blue-600"></i>
                        </div>
                        <p class="text-lg font-bold text-blue-600">Menunggu Finance</p>
                        <p class="text-sm text-gray-500 mt-1">Sudah diapprove Admin</p>
                    @elseif($paymentRequest->status === 'pending')
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-3">
                            <i class="fas fa-hourglass-half text-3xl text-yellow-600"></i>
                        </div>
                        <p class="text-lg font-bold text-yellow-600">Pending</p>
                        <p class="text-sm text-gray-500 mt-1">Menunggu approval Admin</p>
                    @elseif($paymentRequest->status === 'rejected')
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-3">
                            <i class="fas fa-times-circle text-3xl text-red-600"></i>
                        </div>
                        <p class="text-lg font-bold text-red-600">Ditolak</p>
                        @if($paymentRequest->rejection_reason)
                        <p class="text-sm text-gray-500 mt-1">{{ $paymentRequest->rejection_reason }}</p>
                        @endif
                    @else
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-3">
                            <i class="fas fa-question text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-lg font-bold text-gray-600">{{ ucfirst($paymentRequest->status) }}</p>
                    @endif
                </div>
            </div>

            <!-- Approval Timeline -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <h3 class="font-bold text-gray-900 mb-4">
                    <i class="fas fa-tasks text-blue-600 mr-2"></i>Proses Approval
                </h3>
                
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <div class="space-y-6">
                        <!-- Step 1: Trainer Submit -->
                        <div class="relative pl-10">
                            <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="font-semibold text-gray-900">Trainer Submit</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $paymentRequest->created_at->format('d M Y, H:i') }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Request dibuat oleh {{ $paymentRequest->user->name }}
                                </p>
                            </div>
                        </div>

                        <!-- Step 2: Admin Approval -->
                        <div class="relative pl-10">
                            @if(in_array($paymentRequest->status, ['admin_approved', 'finance_approved', 'paid']))
                                <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="font-semibold text-green-900">Admin Approved</p>
                                    @if($paymentRequest->approved_by)
                                    <p class="text-xs text-green-600 mt-1">
                                        Disetujui oleh: {{ $paymentRequest->approvedBy->name ?? '-' }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->approved_at)
                                    <p class="text-xs text-green-600 mt-1">
                                        {{ \Carbon\Carbon::parse($paymentRequest->approved_at)->format('d M Y, H:i') }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->admin_notes)
                                    <p class="text-xs text-green-600 mt-1">
                                        Catatan: {{ $paymentRequest->admin_notes }}
                                    </p>
                                    @endif
                                </div>
                            @elseif($paymentRequest->status === 'admin_rejected')
                                <div class="absolute left-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-times text-white text-sm"></i>
                                </div>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="font-semibold text-red-900">Ditolak Admin</p>
                                    @if($paymentRequest->approved_by)
                                    <p class="text-xs text-red-600 mt-1">
                                        Ditolak oleh: {{ $paymentRequest->approvedBy->name ?? '-' }}
                                    </p>
                                    @endif
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ $paymentRequest->updated_at->format('d M Y, H:i') }}
                                    </p>
                                    @if($paymentRequest->admin_notes)
                                    <p class="text-xs text-red-600 mt-2">
                                        Alasan: {{ $paymentRequest->admin_notes }}
                                    </p>
                                    @endif
                                </div>
                            @else
                                <div class="absolute left-0 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center animate-pulse">
                                    <i class="fas fa-clock text-white text-sm"></i>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <p class="font-semibold text-yellow-900">Menunggu Approval</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Menunggu persetujuan Admin
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Step 3: Finance Approval -->
                        <div class="relative pl-10">
                            @if(in_array($paymentRequest->status, ['finance_approved', 'paid']))
                                <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="font-semibold text-green-900">Finance Approved</p>
                                    @if($paymentRequest->finance_approved_by)
                                    <p class="text-xs text-green-600 mt-1">
                                        Disetujui oleh: {{ $paymentRequest->financeApprovedBy->name ?? '-' }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->finance_approved_at)
                                    <p class="text-xs text-green-600 mt-1">
                                        {{ \Carbon\Carbon::parse($paymentRequest->finance_approved_at)->format('d M Y, H:i') }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->finance_notes)
                                    <p class="text-xs text-green-600 mt-1">
                                        Catatan: {{ $paymentRequest->finance_notes }}
                                    </p>
                                    @endif
                                </div>
                            @elseif($paymentRequest->status === 'finance_rejected')
                                <div class="absolute left-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-times text-white text-sm"></i>
                                </div>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="font-semibold text-red-900">Ditolak Finance</p>
                                    @if($paymentRequest->finance_approved_by)
                                    <p class="text-xs text-red-600 mt-1">
                                        Ditolak oleh: {{ $paymentRequest->financeApprovedBy->name ?? '-' }}
                                    </p>
                                    @endif
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ $paymentRequest->updated_at->format('d M Y, H:i') }}
                                    </p>
                                    @if($paymentRequest->finance_notes)
                                    <p class="text-xs text-red-600 mt-2">
                                        Alasan: {{ $paymentRequest->finance_notes }}
                                    </p>
                                    @endif
                                </div>
                            @elseif($paymentRequest->status === 'admin_approved')
                                <div class="absolute left-0 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center animate-pulse">
                                    <i class="fas fa-clock text-white text-sm"></i>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <p class="font-semibold text-yellow-900">Menunggu Finance</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Menunggu approval dari Finance
                                    </p>
                                </div>
                            @else
                                <div class="absolute left-0 w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-circle text-white text-sm"></i>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="font-semibold text-gray-600">Finance - Menunggu</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Akan diproses setelah Admin approved
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Step 4: Payment Process -->
                        <div class="relative pl-10">
                            @if($paymentRequest->status === 'paid')
                                <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="font-semibold text-green-900">Dibayar</p>
                                    @if($paymentRequest->paid_by)
                                    <p class="text-xs text-green-600 mt-1">
                                        Dibayar oleh: {{ $paymentRequest->paidBy->name ?? '-' }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->paid_at)
                                    <p class="text-xs text-green-600 mt-1">
                                        {{ \Carbon\Carbon::parse($paymentRequest->paid_at)->format('d M Y, H:i') }}
                                    </p>
                                    @endif
                                    @if($paymentRequest->payment_method)
                                    <p class="text-xs text-green-600 mt-1">
                                        Metode: {{ ucfirst($paymentRequest->payment_method) }}
                                    </p>
                                    @endif
                                </div>
                            @elseif($paymentRequest->status === 'finance_approved')
                                <div class="absolute left-0 w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center animate-pulse">
                                    <i class="fas fa-spinner text-white text-sm"></i>
                                </div>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="font-semibold text-blue-900">Proses Pembayaran</p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        Menunggu transfer dari Finance
                                    </p>
                                </div>
                            @else
                                <div class="absolute left-0 w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-circle text-white text-sm"></i>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="font-semibold text-gray-600">Finance - Menunggu</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Akan diproses setelah approved
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
@endsection
