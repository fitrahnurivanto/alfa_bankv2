<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'class_id',
        'request_number',
        'type',
        'requested_amount',
        'approved_amount',
        'hours_worked',
        'status',
        'description',
        'notes',
        'admin_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'finance_approved_by',
        'finance_approved_at',
        'finance_notes',
        'paid_at',
        'paid_by',
        'payment_method',
        'payment_reference',
        'bukti_transfer_url', 'payment_status',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Alias compatibility: beberapa query lama memanggil relasi `trainer`.
    public function trainer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }

    // Alias: class() untuk compatibility (karena 'class' adalah reserved keyword)
    public function class()
    {
        return $this->clas();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    // Alias relations for compatibility
    public function approvedBy()
    {
        return $this->approver();
    }

    public function financeApprovedBy()
    {
        return $this->financeApprover();
    }

    public function paidBy()
    {
        return $this->payer();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAdminApproved($query)
    {
        return $query->where('status', 'admin_approved');
    }

    public function scopeFinanceApproved($query)
    {
        return $query->where('status', 'finance_approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['admin_rejected', 'finance_rejected']);
    }

    public function scopeApprovedOrProcessing($query)
    {
        return $query->whereIn('status', ['admin_approved', 'finance_approved']);
    }

    // Status checkers
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isAdminApproved()
    {
        return $this->status === 'admin_approved';
    }

    public function isFinanceApproved()
    {
        return $this->status === 'finance_approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Accessors for backward compatibility
    public function getAmountAttribute()
    {
        return $this->requested_amount;
    }

    public function getDescriptionAttribute()
    {
        return $this->notes;
    }

    public function getNotesAttribute($value)
    {
        if ($value !== null && $value !== '') {
            return $value;
        }

        return $this->attributes['description'] ?? null;
    }
}

