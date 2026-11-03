<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\CorporateProposal;
use App\Models\CorporateProposalInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CorporateProposalController extends Controller
{
    public static function ensureForClass(Clas $class): CorporateProposal
    {
        return DB::transaction(function () use ($class) {
            if ($class->corporate_proposal_id) return CorporateProposal::findOrFail($class->corporate_proposal_id);
            $status = ['approved'=>'deal','done'=>'completed','rejected'=>'cancelled'][$class->status] ?? 'submitted';
            $proposal = CorporateProposal::create([
                'client_id'=>$class->client_id, 'created_by'=>$class->user_id ?: Auth::id(),
                'proposal_number'=>CorporateProposal::generateProposalNumber(), 'proposal_name'=>$class->proposal_name ?: $class->name,
                'institution_name'=>$class->institution_name ?: $class->instansi ?: ($class->name ?: '-'),
                'institution_address'=>$class->institution_address ?: $class->alamat, 'pic_name'=>$class->pic_name ?: $class->no_pic ?: '-',
                'pic_phone'=>$class->pic_phone ?: $class->no_kontak, 'offered_scheme'=>$class->offered_scheme ?: $class->description,
                'status'=>$class->corporate_status ?: $status, 'deal_date'=>$class->deal_date ?: ($status==='deal' ? $class->start_date : null),
                'training_date'=>$class->training_date ?: $class->start_date, 'training_fee'=>$class->training_fee ?: $class->price ?: 0,
                'payment_start_date'=>$class->payment_start_date, 'installment_count'=>$class->installment_count ?: 1,
                'paid_amount'=>$class->paid_amount ?: 0,
            ]);
            $class->forceFill(['corporate_proposal_id'=>$proposal->id])->saveQuietly();
            $proposal->syncRemainingAmount();
            return $proposal;
        });
    }

    private function syncClass(CorporateProposal $proposal): void
    {
        $class=$proposal->clas;
        if(!$class) return;
        $map=['deal'=>'approved','running'=>'approved','completed'=>'done','cancelled'=>'rejected'];
        $class->forceFill(['corporate_status'=>$proposal->status,'status'=>$map[$proposal->status] ?? $class->status,'proposal_name'=>$proposal->proposal_name,'institution_name'=>$proposal->institution_name,'institution_address'=>$proposal->institution_address,'pic_name'=>$proposal->pic_name,'pic_phone'=>$proposal->pic_phone,'offered_scheme'=>$proposal->offered_scheme,'training_date'=>$proposal->training_date,'training_date_end'=>$proposal->training_date_end,'certification_date_start'=>$proposal->certification_date_start,'certification_date_end'=>$proposal->certification_date_end,'training_fee'=>$proposal->training_fee,'payment_start_date'=>$proposal->payment_start_date,'installment_count'=>$proposal->installment_count,'deal_date'=>$proposal->deal_date])->saveQuietly();
    }

    public function showForClass(Clas $clas)
    {
        self::ensureForClass($clas);

        return redirect()->route('admin.classes.show', $clas);
    }

    public function updateStatus(Request $request, Clas $clas)
    {
        $data=$request->validate(['status'=>'required|in:submitted,under_review,deal,running,cancelled,completed','cancelled_reason'=>'nullable|string|max:2000']);
        abort_unless(Auth::user()->canManageClass(),403);
        return DB::transaction(function() use($data,$clas){
            $p=$clas->corporateProposal ?: self::ensureForClass($clas);
            if ($data['status']==='completed' && (!$p->installments()->exists() || $p->installments()->where('payment_status','!=','paid')->exists())) {
                return back()->withErrors(['status'=>'Proposal belum lunas seluruhnya.']);
            }
            $p->update($data+['reviewed_by'=>Auth::id(),'reviewed_at'=>now()]);
            if(in_array($p->status,['deal','running'],true)) $p->generateInstallments();
            $this->syncClass($p->fresh());
            return back()->with('success','Status Corporate berhasil diperbarui.');
        });
    }

    public function updateInstallmentScheme(Request $request, CorporateProposal $corporateProposal)
    {
        abort_unless(Auth::user()->canManageClass(),403);
        $data=$request->validate(['installment_count'=>'required|integer|min:1|max:12','payment_start_date'=>'nullable|date']);
        if(!in_array($corporateProposal->status,['deal','running'],true)) return back()->withErrors(['status'=>'Skema termin hanya tersedia untuk proposal Deal/Berjalan.']);
        return DB::transaction(function() use($data,$corporateProposal){$corporateProposal->update($data);$corporateProposal->generateInstallments();return back()->with('success','Skema termin disimpan.');});
    }

    public function updateInstallmentDetail(Request $request, CorporateProposalInstallment $installment)
    {
        abort_unless(Auth::user()->canManageClass(),403);
        $data=$request->validate(['amount'=>'nullable|numeric|min:0','due_date'=>'nullable|date']);
        if($installment->payment_status==='paid') return back()->withErrors(['amount'=>'Termin lunas tidak dapat diubah.']);
        $installment->update($data); return back()->with('success','Detail termin disimpan.');
    }

    public function confirmInstallment(Request $request, CorporateProposalInstallment $installment)
    {
        abort_unless(Auth::user()->canManageClass(),403);
        $data=$request->validate(['confirmed_amount'=>'required|numeric|min:0','paid_at'=>'required|date','payment_proof'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120','notes'=>'nullable|string|max:2000']);
        return DB::transaction(function() use($request,$data,$installment){
            $installment = CorporateProposalInstallment::query()->whereKey($installment->id)->lockForUpdate()->firstOrFail(); $planned=$installment->amount===null?null:(float)$installment->amount; $paid=(float)$data['confirmed_amount'];
            $path=$installment->payment_proof_path; if($request->hasFile('payment_proof')) $path=$request->file('payment_proof')->store('corporate-installments');
            $installment->update(['confirmed_amount'=>$paid,'paid_at'=>$data['paid_at'],'payment_status'=>($planned===null||$paid >= $planned)?'paid':'partial','payment_proof_path'=>$path,'notes'=>$data['notes']??$installment->notes,'confirmed_by'=>Auth::id()]);
            $p=$installment->proposal()->lockForUpdate()->firstOrFail(); $p->paid_amount=$p->installments()->sum('confirmed_amount'); $p->syncRemainingAmount();
            if($p->status==='running' && $p->installments()->exists() && !$p->installments()->where('payment_status','!=','paid')->exists()) {$p->update(['status'=>'completed','completed_at'=>now()]);$this->syncClass($p->fresh());}
            return back()->with('success','Pembayaran termin berhasil dikonfirmasi.');
        });
    }
}
