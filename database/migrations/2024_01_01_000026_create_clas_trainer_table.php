<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clas_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clas_id')->constrained('clas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('clas_id');
            $table->index('user_id');
            $table->unique(['clas_id', 'user_id']); // Prevent duplicate trainer assignment
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clas_trainer');
    }
};
