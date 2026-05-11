<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'features',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the service.
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /**
     * Get the packages for the service.
     */
    public function packages()
    {
        return $this->hasMany(ServicePackage::class);
    }

    /**
     * Get the order items for the service.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the projects that use this service.
     * NOTE: Disabled - project_services table removed
     */
    // public function projects()
    // {
    //     return $this->belongsToMany(Project::class, 'project_services')
    //         ->withPivot('allocated_budget')
    //         ->withTimestamps();
    // }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
