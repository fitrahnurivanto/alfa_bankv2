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
        Schema::table('inventaris', function (Blueprint $table) {
            $table->string('kode_barang')->unique()->comment('Auto generated code')->after('id');
            $table->string('nama_barang')->comment('Wajib')->after('kode_barang');
            $table->string('kategori_barang')->comment('Wajib')->after('nama_barang');
            $table->string('merek')->nullable()->after('kategori_barang');
            $table->string('model')->nullable()->after('merek');
            $table->string('nomor_seri')->nullable()->after('model');
            $table->date('tanggal_pengadaan')->comment('Wajib')->after('nomor_seri');
            $table->decimal('harga_beli', 15, 2)->nullable()->after('tanggal_pengadaan');
            $table->integer('jumlah')->default(1)->comment('Wajib - quantity')->after('harga_beli');
            $table->string('satuan')->default('pcs')->comment('Wajib - unit, pcs, set, dll')->after('jumlah');
            $table->string('posisi')->comment('Wajib - gudang, kantor, cabang, dipinjam, dijual, dll')->after('satuan');
            $table->string('kondisi')->comment('Wajib - baru, baik, perbaikan, rusak ringan, rusak berat, hilang')->after('posisi');
            $table->string('status')->default('aktif')->comment('Wajib - aktif, nonaktif, disposed')->after('kondisi');
            $table->string('supplier')->nullable()->after('status');
            $table->string('sumber_pengadaan')->nullable()->comment('beli, hibah, dll')->after('supplier');
            $table->unsignedBigInteger('penanggung_jawab')->nullable()->after('sumber_pengadaan');
            $table->text('catatan')->nullable()->after('penanggung_jawab');
            
            // Add foreign key
            $table->foreign('penanggung_jawab')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropForeign(['penanggung_jawab']);
            $table->dropColumn([
                'kode_barang',
                'nama_barang',
                'kategori_barang',
                'merek',
                'model',
                'nomor_seri',
                'tanggal_pengadaan',
                'harga_beli',
                'jumlah',
                'satuan',
                'posisi',
                'kondisi',
                'status',
                'supplier',
                'sumber_pengadaan',
                'penanggung_jawab',
                'catatan',
            ]);
        });
    }
};
