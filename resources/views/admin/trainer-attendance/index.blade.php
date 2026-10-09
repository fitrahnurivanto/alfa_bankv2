@extends('layouts.app')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">
        <!-- Header -->
        <div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#ffe9c2] border border-[#e28100] px-3 py-0.5 text-xs font-medium uppercase tracking-[0.15em] text-[#e28100]">Admin Monitoring</span>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-900">Absen Pengajar</h1>
            <p class="mt-1 text-base text-gray-800">Monitoring absensi pengajar untuk berangkat, pulang, lokasi, akurasi GPS, materi, dan siswa hadir.</p>
        </div>

        <!-- Filter -->
        <div class="rounded-lg bg-white p-5 shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
            <form method="GET" action="{{ route('admin.trainer-attendance.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-3">
                    <label class="mb-1 block text-xs font-medium uppercase text-gray-900">Kelas</label>
                    <select name="class_id" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#fe0000]">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string)($filters['class_id'] ?? '') === (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}{{ $class->instansi ? ' - ' . $class->instansi : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-1 block text-xs font-medium uppercase text-gray-900">Pengajar</label>
                    <select name="trainer_id" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#fe0000]">
                        <option value="">Semua Pengajar</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ (string)($filters['trainer_id'] ?? '') === (string)$trainer->id ? 'selected' : '' }}>
                                {{ $trainer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase text-gray-900">Periode</label>
                    <select name="period" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#fe0000]">
                        <option value="all" {{ ($filters['period'] ?? '') === 'all' ? 'selected' : '' }}>Semua Periode</option>
                        <option value="month_01" {{ ($filters['period'] ?? '') === 'month_01' ? 'selected' : '' }}>Januari</option>
                        <option value="month_02" {{ ($filters['period'] ?? '') === 'month_02' ? 'selected' : '' }}>Februari</option>
                        <option value="month_03" {{ ($filters['period'] ?? '') === 'month_03' ? 'selected' : '' }}>Maret</option>
                        <option value="month_04" {{ ($filters['period'] ?? '') === 'month_04' ? 'selected' : '' }}>April</option>
                        <option value="month_05" {{ ($filters['period'] ?? '') === 'month_05' ? 'selected' : '' }}>Mei</option>
                        <option value="month_06" {{ ($filters['period'] ?? '') === 'month_06' ? 'selected' : '' }}>Juni</option>
                        <option value="month_07" {{ ($filters['period'] ?? '') === 'month_07' ? 'selected' : '' }}>Juli</option>
                        <option value="month_08" {{ ($filters['period'] ?? '') === 'month_08' ? 'selected' : '' }}>Agustus</option>
                        <option value="month_09" {{ ($filters['period'] ?? '') === 'month_09' ? 'selected' : '' }}>September</option>
                        <option value="month_10" {{ ($filters['period'] ?? '') === 'month_10' ? 'selected' : '' }}>Oktober</option>
                        <option value="month_11" {{ ($filters['period'] ?? '') === 'month_11' ? 'selected' : '' }}>November</option>
                        <option value="month_12" {{ ($filters['period'] ?? '') === 'month_12' ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase text-gray-900">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-[#fe0000]">
                        @foreach(($years ?? collect([date('Y')])) as $year)
                            <option value="{{ $year }}" {{ (string)($filters['year'] ?? date('Y')) === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex flex-wrap gap-2 lg:justify-end">
                    <a href="{{ route('admin.trainer-attendance.export-excel', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg bg-[#13a100] px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#0f8000]">
                        <i class="far fa-file-lines mr-2"></i>Export Excel
                    </a>
                    <a href="{{ route('admin.trainer-attendance.index') }}" class="inline-flex items-center justify-center rounded-lg bg-[#e5e5e5] px-4 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-[#d4d4d4]">Reset</a>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="rounded-lg bg-white p-5 shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
            <h2 class="text-xl font-semibold text-gray-900">Tabel Log Aktivitas</h2>
            <p class="text-sm text-gray-800 mb-4">{{ $attendances->total() }} catatan pada hasil filter ini</p>

            <div class="overflow-x-auto rounded-lg shadow-[0_2px_6px_rgba(0,0,0,0.18)]">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-gray-300">
                        <tr class="text-xs font-medium uppercase text-gray-900">
                            <th class="whitespace-nowrap px-4 py-4 text-left">Tanggal &amp; Sesi</th>
                            <th class="whitespace-nowrap px-4 py-4 text-center">Pengajar &amp; Kelas</th>
                            <th class="whitespace-nowrap px-4 py-4 text-center">Jam (Mulai - Selesai)</th>
                            <th class="whitespace-nowrap px-4 py-4 text-center">Materi</th>
                            <th class="whitespace-nowrap px-4 py-4 text-center">Jumlah Siswa Hadir</th>
                            <th class="whitespace-nowrap px-4 py-4 text-center">Lokasi &amp; Validasi GPS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @forelse($attendanceGroups as $group)
                            @foreach($group->values() as $item)
                                @php
                                    $lat = $item->check_out_latitude ?: $item->check_in_latitude;
                                    $lng = $item->check_out_longitude ?: $item->check_in_longitude;
                                    $acc = $item->check_out_accuracy !== null ? $item->check_out_accuracy : $item->check_in_accuracy;
                                @endphp
                                <tr class="align-middle transition hover:bg-[#f9f0f1]">
                                    <td class="whitespace-nowrap px-4 py-5 text-gray-900">
                                        <div class="font-medium">{{ $item->attendance_date ? $item->attendance_date->format('d M Y') : '-' }}</div>
                                        <div class="text-gray-700">Sesi {{ $item->session_number ?? 1 }}</div>
                                    </td>

                                    <td class="px-4 py-5 text-center text-gray-900">
                                        <div class="font-medium">{{ $item->trainer->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-700">{{ $item->clas->name ?? '-' }}{{ $item->clas && $item->clas->instansi ? ' - ' . $item->clas->instansi : '' }}</div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-5 text-center text-gray-900">
                                        <div class="font-medium">{{ $item->check_in_at ? $item->check_in_at->format('H:i:s') : '-' }} - ({{ $item->check_out_at ? $item->check_out_at->format('H:i:s') : '--:--' }})</div>
                                        <div class="text-xs italic text-gray-800">{{ $item->check_out_at ? 'Selesai' : 'Belum Selesai' }}</div>
                                    </td>

                                    <td class="max-w-xs px-4 py-5 text-center text-xs text-gray-800">{{ $item->material_covered ?: '-' }}</td>

                                    <td class="whitespace-nowrap px-4 py-5 text-center font-medium text-gray-900">{{ $item->students_present ?? '-' }}</td>

                                    <td class="px-4 py-5 text-center text-sm text-gray-900">
                                        @if($lat && $lng)
                                            <a class="font-medium text-[#344bfd] hover:underline" target="_blank" href="https://maps.google.com/?q={{ $lat }},{{ $lng }}">Lihat Maps</a>
                                            <div>{{ $acc !== null ? 'Akurat (' . number_format((float)$acc, 0, ',', '.') . 'm)' : '' }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">Belum ada data absensi pengajar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection