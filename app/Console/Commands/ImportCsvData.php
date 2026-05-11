<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Order;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportCsvData extends Command
{
    protected $signature = 'import:csv {file}';
    protected $description = 'Import data dari CSV dan replace semua data yang ada';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return 1;
        }

        $this->info('Memulai import data dari CSV...');
        
        // 1. Hapus semua data lama
        $this->info('Menghapus data lama...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Project::truncate();
        Order::truncate();
        Client::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('✓ Data lama berhasil dihapus');

        // 2. Baca CSV
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file, 0, ';'); // Header
        
        $imported = 0;
        $errors = 0;
        $line = 1;

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $line++;
            
            // Skip baris kosong atau baris tahun (2024, 2025)
            if (empty($row[2]) || is_numeric($row[0])) {
                continue;
            }

            try {
                $data = array_combine($header, $row);
                // Fix kolom duplikat "Keterangan" - index 17 adalah status project
                $data['Status Keterangan'] = $row[17] ?? '';
                $this->importRow($data);
                $imported++;
                
                if ($imported % 10 == 0) {
                    $this->info("Progress: {$imported} data berhasil diimport...");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error di baris {$line}: " . $e->getMessage());
            }
        }

        fclose($file);

        $this->info("\n=== Import Selesai ===");
        $this->info("✓ Berhasil: {$imported} data");
        if ($errors > 0) {
            $this->warn("✗ Error: {$errors} data");
        }

        return 0;
    }

    private function importRow($data)
    {
        // 1. Buat/Update Client
        $client = Client::updateOrCreate(
            [
                'name' => $data['Nama Client'],
                'phone' => $this->cleanPhone($data['No. Telp']),
            ],
            [
                'company_name' => $data['Nama Usaha'] ?: null,
                'business_type' => $data['Jenis Usaha'] ?: null,
                'email' => $data['Alamat E-mail'] && $data['Alamat E-mail'] != '-' ? $data['Alamat E-mail'] : null,
                'address' => $data['Alamat '] && $data['Alamat '] != '-' ? $data['Alamat '] : null,
                'referral_source' => $data['Sumber Referensi/Informasi'] && $data['Sumber Referensi/Informasi'] != '-' ? $data['Sumber Referensi/Informasi'] : null,
            ]
        );

        // 2. Get Service berdasarkan Jenis Project
        $serviceAndPackage = $this->getServiceAndPackage($data['Jenis Project'], $data['Nama Project']);
        $service = $serviceAndPackage['service'];
        $package = $serviceAndPackage['package'];

        // 3. Parse nilai kontrak
        $totalAmount = $this->parseRupiah($data['Nilai Kontrak']);
        $paidAmount = $this->parseRupiah($data['Telah Dibayar']);
        $remainingAmount = $this->parseRupiah($data['Kekurangan']);

        // 4. Tentukan payment status
        $paymentStatus = 'pending';
        $statusKeterangan = $data['Status Keterangan'] ?? ''; // Sudah di-set di handle() dari $row[17]
        $paymentNotes = $data['Keterangan'] ?? ''; // Index 20 untuk payment notes
        
        // Cek kalau ada keterangan refund di Status Keterangan - priority pertama
        if (stripos($statusKeterangan, 'refund') !== false) {
            $paymentStatus = 'refunded';
            $paidAmount = 0; // Uang dikembalikan, reset paid amount
            $remainingAmount = $totalAmount; // Sisa jadi full amount
        } elseif (stripos($statusKeterangan, 'FAILED') !== false || stripos($paymentNotes, 'batal') !== false) {
            $paymentStatus = 'failed';
        } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
            // Sudah lunas penuh
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0 && $paidAmount < $totalAmount) {
            // Sudah bayar DP/sebagian, status paid dengan sisa outstanding
            $paymentStatus = 'paid';
        }

        // 5. Parse timestamp dari CSV
        $orderTimestamp = $this->parseDate($data['Timestamp']);
        
        // Debug: cek hasil parsing
        if (!$orderTimestamp || $orderTimestamp === null) {
            $this->warn("WARNING: Timestamp '{$data['Timestamp']}' gagal di-parse! Using now()");
            $orderTimestamp = now();
        }

        // 6. Buat Order dengan timestamp yang tepat
        $order = Order::create([
            'client_id' => $client->id,
            'order_number' => $this->generateOrderNumber($data['Timestamp']),
            'pks_number' => $data['Nomor PKS'] ?: null,
            'order_date' => $orderTimestamp,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => 'Transfer',
            'payment_type' => $paidAmount >= $totalAmount ? 'full' : 'installment',
            'payment_notes' => $data['Keterangan'] ?? null,
            'notes' => $package ? $package->name : $data['Nama Project'], // Paket name atau Nama Project
            'created_at' => $orderTimestamp,
            'updated_at' => $orderTimestamp,
        ]);

        // 6b. Buat OrderItem untuk service yang dipilih
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => $totalAmount,
            'subtotal' => $totalAmount,
            'created_at' => $orderTimestamp,
            'updated_at' => $orderTimestamp,
        ]);

        // 7. Parse status project
        $statusBool = strtoupper(trim($data['Status'] ?? '')) === 'TRUE';
        $statusText = trim($data['Status Keterangan'] ?? ''); // Kolom index 17 (bukan "Keterangan" index 20)
        
        $projectStatus = 'pending';
        
        // Check keterangan first untuk status spesifik
        if (stripos($statusText, 'On Proses') !== false) {
            $projectStatus = 'in_progress';
        } elseif (stripos($statusText, 'Stack') !== false) {
            $projectStatus = 'pending';
        } elseif (stripos($statusText, 'FAILED') !== false || stripos($statusText, 'Batal') !== false) {
            $projectStatus = 'cancelled';
        } elseif ($statusBool && (stripos($statusText, 'Done') !== false || stripos($statusText, 'Lunas') !== false)) {
            $projectStatus = 'completed';
        } elseif ($statusBool) {
            $projectStatus = 'completed';
        }

        // 8. Parse tanggal project dari CSV
        $projectStartDate = $this->parseDate($data['Tanggal Mulai Kontrak']);
        $projectEndDate = $this->parseDate($data['Tanggal Berakhir Kontrak']);

        // 9. Buat Project dengan timestamp dari CSV
        $project = Project::create([
            'order_id' => $order->id,
            'client_id' => $client->id,
            'project_name' => $data['Nama Project'],
            'project_code' => 'PRJ-' . date('Ymd', strtotime($orderTimestamp)) . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
            'description' => $data['Nama Project'],
            'status' => $projectStatus,
            'status_notes' => $statusText ?: null,
            'budget' => $totalAmount,
            'actual_cost' => 0,
            'start_date' => $projectStartDate,
            'end_date' => $projectEndDate,
            'duration' => $data['Waktu Kontrak'] && $data['Waktu Kontrak'] != '-' ? $data['Waktu Kontrak'] : null,
            'pic_internal' => $data['Penanggungjawab Pekerjaan'] && $data['Penanggungjawab Pekerjaan'] != '-' ? $data['Penanggungjawab Pekerjaan'] : null,
            'completed_at' => $projectStatus === 'completed' ? $orderTimestamp : null,
            'created_at' => $orderTimestamp,
            'updated_at' => $orderTimestamp,
        ]);
    }

    private function cleanPhone($phone)
    {
        // Remove spaces, +62, and format to 08xxx
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        return $phone ?: null;
    }

    private function parseRupiah($value)
    {
        if (empty($value) || $value === '-' || $value === '?') {
            return 0;
        }
        // Remove Rp, dots, commas
        $clean = preg_replace('/[^0-9,-]/', '', $value);
        $clean = str_replace(',', '.', $clean);
        return floatval($clean);
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '-' || trim($date) === '') {
            return null;
        }

        $date = trim($date);

        try {
            // Cek format tanggal - apakah US (m/d/Y) atau EU (d/m/Y)
            // Jika ada slash, parse manual untuk detect format
            if (strpos($date, '/') !== false && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $date, $matches)) {
                $part1 = (int)$matches[1];
                $part2 = (int)$matches[2];
                $year = $matches[3];
                
                // Logic: jika part1 > 12, pasti format d/m/Y (day/month/year)
                // jika part2 > 12, pasti format m/d/Y (month/day/year)
                // jika keduanya <= 12, coba deteksi dari konteks (lebih aman gunakan d/m/Y untuk data baru)
                if ($part1 > 12) {
                    // Pasti day/month/year (EU format)
                    $format = 'd/m/Y';
                    if (strlen($date) > 10) {
                        $format .= ' H:i:s';
                    }
                } elseif ($part2 > 12) {
                    // Pasti month/day/year (US format)
                    $format = 'm/d/Y';
                    if (strlen($date) > 10) {
                        $format .= ' H:i:s';
                    }
                } else {
                    // Ambiguous - coba keduanya, prioritas d/m/Y untuk data 2024-2025
                    $formats = ['d/m/Y H:i:s', 'd/m/Y', 'n/j/Y G:i:s', 'm/d/Y H:i:s', 'n/j/Y'];
                    foreach ($formats as $fmt) {
                        $parsed = \DateTime::createFromFormat($fmt, $date);
                        if ($parsed !== false) {
                            return $parsed->format('Y-m-d H:i:s');
                        }
                    }
                }
                
                // Try detected format
                if (isset($format)) {
                    $parsed = \DateTime::createFromFormat($format, $date);
                    if ($parsed !== false) {
                        return $parsed->format('Y-m-d H:i:s');
                    }
                }
            }

            // Try multiple formats
            $formats = [
                'd/m/Y H:i:s',   // 13/11/2025 10:05:50 (EU format with time)
                'd/m/Y',         // 13/11/2025 (EU format)
                'n/j/Y G:i:s',   // 6/3/2023 10:05:50 (US format with time)
                'm/d/Y H:i:s',   // 06/03/2023 10:05:50
                'n/j/Y',         // 6/3/2023
                'd-M-y',         // 12-Apr-22
                'j F Y',         // 7 Februari 2022
                'd F Y',         // 06 April 2022
                'Y-m-d',         // 2023-01-01
                'j M Y',         // 7 Oct 2022
                'd M Y',         // 13 Nov 2023
            ];

            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat($format, $date);
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d H:i:s');
                }
            }

            // Fallback dengan Carbon untuk format fleksibel
            try {
                return Carbon::parse($date)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Last resort: return current time
                return now()->format('Y-m-d H:i:s');
            }
        } catch (\Exception $e) {
            return now()->format('Y-m-d H:i:s');
        }
    }

    private function generateOrderNumber($timestamp)
    {
        $date = $this->parseDate($timestamp);
        return 'ORD-' . date('Ymd', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getServiceAndPackage($jenisProject, $namaProject)
    {
        // Mapping ke 11 layanan utama berdasarkan Jenis Project + keyword di Nama Project
        $jenisProject = trim($jenisProject);
        $namaProject = strtolower(trim($namaProject));
        
        // Mapping rules dengan keyword detection
        if (stripos($jenisProject, 'Marketplace') !== false || stripos($jenisProject, 'Toko Online') !== false) {
            // Marketplace services
            if (stripos($namaProject, 'iklan') !== false || stripos($namaProject, 'ads') !== false) {
                $serviceName = 'Jasa Iklan Marketplace';
            } elseif (stripos($namaProject, 'optimasi') !== false || stripos($namaProject, 'optim') !== false) {
                $serviceName = 'Jasa Optimasi Marketplace';
            } elseif (stripos($namaProject, 'upload') !== false || stripos($namaProject, 'produk') !== false) {
                $serviceName = 'Jasa Upload Produk Marketplace';
            } else {
                // Default untuk marketplace
                $serviceName = 'Jasa Iklan Marketplace';
            }
            $categoryName = 'Marketplace';
            $division = 'agency';
            
        } elseif (stripos($jenisProject, 'Sosial Media') !== false || stripos($jenisProject, 'Social Media') !== false) {
            // Social Media services
            if (stripos($namaProject, 'meta ads') !== false || stripos($namaProject, 'instagram ads') !== false || stripos($namaProject, 'ig ads') !== false || stripos($namaProject, 'fb ads') !== false) {
                $serviceName = 'Jasa Meta Ads';
            } else {
                // Default: Kelola Sosial Media (untuk pengelolaan, posting, dll)
                $serviceName = 'Jasa Kelola Media Sosial';
            }
            $categoryName = 'Sosial Media';
            $division = 'agency';
            
        } elseif (stripos($jenisProject, 'Website') !== false || stripos($jenisProject, 'web') !== false || stripos($jenisProject, 'Lainnya') !== false) {
            // Website services
            if (stripos($namaProject, 'seo') !== false) {
                $serviceName = 'Jasa SEO Murah Website';
            } elseif (stripos($namaProject, 'kelola') !== false || stripos($namaProject, 'pengelolaan') !== false || stripos($namaProject, 'maintenance') !== false) {
                $serviceName = 'Jasa Kelola Website';
            } else {
                // Default: Pembuatan Website (untuk compro, landing page, dll)
                $serviceName = 'Jasa Pembuatan Website Berkualitas';
            }
            $categoryName = 'Website';
            $division = 'agency';
            
        } elseif (stripos($jenisProject, 'Pelatihan') !== false || stripos($jenisProject, 'Academy') !== false) {
            // Academy services
            if (stripos($namaProject, 'magang') !== false || stripos($namaProject, 'prakerin') !== false) {
                $serviceName = 'Magang dan Prakerin SMK';
            } elseif (stripos($namaProject, 'sertifikasi') !== false || stripos($namaProject, 'bnsp') !== false) {
                $serviceName = 'Sertifikasi BNSP';
            } else {
                // Default: Pelatihan
                $serviceName = 'Tingkatkan Dirimu dengan Digital Marketing Profesional';
            }
            $categoryName = 'Academy';
            $division = 'academy';
            
        } else {
            // Fallback ke Website
            $serviceName = 'Jasa Pembuatan Website Berkualitas';
            $categoryName = 'Website';
            $division = 'agency';
        }
        
        // Get category
        $category = \App\Models\ServiceCategory::where('name', $categoryName)->where('division', $division)->first();
        
        if (!$category) {
            throw new \Exception("Category '$categoryName' dengan division '$division' tidak ditemukan di database!");
        }
        
        // Get service (TIDAK create baru, harus sudah ada!)
        $service = \App\Models\Service::where('category_id', $category->id)
            ->where('name', $serviceName)
            ->first();
        
        if (!$service) {
            throw new \Exception("Service '$serviceName' tidak ditemukan! Pastikan 11 layanan utama sudah ada di database.");
        }

        // Get default package (paket pertama dari service ini)
        $package = \App\Models\ServicePackage::where('service_id', $service->id)
            ->orderBy('id')
            ->first();

        return [
            'service' => $service,
            'package' => $package // bisa null jika service tidak punya paket
        ];
    }
}
