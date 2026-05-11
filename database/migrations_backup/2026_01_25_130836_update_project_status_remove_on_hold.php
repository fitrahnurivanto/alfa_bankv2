<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all on_hold status to pending
        DB::table('projects')
            ->where('status', 'on_hold')
            ->update(['status' => 'pending']);
        
        // Modify enum to remove on_hold
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back on_hold to enum
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'on_hold') DEFAULT 'pending'");
        
        // Note: We don't convert pending back to on_hold as we don't know which ones were originally on_hold
    }
};
