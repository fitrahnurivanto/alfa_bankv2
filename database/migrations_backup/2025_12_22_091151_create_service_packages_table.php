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
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Basic, Standard, Premium, dll
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->json('features')->nullable(); // Array fitur paket
            $table->integer('duration')->nullable(); // Durasi dalam hari
            $table->boolean('is_popular')->default(false); // Badge "Paling Laris"
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Update order_items untuk support package
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('service_package_id')->nullable()->after('service_id')->constrained()->onDelete('set null');
            $table->string('package_name')->nullable()->after('service_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['service_package_id']);
            $table->dropColumn(['service_package_id', 'package_name']);
        });
        
        Schema::dropIfExists('service_packages');
    }
};
