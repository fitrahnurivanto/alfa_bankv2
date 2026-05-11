<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('training_categories')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('duration')->default(30)->comment('Duration in days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
