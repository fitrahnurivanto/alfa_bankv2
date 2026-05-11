<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->onDelete('set null');
            $table->foreignId('training_id')->nullable()->constrained('trainings')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('instansi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_pic', 100)->nullable();
            $table->string('no_kontak', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('capacity')->default(20);
            $table->integer('enrolled')->default(0);
            $table->integer('meet')->default(1)->comment('Jumlah pertemuan');
            $table->integer('duration')->default(1)->comment('Durasi dalam hari');
            $table->integer('amount')->default(1)->comment('Jumlah peserta');
            $table->enum('method', ['online', 'offline', 'mix'])->default('offline');
            $table->decimal('price', 12, 2)->default(0)->comment('Harga per peserta atau total kontrak');
            $table->decimal('cost', 12, 2)->default(0)->comment('Operational cost');
            $table->decimal('trainer_honor', 12, 2)->default(0)->comment('Honor per trainer');
            $table->decimal('income', 12, 2)->default(0)->comment('Pendapatan bersih');
            $table->enum('payment_type', ['full', 'termin_2x'])->default('full');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('payment_notes')->nullable();
            $table->enum('jenis_reguler', ['mandiri', 'lain_lain'])->nullable();
            $table->boolean('sertifikasi_bnsp')->default(false);
            $table->date('bnsp_tanggal_sertifikasi')->nullable();
            $table->boolean('bnsp_ajj')->default(false);
            $table->string('bnsp_asesor')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'done', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('kategori_id');
            $table->index('training_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clas');
    }
};
