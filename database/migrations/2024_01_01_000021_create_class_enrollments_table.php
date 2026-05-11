<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('clas')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'completed', 'dropout', 'cancelled'])->default('active');
            $table->integer('completion_percentage')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('class_id');
            $table->index('status');
            $table->unique(['client_id', 'class_id']); // Peserta tidak bisa daftar ke class yang sama 2x
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
