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
        // First, modify the enum to add new statuses
        DB::statement("ALTER TABLE payment_requests MODIFY COLUMN status ENUM('pending', 'approved', 'processing', 'paid', 'rejected') DEFAULT 'pending'");
        
        Schema::table('payment_requests', function (Blueprint $table) {
            // Add payment tracking fields
            $table->timestamp('paid_at')->nullable()->after('approved_at');
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            $table->string('payment_method', 50)->nullable()->after('paid_by'); // Transfer, Cash, etc
            $table->string('payment_reference', 100)->nullable()->after('payment_method'); // Transfer reference number
            
            // Add index for better query performance
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropIndex(['paid_at']);
            $table->dropColumn(['paid_at', 'paid_by', 'payment_method', 'payment_reference']);
        });
        
        // Revert enum to original values
        DB::statement("ALTER TABLE payment_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
