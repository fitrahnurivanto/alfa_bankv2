<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','superadmin','trainer','finance','client','employee','marketing','akademik') DEFAULT 'client'");
    }

    public function down(): void
    {
        // Reassign any marketing/akademik users to client before reverting enum
        DB::statement("UPDATE users SET role = 'client' WHERE role IN ('marketing','akademik','superadmin','employee')");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','trainer','finance','client') DEFAULT 'client'");
    }
};
