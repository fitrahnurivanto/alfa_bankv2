<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Clas;
use App\Models\TrainerAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // System is Training-only. Keep activeDivision for backward compatibility with view.
        $activeDivision = 'training';

        // Get filter values
        $period = $request->get('period', 'month_' . date('m'));
        $year = $request->get('year', date('Y'));
        $filters = [
            'year' => $request->get('year', date('Y')),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'period' => $period,
        ];

        // If period filter is used, auto-set start_date & end_date
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m) && empty($filters['start_date'])) {
            $month = (int) $m[1];
            $filterYear = (int) $year;
            $startDate = \Carbon\Carbon::create($filterYear, $month, 1)->startOfMonth();
            $endDate = \Carbon\Carbon::create($filterYear, $month, 1)->endOfMonth();
            $filters['start_date'] = $startDate->format('Y-m-d');
            $filters['end_date'] = $endDate->format('Y-m-d');
        }

        // Get all years for training classes filter dropdown
        $years = \App\Models\Clas::selectRaw('YEAR(start_date) as year')
            ->whereNotNull('start_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('admin.laporan.index', compact('years', 'activeDivision', 'filters', 'period', 'year'));
    }

    public function trainerAttendanceIndex(Request $request)
    {
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));

        $filters = [
            'class_id' => $request->get('class_id'),
            'trainer_id' => $request->get('trainer_id'),
            'period' => $period,
            'year' => $year,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ];

        $query = TrainerAttendance::query()->with(['trainer:id,name', 'clas:id,name,instansi']);

        if (!empty($filters['class_id'])) {
            $query->where('clas_id', $filters['class_id']);
        }
        if (!empty($filters['trainer_id'])) {
            $query->where('trainer_id', $filters['trainer_id']);
        }

        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            $query->whereBetween('attendance_date', [$start, $end]);
        } else {
            $query->whereYear('attendance_date', $year);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['end_date']);
        }

        $attendances = $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('clas_id')
            ->orderByDesc('trainer_id')
            ->orderByDesc('check_in_at')
            ->paginate(30)
            ->withQueryString();

        $attendanceGroups = $attendances->getCollection()->groupBy(function ($item) {
            $dateKey = $item->attendance_date ? $item->attendance_date->format('Y-m-d') : 'unknown-date';

            return $dateKey . '|' . ($item->clas_id ?? '0') . '|' . ($item->trainer_id ?? '0');
        })->values();

        $classes = Clas::query()
            ->whereHas('trainerAttendances')
            ->orderBy('name')
            ->get(['id', 'name', 'instansi']);

        $trainers = User::query()
            ->where('role', 'trainer')
            ->whereHas('trainerAttendances')
            ->orderBy('name')
            ->get(['id', 'name']);

        $years = TrainerAttendance::query()
            ->selectRaw('YEAR(attendance_date) as year')
            ->whereNotNull('attendance_date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        } elseif (!$years->contains($year)) {
            $years = $years->push($year)->unique()->sortDesc()->values();
        }

        return view('admin.trainer-attendance.index', compact('attendances', 'attendanceGroups', 'classes', 'trainers', 'filters', 'years'));
    }

    public function exportTrainerAttendanceExcel(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:clas,id'],
            'trainer_id' => ['nullable', 'integer', 'exists:users,id'],
            'period' => ['nullable', 'in:all,month_01,month_02,month_03,month_04,month_05,month_06,month_07,month_08,month_09,month_10,month_11,month_12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $period = $validated['period'] ?? ('month_' . date('m'));
        $year = (int) ($validated['year'] ?? date('Y'));

        $query = TrainerAttendance::query()
            ->with(['trainer', 'clas']);

        if (!empty($validated['class_id'])) {
            $query->where('clas_id', $validated['class_id']);
        }
        if (!empty($validated['trainer_id'])) {
            $query->where('trainer_id', $validated['trainer_id']);
        }

        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            $query->whereBetween('attendance_date', [$start, $end]);
        } else {
            $query->whereYear('attendance_date', $year);
        }

        if (!empty($validated['start_date'])) {
            $query->whereDate('attendance_date', '>=', $validated['start_date']);
        }
        if (!empty($validated['end_date'])) {
            $query->whereDate('attendance_date', '<=', $validated['end_date']);
        }

        $attendances = $query->orderByDesc('attendance_date')->orderByDesc('check_in_at')->get();

        $classLabel = 'Semua Kelas';
        if (!empty($validated['class_id'])) {
            $selectedClass = Clas::find($validated['class_id']);
            if ($selectedClass) {
                $classLabel = $selectedClass->name . ($selectedClass->instansi ? ' - ' . $selectedClass->instansi : '');
            }
        }

        $trainerLabel = 'Semua Pengajar';
        if (!empty($validated['trainer_id'])) {
            $selectedTrainer = User::find($validated['trainer_id']);
            if ($selectedTrainer) {
                $trainerLabel = $selectedTrainer->name;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LAPORAN ABSENSI PENGAJAR');
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A2', 'Kelas: ' . $classLabel);
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A3', 'Pengajar: ' . $trainerLabel);
        $sheet->mergeCells('A3:O3');
        $sheet->setCellValue('A4', 'Dicetak: ' . now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A4:O4');

        $headers = [
            'Tanggal',
            'Sesi',
            'Kelas',
            'Nama Pengajar',
            'Jadwal Awal',
            'Jam Geser',
            'Keterangan Geser',
            'Jam Berangkat',
            'Lokasi Berangkat',
            'Akurasi Berangkat (m)',
            'Jam Pulang',
            'Lokasi Pulang',
            'Akurasi Pulang (m)',
            'Materi Disampaikan',
            'Jumlah Siswa Hadir',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        $sheet->getStyle('A6:K6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0EA5E9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 7;
        foreach ($attendances as $item) {
            $sheet->setCellValue('A' . $row, $item->attendance_date ? $item->attendance_date->format('d/m/Y') : '-');
            $sheet->setCellValue('B' . $row, 'Sesi ' . ($item->session_number ?? 1));
            $sheet->setCellValue('C' . $row, ($item->clas->name ?? '-') . ($item->clas && $item->clas->instansi ? ' - ' . $item->clas->instansi : ''));
            $sheet->setCellValue('D' . $row, $item->trainer->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->planned_start_time ?: '-');
            $sheet->setCellValue('F' . $row, $item->shifted_start_time ?: '-');
            $sheet->setCellValue('G' . $row, $item->shift_reason ?: '-');
            $sheet->setCellValue('H' . $row, $item->check_in_at ? $item->check_in_at->format('H:i:s') : '-');
            $sheet->setCellValue('I' . $row, $this->formatLocationLabel(
                $item->check_in_latitude,
                $item->check_in_longitude,
                null
            ));
            $sheet->setCellValue('J' . $row, $item->check_in_accuracy !== null ? (float) $item->check_in_accuracy : '-');
            $sheet->setCellValue('K' . $row, $item->check_out_at ? $item->check_out_at->format('H:i:s') : '-');
            $sheet->setCellValue('L' . $row, $this->formatLocationLabel(
                $item->check_out_latitude,
                $item->check_out_longitude,
                null
            ));
            $sheet->setCellValue('M' . $row, $item->check_out_accuracy !== null ? (float) $item->check_out_accuracy : '-');
            $sheet->setCellValue('N' . $row, $item->material_covered ?: '-');
            $sheet->setCellValue('O' . $row, $item->students_present ?? '-');
            $row++;
        }

        $lastRow = max($row - 1, 7);
        $sheet->getStyle('A6:O' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'laporan_absensi_pengajar_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function formatLocationLabel($lat, $lng, $accuracy): string
    {
        if ($lat === null || $lng === null) {
            return '-';
        }

        $label = (string) $lat . ', ' . (string) $lng;
        if ($accuracy !== null) {
            $label .= ' (akurasi ±' . (int) round((float) $accuracy) . 'm)';
        }

        return $label;
    }
    
    public function exportCsv(Request $request)
    {
        // System is Training-only.
        return $this->exportAcademyClasses($request);
        
        // For Agency, export projects data
        $query = Project::with([
            'client', 
            'order.orderItems.service', 
            'order.orderItems.servicePackage',
            'teams.members.user'  // Load team members to get PIC
        ])->join('orders', 'projects.order_id', '=', 'orders.id');
        
        // Filter by division
        $query->whereHas('order.orderItems.service.category', function($q) use ($activeDivision) {
            $q->where('division', $activeDivision);
        });
        
        // Apply filters - if start_date and end_date exist, ignore year filter (to avoid conflicts)
        $hasDateRange = $request->filled('start_date') && $request->filled('end_date');
        
        if ($hasDateRange) {
            // Use date range only - check both order_date and project start_date
            $query->where(function($q) use ($request) {
                $q->whereBetween('projects.start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('orders.order_date', [$request->start_date, $request->end_date]);
            });
        } else {
            // Apply year filter if no date range
            if ($request->filled('year')) {
                $query->where(function($q) use ($request) {
                    $q->whereYear('orders.order_date', $request->year)
                      ->orWhereYear('projects.start_date', $request->year);
                });
            }
            // Apply individual date filters
            if ($request->filled('start_date')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('orders.order_date', '>=', $request->start_date)
                      ->orWhereDate('projects.start_date', '>=', $request->start_date);
                });
            }
            if ($request->filled('end_date')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('orders.order_date', '<=', $request->end_date)
                      ->orWhereDate('projects.start_date', '<=', $request->end_date);
                });
            }
        }
        
        $projects = $query->select('projects.*')->orderBy('orders.order_date', 'asc')->get();
        
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Management System')
            ->setTitle('Laporan Project')
            ->setSubject('Laporan Project')
            ->setDescription('Export data project dari management system');
        
        // Header row
        $headers = [
            'Timestamp',
            'Nama Client',
            'No. Telp',
            'Nama Usaha',
            'Alamat E-mail',
            'Alamat',
            'Jenis Project',
            'Nama Project',
            'Paket',
            'Tanggal Mulai Kontrak',
            'Waktu Kontrak',
            'Tanggal Berakhir Kontrak',
            'Nilai Kontrak',
            'Penanggungjawab Pekerjaan',
            'Sumber Referensi/Informasi',
            'Status',
            'Keterangan',
            'Telah Dibayar',
            'Kekurangan',
            'Keterangan Pembayaran'
        ];
        
        // Write header
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Style header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);
        
        // Data rows
        $row = 2;
        foreach ($projects as $project) {
            $client = $project->client;
            $order = $project->order;
            
            // Get service/package name
            $jenisProject = '';
            $namaProject = '';
            $paketName = '-';
            if ($order && $order->orderItems->count() > 0) {
                $firstItem = $order->orderItems->first();
                if ($firstItem->service) {
                    $jenisProject = $firstItem->service->category->name ?? '';
                    $namaProject = $firstItem->service->name;
                    if ($firstItem->servicePackage) {
                        $paketName = $firstItem->servicePackage->name;
                    }
                }
            }
            
            // Calculate duration if empty
            $duration = $project->duration;
            if (empty($duration) && $project->start_date && $project->end_date) {
                $start = \Carbon\Carbon::parse($project->start_date);
                $end = \Carbon\Carbon::parse($project->end_date);
                $diffInDays = $start->diffInDays($end);
                
                if ($diffInDays < 7) {
                    // Kurang dari 1 minggu = tampilkan hari
                    $duration = $diffInDays . ' Hari';
                } elseif ($diffInDays == 7) {
                    $duration = '1 Minggu';
                } elseif ($diffInDays == 14) {
                    $duration = '2 Minggu';
                } elseif ($diffInDays < 30) {
                    // 1-4 minggu
                    $weeks = floor($diffInDays / 7);
                    $duration = $weeks . ' Minggu';
                } else {
                    // 30 hari atau lebih = hitung bulan
                    $months = floor($diffInDays / 30);
                    $remainingDays = $diffInDays % 30;
                    
                    if ($months == 1) {
                        $duration = $remainingDays > 0 ? "1 Bulan {$remainingDays} Hari" : '1 Bulan';
                    } else {
                        $duration = $remainingDays > 0 ? "{$months} Bulan {$remainingDays} Hari" : "{$months} Bulan";
                    }
                }
            }
            
            // Calculate kekurangan (sisa pembayaran)
            $nilaiKontrak = $order ? $order->total_amount : 0;
            $telahDibayar = $order ? $order->paid_amount : 0;
            $kekurangan = $nilaiKontrak - $telahDibayar;
            
            // Status keterangan
            $statusKeterangan = '';
            if ($order) {
                switch ($order->payment_status) {
                    case 'paid':
                        $statusKeterangan = 'Lunas';
                        break;
                    case 'refunded':
                        $statusKeterangan = 'Refund';
                        break;
                    case 'pending':
                    case 'pending_review':
                        $statusKeterangan = $kekurangan > 0 ? 'Kurang' : 'Lunas';
                        break;
                    default:
                        $statusKeterangan = ucfirst($order->payment_status);
                }
            }
            
            // Get PIC (Penanggung Jawab) from team members
            $picName = '-';
            foreach($project->teams as $team) {
                foreach($team->members as $member) {
                    if(strtolower($member->role) === 'pic' && $member->user) {
                        $picName = $member->user->name;
                        break 2;
                    }
                }
            }
            
            // Timestamp - use order_date if available, otherwise use project created_at
            $timestamp = '';
            if ($order && $order->order_date) {
                $timestamp = \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i:s');
            } elseif ($project->created_at) {
                $timestamp = \Carbon\Carbon::parse($project->created_at)->format('d/m/Y H:i:s');
            }
            
            // Status mapping - TRUE/FALSE untuk completed
            $statusBoolean = $project->status === 'completed' ? 'TRUE' : 'FALSE';
            
            // Keterangan - status dalam bahasa yang lebih jelas
            $keteranganStatus = match($project->status) {
                'completed' => 'Done',
                'in_progress' => 'In Progress',
                'pending' => 'Pending',
                'cancelled' => 'Cancelled',
                default => ucfirst($project->status)
            };
            
            $rowData = [
                $timestamp,
                $client ? $client->name : '',
                $client ? $client->phone : '',
                $client ? $client->company_name : '',
                $client ? $client->email : '',
                $client ? $client->address : '',
                $jenisProject,
                $namaProject,
                $paketName,
                $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d F Y') : '',
                $duration ?? '-',
                $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d F Y') : '',
                $nilaiKontrak,
                $picName,
                $client ? ($client->referral_source ?? '-') : '-',
                $statusBoolean,
                $keteranganStatus,
                $telahDibayar > 0 ? $telahDibayar : 0,
                $kekurangan > 0 ? $kekurangan : 0,
                $statusKeterangan
            ];
            
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            
            // Apply row color based on project status
            $rowColor = match($project->status) {
                'completed' => 'D4EDDA',    // Light green
                'in_progress' => 'D1ECF1',  // Light blue
                'pending' => 'FFF3CD',      // Light yellow
                'cancelled' => 'F8D7DA',    // Light red
                default => 'FFFFFF'         // White
            };
            
            $sheet->getStyle('A' . $row . ':U' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $rowColor]
                ]
            ]);
            
            $row++;
        }
        
        // Format currency columns (N, S, T = Nilai Kontrak, Telah Dibayar, Kekurangan)
        $lastRow = $row - 1;
        $sheet->getStyle('N2:N' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('S2:S' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('T2:T' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        
        // Auto-size all columns
        foreach (range('A', 'U') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Add borders to all data
        $sheet->getStyle('A1:U' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        // Generate filename
        $filename = 'laporan_project_' . date('Y-m-d_His') . '.xlsx';
        
        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        
        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export Training Classes to Excel
     */
    private function exportAcademyClasses(Request $request)
    {
        $query = \App\Models\Clas::with(['kategori', 'trainers']);
        
        // Apply filters - if start_date and end_date exist, ignore year filter
        $hasDateRange = $request->filled('start_date') && $request->filled('end_date');
        
        if ($hasDateRange) {
            // Use date range only
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } else {
            // Apply year filter if no date range
            if ($request->filled('year')) {
                $query->whereYear('start_date', $request->year);
            }
            // Apply individual date filters
            if ($request->filled('start_date')) {
                $query->whereDate('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('start_date', '<=', $request->end_date);
            }
        }
        
        $classes = $query->orderBy('start_date', 'desc')->get();
        
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Management System')
            ->setTitle('Laporan Pelatihan')
            ->setSubject('Laporan Pelatihan')
            ->setDescription('Export data kelas pelatihan dari management system');
        
        // Header row for Training
        $headers = [
            'Nama Kelas',
            'Instansi',
            'Kategori',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (JPL)',
            'Jumlah Pertemuan',
            'Metode',
            'Jumlah Peserta',
            'Nilai Kelas',
            'Harga per Peserta',
            'Total Pendapatan Kotor',
            'Biaya Operasional',
            'Honor Trainer',
            'Profit (Income Bersih)',
            'Type Pembayaran',
            'DP/Termin 1',
            'Sisa Pembayaran',
            'Trainer',
            'Status',
            'Deskripsi'
        ];
        
        // Write header
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Style header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'] // Green for Training
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:U1')->applyFromArray($headerStyle);
        
        // Data rows
        $row = 2;
        foreach ($classes as $class) {
            // Get duration in JPL from database
            $duration = '-';
            if ($class->duration) {
                $duration = $class->duration . ' jpl';
            }
            
            // Calculate revenue correctly based on kategori
            $amount = $class->amount ?? 0;
            $pricePerStudent = $class->price ?? 0;
            $cost = $class->cost ?? 0;
            $trainerHonor = $class->trainer_honor ?? 0;
            
            // Check if corporate training (kategori_id 1 or 3 = corporate)
            $kategoriId = $class->kategori_id ?? 0;
            $isCorporate = in_array($kategoriId, [1, 3]);
            
            // Nilai Kelas (Total class value)
            // Price is now total revenue for all categories (includes all students with their discounts)
            $nilaiKelas = $pricePerStudent;
            
            // Total Pendapatan Kotor = Nilai Kelas
            $totalRevenueGross = $nilaiKelas;
            
            // Profit (Income Bersih) = Total Pendapatan Kotor - Biaya Operasional - Honor Trainer
            $profit = $totalRevenueGross - $cost - $trainerHonor;
            
            // Payment type and paid amount
            $paymentType = '-';
            $paidAmount = 0;
            $remainingAmount = 0;
            
            if ($class->payment_type) {
                $paymentType = $class->payment_type === 'full' ? 'Full Payment' : 'Termin 2x';
                
                if ($class->payment_type === 'termin_2x') {
                    $paidAmount = $class->paid_amount ?? 0;
                    $totalContract = $totalRevenueGross;
                    $remainingAmount = max(0, $totalContract - $paidAmount);
                }
            }
            
            // Get trainer names from relationship
            $trainerNames = '-';
            if ($class->trainers && $class->trainers->count() > 0) {
                $trainerNames = $class->trainers->pluck('name')->join(', ');
            }
            
            // Status mapping
            $statusLabel = match($class->status) {
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'completed' => 'Selesai',
                default => ucfirst($class->status)
            };
            
            $rowData = [
                $class->name,
                $class->instansi ?? '-',
                $class->kategori ? $class->kategori->nama_kategori : '-',
                $class->start_date ? $class->start_date->format('d F Y') : '-',
                $class->end_date ? $class->end_date->format('d F Y') : '-',
                $duration,
                ($class->meet ?? 0) . 'x',
                $class->method ?? '-',
                $amount,
                $nilaiKelas,
                $pricePerStudent,
                $totalRevenueGross,
                $cost,
                $trainerHonor,
                $profit,
                $paymentType,
                $class->payment_type === 'termin_2x' ? $paidAmount : '-',
                $class->payment_type === 'termin_2x' ? $remainingAmount : '-',
                $trainerNames,
                $statusLabel,
                $class->description ?? '-'
            ];
            
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            
            $row++;
        }
        
        // Format currency columns (J, K, L, M, N, O, Q, R = Nilai Kelas, Harga, Total Pendapatan, Biaya, Honor, Profit, DP, Sisa)
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('J2:J' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('K2:K' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('M2:M' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('N2:N' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('O2:O' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('Q2:Q' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('R2:R' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        }
        
        // Auto-size all columns
        foreach (range('A', 'U') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Add borders to all data
        if ($lastRow >= 1) {
            $sheet->getStyle('A1:U' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }

        // Certification summary table (separate section below main table)
        $certificationClasses = $classes->filter(function ($class) {
            return (bool) $class->sertifikasi_bnsp;
        });

        $certTitleRow = max($lastRow + 3, 5);
        $certHeaderRow = $certTitleRow + 1;
        $certDataRow = $certHeaderRow + 1;

        // Section title
        $sheet->mergeCells('A' . $certTitleRow . ':F' . $certTitleRow);
        $sheet->setCellValue('A' . $certTitleRow, 'RINGKASAN SERTIFIKASI BNSP');
        $sheet->getStyle('A' . $certTitleRow . ':F' . $certTitleRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Section headers
        $certHeaders = [
            'Nama Kelas',
            'Tanggal Sertifikasi',
            'Jumlah Siswa Sertifikasi',
            'Fee per Siswa',
            'Omset Sertifikasi',
            'Status',
        ];

        $col = 'A';
        foreach ($certHeaders as $header) {
            $sheet->setCellValue($col . $certHeaderRow, $header);
            $col++;
        }

        $sheet->getStyle('A' . $certHeaderRow . ':F' . $certHeaderRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $currentRow = $certDataRow;
        $totalCertificationStudents = 0;
        $totalCertificationRevenue = 0;

        if ($certificationClasses->isEmpty()) {
            $sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'Tidak ada data sertifikasi pada filter periode ini.');
            $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;
        } else {
            foreach ($certificationClasses as $class) {
                $studentCount = (int) ($class->bnsp_student_count ?? 0);
                $feePerStudent = (float) ($class->bnsp_fee_per_student ?? 0);
                $certificationOmzet = $studentCount * $feePerStudent;

                $totalCertificationStudents += $studentCount;
                $totalCertificationRevenue += $certificationOmzet;

                $sheet->setCellValue('A' . $currentRow, $class->name);
                $sheet->setCellValue('B' . $currentRow, $class->bnsp_tanggal_sertifikasi ? $class->bnsp_tanggal_sertifikasi->format('d F Y') : '-');
                $sheet->setCellValue('C' . $currentRow, $studentCount);
                $sheet->setCellValue('D' . $currentRow, $feePerStudent);
                $sheet->setCellValue('E' . $currentRow, $certificationOmzet);
                $sheet->setCellValue('F' . $currentRow, ucfirst($class->status ?? '-'));
                $currentRow++;
            }

            // Totals rows
            $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'Total Siswa Sertifikasi');
            $sheet->setCellValue('E' . $currentRow, $totalCertificationStudents);
            $sheet->setCellValue('F' . $currentRow, 'siswa');
            $currentRow++;

            $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'Total Omset Sertifikasi');
            $sheet->setCellValue('E' . $currentRow, $totalCertificationRevenue);
            $sheet->setCellValue('F' . $currentRow, '-');

            $sheet->getStyle('A' . ($currentRow - 1) . ':F' . $currentRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ECFDF5'],
                ],
            ]);
        }

        $certLastRow = max($currentRow, $certDataRow);

        // Currency format for certification section
        if ($certLastRow >= $certDataRow) {
            $sheet->getStyle('D' . $certDataRow . ':D' . $certLastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $sheet->getStyle('E' . $certDataRow . ':E' . $certLastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        }

        // Borders for certification section
        $sheet->getStyle('A' . $certHeaderRow . ':F' . $certLastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        // Generate filename
        $filename = 'laporan_pelatihan_' . date('Y-m-d_His') . '.xlsx';
        
        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        
        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
