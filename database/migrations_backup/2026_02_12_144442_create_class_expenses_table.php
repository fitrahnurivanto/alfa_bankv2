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
        Schema::create('class_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('clas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Yang input expense
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('category')->nullable(); // transport, akomodasi, konsumsi, lain-lain
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // Path bukti/receipt
            $table->timestamps();
            
            $table->index(['class_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_expenses');
    }
};
