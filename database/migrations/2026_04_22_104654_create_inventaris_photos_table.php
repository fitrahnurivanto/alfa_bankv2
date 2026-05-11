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
        Schema::create('inventaris_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventaris_id');
            $table->string('file_path')->comment('Path ke foto di storage');
            $table->string('file_name')->comment('Nama file');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('keterangan')->nullable()->comment('Deskripsi foto');
            $table->boolean('is_primary')->default(false)->comment('Foto utama/cover');
            $table->timestamps();
            
            $table->foreign('inventaris_id')->references('id')->on('inventaris')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_photos');
    }
};
