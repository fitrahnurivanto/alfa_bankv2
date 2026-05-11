@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('trainer.classes.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $class->name }}</h1>
            <p class="text-gray-600">Detail Kelas</p>
        </div>
        <span class="px-4 py-2 text-sm rounded-full
            {{ $class->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
            {{ $class->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $class->status === 'done' ? 'bg-blue-100 text-blue-700' : '' }}">
            {{ $class->status === 'done' ? 'Selesai' : ucfirst($class->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Class Info Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-purple-600 mr-2"></i>Informasi Kelas
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Nama Kelas</label>
                        <p class="text-gray-900">{{ $class->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Kategori</label>
                        <p class="text-gray-900">{{ $class->kategori->nama_kategori ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Instansi</label>
                        <p class="text-gray-900">{{ $class->instansi ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jumlah Peserta</label>
                        <p class="text-gray-900">{{ $class->amount ?? 0 }} Peserta</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Siswa Lulus</label>
                        <p class="text-gray-900">{{ $class->passed_students ?? 0 }} Siswa</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Siswa Tidak Lulus</label>
                        <p class="text-gray-900">{{ $class->failed_students ?? 0 }} Siswa</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                        <p class="text-gray-900">
                            <i class="fas fa-calendar mr-1 text-gray-400"></i>
                            {{ $class->start_date ? \Carbon\Carbon::parse($class->start_date)->format('d F Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                        <p class="text-gray-900">
                            <i class="fas fa-calendar mr-1 text-gray-400"></i>
                            {{ $class->end_date ? \Carbon\Carbon::parse($class->end_date)->format('d F Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jumlah Pertemuan</label>
                        <p class="text-gray-900">{{ $class->meet ?? 0 }} Pertemuan</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Durasi per Pertemuan</label>
                        <p class="text-gray-900">{{ $class->duration ?? 0 }} JPL</p>
                    </div>
                    @if($class->alamat)
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-gray-700">
                            <i class="fas fa-map-marker-alt text-red-600 mr-1"></i>Alamat
                        </label>
                        <p class="text-gray-900">{{ $class->alamat }}</p>
                    </div>
                    @endif
                </div>

                @if($class->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label class="text-sm font-semibold text-gray-700 block mb-2">Deskripsi</label>
                    <p class="text-gray-600 whitespace-pre-wrap">{{ $class->description }}</p>
                </div>
                @endif
            </div>

            <!-- Team Trainer -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-users text-emerald-600 mr-2"></i>Team Trainer
                </h2>
                @if($class->trainers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($class->trainers as $trainer)
                    <div class="flex items-center p-3 bg-emerald-50 rounded-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            {{ strtoupper(substr($trainer->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $trainer->name }}</p>
                            <p class="text-sm text-gray-600">{{ $trainer->email }}</p>
                            @if($trainer->phone)
                            <p class="text-xs text-gray-500">{{ $trainer->phone }}</p>
                            @endif
                        </div>
                        @if($trainer->id === \Illuminate\Support\Facades\Auth::id())
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">
                            Anda
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 mt-3">Total: {{ $class->trainers->count() }} trainer</p>
                @else
                <p class="text-center text-gray-400 py-4">Belum ada trainer ditugaskan</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-user-check text-green-600 mr-2"></i>Absensi Pengajar
                    </h2>
                    @if($class->status === 'approved')
                    <a href="{{ route('trainer.attendance.index', ['class_id' => $class->id]) }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-pen mr-1"></i>Isi Absen
                    </a>
                    @endif
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
                            @forelse($class->trainerAttendances->take(15) as $attendance)
                                <tr class="border-b border-gray-100 align-top">
                                    <td class="py-2">{{ $attendance->attendance_date ? $attendance->attendance_date->format('d M Y') : '-' }}</td>
                                    <td class="py-2">{{ $attendance->trainer->name ?? '-' }}</td>
                                    <td class="py-2">{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : '-' }}</td>
                                    <td class="py-2 text-xs">
                                        @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                            <a class="text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $attendance->check_in_latitude }},{{ $attendance->check_in_longitude }}">Lihat Maps</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $attendance->check_in_accuracy !== null ? number_format((float)$attendance->check_in_accuracy, 0, ',', '.') : '-' }}</td>
                                    <td class="py-2">{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : '-' }}</td>
                                    <td class="py-2 text-xs">
                                        @if($attendance->check_out_latitude && $attendance->check_out_longitude)
                                            <a class="text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $attendance->check_out_latitude }},{{ $attendance->check_out_longitude }}">Lihat Maps</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $attendance->check_out_accuracy !== null ? number_format((float)$attendance->check_out_accuracy, 0, ',', '.') : '-' }}</td>
                                    <td class="py-2">{{ $attendance->students_present ?? '-' }}</td>
                                    <td class="py-2">{{ $attendance->material_covered ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-4 text-center text-gray-500">Belum ada absensi pengajar untuk kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-3">Waktu check-in/check-out dicatat otomatis dari sistem.</p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Honor Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-green-100 text-sm font-medium">Honor Trainer</p>
                    <i class="fas fa-money-bill-wave text-2xl text-green-100"></i>
                </div>
                <h3 class="text-3xl font-bold">
                    Rp {{ number_format($class->trainer_honor ?? 0, 0, ',', '.') }}
                </h3>
                <p class="text-green-100 text-xs mt-2">Per trainer</p>
            </div>

            <!-- Honor Payment Status -->
            @if(($class->status === 'done' || $class->status === 'approved'))
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-credit-card text-green-600 mr-2"></i>Status Pembayaran Honor
                </h3>
                @php
                    $honorStatus = $class->getHonorStatus();
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
                <div class="flex items-center justify-center p-4 rounded-lg {{ $statusColor['bg'] }}">
                    <div class="text-center">
                        <i class="fas {{ $statusColor['icon'] }} {{ $statusColor['text'] }} text-3xl mb-2"></i>
                        <p class="text-lg font-semibold {{ $statusColor['text'] }}">{{ $statusLabel }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- File Nilai Siswa -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-user-graduate text-emerald-600 mr-2"></i>Rekap Kelulusan Siswa
                </h3>

                <div class="text-xs text-gray-600 bg-gray-50 p-3 rounded-lg mb-5 border border-gray-200">
                    <i class="fas fa-info-circle mr-1"></i>
                    Input jumlah siswa, lulus, dan tidak lulus sekarang diisi oleh tim akademik pada halaman detail kelas akademik.
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-upload text-indigo-600 mr-2"></i>File Nilai Siswa
                </h3>

                @php
                    $activeGradeFile = $class->activeGradeFile;
                @endphp

                @if($class->status === 'approved')
                <form action="{{ route('trainer.classes.upload-grade-file', $class->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 font-semibold mb-1">Upload File Nilai</label>
                        <input type="file" name="grade_files[]" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.csv" multiple required
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG, XLS, XLSX, CSV. Bisa pilih lebih dari 1 file sekaligus. Maksimal 10MB per file.</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Upload File Nilai
                    </button>
                </form>
                @else
                <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                    <i class="fas fa-info-circle mr-1"></i>
                    Upload file nilai hanya tersedia saat kelas status approved.
                </div>
                @endif

                @if($class->gradeFiles->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-800 mb-2">Daftar File Nilai</p>
                    <div class="space-y-2 max-h-52 overflow-y-auto">
                        @foreach($class->gradeFiles as $gradeFile)
                        <div class="p-2 border rounded-lg flex items-center justify-between gap-2 {{ $loop->first ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 bg-white' }}">
                            <div>
                                <p class="text-xs font-semibold text-gray-900">
                                    {{ $gradeFile->file_name }}
                                    @if($loop->first)
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700">Terbaru</span>
                                    @endif
                                </p>
                                <p class="text-[11px] text-gray-600">
                                    {{ $gradeFile->uploaded_at ? $gradeFile->uploaded_at->format('d M Y H:i') : '-' }} | {{ $gradeFile->status === 'approved' ? 'Disetujui' : ($gradeFile->status === 'pending_review' ? 'Menunggu Review' : 'Ditolak') }}
                                </p>
                            </div>
                            <a href="{{ route('trainer.classes.download-grade-file', ['class' => $class->id, 'gradeFile' => $gradeFile->id]) }}" class="text-xs px-2 py-1 bg-gray-800 text-white rounded hover:bg-black transition">
                                Download
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Payment Request -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-invoice text-blue-600 mr-2"></i>Payment Request
                </h3>
                @php
                    $paymentRequest = $class->paymentRequests()->where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
                @endphp
                
                @if($paymentRequest)
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="px-3 py-1 text-xs rounded-full
                            {{ $paymentRequest->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $paymentRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $paymentRequest->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ ucfirst($paymentRequest->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Amount</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($paymentRequest->amount, 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('trainer.payment-requests.show', $paymentRequest) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mt-3">
                        <i class="fas fa-eye mr-2"></i>Lihat Detail
                    </a>
                </div>
                @else
                <p class="text-sm text-gray-500 mb-4">Belum ada payment request untuk kelas ini.</p>
                @if($class->status === 'done' && $activeGradeFile && $activeGradeFile->status === 'approved')
                <a href="{{ route('trainer.payment-requests.create', ['class_id' => $class->id]) }}" class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i>Buat Payment Request
                </a>
                @else
                <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                    <i class="fas fa-info-circle mr-1"></i>
                    @if($class->status !== 'done')
                        Payment request hanya bisa dibuat setelah kelas selesai (status: done).
                    @elseif(!$activeGradeFile || $activeGradeFile->status !== 'approved')
                        Payment request menunggu file nilai disetujui admin.
                    @endif
                </div>
                @endif
                @endif
            </div>

            <!-- Bukti Pembayaran Honor -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-invoice-dollar text-emerald-600 mr-2"></i>Bukti Pembayaran Honor
                </h3>

                @php
                    $paidPaymentRequest = $class->paymentRequests()
                        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                        ->where('status', 'paid')
                        ->latest('paid_at')
                        ->first();
                @endphp

                @if($paidPaymentRequest && $paidPaymentRequest->bukti_transfer_url)
                    <div class="space-y-3">
                        <div class="text-sm text-gray-600">
                            <p><span class="font-semibold text-gray-700">Tanggal Bayar:</span>
                                {{ $paidPaymentRequest->paid_at ? \Carbon\Carbon::parse($paidPaymentRequest->paid_at)->format('d M Y H:i') : '-' }}
                            </p>
                            <p><span class="font-semibold text-gray-700">Nominal:</span>
                                Rp {{ number_format($paidPaymentRequest->approved_amount ?? $paidPaymentRequest->requested_amount, 0, ',', '.') }}
                            </p>
                        </div>

                        @php
                            $proofUrl = $paidPaymentRequest->bukti_transfer_url;
                            $isPdf = str_contains(strtolower((string) $proofUrl), '.pdf');
                        @endphp

                        <div class="rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            @if($isPdf)
                                <iframe src="{{ $proofUrl }}" class="w-full h-64" title="Preview Bukti Pembayaran"></iframe>
                            @else
                                <img src="{{ $proofUrl }}" alt="Preview Bukti Pembayaran" class="w-full h-64 object-contain bg-white">
                            @endif
                        </div>

                        <a href="{{ route('trainer.payment-requests.download-proof', $paidPaymentRequest) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                            <i class="fas fa-download mr-2"></i>Download Bukti Pembayaran
                        </a>
                    </div>
                @elseif($paidPaymentRequest)
                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pembayaran honor sudah berstatus paid, tetapi bukti transfer belum tersedia.
                    </div>
                @else
                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                        <i class="fas fa-info-circle mr-1"></i>
                        Bukti pembayaran akan tampil setelah Finance menandai payment request Anda sebagai paid.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
