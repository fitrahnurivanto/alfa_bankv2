<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('proposal_number')->unique();
            $table->date('letter_date')->nullable();
            $table->string('letter_number')->nullable();
            $table->string('proposal_name');
            $table->string('institution_name');
            $table->text('institution_address')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone', 30)->nullable();
            $table->string('offered_scheme')->nullable();
            $table->string('proposal_file_path')->nullable();
            $table->enum('status', ['draft','submitted','under_review','deal','cancelled','running','completed'])->default('submitted');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->date('deal_date')->nullable();
            $table->date('training_date')->nullable();
            $table->date('training_date_end')->nullable();
            $table->date('certification_date_start')->nullable();
            $table->date('certification_date_end')->nullable();
            $table->string('training_scheme')->nullable();
            $table->string('trainer_name')->nullable();
            $table->text('assessor_name')->nullable();
            $table->decimal('training_fee', 15, 2)->default(0);
            $table->date('payment_start_date')->nullable();
            $table->unsignedTinyInteger('installment_count')->default(1);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('projects')) {
            Schema::table('corporate_proposals', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            });
        }

        Schema::create('corporate_proposal_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_proposal_id')->constrained('corporate_proposals')->cascadeOnDelete();
            $table->unsignedTinyInteger('installment_no');
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('payment_status', ['pending','paid','overdue','partial'])->default('pending');
            $table->string('payment_proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('confirmed_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['corporate_proposal_id', 'installment_no'], 'cpi_proposal_installment_unique');
        });

        Schema::table('clas', function (Blueprint $table) {
            $table->foreignId('corporate_proposal_id')->nullable()->after('kategori_id')->constrained('corporate_proposals')->nullOnDelete();
            $table->string('letter_number', 100)->nullable()->after('name');
            $table->date('letter_date')->nullable()->after('name');
            $table->string('proposal_name')->nullable()->after('letter_number');
            $table->string('institution_name')->nullable()->after('proposal_name');
            $table->text('institution_address')->nullable()->after('institution_name');
            $table->string('pic_name')->nullable()->after('institution_address');
            $table->string('pic_phone', 30)->nullable()->after('pic_name');
            $table->string('offered_scheme')->nullable()->after('pic_phone');
            $table->string('proposal_file_path')->nullable()->after('offered_scheme');
            $table->enum('corporate_status', ['submitted','under_review','deal','cancelled','running','completed'])->default('submitted')->after('proposal_file_path');
            $table->text('cancelled_reason')->nullable()->after('corporate_status');
            $table->date('deal_date')->nullable()->after('cancelled_reason');
            $table->date('training_date')->nullable()->after('deal_date');
            $table->date('training_date_end')->nullable()->after('training_date');
            $table->date('certification_date_start')->nullable()->after('training_date_end');
            $table->date('certification_date_end')->nullable()->after('certification_date_start');
            $table->string('training_scheme')->nullable()->after('certification_date_end');
            $table->string('trainer_name')->nullable()->after('training_scheme');
            $table->text('assessor_name')->nullable()->after('trainer_name');
            $table->decimal('training_fee', 15, 2)->default(0)->after('assessor_name');
            $table->date('payment_start_date')->nullable()->after('training_fee');
            $table->unsignedTinyInteger('installment_count')->default(1)->after('payment_start_date');
            $table->decimal('corporate_training_price', 15, 2)->nullable()->after('training_fee');
            $table->decimal('corporate_certification_price', 15, 2)->nullable()->after('corporate_training_price');
            $table->decimal('certification_price', 15, 2)->nullable()->after('corporate_certification_price');
        });

        $corporateIds = DB::table('kategoris')->whereRaw('LOWER(nama_kategori) LIKE ?', ['%corporate%'])->pluck('id');
        if ($corporateIds->isEmpty()) return;
        DB::table('clas')->whereIn('kategori_id', $corporateIds)->orderBy('id')->eachById(function ($class) {
            $proposal = DB::table('corporate_proposals')->where('proposal_name', $class->name)
                ->where('institution_name', $class->instansi ?: $class->name)->first();
            if (!$proposal) {
                $status = ['approved' => 'deal', 'done' => 'completed', 'rejected' => 'cancelled'][$class->status] ?? 'submitted';
                $id = DB::table('corporate_proposals')->insertGetId([
                    'client_id' => $class->client_id, 'created_by' => $class->user_id,
                    'proposal_number' => 'PRP-BF-' . now()->format('Ymd') . '-' . $class->id,
                    'proposal_name' => $class->name, 'institution_name' => $class->instansi ?: $class->name,
                    'institution_address' => $class->alamat, 'pic_name' => $class->no_pic ?: '-', 'pic_phone' => $class->no_kontak,
                    'offered_scheme' => $class->description, 'status' => $status, 'deal_date' => $status === 'deal' ? $class->start_date : null,
                    'training_date' => $class->start_date, 'training_fee' => $class->price ?: 0, 'paid_amount' => $class->paid_amount ?: 0,
                    'remaining_amount' => max(($class->price ?: 0) - ($class->paid_amount ?: 0), 0), 'installment_count' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $proposalId = $id;
            } else { $proposalId = $proposal->id; }
            DB::table('clas')->where('id', $class->id)->update(['corporate_proposal_id' => $proposalId]);
            if ((float) ($class->paid_amount ?? 0) > 0 && !DB::table('corporate_proposal_installments')->where('corporate_proposal_id', $proposalId)->exists()) {
                DB::table('corporate_proposal_installments')->insert([
                    'corporate_proposal_id' => $proposalId,
                    'installment_no' => 1,
                    'amount' => $class->paid_amount,
                    'due_date' => $class->created_at ? substr($class->created_at, 0, 10) : null,
                    'paid_at' => $class->created_at,
                    'payment_status' => 'paid',
                    'confirmed_amount' => $class->paid_amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) { $table->dropConstrainedForeignId('corporate_proposal_id'); });
        Schema::dropIfExists('corporate_proposal_installments');
        Schema::dropIfExists('corporate_proposals');
    }
};
