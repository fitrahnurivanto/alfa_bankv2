<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            // Target pendapatan kelas (diisi saat buat kelas)
            // Progress bar = paid_amount / target_revenue * 100
            $table->decimal('target_revenue', 15, 2)->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn('target_revenue');
        });
    }
};