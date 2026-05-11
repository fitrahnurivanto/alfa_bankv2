@extends('layouts.app')

@section('page-title', 'Detail Kelas Akademik')

@section('content')
<div class="p-1 md:p-2">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('akademik.classes.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $class->name }}</h1>
            <p class="text-gray-600">Detail Kelas Akademik</p>
        </div>
        <a href="{{ route('akademik.classes.edit', $class->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <span class="px-4 py-2 text-sm rounded-full {{ $class->status === 'done' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
            {{ strtoupper($class->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-purple-600 mr-2"></i>Informasi Kelas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Kategori</label>
                        <p class="text-gray-900">{{ $class->kategori->nama_kategori ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Program</label>
                        <p class="text-gray-900">{{ $class->training->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Instansi</label>
                        <p class="text-gray-900">{{ $class->instansi ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jumlah Absen</label>
                        <p class="text-gray-900">{{ $class->meet ?? 0 }} sesi</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                        <p class="text-gray-900">{{ $class->start_date ? $class->start_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jam Mulai Kelas</label>
                        <p class="text-gray-900">{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('H:i') : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Rekap Absensi Trainer (Toleransi Keterlambatan 5 Menit)</h3>
                            <p class="text-xs text-gray-500 mt-1">Data rekap sesuai filter periode yang dipilih. Terlambat jika check-in lebih dari 5 menit dari jam mulai kelas.</p>
                        </div>
                        <form method="GET" action="{{ route('akademik.classes.show', $class->id) }}" class="w-full md:w-auto grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <select name="period" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                                <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>Semua Periode</option>
                                <option value="month_01" {{ ($period ?? '') === 'month_01' ? 'selected' : '' }}>Januari</option>
                                <option value="month_02" {{ ($period ?? '') === 'month_02' ? 'selected' : '' }}>Februari</option>
                                <option value="month_03" {{ ($period ?? '') === 'month_03' ? 'selected' : '' }}>Maret</option>
                                <option value="month_04" {{ ($period ?? '') === 'month_04' ? 'selected' : '' }}>April</option>
                                <option value="month_05" {{ ($period ?? '') === 'month_05' ? 'selected' : '' }}>Mei</option>
                                <option value="month_06" {{ ($period ?? '') === 'month_06' ? 'selected' : '' }}>Juni</option>
                                <option value="month_07" {{ ($period ?? '') === 'month_07' ? 'selected' : '' }}>Juli</option>
                                <option value="month_08" {{ ($period ?? '') === 'month_08' ? 'selected' : '' }}>Agustus</option>
                                <option value="month_09" {{ ($period ?? '') === 'month_09' ? 'selected' : '' }}>September</option>
                                <option value="month_10" {{ ($period ?? '') === 'month_10' ? 'selected' : '' }}>Oktober</option>
                                <option value="month_11" {{ ($period ?? '') === 'month_11' ? 'selected' : '' }}>November</option>
                                <option value="month_12" {{ ($period ?? '') === 'month_12' ? 'selected' : '' }}>Desember</option>
                            </select>

                            <select name="year" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                                @foreach(($years ?? collect([date('Y')])) as $yearItem)
                                    <option value="{{ $yearItem }}" {{ (int)($year ?? date('Y')) === (int)$yearItem ? 'selected' : '' }}>{{ $yearItem }}</option>
                                @endforeach
                            </select>

                            <select name="trainer_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                                <option value="0" {{ (int)($selectedTrainerId ?? 0) === 0 ? 'selected' : '' }}>Semua Trainer</option>
                                @foreach($class->trainers as $trainer)
                                    <option value="{{ $trainer->id }}" {{ (int)($selectedTrainerId ?? 0) === (int)$trainer->id ? 'selected' : '' }}>{{ $trainer->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Trainer</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Hadir</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Terlambat</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Rata-rata Telat</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">% Kehadiran</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">% Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($trainerAttendanceRecap as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['trainer_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($row['attended_sessions'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['late_sessions'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ number_format($row['late_sessions'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold {{ ($row['avg_late_minutes'] ?? 0) > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ $row['avg_late_minutes'] ?? 0 }} mnt</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-purple-700">{{ $row['attendance_percentage'] }}%</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $row['late_percentage'] > 20 ? 'text-rose-700' : 'text-amber-700' }}">{{ $row['late_percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data absensi trainer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Log Absensi Terakhir</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Trainer</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Check-in</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Check-out</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Siswa Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($attendanceLogs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $log['attendance_date'] ? $log['attendance_date']->format('d M Y') : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $log['trainer_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $log['check_in_at'] ? $log['check_in_at']->format('H:i:s') : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $log['check_out_at'] ? $log['check_out_at']->format('H:i:s') : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($log['is_late'])
                                            <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">Terlambat {{ $log['late_minutes'] }} mnt</span>
                                        @else
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">On Time</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format((int) ($log['students_present'] ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada log absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-user-graduate text-emerald-600 mr-2"></i>Input Kelulusan (Akademik)
                </h3>

                <form action="{{ route('akademik.classes.update-graduation-summary', $class->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 font-semibold mb-1">Jumlah Siswa</label>
                        <input type="number" min="0" name="total_students" value="{{ old('total_students', $class->amount ?? 0) }}"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        @error('total_students')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 font-semibold mb-1">Jumlah Siswa Lulus</label>
                        <input type="number" min="0" name="passed_students" value="{{ old('passed_students', $class->passed_students ?? 0) }}"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        @error('passed_students')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 font-semibold mb-1">Jumlah Siswa Tidak Lulus</label>
                        <input type="number" min="0" name="failed_students" value="{{ old('failed_students', $class->failed_students ?? 0) }}"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        @error('failed_students')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-xs text-gray-500">Catatan: jumlah lulus + tidak lulus harus sama dengan jumlah siswa.</p>

                    <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Rekap Kelulusan
                    </button>
                </form>
            </div>

            <!-- Honor Payment Status -->
            @if(($class->status === 'done' || $class->status === 'approved') && in_array(auth()->user()->role, ['admin', 'akademik', 'marketing', 'finance']))
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
            @endif

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-2">Info Update Terakhir</h3>
                <p class="text-sm text-gray-600">Penginput terakhir:
                    <span class="font-semibold text-gray-900">{{ $class->passFailUpdatedBy->name ?? '-' }}</span>
                </p>
                <p class="text-sm text-gray-600 mt-1">Waktu update:
                    <span class="font-semibold text-gray-900">{{ $class->pass_fail_updated_at ? $class->pass_fail_updated_at->format('d M Y H:i') : '-' }}</span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
