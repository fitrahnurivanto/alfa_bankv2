<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PksTerm extends Model
{
    protected $fillable = [
        'term',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];



    /**
     * Get all active terms ordered by order
     */
    public static function getActiveTerms()
    {
        return self::where('is_active', true)
            ->orderBy('order')
            ->get();
    }
}
