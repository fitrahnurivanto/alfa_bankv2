<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            // Link to payment request (untuk auto-generated expenses)
            $table->foreignId('payment_request_id')->nullable()->after('project_id')
                  ->constrained('payment_requests')->nullOnDelete();
            
            // Rename expense_type to category for consistency
            $table->renameColumn('expense_type', 'category');
            
            // Add notes column
            $table->text('notes')->nullable()->after('description');
            
            // Index for performance
            $table->index('payment_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropForeign(['payment_request_id']);
            $table->dropIndex(['payment_request_id']);
            $table->dropColumn(['payment_request_id', 'notes']);
            $table->renameColumn('category', 'expense_type');
        });
    }
};
