@extends('layouts.app')

@section('page-title', 'Detail Payment Request - Finance')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-0">
<div class="mb-6">
    <a href="{{ route('finance.payment-requests.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start mb-6">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold text-gray-900">Detail Payment Request #{{ $paymentRequest->id }}</h1>
        <p class="text-gray-600">Employee: {{ $paymentRequest->user->name }}</p>
    </div>
    
    <!-- Status Badge -->
    <div class="shrink-0">
        @if($paymentRequest->status === 'admin_approved')
            <span class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                <i class="fas fa-clock mr-1"></i> Menunggu Validasi Finance
            </span>
        @elseif($paymentRequest->status === 'finance_approved')
            <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                <i class="fas fa-check-double mr-1"></i> Finance Approved
            </span>
        @elseif($paymentRequest->status === 'paid')
            <span class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-800 text-sm font-semibold rounded-full">
                <i class="fas fa-check-circle mr-1"></i> Sudah Dibayar
            </span>
        @elseif($paymentRequest->status === 'finance_rejected')
            <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 text-sm font-semibold rounded-full">
                <i class="fas fa-times-circle mr-1"></i> Ditolak Finance
            </span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Payment Request Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                Informasi Payment Request
            </h3>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                    <div class="text-sm font-medium text-gray-600">Employee:</div>
                    <div class="sm:col-span-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-xs">{{ substr($paymentRequest->user->name, 0, 2) }}</span>
                            </div>
                            <div>
                                <div class="text-gray-900 font-medium">{{ $paymentRequest->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $paymentRequest->user->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                    <div class="text-sm font-medium text-gray-600">Project / Kelas:</div>
                    <div class="sm:col-span-2 text-gray-900">
                        @if($paymentRequest->project)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-project-diagram text-blue-500"></i>
                                {{ $paymentRequest->project->project_name }}
                            </div>
                        @elseif($paymentRequest->clas)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-graduation-cap text-purple-500"></i>
                                {{ $paymentRequest->clas->name }} (Pelatihan)
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                    <div class="text-sm font-medium text-gray-600">Tanggal Pengajuan:</div>
                    <div class="sm:col-span-2 text-gray-900">{{ $paymentRequest->created_at->format('d F Y, H:i') }} WIB</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                    <div class="text-sm font-medium text-gray-600">Nominal Diajukan:</div>
                    <div class="sm:col-span-2 text-blue-600 font-bold text-xl">
                        Rp {{ number_format($paymentRequest->requested_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                    <div class="text-sm font-medium text-gray-600">Nominal Disetujui Admin:</div>
                    <div class="sm:col-span-2 text-green-600 font-bold text-xl">
                        Rp {{ number_format($paymentRequest->approved_amount ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                @if($paymentRequest->hours_worked)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                        <div class="text-sm font-medium text-gray-600">Jam Kerja:</div>
                        <div class="sm:col-span-2 text-gray-900">{{ $paymentRequest->hours_worked }} jam</div>
                    </div>
                @endif

                @if($paymentRequest->notes)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                        <div class="text-sm font-medium text-gray-600">Catatan Employee:</div>
                        <div class="sm:col-span-2 text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $paymentRequest->notes }}</div>
                    </div>
                @endif

                @if($paymentRequest->admin_notes)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                        <div class="text-sm font-medium text-gray-600">Catatan Admin:</div>
                        <div class="sm:col-span-2 text-gray-700 bg-blue-50 p-3 rounded-lg">{{ $paymentRequest->admin_notes }}</div>
                    </div>
                @endif

                @if($paymentRequest->finance_notes)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-gray-100 pb-3">
                        <div class="text-sm font-medium text-gray-600">Catatan Finance:</div>
                        <div class="sm:col-span-2 text-gray-700 bg-green-50 p-3 rounded-lg">{{ $paymentRequest->finance_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Finance Actions - APPROVE/REJECT (Only for admin_approved status) -->
        @if($paymentRequest->status === 'admin_approved')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-green-600"></i>
                    Validasi Finance
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Approve Form -->
                    <form action="{{ route('finance.payment-requests.approve', $paymentRequest) }}" method="POST" class="border-2 border-green-200 rounded-xl p-4 bg-green-50">
                        @csrf
                        <h4 class="font-semibold text-green-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> Setujui Pembayaran
                        </h4>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Finance (Opsional)</label>
                            <textarea name="finance_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm" placeholder="Tambahkan catatan jika perlu..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                        <p class="text-xs text-green-700 mt-2">* Expense akan otomatis ditambahkan ke project</p>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('finance.payment-requests.reject', $paymentRequest) }}" method="POST" class="border-2 border-red-200 rounded-xl p-4 bg-red-50">
                        @csrf
                        <h4 class="font-semibold text-red-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-times-circle"></i> Tolak Pembayaran
                        </h4>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="finance_notes" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2" onclick="return confirm('Yakin ingin menolak payment request ini?')">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Upload Bukti Transfer (Only for finance_approved status) -->
        @if($paymentRequest->status === 'finance_approved')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-upload text-blue-600"></i>
                    Upload Bukti Transfer
                </h3>

                <form action="{{ route('finance.payment-requests.mark-as-paid', $paymentRequest) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran <span class="text-red-500">*</span></label>
                        <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih metode...</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="E-Wallet">E-Wallet (OVO, GoPay, Dana, dll)</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Referensi</label>
                        <input type="text" name="payment_reference" placeholder="Contoh: REF-20260127-001" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Nomor referensi transfer/transaksi (opsional)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer <span class="text-red-500">*</span></label>
                        <input type="file" name="bukti_transfer" required accept="image/*,application/pdf" class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF. Maksimal 5MB</p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Nominal yang akan dibayarkan:</strong> 
                            <span class="text-green-600 font-bold">Rp {{ number_format($paymentRequest->approved_amount, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Upload Bukti & Tandai Sudah Dibayar
                    </button>
                </form>
            </div>
        @endif

        <!-- Display Bukti Transfer (Only for paid status) -->
        @if($paymentRequest->status === 'paid' && $paymentRequest->bukti_transfer_url)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-receipt text-emerald-600"></i>
                    Bukti Transfer
                </h3>

                @php
                    $proofUrl = $paymentRequest->bukti_transfer_url;
                    $proofPath = parse_url((string) $proofUrl, PHP_URL_PATH) ?? '';
                    $proofExt = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION));
                    $isPdfProof = $proofExt === 'pdf';
                    $isImageProof = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                @endphp

                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">Metode:</span>
                        <span class="sm:col-span-2 text-sm font-semibold text-gray-900">{{ $paymentRequest->payment_method }}</span>
                    </div>
                    @if($paymentRequest->payment_reference)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 border-b border-gray-100 pb-2">
                            <span class="text-sm font-medium text-gray-600">Referensi:</span>
                            <span class="sm:col-span-2 text-sm font-semibold text-gray-900">{{ $paymentRequest->payment_reference }}</span>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">Dibayar oleh:</span>
                        <span class="sm:col-span-2 text-sm font-semibold text-gray-900">{{ $paymentRequest->payer->name ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 border-b border-gray-100 pb-2">
                        <span class="text-sm font-medium text-gray-600">Tanggal:</span>
                        <span class="sm:col-span-2 text-sm font-semibold text-gray-900">{{ $paymentRequest->paid_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                    @if($isPdfProof)
                        <iframe src="{{ $proofUrl }}" class="w-full h-80" title="Preview Bukti Transfer"></iframe>
                    @elseif($isImageProof)
                        <img src="{{ $proofUrl }}" alt="Preview Bukti Transfer" class="w-full h-80 object-contain bg-white">
                    @else
                        <div class="p-4 text-sm text-gray-600">
                            Preview tidak tersedia untuk tipe file ini. Silakan download file bukti transfer.
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-full sm:w-auto gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-lg transition">
                        <i class="fas fa-eye"></i>Lihat di Tab Baru
                    </a>
                    <a href="{{ route('finance.payment-requests.download-proof', $paymentRequest) }}" class="inline-flex items-center justify-center w-full sm:w-auto gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-download"></i>Download Bukti Transfer
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6 lg:sticky lg:top-6 h-fit">
        <!-- Timeline -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Timeline</h3>
            
            <div class="space-y-4">
                <!-- Created -->
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-plus text-blue-600 text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">Diajukan</p>
                        <p class="text-xs text-gray-500">{{ $paymentRequest->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-600">oleh {{ $paymentRequest->user->name }}</p>
                    </div>
                </div>

                <!-- Admin Approved/Rejected -->
                @if($paymentRequest->approved_at)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 {{ $paymentRequest->status === 'admin_rejected' ? 'bg-red-100' : 'bg-green-100' }} rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $paymentRequest->status === 'admin_rejected' ? 'fa-times' : 'fa-check' }} {{ $paymentRequest->status === 'admin_rejected' ? 'text-red-600' : 'text-green-600' }} text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $paymentRequest->status === 'admin_rejected' ? 'Ditolak Admin' : 'Disetujui Admin' }}</p>
                            <p class="text-xs text-gray-500">{{ $paymentRequest->approved_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-600">oleh {{ $paymentRequest->approver->name ?? '-' }}</p>
                        </div>
                    </div>
                @endif

                <!-- Finance Approved/Rejected -->
                @if($paymentRequest->finance_approved_at)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 {{ $paymentRequest->status === 'finance_rejected' ? 'bg-red-100' : 'bg-green-100' }} rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $paymentRequest->status === 'finance_rejected' ? 'fa-times' : 'fa-check-double' }} {{ $paymentRequest->status === 'finance_rejected' ? 'text-red-600' : 'text-green-600' }} text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $paymentRequest->status === 'finance_rejected' ? 'Ditolak Finance' : 'Disetujui Finance' }}</p>
                            <p class="text-xs text-gray-500">{{ $paymentRequest->finance_approved_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-600">oleh {{ $paymentRequest->financeApprover->name ?? '-' }}</p>
                        </div>
                    </div>
                @endif

                <!-- Paid -->
                @if($paymentRequest->paid_at)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-money-check-alt text-emerald-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Sudah Dibayar</p>
                            <p class="text-xs text-gray-500">{{ $paymentRequest->paid_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-600">oleh {{ $paymentRequest->payer->name ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Amount Summary -->
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl p-6 border border-indigo-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Nominal</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Diajukan:</span>
                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($paymentRequest->requested_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Disetujui Admin:</span>
                    <span class="text-sm font-semibold text-green-600">Rp {{ number_format($paymentRequest->approved_amount ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($paymentRequest->approved_amount && $paymentRequest->approved_amount != $paymentRequest->requested_amount)
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Selisih:</span>
                        <span>Rp {{ number_format(abs($paymentRequest->requested_amount - $paymentRequest->approved_amount), 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
