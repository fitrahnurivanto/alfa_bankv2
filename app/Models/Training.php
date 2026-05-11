<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'price',
        'duration',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    /**
     * Get status attribute (accessor for is_active).
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }
    
    /**
     * Set status attribute (mutator for is_active).
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['is_active'] = $value === 'active' || $value === true || $value === 1;
    }
    
    /**
     * Get the classes for the training.
     */
    public function classes()
    {
        return $this->hasMany(Clas::class, 'training_id');
    }

    /**
     * Scope a query to only include active trainings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include reguler trainings.
     */
    public function scopeReguler($query)
    {
        return $query->where('type', 'reguler');
    }

    /**
     * Scope a query to only include private trainings.
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Scope a query to only include corporate trainings.
     */
    public function scopeCorporate($query)
    {
        return $query->where('type', 'corporate');
    }
}
