@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/60 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex w-fit items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Admin Monitoring</span>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Absen Pengajar</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Monitoring absensi pengajar untuk berangkat, pulang, lokasi, akurasi GPS, materi, dan siswa hadir.</p>
                </div>
                <div class="inline-flex items-center rounded-xl bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600">
                    <i class="fas fa-clipboard-list mr-2 text-sky-600"></i>
                    {{ $attendances->total() }} catatan pada hasil filter ini
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('admin.trainer-attendance.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kelas</label>
                    <select name="class_id" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string)($filters['class_id'] ?? '') === (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}{{ $class->instansi ? ' - ' . $class->instansi : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Pengajar</label>
                    <select name="trainer_id" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
                        <option value="">Semua Pengajar</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ (string)($filters['trainer_id'] ?? '') === (string)$trainer->id ? 'selected' : '' }}>
                                {{ $trainer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                    <select name="period" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
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
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
                        @foreach(($years ?? collect([date('Y')])) as $year)
                            <option value="{{ $year }}" {{ (string)($filters['year'] ?? date('Y')) === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex flex-wrap gap-2 lg:justify-end">
                    <a href="{{ route('admin.trainer-attendance.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Reset</a>
                    <a href="{{ route('admin.trainer-attendance.export-excel', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                        @forelse($attendanceGroups as $group)
                            @php
                                $groupRows = $group->values();
                                $rowCount = $groupRows->count();
                                $firstRow = $groupRows->first();
                            @endphp

                            @foreach($groupRows as $index => $item)
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    @if($index === 0)
                                        <td class="whitespace-nowrap px-4 py-4 font-medium text-slate-700" rowspan="{{ $rowCount }}">
                                            <div class="flex flex-col">
                                                <span>{{ $item->attendance_date ? $item->attendance_date->format('d M Y') : '-' }}</span>
                                                <span class="mt-1 inline-flex w-fit rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $rowCount > 1 ? $rowCount . ' sesi' : '1 sesi' }}</span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 text-slate-700" rowspan="{{ $rowCount }}">
                                            <div class="font-medium text-slate-900">{{ $item->trainer->name ?? '-' }}</div>
                                        </td>

                                        <td class="px-4 py-4 text-slate-700" rowspan="{{ $rowCount }}">
                                            <div class="font-medium text-slate-900">{{ $item->clas->name ?? '-' }}</div>
                                            @if($item->clas && $item->clas->instansi)
                                                <div class="mt-1 text-xs text-slate-500">{{ $item->clas->instansi }}</div>
                                            @endif
                                        </td>
                                    @endif

                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-900">Sesi {{ $item->session_number ?? 1 }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_in_at ? $item->check_in_at->format('H:i:s') : '-' }}</td>
                                    <td class="max-w-xs px-4 py-4 text-slate-700">{{ $item->material_covered ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->students_present ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_out_at ? $item->check_out_at->format('H:i:s') : '-' }}</td>
                                    <td class="px-4 py-4 text-xs text-slate-700">
                                        @if($item->check_out_latitude && $item->check_out_longitude)
                                            <a class="font-medium text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $item->check_out_latitude }},{{ $item->check_out_longitude }}">Lihat Maps</a>
                                            <div class="mt-1 text-slate-500">{{ $item->check_out_latitude }}, {{ $item->check_out_longitude }}</div>
                                        @elseif($item->check_in_latitude && $item->check_in_longitude)
                                            <a class="font-medium text-blue-600 hover:underline" target="_blank" href="https://maps.google.com/?q={{ $item->check_in_latitude }},{{ $item->check_in_longitude }}">Lihat Maps</a>
                                            <div class="mt-1 text-slate-500">{{ $item->check_in_latitude }}, {{ $item->check_in_longitude }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ $item->check_out_accuracy !== null ? number_format((float)$item->check_out_accuracy, 0, ',', '.') : ($item->check_in_accuracy !== null ? number_format((float)$item->check_in_accuracy, 0, ',', '.') : '-') }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-slate-500">Belum ada data absensi pengajar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
