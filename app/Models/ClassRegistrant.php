<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRegistrant extends Model
{
    protected $fillable = [
        'class_id',
        'external_registration_id',
        'full_name',
        'email',
        'phone',
        'status',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registered', 'paid', 'confirmed']);
    }
}
