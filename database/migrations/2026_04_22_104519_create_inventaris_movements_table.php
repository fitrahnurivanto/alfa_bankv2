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
        Schema::create('inventaris_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventaris_id');
            $table->string('tipe_perubahan')->comment('posisi_change, kondisi_change');
            $table->string('dari')->nullable()->comment('Previous value');
            $table->string('ke')->comment('New value');
            $table->text('alasan')->nullable();
            $table->unsignedBigInteger('diubah_oleh');
            $table->timestamp('waktu_perubahan')->useCurrent();
            $table->timestamps();
            
            $table->foreign('inventaris_id')->references('id')->on('inventaris')->onDelete('cascade');
            $table->foreign('diubah_oleh')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_movements');
    }
};
