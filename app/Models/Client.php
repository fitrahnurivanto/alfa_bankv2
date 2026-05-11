<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'company_address',
        'business_type',
        'npwp',
        'contact_person',
        'contact_phone',
        'position',
        'logo',
        'referral_source',
    ];

    /**
     * Get the user that owns the client.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders for the client.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the projects for the client.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the total revenue from this client.
     * Hitung dari paid_amount (yang sudah dibayar), bukan total_amount
     */
    public function getTotalRevenueAttribute()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('paid_amount');
    }
}
