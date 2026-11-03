<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateProposal extends Model
{
    protected $fillable = ['client_id','project_id','created_by','reviewed_by','proposal_number','letter_date','letter_number','proposal_name','institution_name','institution_address','pic_name','pic_phone','offered_scheme','proposal_file_path','status','review_notes','reviewed_at','cancelled_reason','deal_date','training_date','training_date_end','certification_date_start','certification_date_end','training_scheme','trainer_name','assessor_name','training_fee','payment_start_date','installment_count','paid_amount','remaining_amount','progress_percentage','completed_at'];
    protected $casts = ['letter_date'=>'date','reviewed_at'=>'datetime','deal_date'=>'date','training_date'=>'date','training_date_end'=>'date','certification_date_start'=>'date','certification_date_end'=>'date','payment_start_date'=>'date','training_fee'=>'decimal:2','paid_amount'=>'decimal:2','remaining_amount'=>'decimal:2','progress_percentage'=>'decimal:2','completed_at'=>'datetime'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function installments(): HasMany { return $this->hasMany(CorporateProposalInstallment::class)->orderBy('installment_no'); }
    public function clas() { return $this->hasOne(Clas::class, 'corporate_proposal_id'); }
    public static function generateProposalNumber(): string { $date = now()->format('Ymd'); $n = static::whereDate('created_at', today())->count() + 1; return 'PRP-'.$date.'-'.str_pad((string)$n, 4, '0', STR_PAD_LEFT); }
    public function syncRemainingAmount(): void { $fee=(float)$this->training_fee; $paid=(float)$this->paid_amount; $this->remaining_amount=max($fee-$paid,0); $this->progress_percentage=$fee>0 ? min($paid/$fee*100,100) : 0; $this->save(); }
    public function generateInstallments(): void { $count=max((int)$this->installment_count,1); $start=$this->payment_start_date; $ids=[]; for($i=1;$i<=$count;$i++){ $term=$this->installments()->firstOrNew(['installment_no'=>$i]); if(!$term->exists || !in_array($term->payment_status,['paid','partial'],true)) $term->amount=$term->amount ?? null; $term->due_date=$start ? $start->copy()->addMonthsNoOverflow($i-1) : null; $term->payment_status=$term->exists ? $term->payment_status : 'pending'; $term->confirmed_amount=$term->confirmed_amount ?? 0; $term->save(); $ids[]=$term->id; } $this->installments()->whereNotIn('id',$ids)->where('payment_status','pending')->delete(); }
}
