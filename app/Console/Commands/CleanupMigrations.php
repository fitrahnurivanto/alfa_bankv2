<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupMigrations extends Command
{
    protected $signature = 'migrate:cleanup-failed';
    protected $description = 'Clean up failed migrations by dropping corrupt tables';

    public function handle()
    {
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop problematic tables in correct order
            DB::statement('DROP TABLE IF EXISTS payments');
            DB::statement('DROP TABLE IF EXISTS invoices');
            DB::statement('DROP TABLE IF EXISTS otp_verifications');
            DB::statement('DROP TABLE IF EXISTS registrations');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Delete migration entries
            DB::table('migrations')
                ->whereIn('migration', [
                    '2026_05_21_000001_create_registrations_table',
                    '2026_05_21_000002_create_otp_verifications_table',
                    '2026_05_21_000003_create_invoices_table',
                    '2026_05_21_000004_create_payments_table',
                ])
                ->delete();

            $this->info('✅ Cleaned up failed migrations and tables');
            $this->info('✅ Ready to run: php artisan migrate');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
