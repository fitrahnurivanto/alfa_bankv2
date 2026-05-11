<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassExpense extends Model
{
    public const CATEGORY_LABELS = [
        'honor' => 'Honor',
        'transport' => 'Transportasi',
        'meal' => 'Konsumsi',
        'accommodation' => 'Akomodasi',
        'equipment' => 'Modul',
        'marketing' => 'Marketing',
        'venue_rent' => 'Sewa Tempat',
        'electricity' => 'Listrik',
        'goodie_bag' => 'Goodibag',
        'other' => 'Lainnya',

        // Keep legacy keys mapped to the new naming standard.
        'operational_cost' => 'Lainnya',
        'trainer_honor' => 'Honor',
        // legacy Indonesian keys
        'akomodasi' => 'Akomodasi',
        'konsumsi' => 'Konsumsi',
        'peralatan' => 'Modul',
        'lain-lain' => 'Lainnya',
    ];

    protected $fillable = [
        'clas_id',
        'user_id',
        'description',
        'amount',
        'expense_date',
        'category',
        'receipt_file',
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

    /**
     * Get the class that owns the expense
     */
    public function clas()
    {
        return $this->belongsTo(Clas::class, 'clas_id');
    }

    /**
     * Get the user who created the expense
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved/rejected the expense
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCategoryLabelAttribute(): ?string
    {
        if (!$this->category) {
            return null;
        }

        return self::CATEGORY_LABELS[$this->category] ?? ucwords(str_replace('_', ' ', $this->category));
    }
}
