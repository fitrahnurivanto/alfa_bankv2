@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/60 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 px-6 py-6 shadow-sm sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-3 inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/80">
                        Trainer Attendance
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Absensi Pengajar</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base">
                        Check-in, check-out, dan tambah sesi untuk kelas berjalan dengan komposisi yang lebih rapi dan konsisten.
                    </p>
                </div>

                @if($selectedClass)
                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-3xl">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white backdrop-blur">
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-200">Kelas Aktif</div>
                            <div class="mt-1 text-sm font-semibold">{{ $selectedClass->name }}</div>
                            <div class="text-xs text-slate-300">{{ $selectedClass->instansi ?: 'Tanpa instansi' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white backdrop-blur">
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-200">Status Hari Ini</div>
                            <div class="mt-1 text-sm font-semibold">
                                {{ $currentAttendance && $currentAttendance->check_in_at && !$currentAttendance->check_out_at ? 'Sedang Berjalan' : (($todayAttendance && $todayAttendance->check_out_at) ? 'Selesai' : 'Menunggu') }}
                            </div>
                            <div class="text-xs text-slate-300">Sesi {{ $todayAttendance ? $todayAttendance->session_number : '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-white backdrop-blur">
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-200">Jadwal Aktif</div>
                            <div class="mt-1 text-sm font-semibold">
                                {{ $currentAttendance && $currentAttendance->effective_start_time ? $currentAttendance->effective_start_time : ($selectedClass->start_time ?? '-') }}
                            </div>
                            <div class="text-xs text-slate-300">Waktu sesi berjalan</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('trainer.attendance.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-6">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih Kelas Berjalan</label>
                    <select name="class_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200" onchange="this.form.submit()">
                        @forelse($classes as $class)
                            <option value="{{ $class->id }}" {{ (int) $selectedClassId === (int) $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->instansi ?: 'Tanpa Instansi' }}
                            </option>
                        @empty
                            <option value="">Tidak ada kelas berjalan yang Anda ajar</option>
                        @endforelse
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Periode</label>
                    <select name="period" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200" onchange="this.form.submit()">
                        <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="month_01" {{ ($period ?? '') === 'month_01' ? 'selected' : '' }}>Jan</option>
                        <option value="month_02" {{ ($period ?? '') === 'month_02' ? 'selected' : '' }}>Feb</option>
                        <option value="month_03" {{ ($period ?? '') === 'month_03' ? 'selected' : '' }}>Mar</option>
                        <option value="month_04" {{ ($period ?? '') === 'month_04' ? 'selected' : '' }}>Apr</option>
                        <option value="month_05" {{ ($period ?? '') === 'month_05' ? 'selected' : '' }}>Mei</option>
                        <option value="month_06" {{ ($period ?? '') === 'month_06' ? 'selected' : '' }}>Jun</option>
                        <option value="month_07" {{ ($period ?? '') === 'month_07' ? 'selected' : '' }}>Jul</option>
                        <option value="month_08" {{ ($period ?? '') === 'month_08' ? 'selected' : '' }}>Agu</option>
                        <option value="month_09" {{ ($period ?? '') === 'month_09' ? 'selected' : '' }}>Sep</option>
                        <option value="month_10" {{ ($period ?? '') === 'month_10' ? 'selected' : '' }}>Okt</option>
                        <option value="month_11" {{ ($period ?? '') === 'month_11' ? 'selected' : '' }}>Nov</option>
                        <option value="month_12" {{ ($period ?? '') === 'month_12' ? 'selected' : '' }}>Des</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tahun</label>
                    <select name="year" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200" onchange="this.form.submit()">
                        @foreach(($availableYears ?? collect([date('Y')])) as $yearItem)
                            <option value="{{ $yearItem }}" {{ (int) ($year ?? date('Y')) === (int) $yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 lg:justify-self-end">
                    @if($selectedClass)
                        <a href="{{ route('trainer.classes.show', $selectedClass->id) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                            <i class="fas fa-eye mr-2"></i>Detail Kelas
                        </a>
                    @endif
                </div>
            </form>
        </div>

    @if($selectedClass)
        @php
            $hasOpenSession = $currentAttendance && $currentAttendance->check_in_at && !$currentAttendance->check_out_at;
            $hasDraftSession = $currentAttendance && !$currentAttendance->check_in_at;
            $canAddSession = $todayAttendance && $todayAttendance->check_out_at;
            $sessionLabel = $todayAttendance ? 'Sesi ' . $todayAttendance->session_number : '-';
            $activeStartTime = $currentAttendance && $currentAttendance->effective_start_time ? $currentAttendance->effective_start_time : ($selectedClass->start_time ?? '-');
        @endphp

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesi Hari Ini</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $sessionLabel }}</div>
                        <div class="mt-1 text-sm text-slate-500">Total sesi terdata hari ini</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jadwal Aktif</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $activeStartTime }}</div>
                        <div class="mt-1 text-sm text-slate-500">Waktu yang dipakai untuk absen</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</div>
                        <div class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                            {{ $hasOpenSession ? 'Sedang Berjalan' : ($hasDraftSession ? 'Menunggu Check-in' : (($todayAttendance && $todayAttendance->check_out_at) ? 'Selesai' : 'Belum Mulai')) }}
                        </div>
                        <div class="mt-1 text-sm text-slate-500">Kondisi sesi terbaru</div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Absen Berangkat</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Lokasi wajib diambil dari browser dan waktu dicatat otomatis oleh server.</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 px-3 py-2 text-right text-xs font-medium text-emerald-700">
                            <div class="uppercase tracking-wide text-emerald-500">Mode</div>
                            <div class="mt-1 text-sm font-semibold text-emerald-700">Check-in realtime</div>
                        </div>
                    </div>

                    <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesi aktif</div>
                            <div class="mt-1 text-base font-semibold text-slate-900">{{ $sessionLabel }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jadwal aktif</div>
                            <div class="mt-1 text-base font-semibold text-slate-900">{{ $activeStartTime }}</div>
                        </div>
                    </div>

                    <form id="checkin-form" method="POST" action="{{ route('trainer.attendance.check-in') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                        <input type="hidden" name="check_in_latitude" id="check_in_latitude">
                        <input type="hidden" name="check_in_longitude" id="check_in_longitude">
                        <input type="hidden" name="check_in_accuracy" id="check_in_accuracy">

                        <button type="button" id="checkin-btn" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition {{ $hasOpenSession ? 'cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700' }}" {{ $hasOpenSession ? 'disabled' : '' }}>
                            <i class="fas fa-sign-in-alt mr-2"></i>{{ $hasOpenSession ? 'Sudah Berangkat untuk Sesi Ini' : 'Klik Absen Masuk' }}
                        </button>
                    </form>

                    @if($hasOpenSession)
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <span class="font-semibold">Berangkat tercatat:</span> {{ $currentAttendance->check_in_at->format('d M Y H:i:s') }}
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Absen Pulang</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Isi materi, jumlah siswa hadir, lalu tandai lokasi saat pulang.</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 px-3 py-2 text-right text-xs font-medium text-blue-700">
                            <div class="uppercase tracking-wide text-blue-500">Syarat</div>
                            <div class="mt-1 text-sm font-semibold text-blue-700">Wajib lengkap</div>
                        </div>
                    </div>

                    @php
                        $canCheckout = $hasOpenSession;
                    @endphp

                    <form id="checkout-form" method="POST" action="{{ route('trainer.attendance.check-out') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                        <input type="hidden" name="check_out_latitude" id="check_out_latitude">
                        <input type="hidden" name="check_out_longitude" id="check_out_longitude">
                        <input type="hidden" name="check_out_accuracy" id="check_out_accuracy">

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700">Materi yang Disampaikan</label>
                            <textarea name="material_covered" rows="4" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200" placeholder="Contoh: Pembahasan modul komunikasi efektif, roleplay, evaluasi akhir."></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700">Jumlah Siswa Hadir</label>
                            <input type="number" name="students_present" min="0" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200" placeholder="Contoh: 24">
                        </div>

                        <button type="button" id="checkout-btn" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition {{ $canCheckout ? 'bg-sky-600 hover:bg-sky-700' : 'cursor-not-allowed bg-slate-400' }}" {{ $canCheckout ? '' : 'disabled' }}>
                            <i class="fas fa-sign-out-alt mr-2"></i>{{ $todayAttendance && $todayAttendance->check_out_at ? 'Sudah Pulang Hari Ini' : 'Klik Absen Pulang' }}
                        </button>
                    </form>

                    @if($todayAttendance && $todayAttendance->check_out_at)
                        <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                            <span class="font-semibold">Pulang tercatat:</span> {{ $todayAttendance->check_out_at->format('d M Y H:i:s') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Sesi Tambahan</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Dipakai setelah sesi sebelumnya selesai dan akan membuka absensi sesi berikutnya.</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 px-3 py-2 text-right text-xs font-medium text-indigo-700">
                            <div class="uppercase tracking-wide text-indigo-500">Fungsi</div>
                            <div class="mt-1 text-sm font-semibold text-indigo-700">Tambah sesi</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('trainer.attendance.add-session') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition {{ $canAddSession ? 'bg-indigo-600 hover:bg-indigo-700' : 'cursor-not-allowed bg-slate-400' }}" {{ $canAddSession ? '' : 'disabled' }}>
                            <i class="fas fa-plus-circle mr-2"></i>Tambah Sesi
                        </button>
                    </form>

                    @if($hasDraftSession)
                        <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                            Sesi tambahan sudah dibuka dan menunggu absen masuk.
                        </div>
                    @elseif(!$canAddSession)
                        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            Sesi tambahan bisa dibuka setelah sesi sebelumnya ditutup.
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Riwayat Absensi</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        10 data terakhir
                        @if(($period ?? '') !== 'all')
                            - Bulan terpilih
                        @else
                            - Semua periode tahun {{ $year ?? date('Y') }}
                        @endif
                    </p>
                </div>
                <div class="text-sm text-slate-500">
                    {{ $recentAttendances->count() }} data ditampilkan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Pengajar</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Kelas</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Sesi</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Jam Mulai</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Materi</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Jumlah Siswa Hadir</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Jam Selesai</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Lokasi</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Akurasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentAttendances as $item)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->attendance_date ? $item->attendance_date->format('d M Y') : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ auth()->user()->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-700">
                                    <div class="font-medium text-slate-900">{{ $selectedClass->name ?? '-' }}</div>
                                    @if($selectedClass && $selectedClass->instansi)
                                        <div class="mt-1 text-xs text-slate-500">{{ $selectedClass->instansi }}</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-900">Sesi {{ $item->session_number ?? 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_in_at ? $item->check_in_at->format('H:i:s') : '-' }}</td>
                                <td class="max-w-md px-4 py-4 text-slate-700">{{ $item->material_covered ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->students_present ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_out_at ? $item->check_out_at->format('H:i:s') : '-' }}</td>
                                <td class="px-4 py-4 text-xs text-slate-700">
                                    @if($item->check_out_latitude && $item->check_out_longitude)
                                        <a class="font-medium text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $item->check_out_latitude }},{{ $item->check_out_longitude }}">
                                            Lihat Maps
                                        </a>
                                        <div class="mt-1 text-slate-500">{{ $item->check_out_latitude }}, {{ $item->check_out_longitude }}</div>
                                    @elseif($item->check_in_latitude && $item->check_in_longitude)
                                        <a class="font-medium text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $item->check_in_latitude }},{{ $item->check_in_longitude }}">
                                            Lihat Maps
                                        </a>
                                        <div class="mt-1 text-slate-500">{{ $item->check_in_latitude }}, {{ $item->check_in_longitude }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_out_accuracy !== null ? number_format((float) $item->check_out_accuracy, 0, ',', '.') : ($item->check_in_accuracy !== null ? number_format((float) $item->check_in_accuracy, 0, ',', '.') : '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-slate-500">Belum ada data absensi untuk kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
</div>

<script>
    function getCurrentLocation(onSuccess) {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung geolocation. Lokasi wajib untuk absensi.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                onSuccess(position.coords);
            },
            function(error) {
                let message = 'Gagal mengambil lokasi. Lokasi wajib untuk absensi.';
                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Izin lokasi ditolak. Mohon izinkan akses lokasi untuk absensi.';
                }
                alert(message);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            }
        );
    }

    const checkinBtn = document.getElementById('checkin-btn');
    if (checkinBtn) {
        checkinBtn.addEventListener('click', function() {
            getCurrentLocation(function(coords) {
                document.getElementById('check_in_latitude').value = coords.latitude;
                document.getElementById('check_in_longitude').value = coords.longitude;
                document.getElementById('check_in_accuracy').value = coords.accuracy || 0;
                document.getElementById('checkin-form').submit();
            });
        });
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            getCurrentLocation(function(coords) {
                document.getElementById('check_out_latitude').value = coords.latitude;
                document.getElementById('check_out_longitude').value = coords.longitude;
                document.getElementById('check_out_accuracy').value = coords.accuracy || 0;
                document.getElementById('checkout-form').submit();
            });
        });
    }
</script>
@endsection
