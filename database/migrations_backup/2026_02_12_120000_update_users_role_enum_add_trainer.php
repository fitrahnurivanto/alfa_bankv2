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
        // Add 'trainer' to role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'client', 'employee', 'superadmin', 'finance', 'trainer') DEFAULT 'client'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'trainer' from role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'client', 'employee', 'superadmin', 'finance') DEFAULT 'client'");
    }
};
