<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CorporateProposalInstallment extends Model
{
    protected $fillable=['corporate_proposal_id','installment_no','amount','due_date','paid_at','payment_status','payment_proof_path','notes','confirmed_by','confirmed_amount'];
    protected $casts=['amount'=>'decimal:2','due_date'=>'date','paid_at'=>'datetime','confirmed_amount'=>'decimal:2'];
    public function proposal(){return $this->belongsTo(CorporateProposal::class,'corporate_proposal_id');}
    public function confirmer(){return $this->belongsTo(User::class,'confirmed_by');}
}
