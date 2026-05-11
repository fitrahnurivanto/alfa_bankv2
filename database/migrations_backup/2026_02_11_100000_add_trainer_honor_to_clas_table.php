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
            // Add trainer_honor field (honor trainer dibayar via Payment Request, tidak dikurangi dari income)
            $table->decimal('trainer_honor', 15, 2)->default(0)->after('cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn('trainer_honor');
        });
    }
};
