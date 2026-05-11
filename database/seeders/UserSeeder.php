<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔧 Seeding Users for Alfa Bank...\n\n";

        // Create Kategori default
        $kategoris = [
            ['nama' => 'Corporate Training', 'deskripsi' => 'Pelatihan untuk perusahaan dan organisasi'],
            ['nama' => 'Regular Training', 'deskripsi' => 'Pelatihan reguler terbuka untuk umum'],
            ['nama' => 'Private Training', 'deskripsi' => 'Pelatihan private individual atau kelompok kecil'],
        ];
        
        foreach ($kategoris as $kategori) {
            Kategori::create([
                'nama_kategori' => $kategori['nama'],
                'slug' => Str::slug($kategori['nama']),
                'deskripsi' => $kategori['deskripsi'],
                'is_active' => true,
            ]);
        }
        
        echo "✅ Kategori Kelas created (3 items)\n\n";

        // Create Admin User (Super Admin for Alfa Bank)
        $admin = User::create([
            'name' => 'Admin Alfa Bank',
            'email' => 'admin_alfayk@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '6289671481943',
        ]);
        
        echo "✅ Super Admin created:\n";
        echo "   Email: {$admin->email}\n";
        echo "   Password: password123\n\n";

        // Create Finance User
        $finance = User::create([
            'name' => 'Finance Alfa Bank',
            'email' => 'finance_alfayk@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'finance',
            'phone' => '6289671481944',
        ]);
        
        echo "✅ Finance created:\n";
        echo "   Email: {$finance->email}\n";
        echo "   Password: password123\n\n";

        echo "==================================================\n";
        echo "  ALFA BANK USERS SEEDED SUCCESSFULLY!\n";
        echo "==================================================\n";
        echo "  Total Users: 2 (Admin + Finance)\n";
        echo "  Default password: password123\n";
        echo "  Admin: admin_alfayk@gmail.com\n";
        echo "  Finance: finance_alfayk@gmail.com\n";
        echo "  WhatsApp: +6289671481943\n";
        echo "\n";
        echo "  ℹ️  Trainer & Client dapat ditambahkan via Admin Panel\n";
        echo "==================================================\n\n";
    }
}
