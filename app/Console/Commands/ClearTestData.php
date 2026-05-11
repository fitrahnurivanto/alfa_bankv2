<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Project;
use App\Models\Client;
use App\Models\Clas;
use App\Models\PaymentRequest;
use App\Models\ProjectExpense;
use App\Models\ActivityLog;
use App\Models\Notification;

class ClearTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clear {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all test data (orders, projects, clients, classes, expenses, payment requests)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  PERINGATAN: Ini akan menghapus SEMUA data order, project, klien, kelas, expenses, dan payment requests. Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return;
            }
        }

        $this->info('🗑️  Memulai penghapusan data...');
        
        DB::beginTransaction();
        try {
            // 1. Hapus Activity Logs dulu (paling aman)
            $activityCount = ActivityLog::count();
            ActivityLog::truncate();
            $this->line("✅ Activity Logs: {$activityCount} dihapus");

            // 2. Hapus Notifications
            $notifCount = Notification::count();
            Notification::truncate();
            $this->line("✅ Notifications: {$notifCount} dihapus");

            // 3. Hapus Payment Requests (ada relasi ke project_expenses)
            $paymentRequestCount = PaymentRequest::count();
            PaymentRequest::query()->delete();
            $this->line("✅ Payment Requests: {$paymentRequestCount} dihapus");

            // 4. Hapus Project Expenses
            $expenseCount = ProjectExpense::count();
            ProjectExpense::query()->delete();
            $this->line("✅ Project Expenses: {$expenseCount} dihapus");

            // 5. Hapus project-related tables (milestones, tasks, chats, time tracking, team members)
            $milestoneCount = DB::table('project_milestones')->count();
            DB::table('project_milestones')->truncate();
            $this->line("✅ Project Milestones: {$milestoneCount} dihapus");

            $taskCount = DB::table('project_tasks')->count();
            DB::table('project_tasks')->truncate();
            $this->line("✅ Project Tasks: {$taskCount} dihapus");

            $chatCount = DB::table('project_chats')->count();
            DB::table('project_chats')->truncate();
            $this->line("✅ Project Chats: {$chatCount} dihapus");

            $timeCount = DB::table('time_trackings')->count();
            DB::table('time_trackings')->truncate();
            $this->line("✅ Time Trackings: {$timeCount} dihapus");

            $teamCount = DB::table('team_members')->count();
            DB::table('team_members')->truncate();
            $this->line("✅ Team Members: {$teamCount} dihapus");

            // 6. Hapus Projects
            $projectCount = Project::count();
            Project::query()->delete();
            $this->line("✅ Projects: {$projectCount} dihapus");

            // 7. Hapus Order Items dulu sebelum Orders
            $orderItemCount = DB::table('order_items')->count();
            DB::table('order_items')->truncate();
            $this->line("✅ Order Items: {$orderItemCount} dihapus");

            // 8. Hapus Orders
            $orderCount = Order::count();
            Order::query()->delete();
            $this->line("✅ Orders: {$orderCount} dihapus");

            // 9. Hapus Classes
            $classCount = Clas::count();
            Clas::query()->delete();
            $this->line("✅ Classes: {$classCount} dihapus");

            // 10. Hapus Clients (terakhir karena banyak relasi)
            $clientCount = Client::count();
            Client::query()->delete();
            $this->line("✅ Clients: {$clientCount} dihapus");

            DB::commit();

            $this->newLine();
            $this->info('🎉 Semua data berhasil dihapus!');
            $this->info('📊 Sekarang dashboard akan menampilkan data kosong untuk testing.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
