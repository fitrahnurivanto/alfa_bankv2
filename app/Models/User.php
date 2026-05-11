<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the client profile for the user.
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get the team memberships for the user.
     */
    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get the teams the user belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role', 'hourly_rate', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'assigned_to');
    }

    /**
     * Get the time trackings for the user.
     */
    public function timeTrackings()
    {
        return $this->hasMany(TimeTracking::class);
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the classes the trainer is assigned to.
     */
    public function classes()
    {
        return $this->belongsToMany(Clas::class, 'clas_trainer', 'user_id', 'clas_id')
            ->withTimestamps();
    }

    /**
     * Trainer attendance records for this user.
     */
    public function trainerAttendances()
    {
        return $this->hasMany(TrainerAttendance::class, 'trainer_id');
    }

    /**
     * Check if user is admin (any type of admin).
     */
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is agency admin (deprecated - system is Training only).
     */
    public function isAgencyAdmin()
    {
        return false; // No Agency in this system
    }

    /**
     * Check if user is academy admin (all admins are training admin).
     */
    public function isAcademyAdmin()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user is client.
     */
    public function isClient()
    {
        return $this->role === 'client';
    }

    /**
     * Check if user is employee.
     */
    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    /**
     * Check if user is finance.
     */
    public function isFinance()
    {
        return $this->role === 'finance';
    }

    /**
     * Check if user is trainer.
     */
    public function isTrainer()
    {
        return $this->role === 'trainer';
    }

    /**
     * Check if user is marketing.
     */
    public function isMarketing()
    {
        return $this->role === 'marketing';
    }

    /**
     * Check if user is akademik.
     */
    public function isAkademik()
    {
        return $this->role === 'akademik';
    }

    /**
     * Check if user can approve/reject classes (admin or akademik).
     */
    public function canApproveClass()
    {
        return in_array($this->role, ['admin', 'superadmin', 'akademik']);
    }

    /**
     * Check if user can create or edit classes (admin or marketing).
     * Akademik cannot create/edit — they only approve/reject.
     */
    public function canCreateEditClass()
    {
        return in_array($this->role, ['admin', 'superadmin', 'marketing', 'akademik']);
    }

    /**
     * Check if user can access Training features.
     */
    public function canAccessAcademy()
    {
        return in_array($this->role, ['admin', 'superadmin', 'finance', 'trainer', 'marketing', 'akademik']);
    }

    /**
     * Check if user can manage (edit/delete/mark-done) classes.
     * Marketing can only create, not manage.
     */
    public function canManageClass()
    {
        return in_array($this->role, ['admin', 'superadmin', 'akademik']);
    }

    /**
     * Check if user can access Agency features (deprecated - system is Training only).
     */
    public function canAccessAgency()
    {
        return false; // No Agency in this system
    }

    /**
     * Get user notifications.
     */
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Get notification settings.
     */
    public function notificationSettings()
    {
        return $this->hasMany(\App\Models\NotificationSetting::class);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }
}

