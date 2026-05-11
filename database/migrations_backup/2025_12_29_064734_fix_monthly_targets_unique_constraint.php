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
        Schema::table('monthly_targets', function (Blueprint $table) {
            // Drop old unique constraint (year, month)
            $table->dropUnique(['year', 'month']);
            
            // Add new unique constraint (year, month, division)
            $table->unique(['year', 'month', 'division']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_targets', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique(['year', 'month', 'division']);
            
            // Restore old unique constraint
            $table->unique(['year', 'month']);
        });
    }
};
