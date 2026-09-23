<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundPayment extends Model
{
    protected $fillable = [
        'class_id', 'student_id', 'registration_id', 'nis', 'external_transaction_id',
        'payment_type', 'amount', 'paid_at', 'payment_method', 'status', 'proof_url', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }
}