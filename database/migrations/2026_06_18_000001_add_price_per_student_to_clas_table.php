<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            if (!Schema::hasColumn('clas', 'price_per_student')) {
                $table->decimal('price_per_student', 12, 2)->default(0)->after('no_kontak');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            if (Schema::hasColumn('clas', 'price_per_student')) {
                $table->dropColumn('price_per_student');
            }
        });
    }
};