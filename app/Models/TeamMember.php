<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'hourly_rate',
        'assigned_at',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the team that owns the member.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user for the team member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get role label in Indonesian.
     */
    public function getRoleLabelAttribute()
    {
        $roles = [
            'pic' => 'Person In Charge',
            'project_manager' => 'Project Manager',
            'content_creator' => 'Content Creator',
            'developer' => 'Developer',
            'designer' => 'Designer',
            'marketing' => 'Marketing',
            'seo_specialist' => 'SEO Specialist',
            'other' => 'Lainnya',
        ];

        return $roles[$this->role] ?? $this->role;
    }
}
