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
        Schema::table('payment_requests_safely', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('payment_requests', 'type')) {
                $table->string('type', 50)->nullable()->after('project_id')
                      ->comment('trainer_honor, project_expense, etc');
            }
            
            if (!Schema::hasColumn('payment_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payment_requests', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('payment_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
