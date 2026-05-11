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
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->foreignId('finance_approved_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('finance_approved_at')->nullable()->after('finance_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['finance_approved_by']);
            $table->dropColumn(['finance_approved_by', 'finance_approved_at']);
        });
    }
};
