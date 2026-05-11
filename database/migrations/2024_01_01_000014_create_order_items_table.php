<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_package_id')->nullable()->constrained('service_packages')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->text('specifications')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
