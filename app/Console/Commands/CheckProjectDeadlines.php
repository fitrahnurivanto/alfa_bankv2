<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectDeadlineNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckProjectDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check project deadlines and send notifications (H-3 and H-1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking project deadlines...');

        $now = Carbon::now();
        $deadlineDays = [3, 1]; // H-3 and H-1

        foreach ($deadlineDays as $days) {
            $targetDate = $now->copy()->addDays($days)->format('Y-m-d');
            
            $projects = Project::whereDate('end_date', $targetDate)
                ->whereIn('status', ['in_progress', 'pending'])
                ->with(['client', 'order.items.service'])
                ->get();

            $this->info("Found {$projects->count()} projects with deadline in {$days} days (H-{$days})");

            foreach ($projects as $project) {
                $this->line("  → {$project->project_name} ({$project->project_code})");

                // Get PIC user
                $picUser = User::where('name', $project->pic_internal)->first();
                
                if ($picUser) {
                    $picUser->notify(new ProjectDeadlineNotification($project, $days));
                    $this->line("    ✓ Notified PIC: {$picUser->name}");
                }

                // Notify all admins
                $admins = User::whereIn('role', ['super_admin', 'agency_admin', 'academy_admin'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new ProjectDeadlineNotification($project, $days));
                }
                $this->line("    ✓ Notified {$admins->count()} admins");
            }
        }

        $this->info('✓ Deadline check completed!');
        return 0;
    }
}
