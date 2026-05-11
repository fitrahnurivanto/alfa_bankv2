<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Notifications\ProjectDeadlineNotification;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:send-deadline-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminder notifications for projects that are due soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for projects with upcoming deadlines...');

        // Get projects that are due in 3 days
        $projects = Project::with(['client', 'teams.members.user'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(3))
            ->get();

        $notificationCount = 0;

        foreach ($projects as $project) {
            $daysUntilDeadline = now()->diffInDays($project->end_date, false);
            
            // Send notification to client
            if ($project->client && $project->client->user) {
                $project->client->user->notify(new ProjectDeadlineNotification($project, $daysUntilDeadline));
                $notificationCount++;
            }

            // Send notification to all team members
            foreach ($project->teams as $team) {
                foreach ($team->members as $member) {
                    if ($member->user) {
                        $member->user->notify(new ProjectDeadlineNotification($project, $daysUntilDeadline));
                        $notificationCount++;
                    }
                }
            }

            $this->line("✓ Sent reminders for: {$project->project_name} (Due in {$daysUntilDeadline} days)");
        }

        if ($notificationCount > 0) {
            $this->info("✅ Successfully sent {$notificationCount} deadline reminder notifications for " . $projects->count() . " projects.");
        } else {
            $this->info("ℹ️ No projects with upcoming deadlines found.");
        }

        return 0;
    }
}
