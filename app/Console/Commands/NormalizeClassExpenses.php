<?php

namespace App\Console\Commands;

use App\Models\ClassExpense;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeClassExpenses extends Command
{
    protected $signature = 'expenses:normalize
        {--delete : Delete non-honor expenses instead of approving them}
        {--force : Skip confirmation prompt}';

    protected $description = 'Normalize class expenses so only honor categories stay in finance workflow';

    private const HONOR_CATEGORIES = ['honor', 'trainer_honor'];

    public function handle()
    {
        if (!$this->option('force')) {
            $message = $this->option('delete')
                ? 'Ini akan menghapus semua expense non-honor dari class expenses. Lanjutkan?'
                : 'Ini akan meng-approve semua expense non-honor agar tidak masuk antrian finance. Lanjutkan?';

            if (!$this->confirm($message)) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        $this->info('Memulai normalisasi expense kelas...');

        $nonHonorQuery = ClassExpense::query()
            ->whereNotIn('category', self::HONOR_CATEGORIES);

        $totalNonHonor = (clone $nonHonorQuery)->count();

        if ($totalNonHonor === 0) {
            $this->info('Tidak ada expense non-honor yang perlu diproses.');
            return self::SUCCESS;
        }

        $this->line('Total expense non-honor: ' . $totalNonHonor);

        DB::beginTransaction();
        try {
            if ($this->option('delete')) {
                $expenseIds = (clone $nonHonorQuery)->pluck('id');

                $notificationCount = Notification::where('type', 'expense_pending')
                    ->whereIn('data->expense_id', $expenseIds)
                    ->count();

                Notification::where('type', 'expense_pending')
                    ->whereIn('data->expense_id', $expenseIds)
                    ->delete();

                $deletedCount = (clone $nonHonorQuery)->delete();

                $this->line('Notifikasi expense pending dihapus: ' . $notificationCount);
                $this->line('Expense non-honor dihapus: ' . $deletedCount);

                DB::commit();
                $this->info('Selesai. Expense non-honor sudah dihapus.');
                return self::SUCCESS;
            }

            $updatedCount = 0;
            $approvedExpenseIds = [];

            (clone $nonHonorQuery)
                ->orderBy('id')
                ->chunkById(200, function ($expenses) use (&$updatedCount, &$approvedExpenseIds) {
                    foreach ($expenses as $expense) {
                        $expense->update([
                            'approval_status' => 'approved',
                            'approved_by' => null,
                            'approved_at' => now(),
                        ]);

                        $approvedExpenseIds[] = $expense->id;
                        $updatedCount++;
                    }
                });

            if (!empty($approvedExpenseIds)) {
                $deletedNotifications = Notification::where('type', 'expense_pending')
                    ->whereIn('data->expense_id', $approvedExpenseIds)
                    ->delete();

                $this->line('Notifikasi expense pending dibersihkan: ' . $deletedNotifications);
            }

            DB::commit();

            $this->info('Selesai. Expense non-honor sudah di-approve otomatis.');
            $this->line('Total di-update: ' . $updatedCount);
            $this->line('Sekarang finance hanya akan melihat kategori honor.');

            return self::SUCCESS;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->error('Gagal normalisasi expense: ' . $throwable->getMessage());
            return self::FAILURE;
        }
    }
}
