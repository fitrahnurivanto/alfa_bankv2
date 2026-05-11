<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventaris extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'harga_beli' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(\App\Models\InventarisMovement::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(\App\Models\InventarisPhoto::class);
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab');
    }

    public static function generateKodeBarang(): string
    {
        $maxAttempts = 5;
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $date = now()->format('Ymd');
            $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $kode = 'INV-' . $date . '-' . $random;
            
            // Check if this code already exists
            if (!static::where('kode_barang', $kode)->exists()) {
                return $kode;
            }
        }
        
        // Fallback: if random fails, use timestamp-based code
        return 'INV-' . now()->format('YmdHis') . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }
}
