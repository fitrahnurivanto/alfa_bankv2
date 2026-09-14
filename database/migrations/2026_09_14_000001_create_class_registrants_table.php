<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_registrants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('clas')->cascadeOnDelete();
            $table->string('external_registration_id', 100);
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('status', 30)->default('registered');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'external_registration_id']);
            $table->index(['class_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_registrants');
    }
};
