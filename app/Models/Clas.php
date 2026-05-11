<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Clas extends Model
{
    protected $table = 'clas';

    /**
     * Boot the model and auto-delete related notifications when class is deleted
     */
    protected static function boot()
    {
        parent::boot();

        // When a class is being deleted, also delete related notifications
        static::deleting(function ($class) {
            // Delete notifications where data contains this class_id
            DB::table('notifications')
                ->whereRaw("JSON_EXTRACT(data, '$.class_id') = ?", [$class->id])
                ->delete();
        });
    }

    protected $fillable = [
        'user_id',
        'client_id',
        'trainer_id',
        'kategori_id',
        'training_id',
        'name',
        'private_student_name',
        'instansi',
        'alamat',
        'no_pic',
        'no_kontak',
        'payment_type',
        'paid_amount',
        'payment_notes',
        'sertifikasi_bnsp',
        'bnsp_tanggal_sertifikasi',
        'bnsp_ajj',
        'bnsp_asesor',
        'bnsp_student_count',
        'bnsp_fee_per_student',
        'jenis_reguler',
        'slug',
        'price',
        'amount',
        'cost',
        'trainer_honor',
        'meet',
        'duration',
        'method',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'trainer',
        'income',
        'description',
        'status',
        'done_at',
        'passed_students',
        'failed_students',
        'pass_fail_updated_by',
        'pass_fail_updated_at',
        'rejection_reason',
        'grade_file_path',
        'grade_file_url',
        'grade_file_name',
        'grade_file_mime',
        'grade_file_size',
        'grade_file_uploaded_by',
        'grade_file_uploaded_at',
        'grade_file_status',
        'grade_file_review_notes',
        'grade_file_reviewed_by',
        'grade_file_reviewed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'trainer_honor' => 'decimal:2',
        'income' => 'decimal:2',
        'trainer_honor' => 'decimal:2',
        'income' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'amount' => 'integer',
        'passed_students' => 'integer',
        'failed_students' => 'integer',
        'meet' => 'integer',
        'duration' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'bnsp_tanggal_sertifikasi' => 'date',
        'bnsp_student_count' => 'integer',
        'bnsp_fee_per_student' => 'decimal:2',
        'done_at' => 'datetime',
        'pass_fail_updated_at' => 'datetime',
        'grade_file_uploaded_at' => 'datetime',
        'grade_file_reviewed_at' => 'datetime',
        'trainer' => 'array',
        'sertifikasi_bnsp' => 'boolean',
        'bnsp_ajj' => 'boolean',
    ];

    /**
     * Get the category for the class
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Get the training for the class
     */
    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    /**
     * Get the client (organization) for the class
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the trainers assigned to the class
     */
    public function trainers()
    {
        return $this->belongsToMany(User::class, 'clas_trainer', 'clas_id', 'user_id')
            ->where('role', 'trainer')
            ->withTimestamps();
    }

    /**
     * Get payment requests for this class
     */
    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class, 'class_id');
    }

    /**
     * Trainer attendance logs for this class.
     */
    public function trainerAttendances()
    {
        return $this->hasMany(TrainerAttendance::class, 'clas_id')->orderByDesc('attendance_date');
    }

    /**
     * Get class sessions (class_sessions table)
     */
    public function sessions()
    {
        return $this->hasMany(\App\Models\ClassSession::class, 'class_id');
    }

    /**
     * Get expenses for this class
     */
    public function expenses()
    {
        return $this->hasMany(ClassExpense::class, 'clas_id');
    }

    /**
     * Sync trainer_honor field from the latest non-rejected honor expense.
     * This becomes the reference amount for trainer payment requests.
     */
    public function syncTrainerHonorFromExpenses(): void
    {
        $latestHonorExpense = $this->expenses()
            ->whereIn('category', ['trainer_honor', 'honor'])
            ->where('approval_status', '!=', 'rejected')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->first();

        $this->update([
            'trainer_honor' => $latestHonorExpense?->amount ?? 0,
        ]);
    }

    /**
     * Grade files uploaded for this class.
     */
    public function gradeFiles()
    {
        return $this->hasMany(ClassGradeFile::class, 'class_id')
            ->orderByDesc('uploaded_at')
            ->orderByDesc('id');
    }

    /**
     * Latest active grade file used for completion rules.
     */
    public function activeGradeFile()
    {
        return $this->hasOne(ClassGradeFile::class, 'class_id')->where('is_active', true);
    }

    /**
     * Akademik user who last updated pass/fail summary.
     */
    public function passFailUpdatedBy()
    {
        return $this->belongsTo(User::class, 'pass_fail_updated_by');
    }

    /**
     * Keep legacy class columns synced with active grade file data.
     */
    public function syncActiveGradeFileSummary(): void
    {
        $activeFile = $this->activeGradeFile()->first();

        if (!$activeFile) {
            $this->update([
                'grade_file_path' => null,
                'grade_file_url' => null,
                'grade_file_name' => null,
                'grade_file_mime' => null,
                'grade_file_size' => null,
                'grade_file_uploaded_by' => null,
                'grade_file_uploaded_at' => null,
                'grade_file_status' => null,
                'grade_file_review_notes' => null,
                'grade_file_reviewed_by' => null,
                'grade_file_reviewed_at' => null,
            ]);
            return;
        }

        $this->update([
            'grade_file_path' => $activeFile->file_path,
            'grade_file_url' => $activeFile->file_url,
            'grade_file_name' => $activeFile->file_name,
            'grade_file_mime' => $activeFile->file_mime,
            'grade_file_size' => $activeFile->file_size,
            'grade_file_uploaded_by' => $activeFile->uploaded_by,
            'grade_file_uploaded_at' => $activeFile->uploaded_at,
            'grade_file_status' => $activeFile->status,
            'grade_file_review_notes' => $activeFile->review_notes,
            'grade_file_reviewed_by' => $activeFile->reviewed_by,
            'grade_file_reviewed_at' => $activeFile->reviewed_at,
        ]);
    }

    /**
     * Get total expenses for this class
     */
    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get actual income (revenue - cost - trainer_honor - total_expenses)
     */
    public function getActualIncomeAttribute()
    {
        return $this->income - $this->total_expenses;
    }

    /**
     * Get honor payment status
     * Returns: 'pending' (Belum Diajukan), 'submitted' (Pengajuan), 'paid' (Sudah Dibayar)
     */
    public function getHonorStatus()
    {
        $honorExpense = $this->expenses()
            ->whereIn('category', ['trainer_honor', 'honor'])
            ->latest('created_at')
            ->first();

        if (!$honorExpense) {
            return 'pending'; // Belum Diajukan
        }

        if ($honorExpense->approval_status === 'approved') {
            return 'paid'; // Sudah Dibayar
        }

        return 'submitted'; // Pengajuan
    }

    /**
     * Get honor payment status label
     */
    public function getHonorStatusLabel()
    {
        $status = $this->getHonorStatus();

        return match($status) {
            'pending' => 'Belum Diajukan',
            'submitted' => 'Pengajuan',
            'paid' => 'Sudah Dibayar',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get honor payment status badge color
     */
    public function getHonorStatusColor()
    {
        $status = $this->getHonorStatus();

        return match($status) {
            'pending' => 'red',      // Merah
            'submitted' => 'yellow',  // Kuning
            'paid' => 'green',        // Hijau
            default => 'gray',
        };
    }
}

