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
            if (!Schema::hasColumn('orders', 'pks_number')) {
                $table->string('pks_number')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'pks_date')) {
                $table->date('pks_date')->nullable()->after('pks_number');
            }
            if (!Schema::hasColumn('orders', 'duration')) {
                $table->string('duration')->nullable()->after('order_number'); // e.g., "1 bulan", "2 minggu"
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pks_number', 'pks_date', 'duration']);
        });
    }
};
