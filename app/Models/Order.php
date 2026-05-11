<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_type',
        'registration_type',
        'institution_name',
        'participant_address',
        'participant_age',
        'order_number',
        'order_date',
        'pks_number',
        'pks_date',
        'duration',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'payment_proof',
        'payment_type',
        'payment_notes',
        'installment_count',
        'paid_installments',
        'installment_amount',
        'remaining_amount',
        'notes',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the client that owns the order.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for items relationship (for consistency).
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the project for the order.
     */
    public function project()
    {
        return $this->hasOne(Project::class);
    }

    /**
     * Get the user who confirmed the order.
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scope a query to only include paid orders.
     * Status 'paid' = sudah terkonfirmasi (bisa lunas penuh atau DP yang sudah dikonfirmasi)
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include pending orders.
     * Status 'pending_review' = menunggu review/approval dari admin
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending_review');
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest()->first();
        $number = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;
        
        return 'ORD-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the presenter instance for this order.
     */
    public function getPresenterAttribute()
    {
        return new \App\Presenters\OrderPresenter($this);
    }
}
