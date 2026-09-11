<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'confirmed_by',
        'method',
        'reference_number',
        'amount',
        'notes',
        'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function registration()
    {
        return $this->hasOneThrough(Registration::class, Invoice::class);
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('confirmed_at');
    }

    // Methods
    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function getMethodLabel(): string
    {
        return match ($this->method) {
            'bank_transfer' => 'Transfer Bank',
            'transfer_bank' => 'Transfer Bank Manual',
            'atm' => 'Transfer via ATM',
            'mbanking' => 'Mobile Banking',
            'ebanking' => 'Internet Banking',
            'cash' => 'Tunai',
            default => 'Unknown',
        };
    }
}
