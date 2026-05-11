<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('clas')->onDelete('cascade');
            $table->integer('session_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable(); // Online/Offline, Room name
            $table->string('meeting_link')->nullable(); // Zoom, Google Meet, etc
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->text('materials')->nullable(); // JSON: list of materials/topics covered
            $table->timestamps();
            
            $table->index('class_id');
            $table->index('session_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
