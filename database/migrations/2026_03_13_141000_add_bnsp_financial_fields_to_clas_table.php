<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->unsignedInteger('bnsp_student_count')->nullable()->after('bnsp_asesor');
            $table->decimal('bnsp_fee_per_student', 15, 2)->nullable()->after('bnsp_student_count');
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn(['bnsp_student_count', 'bnsp_fee_per_student']);
        });
    }
};