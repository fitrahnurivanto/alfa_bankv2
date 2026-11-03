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
            $kategoriName = strtolower($clas->kategori->nama_kategori ?? '');
            $isCorporate = true;
            $corporateProposal = $clas->corporateProposal;
            $corporateFlowStatus = $corporateProposal?->status
                ?? $clas->corporate_status
                ?? match ($clas->status) {
                    'approved' => 'running',
                    'done' => 'completed',
                    'rejected' => 'cancelled',
                    default => 'submitted',
                };
            $syncedParticipantCount = $clas->classRegistrants
                ->whereIn('status', ['registered', 'paid', 'confirmed', 'approved'])
                ->count();
            $displayParticipantCount = $syncedParticipantCount > 0
                ? $syncedParticipantCount
                : max(
                    (int) ($clas->manual_enrollment_count ?? 0),
                    (int) ($clas->amount ?? 0)
                );
            $hasCertification = (bool) $clas->sertifikasi_bnsp
                || str_contains(strtolower((string) $clas->jenis_kelas), 'sertifikasi');
            $paidAmountForDone = $isCorporate && $clas->corporateProposal
                ? (float) $clas->corporateProposal->installments->where('payment_status', 'paid')->sum('confirmed_amount')
                : (float) ($clas->paid_amount ?? 0);
            $remainingPaymentForDone = (float) ($clas->price ?? 0) - $paidAmountForDone;
            $displayStatus = $clas->status === 'done'
                ? 'completed'
                : ($clas->status === 'approved'
                    ? 'running'
                    : ($isCorporate ? $corporateFlowStatus : $clas->status));
            $assignedTrainerNames = $clas->trainers->pluck('name')->filter()->implode(', ');
            $displayStatusLabel = [
                'submitted' => 'Menunggu Konfirmasi',
                'under_review' => 'Sedang Direview',
                'deal' => 'Deal',
                'running' => 'Berjalan',
                'completed' => 'Selesai',
                'cancelled' => 'Batal',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'done' => 'Selesai',
                'rejected' => 'Rejected',
            ][$displayStatus] ?? ucfirst(str_replace('_', ' ', $displayStatus));
            $displayStatusClasses = [
                'submitted' => 'bg-yellow-100 text-yellow-800',
                'under_review' => 'bg-purple-100 text-purple-800',
                'deal' => 'bg-emerald-100 text-emerald-800',
                'running' => 'bg-blue-100 text-blue-800',
                'completed' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800',
                'pending' => 'bg-yellow-100 text-yellow-800',
                'approved' => 'bg-green-100 text-green-800',
                'done' => 'bg-blue-100 text-blue-800',
                'rejected' => 'bg-red-100 text-red-800',
            ][$displayStatus] ?? 'bg-gray-100 text-gray-800';
            $displayStatusIcon = [
                'submitted' => 'fa-clock',
                'under_review' => 'fa-search',
                'deal' => 'fa-handshake',
                'running' => 'fa-play-circle',
                'completed' => 'fa-check-circle',
                'cancelled' => 'fa-times-circle',
                'pending' => 'fa-clock',
                'approved' => 'fa-check-circle',
                'done' => 'fa-check-double',
                'rejected' => 'fa-times-circle',
            ][$displayStatus] ?? 'fa-circle';
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
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $displayStatusClasses }}">
                                <i class="fas {{ $displayStatusIcon }} mr-1"></i>{{ $displayStatusLabel }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 xl:justify-end">
                          @if($clas->status === 'pending' && \Illuminate\Support\Facades\Auth::user()->canApproveClass())
                        <!-- Approve Button -->
                        <form action="{{ route('admin.classes.approve', $clas) }}"
                              method="POST"
                            onsubmit="return confirm('{{ $isCorporate ? 'Set status Deal dan lanjut isi detail corporate?' : 'Approve kelas ini?' }}');">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-linear-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg transition shadow-sm hover:shadow-md">
                                    <i class="fas {{ $isCorporate ? 'fa-handshake' : 'fa-check' }} mr-2"></i>{{ $isCorporate ? 'Deal' : 'Approve' }}
                            </button>
                        </form>
                        <!-- Reject Button -->
                        <button type="button"
                                onclick="showRejectModal({{ $clas->id }}, '{{ addslashes($clas->name) }}')"
                                class="px-4 py-2 bg-linear-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white rounded-lg transition shadow-sm hover:shadow-md">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                    @endif
                          @if($clas->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                        @php
                            $isCorporateLunas = $remainingPaymentForDone <= 0;
                        @endphp
                        @if((float)($clas->price ?? 0) > 0 && $isCorporateLunas)
                            <form action="{{ route('admin.classes.mark-as-done', $clas) }}"
                                  method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kelas ini?');">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                    <i class="fas fa-check-circle mr-2"></i>Selesaikan Kelas
                                </button>
                            </form>
                        @elseif((float)($clas->price ?? 0) > 0 && !$isCorporateLunas)
                            <button type="button" disabled
                                    class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed"
                                    title="Pembayaran dari klien belum lunas. Sisa: Rp {{ number_format($remainingPaymentForDone, 0, ',', '.') }}">
                                <i class="fas fa-lock mr-2"></i>Selesaikan Kelas
                            </button>
                        @else
                            <button type="button" disabled
                                    class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed"
                                    title="Nilai Kontrak (Pendapatan Kelas) belum terisi. Lengkapi Nilai Kontrak Pelatihan/Sertifikasi di Step 2.">
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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
            <!-- Left Column -->
            <div class="flex flex-col gap-5 lg:col-span-8">
                <!-- Ringkasan -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white border border-gray-200 rounded-xl p-4 min-w-0">
                        <p class="text-xs text-gray-500 mb-1">Periode Kelas</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $clas->start_date ? $clas->start_date->format('d M Y') : '-' }} - {{ $clas->end_date ? $clas->end_date->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4 min-w-0">
                        <p class="text-xs text-gray-500 mb-1">Total Peserta</p>
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($displayParticipantCount, 0, ',', '.') }} orang</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4 min-w-0">
                        <p class="text-xs text-gray-500 mb-1">Pertemuan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $clas->meet ?? 0 }}x  -  {{ $clas->duration ?? 0 }} JPL</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4 min-w-0">
                        <p class="text-xs text-gray-500 mb-1">Pendapatan Kelas</p>
                        <p class="text-sm font-semibold text-emerald-700">Rp {{ number_format((float) ($clas->price ?? 0), 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Step 1: Informasi Umum -->
                <div id="info-umum" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Step 1 - Informasi Umum</h2>
                    </div>
                    <div class="p-6">
                    <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Kelas</p>
                            <p class="font-medium text-gray-900">{{ $clas->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kategori</p>
                            <p class="font-medium text-gray-900">{{ $clas->kategori->nama_kategori ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jenis Kelas</p>
                            <p class="font-medium text-gray-900">{{ $clas->classTypeLabel() }}</p>
                        </div>

                        @php
                            $kategoriName = strtolower($clas->kategori->nama_kategori ?? '');
                            $isCorporate = true;
                        @endphp

                            <!-- Field untuk Corporate Training -->
                            <div>
                                <p class="text-sm text-gray-600">Instansi</p>
                                <p class="font-medium text-gray-900">{{ $clas->instansi ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nama Penawaran</p>
                                <p class="font-medium text-gray-900">{{ $clas->proposal_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nomor Surat</p>
                                <p class="font-medium text-gray-900">{{ $clas->letter_number ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Surat</p>
                                <p class="font-medium text-gray-900">{{ $clas->letter_date ? $clas->letter_date->format('d M Y') : '-' }}</p>
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
                            <div>
                                <p class="text-sm text-gray-600">Skema Ditawarkan</p>
                                <p class="font-medium text-gray-900">{{ $clas->offered_scheme ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status Penawaran</p>
                                <p class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $clas->corporate_status ?? 'submitted')) }}</p>
                            </div>
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

                            <div>
                                <p class="text-sm text-gray-600">Jumlah Peserta</p>
                                <p class="font-medium text-gray-900"><i class="fas fa-users text-gray-500 mr-1"></i>{{ number_format($displayParticipantCount, 0, ',', '.') }} peserta</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Metode</p>
                                <p class="font-medium text-gray-900">{{ $clas->getMethodLabel() ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <p class="font-medium text-gray-900">{{ $displayStatusLabel }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Sertifikasi BNSP</p>
                                <p class="font-medium text-gray-900">
                                    <span class="px-2 py-1 {{ $hasCertification ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-xs">
                                        <i class="fas {{ $hasCertification ? 'fa-certificate' : 'fa-times' }} mr-1"></i>{{ $hasCertification ? 'Ya' : 'Tidak' }}
                                    </span>
                                </p>
                            </div>
                            <div id="detail-penawaran-corporate" class="col-span-2 border-t border-gray-200 pt-4 mt-2 scroll-mt-24">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 2 - Detail Penawaran / Deal</h3>
                                @php
                                    $proposalTypeLower = strtolower($clas->proposal_name ?? '');
                                    $showTrainingDates = str_starts_with($proposalTypeLower, 'pelatihan');
                                    $showCertificationDates = str_contains($proposalTypeLower, 'sertifikasi');
                                @endphp
                                @if($clas->description)
                                <div class="mb-4 pb-4 border-b border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2">Deskripsi</h3>
                                    <p class="text-gray-700 text-sm leading-relaxed">{{ $clas->description }}</p>
                                </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    @if($showTrainingDates)
                                    <div>
                                        <p class="text-sm text-gray-600">Tanggal Pelatihan</p>
                                        <p class="font-medium text-gray-900">
                                            {{ $clas->training_date ? $clas->training_date->format('d M Y') : '-' }}
                                            @if($clas->training_date_end)
                                                &ndash; {{ $clas->training_date_end->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    @if($showCertificationDates)
                                    <div>
                                        <p class="text-sm text-gray-600">Tanggal Sertifikasi</p>
                                        <p class="font-medium text-gray-900">
                                            {{ $clas->certification_date_start ? $clas->certification_date_start->format('d M Y') : '-' }}
                                            @if($clas->certification_date_end)
                                                &ndash; {{ $clas->certification_date_end->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    <div>
                                        <p class="text-sm text-gray-600">Skema/Materi Pelatihan</p>
                                        <p class="font-medium text-gray-900">{{ $clas->training_scheme ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Trainer</p>
                                        <p class="font-medium text-gray-900">{{ $assignedTrainerNames ?: ($clas->trainer_name ?? '-') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Asesor</p>
                                        <p class="font-medium text-gray-900">{{ $clas->assessor_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Biaya Pelatihan</p>
                                        <p class="font-medium text-emerald-700">Rp {{ number_format((float) ($clas->training_fee ?? $clas->price ?? 0), 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Rencana Tanggal Pembayaran</p>
                                        <p class="font-medium text-gray-900">{{ $clas->payment_start_date ? $clas->payment_start_date->format('d M Y') : '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status Pelunasan</p>
                                        <p class="font-medium {{ $remainingPaymentForDone > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            @if($remainingPaymentForDone > 0)
                                                Belum Lunas (sisa Rp {{ number_format($remainingPaymentForDone, 0, ',', '.') }})
                                            @else
                                                Lunas
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>




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
                                            class="w-full px-4 py-2 bg-linear-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition">
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

                    </div>

                    <!-- Deskripsi Corporate -->
                    </div>
                </div>

                <!-- Team Trainer & Asesor (sejajar dengan kolom kanan, dibagi sesuai jumlah orang) -->
                <div id="team-trainer" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-base font-semibold text-gray-900 mb-3">Team Trainer &amp; Asesor</h2>
                    @php
                        $assignedAssessors = collect(explode(',', (string) $clas->assessor_name))
                            ->map(fn ($name) => trim($name))
                            ->filter()
                            ->values();

                        // Asesor = trainer (tabel users, role trainer) yang ditunjuk sebagai asesor; disimpan sebagai nama.
                        $assessorUserIds = $assignedAssessors->isEmpty()
                            ? collect()
                            : \App\Models\User::where('role', 'trainer')->whereIn('name', $assignedAssessors)->pluck('id', 'name');
                        $teamPeople = collect();
                        foreach ($clas->trainers as $trainerItem) {
                            $teamPeople->push(['type' => 'trainer', 'id' => $trainerItem->id, 'name' => $trainerItem->name, 'email' => $trainerItem->email, 'phone' => $trainerItem->phone]);
                        }
                        foreach ($assignedAssessors as $assessorName) {
                            $teamPeople->push(['type' => 'asesor', 'id' => $assessorUserIds[$assessorName] ?? null, 'name' => $assessorName, 'email' => null, 'phone' => null]);
                        }
                        $teamCount = $teamPeople->count();
                        $teamCols = min(max($teamCount, 1), 3);

                        $showHonorStatus = ($clas->status === 'done' || $clas->status === 'approved')
                            && in_array(auth()->user()->role, ['admin', 'akademik', 'marketing', 'finance']);

                        $classHonorStatus = 'pending';
                        $honorExpenses = collect();
                        $expenseHasPayee = false;
                        $classTrainerIds = $clas->trainers->pluck('id');

                        // Penerima honor disimpan di catatan expense dengan format "Dibayarkan ke: <nama>".
                        $honorPayeeOf = function ($expense) {
                            return preg_match('/^Dibayarkan ke:\s*([^\r\n]+)/u', (string) $expense->notes, $m)
                                ? mb_strtolower(trim($m[1]))
                                : null;
                        };

                        if ($showHonorStatus) {
                            $honorExpenses = $clas->expenses()->whereIn('category', ['trainer_honor', 'honor'])->get();
                            $expenseHasPayee = $honorExpenses->contains(fn ($row) => $honorPayeeOf($row) !== null);

                            if ($isCorporate) {
                                // Corporate: honor diajukan ADMIN lewat Expense (kategori honor/trainer_honor).
                                $classHonorStatus = $honorExpenses->contains('approval_status', 'approved')
                                    ? 'paid'
                                    : ($honorExpenses->contains('approval_status', 'pending') ? 'submitted' : 'pending');
                            }
                        }

                        // Status per orang (trainer/asesor). Orang yang sama bisa jadi trainer sekaligus asesor,
                        // jadi penerima di expense ditulis "Nama (Trainer)" / "Nama (Asesor)".
                        $honorStatusFor = function (array $person) use ($classHonorStatus, $honorExpenses, $expenseHasPayee, $honorPayeeOf) {
                            $key = mb_strtolower(trim($person['name']));
                            $isAsesor = $person['type'] === 'asesor';
                            $keys = $isAsesor ? [$key . ' (asesor)'] : [$key . ' (trainer)', $key];

                            $fromExpenses = function () use ($honorExpenses, $honorPayeeOf, $keys) {
                                $rows = $honorExpenses->filter(fn ($row) => in_array($honorPayeeOf($row), $keys, true));
                                if ($rows->contains('approval_status', 'approved')) return 'paid';
                                if ($rows->contains('approval_status', 'pending')) return 'submitted';
                                if ($rows->contains('approval_status', 'rejected')) return 'rejected';
                                return 'pending';
                            };

                            return $expenseHasPayee ? $fromExpenses() : $classHonorStatus;
                        };
                        $honorLabels = [
                            'pending' => 'Belum Diajukan',
                            'submitted' => 'Menunggu Validasi Finance',
                            'paid' => 'Disetujui Finance',
                            'rejected' => 'Ditolak Finance',
                        ];
                        $honorColors = [
                            'pending' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-circle'],
                            'submitted' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-hourglass-half'],
                            'paid' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
                            'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
                        ];
                    @endphp

                    @if($teamCount === 0)
                        <div class="text-center py-8">
                            <i class="fas fa-user-slash text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">Belum ada trainer atau asesor yang ditugaskan</p>
                        </div>
                    @else
                        <div class="flex items-stretch gap-3 overflow-x-auto snap-x snap-mandatory pb-1" style="--cell-basis: calc((100% - {{ ($teamCols - 1) * 0.75 }}rem) / {{ $teamCols }});">
                            @foreach($teamPeople as $person)
                                @php
                                    $isTrainerPerson = $person['type'] === 'trainer';
                                    $initials = strtoupper(collect(explode(' ', trim($person['name'])))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
                                    $personStatus = $honorStatusFor($person);
                                    $sc = $honorColors[$personStatus] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question-circle'];
                                @endphp
                                <div class="flex flex-col min-w-0 grow-0 shrink-0 snap-start basis-[85%] md:[flex-basis:var(--cell-basis)] rounded-lg border overflow-hidden {{ $isTrainerPerson ? 'border-green-200' : 'border-orange-200' }}">
                                    <div class="flex items-center gap-3 p-3 min-h-[84px] {{ $isTrainerPerson ? 'bg-green-50' : 'bg-orange-50' }}">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-sm font-semibold text-white {{ $isTrainerPerson ? 'bg-blue-600' : 'bg-orange-500' }}">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $person['name'] }}</h4>
                                            @if($isTrainerPerson)
                                                <p class="text-[10px] text-gray-600 truncate">{{ $person['email'] }}</p>
                                                @if($person['phone'])
                                                    <p class="text-[10px] text-gray-500">{{ $person['phone'] }}</p>
                                                @endif
                                            @else
                                                <p class="text-xs text-orange-700">Asesor</p>
                                            @endif
                                        </div>
                                        @if($isTrainerPerson)
                                            <span class="self-start shrink-0 px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-semibold rounded-full">
                                                <i class="fas fa-check-circle"></i> Aktif
                                            </span>
                                        @endif
                                    </div>

                                    @if($showHonorStatus)
                                        <div class="bg-white p-2 border-t {{ $isTrainerPerson ? 'border-green-200' : 'border-orange-200' }}">
                                            <p class="text-[10px] font-semibold text-gray-600 mb-1">Status Pembayaran Honor</p>
                                            <div class="flex items-center justify-center gap-2 py-2 rounded-lg {{ $sc['bg'] }}">
                                                <i class="fas {{ $sc['icon'] }} {{ $sc['text'] }} text-sm"></i>
                                                <span class="text-xs font-semibold {{ $sc['text'] }}">{{ $honorLabels[$personStatus] ?? 'Unknown' }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-600 mt-3">
                            <strong>Total: {{ $clas->trainers->count() }} trainer</strong> ditugaskan untuk kelas ini
                        </p>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-4 lg:col-span-4">
                <!-- Jadwal -->
            <div id="jadwal-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="mb-4 flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-calendar-days text-violet-600"></i>Jadwal
                </h2>
                   <div class="flex justify-end">
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
                           class="relative z-20 pointer-events-auto px-3 py-1 text-xs bg-linear-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-md transition shadow-sm hover:shadow-md">
                           <i class="fas fa-edit mr-2"></i>Edit Jadwal
                       </button>
                       @endif
                   </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">{{ $isCorporate ? 'Tanggal Pelaksanaan Mulai' : 'Tanggal Mulai' }}</p>
                        <p class="font-medium text-gray-900">{{ $clas->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ $isCorporate ? 'Tanggal Pelaksanaan Selesai' : 'Tanggal Selesai' }}</p>
                        <p class="font-medium text-gray-900">{{ $clas->end_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Surat</p>
                        <p class="font-medium text-gray-900">{{ $clas->letter_date ? $clas->letter_date->format('d M Y') : '-' }}</p>
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
                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tanggal Pelaksanaan di atas mengikuti Tanggal Pelatihan/Sertifikasi kelas ini.
                        Detail lengkapnya ada di bagian
                        <a href="#detail-penawaran-corporate" class="text-blue-600 hover:underline">Detail Penawaran Corporate</a> di bawah.
                    </p>
            </div>

            <!-- Progress Kelas -->
            <div id="progress-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
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
                            $progressColor = 'bg-linear-to-r from-yellow-400 to-amber-500';
                            $iconClass = 'fa-spinner fa-pulse';
                            $statusBadgeColor = 'bg-green-100 text-green-800';
                        } elseif($clas->status === 'done') {
                            $progress = 100;
                            $progressText = 'Selesai';
                            $progressColor = 'bg-linear-to-r from-green-400 to-emerald-500';
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

                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Progress Kelas</h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="px-3 py-1.5 {{ $statusBadgeColor }} rounded-full text-xs font-semibold">
                                <i class="fas {{ $iconClass }} mr-1"></i>{{ $progressText }}
                            </span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-semibold text-gray-700">Progress</span>
                                <span class="font-bold text-lg text-indigo-900">
                                    {{ $progress }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%; background: linear-gradient(to right, #8393FE, #4E65FF);"></div>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Finansial -->
            <div id="finansial-kelas" class="flex-1 scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-sack-dollar mr-2 text-green-600"></i>Finansial</h2>
                @php
                    $usesCorporateFinancials = $isCorporate && $corporateProposal;
                    $contractValue = $usesCorporateFinancials
                        ? (float) ($corporateProposal->training_fee ?? $clas->price ?? 0)
                        : (float) ($clas->price ?? 0);
                    $corporateTrainingValue = $usesCorporateFinancials ? $clas->corporate_training_price : null;
                    $corporateCertificationValue = $usesCorporateFinancials ? $clas->corporate_certification_price : null;
                    $hasCorporatePriceBreakdown = $corporateTrainingValue !== null || $corporateCertificationValue !== null;
                    $certificationValue = !$usesCorporateFinancials ? $clas->certification_price : null;
                    $paidValue = $usesCorporateFinancials
                        ? (float) $corporateProposal->installments->where('payment_status', 'paid')->sum('confirmed_amount')
                        : (float) ($clas->paid_amount ?? 0);
                    $remainingValue = $usesCorporateFinancials
                        ? (float) ($corporateProposal->remaining_amount ?? max($contractValue - $paidValue, 0))
                        : max($contractValue - $paidValue, 0);
                    $installmentCount = $usesCorporateFinancials
                        ? (int) ($corporateProposal->installment_count ?? 0)
                        : (int) ($clas->installment_count ?? 0);
                    $paidInstallmentCount = $usesCorporateFinancials
                        ? (int) $corporateProposal->installments->where('payment_status', 'paid')->count()
                        : (int) $clas->paymentTerms()->where('status', 'paid')->count();
                    $termCount = $usesCorporateFinancials
                        ? max($installmentCount, 1)
                        : max((int) $clas->paymentTerms()->count(), (int) ($clas->payment_type === 'termin_2x' ? 2 : 0), 1);

                    $approvedOperationalCost = (float) $clas->expenses()
                        ->where('approval_status', 'approved')
                        ->whereNotIn('category', ['trainer_honor', 'honor'])
                        ->sum('amount');

                    $pendingOperationalCost = (float) $clas->expenses()
                        ->where('approval_status', 'pending')
                        ->whereNotIn('category', ['trainer_honor', 'honor'])
                        ->sum('amount');

                    $approvedTrainerHonor = (float) $clas->expenses()
                        ->where('approval_status', 'approved')
                        ->whereIn('category', ['trainer_honor', 'honor'])
                        ->sum('amount');

                    $pendingTrainerHonor = (float) $clas->expenses()
                        ->where('approval_status', 'pending')
                        ->whereIn('category', ['trainer_honor', 'honor'])
                        ->sum('amount');

                    $recognizedRevenue = $contractValue;
                    $finalRevenue = $usesCorporateFinancials ? $paidValue : $contractValue;
                    $totalApprovedCost = $approvedOperationalCost + $approvedTrainerHonor;
                    $incomeBersih = $recognizedRevenue - $totalApprovedCost;
                    $profitDelta = $finalRevenue - $totalApprovedCost;
                    $profitLabel = $profitDelta >= 0 ? 'Laba Akhir' : 'Kekurangan Biaya';
                    $contractLabel = $usesCorporateFinancials ? 'Harga Kontrak (Total)' : 'Nilai Kelas / Pendapatan';
                    $paidLabel = $usesCorporateFinancials ? 'Termin Terkonfirmasi' : 'Pembayaran Masuk';
                    $remainingLabel = $usesCorporateFinancials ? 'Sisa Kontrak' : 'Sisa Nilai Kelas';
                @endphp

                @if($clas->status === 'approved' && \Illuminate\Support\Facades\Auth::user()->canManageClass())
                <form action="{{ route('admin.classes.update-revenue', $clas) }}" method="POST" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    @csrf
                    @method('PATCH')
                    <label for="price_display" class="block text-sm font-medium text-blue-900 mb-2">
                        {{ $usesCorporateFinancials ? 'Nilai Kontrak / Pendapatan Kelas' : 'Pendapatan Kelas' }}
                    </label>
                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="text" name="price_display" id="price_display" value="{{ old('price', $clas->price) ? number_format(old('price', $clas->price), 0, ',', '.') : '' }}" class="w-full pl-10 pr-3 py-2 border-2 border-blue-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ $usesCorporateFinancials ? 'Ikuti nilai kontrak dari penawaran terbaru' : 'Isi pendapatan kelas' }}">
                            <input type="hidden" name="price" id="price" value="{{ old('price', $clas->price) }}">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-1"></i>Simpan
                        </button>
                    </div>
                </form>
                @endif

                <div class="border-l-4 border-slate-200 pl-4 md:pl-5">
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-slate-800">Alur Kalkulator Finansial</p>
                            <span class="inline-flex mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">Corporate</span>
                    </div>

                    <div class="space-y-1 text-sm md:text-base">
                        <div class="flex items-start justify-between gap-4 py-3 border-b border-slate-200">
                            <div>
                                <p class="font-medium text-gray-800">{{ $contractLabel }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Sumber: penawaran corporate terbaru</p>
                            </div>
                            <p class="font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($contractValue, 0, ',', '.') }}</p>
                        </div>
                            <div class="py-2 pl-4 border-b border-slate-200 text-xs text-gray-600 space-y-1">
                                @if($corporateTrainingValue !== null)
                                    <p>Nilai Pelatihan: <span class="font-semibold text-gray-800">Rp {{ number_format((float) $corporateTrainingValue, 0, ',', '.') }}</span></p>
                                @endif
                                @if($corporateCertificationValue !== null)
                                    <p>Nilai Sertifikasi: <span class="font-semibold text-gray-800">Rp {{ number_format((float) $corporateCertificationValue, 0, ',', '.') }}</span></p>
                                @endif
                            </div>

                        <div class="flex items-center justify-center text-gray-400 text-xs py-1">
                            <i class="fas fa-arrow-down"></i>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3 border-b border-slate-200">
                            <div>
                                <p class="font-medium text-emerald-700">{{ $paidLabel }}</p>
                                <p class="text-xs text-emerald-600 mt-1">{{ $usesCorporateFinancials ? ($paidInstallmentCount . ' dari ' . $termCount . ' termin') : (($clas->payment_type === 'termin_2x') ? ($paidInstallmentCount . ' termin terisi') : 'Pembayaran yang sudah masuk') }}</p>
                            </div>
                            <p class="font-bold text-emerald-700 whitespace-nowrap">Rp {{ number_format($paidValue, 0, ',', '.') }}</p>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3 border-b border-slate-200">
                            <div>
                                <p class="font-medium text-amber-700">{{ $remainingLabel }}</p>
                                <p class="text-xs text-amber-600 mt-1">{{ $usesCorporateFinancials ? 'Sisa yang belum ditagih / belum dibayar' : 'Selisih antara nilai kelas dan pembayaran yang sudah masuk' }}</p>
                            </div>
                            <p class="font-bold text-amber-700 whitespace-nowrap">Rp {{ number_format($remainingValue, 0, ',', '.') }}</p>
                        </div>

                        <div class="flex items-center justify-center text-gray-400 text-xs py-1">
                            <i class="fas fa-arrow-down"></i>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3 border-b border-slate-200">
                            <div>
                                <p class="font-medium text-orange-700">Biaya Operasional</p>
                                @if($pendingOperationalCost > 0)
                                    <p class="text-xs text-amber-700 mt-1"><i class="fas fa-clock mr-1"></i>Pending: Rp {{ number_format($pendingOperationalCost, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <p class="font-bold text-orange-700 whitespace-nowrap">Rp {{ number_format($approvedOperationalCost, 0, ',', '.') }}</p>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3 border-b border-slate-200">
                            <div>
                                <p class="font-medium text-rose-700">Honor Per Trainer</p>
                                @if($pendingTrainerHonor > 0)
                                    <p class="text-xs text-amber-700 mt-1"><i class="fas fa-clock mr-1"></i>Pending: Rp {{ number_format($pendingTrainerHonor, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <p class="font-bold text-rose-700 whitespace-nowrap">Rp {{ number_format($approvedTrainerHonor, 0, ',', '.') }}</p>
                        </div>

                        <div class="flex items-center justify-center text-gray-400 text-xs py-1">
                            <i class="fas fa-arrow-down"></i>
                        </div>

                        <div class="py-3 border-b border-slate-200">
                            <p class="font-semibold text-gray-900 mb-1">Income Bersih (Setelah Biaya Approved)</p>
                            <p class="text-xl md:text-2xl font-bold text-green-700">Rp {{ number_format($incomeBersih, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $recognizedRevenue >= $totalApprovedCost ? 'Berbasis nilai kontrak sebelum melihat pembayaran yang masuk' : 'Biaya approved sudah melewati nilai kontrak' }}</p>
                        </div>

                        <div class="pt-3">
                            <p class="font-semibold text-gray-900 mb-1">Profit Akhir / {{ $profitLabel }}</p>
                            <p class="text-2xl md:text-3xl font-black {{ $profitDelta >= 0 ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format(abs($profitDelta), 0, ',', '.') }}</p>
                            <p class="text-xs {{ $profitDelta >= 0 ? 'text-emerald-700' : 'text-red-700' }} mt-1">
                                {{ $profitDelta >= 0 ? 'Laba aktual dari pembayaran yang sudah masuk' : 'Masih kekurangan biaya dibanding pembayaran yang diterima' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>




        </div>

            </div>


                <!-- Step 3: Skema Pembayaran (Corporate) -->
                @php
                    $showPaymentSteps = in_array($corporateFlowStatus, ['deal', 'running', 'completed'], true);
                @endphp
                @if($showPaymentSteps)
                    @if(!$corporateProposal)
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg px-4 py-3">
                                        <i class="fas fa-triangle-exclamation mr-2"></i>
                                        Kelas ini belum terhubung ke data penawaran corporate (corporate_proposal_id kosong), jadi Step 3 & 4 belum bisa ditampilkan. Hubungi admin sistem untuk diperbaiki.
                                    </div>
                                </div>
                    @else
<div id="step-3-skema" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
<div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">Step 3 - Skema Pembayaran</h2></div>
<div class="p-6">
                                                                            <p class="text-xs text-gray-500 mb-3">Tentukan jumlah termin dan, jika sudah tahu, isi rencana jatuh tempo serta nominal tiap termin di sini. Kolom rencana boleh dikosongkan dulu; Step 4 khusus untuk konfirmasi pembayaran.</p>
                                    <form method="POST" action="{{ route('admin.corporate-proposals.installments.update', $corporateProposal) }}" class="flex flex-wrap gap-3 items-end bg-indigo-100 border border-indigo-200 rounded-lg p-4">
                                        @csrf
                                        <div class="flex-1 min-w-[200px]">
                                            <label class="block text-xs text-gray-500 mb-1">Jumlah Termin</label>
                                            <input type="number" name="installment_count" min="1" max="12" required
                                                   value="{{ old('installment_count', $corporateProposal->installment_count ?? 1) }}"
                                                   class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
                                        </div>
                                        <div>
                                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                                Simpan Termin
                                            </button>
                                        </div>
                                    </form>
                                    @error('installment_count')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror

                                    @if($corporateProposal->installments->count())
                                    <div class="mt-4 overflow-x-auto border rounded-lg">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                                <tr>
                                                    <th class="text-left px-4 py-3">Termin</th>
                                                    <th class="text-left px-4 py-3">Rencana Jatuh Tempo</th>
                                                    <th class="text-left px-4 py-3">Rencana Nominal</th>
                                                    <th class="text-left px-4 py-3">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                @foreach($corporateProposal->installments as $installment)
                                                    <tr>
                                                        <td class="px-4 py-3">Termin {{ $installment->installment_no }}</td>
                                                        <td class="px-4 py-3">{{ $installment->due_date ? $installment->due_date->format('d M Y') : '-' }}</td>
                                                        <td class="px-4 py-3">{{ $installment->amount !== null ? 'Rp ' . number_format((float) $installment->amount, 0, ',', '.') : '-' }}</td>
                                                        <td class="px-4 py-3">
                                                            @if($installment->payment_status !== 'paid')
                                                                <details>
                                                                    <summary class="cursor-pointer text-purple-600 hover:text-purple-800 text-xs font-medium">Atur Rencana</summary>
                                                                    <form method="POST" action="{{ route('admin.corporate-proposals.installments.detail', $installment) }}" class="mt-3 space-y-2 bg-gray-50 p-3 rounded-lg w-64">
                                                                        @csrf
                                                                        <div>
                                                                            <label class="block text-xs text-gray-500 mb-1">Tanggal Jatuh Tempo <span class="text-gray-400">(opsional)</span></label>
                                                                            <input type="date" name="due_date" value="{{ optional($installment->due_date)->format('Y-m-d') }}" class="w-full px-3 py-1.5 border rounded-lg text-sm">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-xs text-gray-500 mb-1">Nominal Rencana <span class="text-gray-400">(opsional)</span></label>
                                                                            <input type="text" name="amount_display" value="{{ $installment->amount !== null ? number_format((float) $installment->amount, 0, ',', '.') : '' }}" class="w-full px-3 py-1.5 border rounded-lg text-sm installment-amount-display" inputmode="numeric" autocomplete="off">
                                                                            <input type="hidden" name="amount" value="{{ $installment->amount }}" class="installment-amount-hidden">
                                                                        </div>
                                                                        <button type="submit" class="w-full px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700">Simpan Rencana</button>
                                                                    </form>
                                                                </details>
                                                            @else
                                                                <span class="text-xs text-gray-400">Lunas {{ optional($installment->paid_at)->format('d M Y') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-gray-500 text-sm mt-4">Belum ada termin yang dibuat. Isi jumlah termin di atas dulu.</p>
                                    @endif
                                </div>
</div>
                    @endif
                @endif

            <!-- Step 4: Termin & Konfirmasi Pembayaran (Corporate, full width) -->
<div id="step-4-termin" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
<div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">Step 4 - Termin &amp; Konfirmasi Pembayaran</h2></div>
<div class="p-6">
                                                                                @if($corporateProposal && $corporateProposal->installments->count())
                                        <div class="overflow-x-auto border rounded-lg">
                                            <table class="w-full text-sm">
                                                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                                    <tr>
                                                        <th class="text-left px-4 py-3">Termin</th>
                                                        <th class="text-left px-4 py-3">Jatuh Tempo (Rencana)</th>
                                                        <th class="text-left px-4 py-3">Nominal (Rencana)</th>
                                                        <th class="text-left px-4 py-3">Sudah Dibayar</th>
                                                        <th class="text-left px-4 py-3">Status</th>
                                                        <th class="text-left px-4 py-3">Konfirmasi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y">
                                                    @foreach($corporateProposal->installments as $installment)
                                                        <tr>
                                                            <td class="px-4 py-3">Termin {{ $installment->installment_no }}</td>
                                                            <td class="px-4 py-3">{{ $installment->due_date ? $installment->due_date->format('d M Y') : '-' }}</td>
                                                            <td class="px-4 py-3">{{ $installment->amount !== null ? 'Rp ' . number_format((float) $installment->amount, 0, ',', '.') : '-' }}</td>
                                                            <td class="px-4 py-3">Rp {{ number_format((float) $installment->confirmed_amount, 0, ',', '.') }}</td>
                                                            <td class="px-4 py-3">
                                                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold
                                                                    {{ $installment->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                                                    {{ $installment->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                                    {{ $installment->payment_status === 'pending' ? 'bg-gray-100 text-gray-600' : '' }}
                                                                    {{ $installment->payment_status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}">
                                                                    {{ ucfirst($installment->payment_status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                <div class="flex flex-col gap-2">
                                                                @if($installment->payment_status !== 'paid')
                                                                    <details>
                                                                        <summary class="cursor-pointer text-purple-600 hover:text-purple-800 text-xs font-medium">Konfirmasi Bayar</summary>
                                                                        <form method="POST" action="{{ route('admin.corporate-proposals.installments.confirm', $installment) }}" enctype="multipart/form-data" class="mt-3 space-y-2 bg-purple-50 p-3 rounded-lg w-72">
                                                                            @csrf
                                                                            <div>
                                                                                <label class="block text-xs text-gray-500 mb-1">Nominal Dibayar</label>
                                                                                <input type="text" name="confirmed_amount_display" required value="{{ $installment->amount !== null ? number_format((float) $installment->amount, 0, ',', '.') : '' }}" class="w-full px-3 py-1.5 border rounded-lg text-sm confirmed-amount-display" inputmode="numeric" autocomplete="off">
                                                                                <input type="hidden" name="confirmed_amount" value="{{ $installment->amount }}" class="confirmed-amount-hidden">
                                                                            </div>
                                                                            <div>
                                                                                <label class="block text-xs text-gray-500 mb-1">Tanggal Bayar</label>
                                                                                <input type="date" name="paid_at" required value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-1.5 border rounded-lg text-sm">
                                                                            </div>
                                                                            <div>
                                                                                <label class="block text-xs text-gray-500 mb-1">Bukti Transfer <span class="text-gray-400">(opsional)</span></label>
                                                                                <input type="file" name="payment_proof" class="w-full text-xs">
                                                                            </div>
                                                                            <div>
                                                                                <label class="block text-xs text-gray-500 mb-1">Keterangan <span class="text-gray-400">(opsional)</span></label>
                                                                                <textarea name="notes" rows="2" class="w-full px-3 py-1.5 border rounded-lg text-sm">{{ $installment->notes }}</textarea>
                                                                            </div>
                                                                            <button type="submit" class="w-full px-3 py-1.5 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700">Simpan Konfirmasi</button>
                                                                        </form>
                                                                    </details>
                                                                @else
                                                                    <span class="text-xs text-gray-400">Lunas {{ optional($installment->paid_at)->format('d M Y') }}</span>
                                                                @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <p class="text-gray-500 text-sm">Belum ada termin yang dibuat. Isi Step 3 di atas dulu untuk membuat slot terminnya.</p>
                                        @endif
                                    </div>
</div>

            <!-- Expenses -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between mb-4 gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-receipt text-orange-600 mr-2"></i>Biaya Tambahan / Expenses
                        </h2>
                        @if(in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin', 'superadmin', 'marketing', 'akademik']))
                        <button type="button"
                                onclick="document.getElementById('addExpenseModal').classList.remove('hidden')"
                                class="px-3 py-1.5 bg-orange-600 text-white text-xs rounded-lg hover:bg-orange-700 transition shadow-sm">
                            <i class="fas fa-plus mr-1"></i>Tambah Expense
                        </button>
                        @endif
                    </div>

                    <div>
                    @if($clas->expenses()->count() > 0)
                    <div class="hidden md:grid grid-cols-[minmax(0,1.4fr)_minmax(0,1.2fr)_auto_auto_auto] gap-3 pb-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                        <span>Expense</span>
                        <span>Penerima / Catatan</span>
                        <span>Status</span>
                        <span class="text-right">Nominal</span>
                        <span class="text-right">Aksi</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($clas->expenses()->with('user', 'approvedBy')->latest()->get() as $expense)
                        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1.4fr)_minmax(0,1.2fr)_auto_auto_auto] gap-3 items-center border-b border-gray-100 hover:bg-orange-50 transition px-2 py-3 rounded">
                            @php
                                $isHonorExpense = in_array($expense->category, ['trainer_honor', 'honor']);
                                $notesRaw = (string) $expense->notes;
                                $payeeName = null;
                                $extraNotes = trim($notesRaw);
                                if (preg_match('/^Dibayarkan ke:\s*([^\r\n]+)/u', $notesRaw, $payeeMatch)) {
                                    $payeeName = trim($payeeMatch[1]);
                                    $extraNotes = trim(preg_replace('/^Dibayarkan ke:[^\r\n]*\R?/u', '', $notesRaw));
                                }
                                $canEditDeleteExpense = \Illuminate\Support\Facades\Auth::user()->isAdmin()
                                    || (\Illuminate\Support\Facades\Auth::user()->role === 'marketing' && !$isHonorExpense)
                                    || (\Illuminate\Support\Facades\Auth::user()->role === 'akademik' && $isHonorExpense);

                                // Non-honor dapat edit/delete kapan saja, honor hanya saat pending
                                $canPerformAction = $canEditDeleteExpense && (!$isHonorExpense || $expense->approval_status === 'pending');
                            @endphp

                            <!-- Kolom 1: Expense -->
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $expense->description }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500">
                                    <span><i class="fas fa-calendar text-orange-600"></i> {{ $expense->expense_date->format('d M Y') }}</span>
                                    @if($expense->category)
                                    <span class="px-2 py-0.5 rounded {{ $isHonorExpense ? 'bg-purple-100 text-purple-700' : ($expense->category === 'operational_cost' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                        <i class="fas fa-tag mr-1"></i>{{ $expense->category_label }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Kolom 2: Penerima / Catatan -->
                            <div class="min-w-0 text-xs text-gray-600">
                                @if($payeeName)
                                    <p class="font-medium text-gray-900 truncate"><i class="fas fa-user-check text-purple-600 mr-1"></i>{{ $payeeName }}</p>
                                @endif
                                @if($extraNotes !== '')
                                    <p class="truncate" title="{{ $extraNotes }}"><i class="fas fa-sticky-note text-orange-500 mr-1"></i>{{ $extraNotes }}</p>
                                @endif
                                @if(!$payeeName && $extraNotes === '')
                                    <p class="text-gray-400">-</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-0.5 truncate">Diinput: {{ $expense->user?->name ?? 'Unknown' }}</p>
                            </div>

                            <!-- Kolom 3: Status -->
                            <div>
                                @if($expense->approval_status === 'approved')
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-semibold whitespace-nowrap">
                                        <i class="fas fa-check-circle mr-1"></i>Approved
                                    </span>
                                @elseif($expense->approval_status === 'rejected')
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-semibold whitespace-nowrap">
                                        <i class="fas fa-times-circle mr-1"></i>Rejected
                                    </span>
                                    @if($expense->rejection_reason)
                                        <p class="text-[10px] text-red-600 mt-1 italic">{{ $expense->rejection_reason }}</p>
                                    @endif
                                @else
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-semibold whitespace-nowrap">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                @endif
                            </div>

                            <!-- Kolom 4: Nominal -->
                            <div class="text-right font-bold text-orange-600 whitespace-nowrap">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </div>

                            <!-- Kolom 5: Aksi -->
                            <div class="flex items-center justify-end gap-3">
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
                    <div class="border border-dashed border-gray-300 rounded-lg px-4 py-8 text-center text-gray-500">
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

        <!-- Penutupan Kelas -->
        <div id="penutupan-kelas" class="scroll-mt-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-flag-checkered text-emerald-600 mr-2"></i>Penutupan Kelas
                </h2>
                <p class="text-xs text-gray-500">Lengkapi rekap kelulusan dan validasi file nilai sebelum kelas diselesaikan.</p>
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-300 shadow-sm p-6">
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

            <!-- File Pelengkap Corporate -->
            <div class="bg-white rounded-xl border border-gray-300 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-signature text-indigo-600 mr-2"></i>File Pelengkap Kelas
                        <span class="ml-2 align-middle px-2 py-0.5 text-[10px] font-semibold rounded-full bg-gray-100 text-gray-600">Opsional</span>
                </h3>

                @if(!$activeGradeFile)
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                            Belum ada file pelengkap yang diupload. Ini opsional untuk kategori Corporate, jadi kelas tetap bisa diselesaikan tanpa file ini.
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
                            class="flex-1 px-4 py-2.5 bg-linear-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition">
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
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai @if(!$isCorporate)<span class="text-red-500">*</span>@endif</label>
                            <input type="date" name="start_date" id="start_date" {{ $isCorporate ? 'disabled' : 'required' }} class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition {{ $isCorporate ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}">
                            <p class="text-xs text-gray-500 mt-1">{{ $isCorporate ? 'Otomatis mengikuti Tanggal Pelatihan/Sertifikasi di Step 2 (Deal).' : 'Format: YYYY-MM-DD' }}</p>
                        </div>
                        <!-- Tanggal Selesai -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai @if(!$isCorporate)<span class="text-red-500">*</span>@endif</label>
                            <input type="date" name="end_date" id="end_date" {{ $isCorporate ? 'disabled' : 'required' }} class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition {{ $isCorporate ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}">
                            <p class="text-xs text-gray-500 mt-1">{{ $isCorporate ? 'Otomatis mengikuti Tanggal Pelatihan/Sertifikasi di Step 2 (Deal).' : 'Harus >= Tanggal Mulai' }}</p>
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
                            class="flex-1 px-4 py-2.5 bg-linear-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition shadow-sm hover:shadow-md">
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

function bindRupiahInput(displaySelector) {
    document.querySelectorAll(displaySelector).forEach(function(displayInput) {
        const hiddenInput = displayInput.nextElementSibling;

        if (!hiddenInput || hiddenInput.type !== 'hidden') {
            return;
        }

        const syncValue = function() {
            const rawValue = this.value;
            const unformatted = unformatRupiah(rawValue);
            this.value = formatRupiah(unformatted);
            hiddenInput.value = unformatted;
        };

        displayInput.addEventListener('input', syncValue);
        displayInput.addEventListener('blur', syncValue);

        if (displayInput.value) {
            hiddenInput.value = unformatRupiah(displayInput.value);
            displayInput.value = formatRupiah(displayInput.value);
        }
    });
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

    bindRupiahInput('.installment-amount-display');
    bindRupiahInput('.confirmed-amount-display');
    bindRupiahInput('#price_display');
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
                            class="flex-1 px-4 py-2.5 bg-linear-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white rounded-lg transition shadow-sm hover:shadow-md">
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
