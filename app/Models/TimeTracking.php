<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'task_id',
        'description',
        'hours',
        'work_date',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'work_date' => 'date',
    ];

    /**
     * Get the project that owns the time tracking.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that owns the time tracking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task for the time tracking.
     */
    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    /**
     * Calculate cost based on hourly rate.
     */
    public function getCostAttribute()
    {
        $teamMember = TeamMember::where('user_id', $this->user_id)
            ->whereHas('team', function($query) {
                $query->where('project_id', $this->project_id);
            })
            ->first();

        if ($teamMember) {
            return $this->hours * $teamMember->hourly_rate;
        }

        return 0;
    }
}
