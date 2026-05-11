<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $table = 'class_sessions';

    protected $fillable = [
        'class_id',
        'session_number',
        'title',
        'description',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'status',
        'materials',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'materials' => 'json',
    ];

    /**
     * Get the class this session belongs to
     */
    public function clas()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }
}
