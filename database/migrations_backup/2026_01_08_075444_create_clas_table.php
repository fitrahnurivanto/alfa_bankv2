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
        Schema::create('clas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2); // Total pendapatan kelas (sudah termasuk semua siswa dengan diskon)
            $table->integer('amount'); // Jumlah siswa
            $table->decimal('cost', 15, 2); // Biaya operasional
            $table->integer('meet'); // Jumlah pertemuan
            $table->integer('duration'); // Durasi per pertemuan (menit)
            $table->enum('method', ['online', 'offline']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('trainer');
            $table->decimal('income', 15, 2); // Total pendapatan
            $table->text('description')->nullable();
            $table->enum('status', ['pending','approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clas');
    }
};
