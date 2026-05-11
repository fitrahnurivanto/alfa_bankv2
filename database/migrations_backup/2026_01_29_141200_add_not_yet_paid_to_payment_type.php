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
        // Modify ENUM untuk menambahkan 'not_yet_paid'
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_type` ENUM('full', 'installment', 'not_yet_paid') NOT NULL DEFAULT 'full'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM sebelumnya
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_type` ENUM('full', 'installment') NOT NULL DEFAULT 'full'");
    }
};
