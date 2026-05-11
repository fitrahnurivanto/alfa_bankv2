<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database for Alfa Bank System.
     */
    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════╗\n";
        echo "║                                                  ║\n";
        echo "║         ALFA BANK - DATABASE SEED                ║\n";
        echo "║                                                  ║\n";
        echo "╚══════════════════════════════════════════════════╝\n";
        
        $this->call([
            // SettingSeeder::class,      // Settings & company info first
            // TrainingSeeder::class,     // Training programs (reguler, private, corporate)
            UserSeeder::class,         // Users, trainers, clients (includes Kategoris)
        ]);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════╗\n";
        echo "║                                                  ║\n";
        echo "║         ✅  SEEDING COMPLETED SUCCESSFULLY       ║\n";
        echo "║                                                  ║\n";
        echo "║  Login as Admin:                                 ║\n";
        echo "║  Email: admin_alfayk@gmail.com                   ║\n";
        echo "║  Password: password123                           ║\n";
        echo "║                                                  ║\n";
        echo "║  Contact: +6289671481943                         ║\n";
        echo "║  Email: mkt.alfayk@gmail.com                     ║\n";
        echo "║                                                  ║\n";
        echo "╚══════════════════════════════════════════════════╝\n";
        echo "\n";
    }
}
