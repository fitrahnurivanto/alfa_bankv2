@extends('layouts.app')

@section('page-title', 'Review Payment Request')

@section('content')
<!-- Header -->
<div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.payment-requests.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Permintaan Pembayaran</h1>
                <p class="text-gray-600">Detail dan approval payment request</p>
            </div>
        </div>
        
        @if($paymentRequest->status === 'pending')
            <span class="px-4 py-2 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-clock"></i> Menunggu Review
            </span>
        @elseif($paymentRequest->status === 'admin_approved')
            <span class="px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Admin Approved - Menunggu Finance
            </span>
        @elseif($paymentRequest->status === 'finance_approved')
            <span class="px-4 py-2 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-check-double"></i> Finance Approved - Menunggu Pembayaran
            </span>
        @elseif($paymentRequest->status === 'paid')
            <span class="px-4 py-2 bg-emerald-100 text-emerald-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-money-check-alt"></i> Sudah Dibayar
            </span>
        @elseif($paymentRequest->status === 'admin_rejected')
            <span class="px-4 py-2 bg-red-100 text-red-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-times-circle"></i> Ditolak Admin
            </span>
        @else
            <span class="px-4 py-2 bg-red-100 text-red-800 text-sm font-semibold rounded-full flex items-center gap-2">
                <i class="fas fa-times-circle"></i> Ditolak Finance
                <i class="fas fa-times-circle"></i> Ditolak
            </span>
        @endif
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Employee & Project Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Informasi Permintaan</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Employee Info -->
                    <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-blue-700 font-medium mb-1">Employee</p>
                            <p class="text-lg font-bold text-gray-900">{{ $paymentRequest->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $paymentRequest->user->email }}</p>
                        </div>
                    </div>

                    <!-- Project/Class Info -->
                    <div class="flex items-start gap-4 p-4 bg-purple-50 rounded-lg border border-purple-100">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $paymentRequest->project ? 'fa-project-diagram' : 'fa-graduation-cap' }} text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            @if($paymentRequest->project)
                                <p class="text-sm text-purple-700 font-medium mb-1">Project Agency</p>
                                <p class="text-lg font-bold text-gray-900">{{ $paymentRequest->project->project_name }}</p>
                                <p class="text-sm text-gray-600">{{ $paymentRequest->project->project_code }}</p>
                            @elseif($paymentRequest->clas)
                                <p class="text-sm text-purple-700 font-medium mb-1">Kelas Pelatihan</p>
                                <p class="text-lg font-bold text-gray-900">{{ $paymentRequest->clas->name }}</p>
                                <div class="mt-2 space-y-1 text-sm">
                                    <p class="text-gray-600">
                                        <i class="fas fa-tag w-4 text-gray-400"></i>
                                        <span class="font-semibold">Kategori:</span> 
                                        {{ $paymentRequest->clas->kategori ? $paymentRequest->clas->kategori->name : '-' }}
                                    </p>
                                    @if($paymentRequest->clas->instansi)
                                        <p class="text-gray-600">
                                            <i class="fas fa-building w-4 text-gray-400"></i>
                                            <span class="font-semibold">Instansi:</span> {{ $paymentRequest->clas->instansi }}
                                        </p>
                                    @endif
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-alt w-4 text-gray-400"></i>
                                        <span class="font-semibold">Pertemuan:</span> {{ $paymentRequest->clas->meet ?? 0 }} x {{ $paymentRequest->clas->duration ?? 0 }} JPL
                                        = {{ ($paymentRequest->clas->meet ?? 0) * ($paymentRequest->clas->duration ?? 0) }} JPL Total
                                    </p>
                                    @if($paymentRequest->clas->trainer)
                                        @php
                                            $trainers = is_array($paymentRequest->clas->trainer) 
                                                ? $paymentRequest->clas->trainer 
                                                : json_decode($paymentRequest->clas->trainer, true) ?? [];
                                        @endphp
                                        @if(!empty($trainers))
                                            <p class="text-gray-600">
                                                <i class="fas fa-chalkboard-teacher w-4 text-gray-400"></i>
                                                <span class="font-semibold">Trainer:</span> {{ implode(', ', $trainers) }}
                                            </p>
                                        @endif
                                    @endif
                                    @if($paymentRequest->clas->trainer_honor)
                                        <p class="text-green-600 font-semibold">
                                            <i class="fas fa-money-bill-wave w-4 text-gray-400"></i>
                                            <span class="font-semibold">Honor Trainer:</span> Rp {{ number_format($paymentRequest->clas->trainer_honor, 0, ',', '.') }}
                                        </p>
                                    @endif
                                    @if($paymentRequest->clas->start_date && $paymentRequest->clas->end_date)
                                        <p class="text-gray-600">
                                            <i class="fas fa-clock w-4 text-gray-400"></i>
                                            <span class="font-semibold">Periode:</span> 
                                            {{ $paymentRequest->clas->start_date->format('d/m/Y') }} - {{ $paymentRequest->clas->end_date->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-purple-700 font-medium mb-1">Project/Kelas</p>
                                <p class="text-lg font-bold text-gray-400">Tidak ada relasi</p>
                            @endif
                        </div>
                    </div>

                    <!-- Amount Requested -->
                    <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-700 font-medium mb-1">Nominal Diajukan</p>
                                <p class="text-3xl font-bold text-green-900">
                                    Rp {{ number_format($paymentRequest->requested_amount, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="w-16 h-16 bg-green-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-green-700 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Notes -->
                    @if($paymentRequest->notes)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-comment-alt text-gray-400 mt-1"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-700 mb-1">Catatan dari Employee:</p>
                                    <p class="text-gray-900">{{ $paymentRequest->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Notes (if processed) -->
                    @if($paymentRequest->admin_notes)
                        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-user-shield text-indigo-600 mt-1"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-indigo-700 mb-1">Catatan dari Admin:</p>
                                    <p class="text-gray-900">{{ $paymentRequest->admin_notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Form -->
            @if($paymentRequest->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fas fa-clipboard-check"></i>
                            Proses Permintaan
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.payment-requests.update', $paymentRequest) }}" method="POST" id="approvalForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" id="actionInput">

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nominal Disetujui <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                                    <input type="text" name="approved_amount_display" id="approved_amount_display"
                                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           value="{{ old('approved_amount', $paymentRequest->requested_amount) ? number_format(old('approved_amount', $paymentRequest->requested_amount), 0, ',', '.') : '' }}"
                                           required>
                                    <input type="hidden" name="approved_amount" id="approved_amount" value="{{ old('approved_amount', $paymentRequest->requested_amount) }}">
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle"></i> Anda dapat mengubah nominal jika diperlukan
                                </p>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan Admin (Optional)</label>
                                <textarea name="admin_notes" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                                          rows="4" maxlength="1000" 
                                          placeholder="Tambahkan catatan untuk employee...">{{ old('admin_notes') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" onclick="submitForm('approve')" 
                                        class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Setujui Pembayaran
                                </button>
                                <button type="button" onclick="submitForm('reject')" 
                                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i> Tolak Permintaan
                                </button>
                            </div>
                        </form>

<script>
function submitForm(action) {
    document.getElementById('actionInput').value = action;
    document.getElementById('approvalForm').submit();
}
</script>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                        <div>
                            <p class="text-blue-900 font-semibold mb-1">Permintaan Sudah Diproses</p>
                            <p class="text-blue-800">
                                Diproses pada {{ $paymentRequest->approved_at ? $paymentRequest->approved_at->format('d/m/Y H:i') : $paymentRequest->updated_at->format('d/m/Y H:i') }}
                                @if($paymentRequest->approver)
                                    oleh <span class="font-semibold">{{ $paymentRequest->approver->name }}</span>
                                @endif
                            </p>
                            @if($paymentRequest->approved_amount)
                                <p class="text-green-700 font-bold text-lg mt-2">
                                    Nominal Disetujui: Rp {{ number_format($paymentRequest->approved_amount, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mark as Paid Form (for Finance - only show if approved or processing) -->
            @if(in_array($paymentRequest->status, ['approved', 'processing']))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-600 to-emerald-600">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fas fa-money-check-alt"></i>
                            Finance - Konfirmasi Pembayaran
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-semibold">Untuk Tim Finance:</p>
                                    <p>Pastikan transfer sudah dilakukan sebelum mengklik tombol "Tandai Sudah Dibayar"</p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.payment-requests.mark-as-paid', $paymentRequest) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Pilih metode...</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="Cash">Cash</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                    <option value="Cek">Cek</option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nomor Referensi Transfer (Optional)
                                </label>
                                <input type="text" name="payment_reference" maxlength="100"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       placeholder="Contoh: TRF2026011400123">
                                <p class="text-xs text-gray-500 mt-1">Nomor transaksi/referensi dari bank</p>
                            </div>

                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                                <p class="text-green-800 font-semibold text-lg">
                                    Jumlah: Rp {{ number_format($paymentRequest->approved_amount, 0, ',', '.') }}
                                </p>
                                <p class="text-green-700 text-sm">Kepada: {{ $paymentRequest->user->name }}</p>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-green-600 text-white px-6 py-4 rounded-lg hover:bg-green-700 transition font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-check-double"></i> Tandai Sudah Dibayar
                            </button>
                        </form>

                        @if($paymentRequest->status === 'approved')
                        <form action="{{ route('admin.payment-requests.mark-as-processing', $paymentRequest) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium flex items-center justify-center gap-2">
                                <i class="fas fa-spinner"></i> Tandai Sebagai "Processing"
                            </button>
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                Ubah status menjadi "sedang diproses pembayaran"
                            </p>
                        </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Payment Confirmation Info (if already paid) -->
            @if($paymentRequest->status === 'paid')
                <div class="bg-white rounded-xl shadow-sm border border-green-300">
                    <div class="px-6 py-4 border-b border-green-200 bg-gradient-to-r from-green-500 to-emerald-500">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            Pembayaran Sudah Cair
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Jumlah Dibayar:</span>
                                <span class="text-lg font-bold text-green-700">
                                    Rp {{ number_format($paymentRequest->approved_amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Metode:</span>
                                <span class="font-semibold text-gray-900">{{ $paymentRequest->payment_method }}</span>
                            </div>
                            @if($paymentRequest->payment_reference)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Referensi:</span>
                                <span class="font-mono text-sm font-semibold text-gray-900">{{ $paymentRequest->payment_reference }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibayar pada:</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $paymentRequest->paid_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                            @if($paymentRequest->payer)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibayar oleh:</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $paymentRequest->payer->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Created -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-plus text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Permintaan Dibuat</p>
                                <p class="text-xs text-gray-500">{{ $paymentRequest->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if($paymentRequest->status !== 'pending')
                            <!-- Processed -->
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 {{ $paymentRequest->status === 'approved' ? 'bg-green-100' : ($paymentRequest->status === 'paid' ? 'bg-green-100' : 'bg-red-100') }} rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas {{ in_array($paymentRequest->status, ['approved', 'processing', 'paid']) ? 'fa-check' : 'fa-times' }} {{ in_array($paymentRequest->status, ['approved', 'processing', 'paid']) ? 'text-green-600' : 'text-red-600' }} text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ in_array($paymentRequest->status, ['approved', 'processing', 'paid']) ? 'Disetujui' : 'Ditolak' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $paymentRequest->approved_at ? $paymentRequest->approved_at->format('d M Y, H:i') : $paymentRequest->updated_at->format('d M Y, H:i') }}
                                    </p>
                                    @if($paymentRequest->approver)
                                        <p class="text-xs text-gray-600 mt-1">oleh {{ $paymentRequest->approver->name }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($paymentRequest->status === 'processing')
                            <!-- Processing -->
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-spinner text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Sedang Diproses</p>
                                    <p class="text-xs text-gray-500">Menunggu pembayaran dari Finance</p>
                                </div>
                            </div>
                            @endif

                            @if($paymentRequest->status === 'paid' && $paymentRequest->paid_at)
                            <!-- Paid -->
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-money-check-alt text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Sudah Dibayar</p>
                                    <p class="text-xs text-gray-500">{{ $paymentRequest->paid_at->format('d M Y, H:i') }}</p>
                                    @if($paymentRequest->payer)
                                        <p class="text-xs text-gray-600 mt-1">oleh {{ $paymentRequest->payer->name }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @if($paymentRequest->clas)
                        <a href="{{ route('admin.classes.show', $paymentRequest->clas) }}" 
                           class="block w-full bg-white text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition text-sm font-medium border border-gray-200">
                            <i class="fas fa-graduation-cap mr-2"></i> Lihat Kelas
                        </a>
                    @endif
                    @if($paymentRequest->user && $paymentRequest->user->role === 'trainer')
                        <a href="{{ route('admin.trainers.show', $paymentRequest->user->id) }}" 
                           class="block w-full bg-white text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition text-sm font-medium border border-gray-200">
                            <i class="fas fa-chalkboard-teacher mr-2"></i> Lihat Profil Trainer
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
