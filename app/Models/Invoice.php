<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_id',
        'invoice_number',
        'amount',
        'payment_status',
        'bank_name',
        'account_number',
        'account_holder',
        'payment_deadline',
        'confirmed_at',
        'expires_at',
        // Receipt / kwitansi fields
        'receipt_sequence',
        'receipt_number',
        'receipt_printed_at',
        'receipt_category',
        'receipt_start_date',
        'receipt_program_name',
        'receipt_intensif_amount',
        'receipt_privat_amount',
        'receipt_bnsp_amount',
        'receipt_uji_kompetensi_amount',
        'receipt_lain_lain_amount',
        'receipt_keterangan',
        'receipt_filled',
        'related_invoice_id',
        'is_settlement',
    ];

    protected $casts = [
        'amount'                        => 'decimal:2',
        'payment_deadline'              => 'datetime',
        'confirmed_at'                  => 'datetime',
        'expires_at'                    => 'datetime',
        'receipt_printed_at'            => 'datetime',
        'receipt_start_date'            => 'date',
        'receipt_intensif_amount'       => 'decimal:2',
        'receipt_privat_amount'         => 'decimal:2',
        'receipt_bnsp_amount'           => 'decimal:2',
        'receipt_uji_kompetensi_amount' => 'decimal:2',
        'receipt_lain_lain_amount'      => 'decimal:2',
        'receipt_filled'                => 'boolean',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
        'is_settlement'                 => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // -------------------------------------------------------------------------
    // Computed attributes
    // -------------------------------------------------------------------------
        public function relatedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }
    
    public function settlements()
    {
        return $this->hasMany(Invoice::class, 'related_invoice_id');
    }
    public function getPaidAmountAttribute()
    {
        return (float) $this->payments()->confirmed()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    /**
     * Total biaya kwitansi (jumlah semua komponen rincian).
     * Jika belum diisi rincian, fallback ke amount invoice.
     */
    public function getReceiptTotalAmountAttribute(): float
    {
        $rincian = (float) $this->receipt_intensif_amount
            + (float) $this->receipt_privat_amount
            + (float) $this->receipt_bnsp_amount
            + (float) $this->receipt_uji_kompetensi_amount
            + (float) $this->receipt_lain_lain_amount;

        return $rincian > 0 ? $rincian : (float) $this->amount;
    }

    /**
     * Generate nomor urut kwitansi berikutnya (melanjutkan dari yang sudah ada).
     */
    public static function nextReceiptSequence(): int
    {
        return (int) static::max('receipt_sequence') + 1;
    }

    /**
     * Apakah kwitansi sudah bisa dicetak (data lengkap).
     */
    public function isReceiptReady(): bool
    {
        return $this->receipt_filled
            && $this->receipt_number
            && $this->receipt_program_name
            && $this->receipt_printed_at;
    }

    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function isFullyPaid(): bool
    {
        return (float) $this->remaining_amount <= 0;
    }

    public function confirmedBy()
    {
        if (!$this->payments()->first()) {
            return null;
        }
        return $this->payments()->first()->confirmedBy;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeUnpaid($query)
{
    // Invoice yang masih punya sisa tagihan (belum lunas)
    // dan bukan corporate manual (karena corporate manual langsung dianggap selesai)
    return $query->where('is_corporate_manual', false)
        ->whereIn('payment_status', ['partial', 'pending', 'confirmed'])
        ->whereRaw('amount > (
            SELECT COALESCE(SUM(amount), 0) FROM payments
            WHERE payments.invoice_id = invoices.id AND payments.confirmed_at IS NOT NULL
        )');
}

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('payment_status', 'confirmed');
    }

    public function scopeExpired($query)
    {
        return $query->where('payment_status', 'expired');
    }

    public function scopeReceiptFilled($query)
    {
        return $query->where('receipt_filled', true);
    }

    public function scopeReceiptPending($query)
    {
        return $query->where('receipt_filled', false);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function isOverdue(): bool
    {
        return $this->payment_deadline && $this->payment_deadline < now() && in_array($this->payment_status, ['pending', 'partial'], true);
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial' || ((float) $this->paid_amount > 0 && (float) $this->remaining_amount > 0);
    }

    public function generateInvoiceNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function getPaymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'pending'   => 'Menunggu Pembayaran',
            'partial'   => 'DP / Pembayaran Bertahap',
            'confirmed' => 'Pembayaran Dikonfirmasi',
            'expired'   => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            default     => 'Unknown',
        };
    }

    public function getPaymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'pending'   => 'orange',
            'partial'   => 'amber',
            'confirmed' => 'green',
            'expired'   => 'red',
            'cancelled' => 'gray',
            default     => 'slate',
        };
    }

    public function markAsConfirmed(): void
    {
        $this->update([
            'payment_status' => 'confirmed',
            'confirmed_at'   => now(),
        ]);

        $this->registration()->update(['status' => 'paid']);
    }

    public function markAsPartial(): void
    {
        $this->update(['payment_status' => 'partial']);
        $this->registration()->update(['status' => 'payment_pending']);
    }

    public function markAsExpired(): void
    {
        $this->update(['payment_status' => 'expired']);
        $this->registration()->update(['status' => 'expired']);
    }

     public function autoFillReceipt(): void
    {
        $registration = $this->registration;
        $training     = $registration?->training;
 
        if (!$registration || !$training) {
            return;
        }
 
        // Mapping tipe training → kategori kwitansi
        $category = match ($training->type) {
            'private'   => 'privat',
            'corporate' => 'corporate',
            default     => 'intensif', // reguler → intensif
        };
 
        // Hitung rincian biaya berdasarkan tipe training
        // Jika training punya sertifikasi BNSP (bisa dari field is_bnsp atau nama)
        // Untuk sekarang: jika bukan corporate, amount masuk ke intensif atau privat
        $intensifAmount = 0;
        $privatAmount   = 0;
        $bnspAmount     = 0;
 
        $amount = (float) $this->amount;
 
        // Cek apakah training ini termasuk sertifikasi BNSP
        // (dari nama training mengandung kata 'sertifikasi' atau 'bnsp')
        $hasBnsp = str_contains(strtolower($training->name), 'sertifikasi')
                || str_contains(strtolower($training->name), 'bnsp');
 
        if ($training->type === 'private') {
            if ($hasBnsp && isset($training->bnsp_price)) {
                $bnspAmount   = (float) $training->bnsp_price;
                $privatAmount = max(0, $amount - $bnspAmount);
            } else {
                $privatAmount = $amount;
            }
        } else {
            // reguler/intensif
            if ($hasBnsp && isset($training->bnsp_price)) {
                $bnspAmount     = (float) $training->bnsp_price;
                $intensifAmount = max(0, $amount - $bnspAmount);
            } else {
                $intensifAmount = $amount;
            }
        }
 
        // Generate nomor urut jika belum ada
        if (!$this->receipt_sequence) {
            $nextSeq = static::nextReceiptSequence();
            $this->receipt_sequence = $nextSeq;
            $this->receipt_number   = (string) $nextSeq;
        }
 
        // Keterangan otomatis: Lunas atau sisa
        $remaining    = $this->remaining_amount;
        $keterangan   = $remaining <= 0 ? 'Lunas' : 'Sisa Rp ' . number_format($remaining, 0, ',', '.');
 
        $this->update([
            'receipt_sequence'        => $this->receipt_sequence,
            'receipt_number'          => $this->receipt_number,
            'receipt_filled'          => true,
            'receipt_printed_at'      => now(),
            'receipt_category'        => $category,
            'receipt_program_name'    => $training->name,
            'receipt_intensif_amount' => $intensifAmount,
            'receipt_privat_amount'   => $privatAmount,
            'receipt_bnsp_amount'     => $bnspAmount,
            'receipt_keterangan'      => $keterangan,
        ]);
    }
}