<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectChat extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'message',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
