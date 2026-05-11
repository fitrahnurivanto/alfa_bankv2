<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->unsignedInteger('passed_students')->nullable()->after('amount');
            $table->unsignedInteger('failed_students')->nullable()->after('passed_students');
            $table->foreignId('pass_fail_updated_by')->nullable()->after('failed_students')->constrained('users')->nullOnDelete();
            $table->timestamp('pass_fail_updated_at')->nullable()->after('pass_fail_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropForeign(['pass_fail_updated_by']);
            $table->dropColumn([
                'passed_students',
                'failed_students',
                'pass_fail_updated_by',
                'pass_fail_updated_at',
            ]);
        });
    }
};
