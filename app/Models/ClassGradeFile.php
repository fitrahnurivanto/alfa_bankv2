<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGradeFile extends Model
{
    protected $fillable = [
        'class_id',
        'version',
        'is_active',
        'file_path',
        'file_url',
        'file_name',
        'file_mime',
        'file_size',
        'uploaded_by',
        'uploaded_at',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
