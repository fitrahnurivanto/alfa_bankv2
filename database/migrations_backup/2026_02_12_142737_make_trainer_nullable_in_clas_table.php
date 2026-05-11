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
        Schema::table('clas', function (Blueprint $table) {
            // Ubah kolom trainer jadi nullable karena sekarang pakai pivot table clas_trainer
            $table->string('trainer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            // Kembalikan jadi not null
            $table->string('trainer')->nullable(false)->change();
        });
    }
};
