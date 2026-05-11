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
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique()->comment('Auto generated code');
            $table->string('nama_barang')->comment('Wajib');
            $table->string('kategori_barang')->comment('Wajib');
            $table->string('merek')->nullable();
            $table->string('model')->nullable();
            $table->string('nomor_seri')->nullable();
            $table->date('tanggal_pengadaan')->comment('Wajib');
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->integer('jumlah')->default(1)->comment('Wajib - quantity');
            $table->string('satuan')->default('pcs')->comment('Wajib - unit, pcs, set, dll');
            $table->string('posisi')->comment('Wajib - gudang, kantor, cabang, dipinjam, dijual, dll');
            $table->string('kondisi')->comment('Wajib - baru, baik, perbaikan, rusak ringan, rusak berat, hilang');
            $table->string('status')->default('aktif')->comment('Wajib - aktif, nonaktif, disposed');
            $table->string('supplier')->nullable();
            $table->string('sumber_pengadaan')->nullable()->comment('beli, hibah, dll');
            $table->unsignedBigInteger('penanggung_jawab')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('penanggung_jawab')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
