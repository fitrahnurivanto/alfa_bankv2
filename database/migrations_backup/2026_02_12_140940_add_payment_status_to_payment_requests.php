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
            if (!Schema::hasColumn('payment_requests', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid'])
                      ->default('pending')
                      ->after('status')
                      ->comment('Status pembayaran aktual: pending atau paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payment_requests', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
