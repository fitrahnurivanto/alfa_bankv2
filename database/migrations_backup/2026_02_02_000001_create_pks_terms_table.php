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
        Schema::create('pks_terms', function (Blueprint $table) {
            $table->id();
            $table->integer('order_number'); // Urutan poin (1, 2, 3, dst)
            $table->text('content'); // Isi poin dengan variable support: {service_description}, {duration}, {payment_amount}
            $table->text('available_variables')->nullable(); // JSON: list variable yang bisa dipakai
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pks_terms');
    }
};
