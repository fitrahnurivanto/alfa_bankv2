<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update status enum - tambah status untuk multi-level approval
        DB::statement("ALTER TABLE payment_requests MODIFY COLUMN status ENUM('pending', 'admin_approved', 'admin_rejected', 'finance_approved', 'finance_rejected', 'paid') DEFAULT 'pending'");
        
        Schema::table('payment_requests', function (Blueprint $table) {
            // Finance approval tracking
            $table->foreignId('finance_approved_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->timestamp('finance_approved_at')->nullable()->after('approved_at');
            $table->text('finance_notes')->nullable()->after('admin_notes');
            
            // Bukti transfer
            $table->string('bukti_transfer_url')->nullable()->after('payment_reference');
            
            // Index untuk performance
            $table->index('finance_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['finance_approved_by']);
            $table->dropIndex(['finance_approved_at']);
            $table->dropColumn([
                'finance_approved_by',
                'finance_approved_at',
                'finance_notes',
                'bukti_transfer_url'
            ]);
        });
        
        // Revert ke status lama
        DB::statement("ALTER TABLE payment_requests MODIFY COLUMN status ENUM('pending', 'approved', 'processing', 'paid', 'rejected') DEFAULT 'pending'");
    }
};
