<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'payment_request_id',
        'category',
        'recipient_id',
        'recipient_name',
        'description',
        'amount',
        'expense_date',
        'receipt_file',
        'notes',
        'created_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recipient()
    {
        return $this->belongsTo(TeamMember::class, 'recipient_id');
    }

    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class);
    }
}

