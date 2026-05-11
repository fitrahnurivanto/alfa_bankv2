<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_expenses', function (Blueprint $table) {
            $table->enum('category', ['lain-lain', 'honor'])->default('lain-lain')->after('expense_date');
        });
    }

    public function down(): void
    {
        Schema::table('class_expenses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
