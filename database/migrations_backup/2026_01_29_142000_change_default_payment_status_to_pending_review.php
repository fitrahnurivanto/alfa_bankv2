<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah default payment_status dari 'pending' ke 'pending_review'
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('pending', 'pending_review', 'paid', 'rejected', 'failed', 'refunded') DEFAULT 'pending_review'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke default 'pending'
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('pending', 'pending_review', 'paid', 'rejected', 'failed', 'refunded') DEFAULT 'pending'");
    }
};
