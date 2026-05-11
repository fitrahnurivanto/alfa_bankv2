<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training;
use Illuminate\Support\Str;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds for Alfa Bank.
     */
    public function run(): void
    {
        echo "\n🔧 Seeding Training Programs...\n\n";

        // Data pelatihan berdasarkan tipe (harga tidak tetap, diinput saat create class)
        $trainings = [
            // KELAS REGULER (8 items)
            [
                'type' => 'reguler',
                'name' => 'Microsoft Office',
                'description' => 'Pelatihan Microsoft Office (Word, Excel, PowerPoint)',
                'duration' => 30,
            ],
            [
                'type' => 'reguler',
                'name' => 'Microsoft Office 12 Pertemuan',
                'description' => 'Pelatihan Microsoft Office intensif 12 pertemuan',
                'duration' => 45,
            ],
            [
                'type' => 'reguler',
                'name' => 'Kursus Microsoft Excel',
                'description' => 'Pelatihan khusus Microsoft Excel dari basic hingga advanced',
                'duration' => 20,
            ],
            [
                'type' => 'reguler',
                'name' => 'Administrasi Profesional',
                'description' => 'Pelatihan administrasi perkantoran profesional',
                'duration' => 30,
            ],
            [
                'type' => 'reguler',
                'name' => 'Kursus Akuntansi',
                'description' => 'Pelatihan akuntansi dasar hingga menengah',
                'duration' => 40,
            ],
            [
                'type' => 'reguler',
                'name' => 'Kursus Excel Accounting',
                'description' => 'Pelatihan Excel untuk akuntansi dan pembukuan',
                'duration' => 25,
            ],
            [
                'type' => 'reguler',
                'name' => 'Teknik Gambar Desain Bangunan',
                'description' => 'Pelatihan teknik gambar dan desain bangunan dengan AutoCAD',
                'duration' => 60,
            ],
            [
                'type' => 'reguler',
                'name' => 'Social Media Marketing',
                'description' => 'Pelatihan strategi pemasaran di media sosial',
                'duration' => 20,
            ],
            
            // KELAS PRIVATE (5 items)
            [
                'type' => 'private',
                'name' => 'Privat Akuntansi',
                'description' => 'Pelatihan akuntansi secara privat dengan jadwal fleksibel',
                'duration' => 30,
            ],
            [
                'type' => 'private',
                'name' => 'Privat Arsitek',
                'description' => 'Pelatihan desain arsitektur secara privat',
                'duration' => 40,
            ],
            [
                'type' => 'private',
                'name' => 'Privat Multimedia',
                'description' => 'Pelatihan multimedia (desain grafis, video editing) secara privat',
                'duration' => 30,
            ],
            [
                'type' => 'private',
                'name' => 'Privat Olahdata',
                'description' => 'Pelatihan pengolahan data dan analisis secara privat',
                'duration' => 30,
            ],
            [
                'type' => 'private',
                'name' => 'Privat Perpajakan',
                'description' => 'Pelatihan perpajakan secara privat',
                'duration' => 35,
            ],
            
            // CORPORATE - kosong (akan ditambahkan melalui admin panel)
            // Corporate training bisa custom sesuai kebutuhan perusahaan
        ];

        // Insert trainings
        $regulerCount = 0;
        $privateCount = 0;
        $corporateCount = 0;

        foreach ($trainings as $trainingData) {
            Training::create([
                'type' => $trainingData['type'],
                'name' => $trainingData['name'],
                'slug' => Str::slug($trainingData['name']),
                'description' => $trainingData['description'],
                'price' => 0, // Harga diinput saat create class
                'duration' => $trainingData['duration'],
                'is_active' => true,
            ]);

            // Count by type
            if ($trainingData['type'] === 'reguler') {
                $regulerCount++;
            } elseif ($trainingData['type'] === 'private') {
                $privateCount++;
            } elseif ($trainingData['type'] === 'corporate') {
                $corporateCount++;
            }
        }

        $totalCount = count($trainings);

        echo "✅ Training Programs seeded successfully!\n\n";
        
        echo "==================================================\n";
        echo "  ALFA BANK TRAININGS SEEDED!\n";
        echo "==================================================\n";
        echo "  📚 Kelas Reguler  : {$regulerCount} programs\n";
        echo "  👤 Kelas Private  : {$privateCount} programs\n";
        echo "  🏢 Kelas Corporate: {$corporateCount} programs (custom via admin)\n";
        echo "  ─────────────────────────────────────────────\n";
        echo "  📊 Total Programs : {$totalCount}\n";
        echo "==================================================\n\n";
    }
}
