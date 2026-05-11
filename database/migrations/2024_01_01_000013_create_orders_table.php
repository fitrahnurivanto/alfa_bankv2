<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->date('order_date')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['pending_review', 'paid', 'partial', 'unpaid', 'cancelled'])->default('pending_review');
            $table->enum('payment_type', ['full', 'installment'])->default('full');
            $table->enum('payment_method', ['transfer', 'cash', 'other'])->default('transfer');
            $table->integer('installment_count')->nullable();
            $table->decimal('installment_amount', 12, 2)->nullable();
            $table->string('payment_proof')->nullable();
            $table->text('notes')->nullable();
            $table->enum('order_type', ['order', 'registration'])->default('order');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('payment_status');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
