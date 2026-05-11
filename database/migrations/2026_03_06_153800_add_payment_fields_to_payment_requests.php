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
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->onDelete('set null');
            $table->string('payment_method')->nullable()->after('paid_by');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->text('bukti_transfer_url')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['paid_by', 'payment_method', 'payment_reference', 'bukti_transfer_url']);
        });
    }
};
