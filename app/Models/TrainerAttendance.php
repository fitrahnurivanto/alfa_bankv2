<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerAttendance extends Model
{
    protected $fillable = [
        'clas_id',
        'trainer_id',
        'attendance_date',
        'session_number',
        'planned_start_time',
        'shifted_start_time',
        'shift_reason',
        'shifted_at',
        'check_in_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_out_at',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'material_covered',
        'students_present',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'session_number' => 'integer',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'shifted_at' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'check_in_accuracy' => 'decimal:2',
        'check_out_accuracy' => 'decimal:2',
        'students_present' => 'integer',
    ];

    protected $appends = [
        'effective_start_time',
    ];

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'clas_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function getEffectiveStartTimeAttribute(): ?string
    {
        return $this->shifted_start_time ?: $this->planned_start_time;
    }
}
