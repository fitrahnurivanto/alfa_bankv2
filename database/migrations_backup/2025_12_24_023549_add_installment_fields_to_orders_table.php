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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_type', ['full', 'installment'])->default('full')->after('payment_status');
            $table->integer('installment_count')->nullable()->after('payment_type')->comment('Total cicilan (2 untuk DP + 1x)');
            $table->integer('paid_installments')->default(0)->after('installment_count')->comment('Sudah bayar berapa kali');
            $table->decimal('installment_amount', 15, 2)->nullable()->after('paid_installments')->comment('Nominal per cicilan');
            $table->decimal('remaining_amount', 15, 2)->nullable()->after('installment_amount')->comment('Sisa yang harus dibayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'installment_count', 'paid_installments', 'installment_amount', 'remaining_amount']);
        });
    }
};
