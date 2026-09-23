<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('clas')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->string('nis', 50)->nullable();
            $table->string('external_transaction_id', 150)->unique();
            $table->enum('payment_type', ['registration', 'installment', 'remedial']);
            $table->decimal('amount', 14, 2);
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 30)->default('verified');
            $table->text('proof_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['class_id', 'student_id']);
            $table->index(['class_id', 'payment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_payments');
    }
};