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
        // Update method enum to include 'mix'
        DB::statement("ALTER TABLE clas MODIFY COLUMN method ENUM('online', 'offline', 'mix') NOT NULL");
        
        // Add jenis_reguler field
        Schema::table('clas', function (Blueprint $table) {
            $table->enum('jenis_reguler', ['mandiri', 'lain_lain'])->nullable()->after('sertifikasi_bnsp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove jenis_reguler field
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn('jenis_reguler');
        });
        
        // Revert method enum to original
        DB::statement("ALTER TABLE clas MODIFY COLUMN method ENUM('online', 'offline') NOT NULL");
    }
};
