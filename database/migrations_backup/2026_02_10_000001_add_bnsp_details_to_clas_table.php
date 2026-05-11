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
            $table->date('bnsp_tanggal_sertifikasi')->nullable()->after('sertifikasi_bnsp');
            $table->boolean('bnsp_ajj')->default(false)->after('bnsp_tanggal_sertifikasi');
            $table->string('bnsp_asesor')->nullable()->after('bnsp_ajj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn(['bnsp_tanggal_sertifikasi', 'bnsp_ajj', 'bnsp_asesor']);
        });
    }
};
