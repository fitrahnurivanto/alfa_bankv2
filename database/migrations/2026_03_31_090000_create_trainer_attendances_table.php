<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clas_id')->constrained('clas')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');

            $table->timestamp('check_in_at')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_in_accuracy', 8, 2)->nullable();

            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_out_accuracy', 8, 2)->nullable();

            $table->text('material_covered')->nullable();
            $table->unsignedInteger('students_present')->nullable();

            $table->timestamps();

            $table->unique(['clas_id', 'trainer_id', 'attendance_date'], 'unique_daily_attendance_per_class_trainer');
            $table->index(['trainer_id', 'attendance_date']);
            $table->index(['clas_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_attendances');
    }
};
