<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_registrants', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('class_id');
            $table->unsignedBigInteger('registration_id')->nullable()->after('student_id');
            $table->string('nis', 50)->nullable()->after('registration_id');
            $table->index(['class_id', 'student_id']);
            $table->index('registration_id');
        });
    }

    public function down(): void
    {
        Schema::table('class_registrants', function (Blueprint $table) {
            $table->dropIndex(['class_id', 'student_id']);
            $table->dropIndex(['registration_id']);
            $table->dropColumn(['student_id', 'registration_id', 'nis']);
        });
    }
};