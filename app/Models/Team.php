<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'team_name',
        'description',
    ];

    /**
     * Get the project that owns the team.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the team members.
     */
    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get the users in this team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role', 'hourly_rate', 'assigned_at')
            ->withTimestamps();
    }
}
