<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('class_enrollments')->onDelete('cascade');
            $table->string('evaluation_type')->default('final_exam'); // quiz, assignment, midterm, final_exam, project
            $table->string('title');
            $table->decimal('score', 5, 2)->nullable(); // 0.00 - 100.00
            $table->decimal('max_score', 5, 2)->default(100); 
            $table->string('grade')->nullable(); // A, B, C, D, E
            $table->text('feedback')->nullable();
            $table->date('evaluation_date');
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('enrollment_id');
            $table->index('evaluation_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_evaluations');
    }
};
