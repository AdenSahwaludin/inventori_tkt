<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lokasi extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'lokasi';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kode_lokasi';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'gedung',
        'lantai',
        'keterangan',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lokasi) {
            if (empty($lokasi->kode_lokasi)) {
                $lokasi->kode_lokasi = self::generateKodeLokasi(
                    $lokasi->gedung,
                    $lokasi->lantai,
                    $lokasi->nama_lokasi
                );
            }
        });
    }

    /**
     * Generate kode lokasi from gedung, lantai, and nama.
     * Pattern: GD{X}-LT{Y}-R{ZZ} or simplified if fields are null.
     */
    public static function generateKodeLokasi(?string $gedung, ?string $lantai, string $namaLokasi): string
    {
        $parts = [];

        // Gedung part
        if ($gedung) {
            // Extract letter from gedung (e.g., "Gedung A" -> "A")
            preg_match('/[A-Za-z]/', $gedung, $matches);
            $gedungCode = $matches[0] ?? 'X';
            $parts[] = 'GD'.strtoupper($gedungCode);
        }

        // Lantai part
        if ($lantai) {
            // Extract number from lantai (e.g., "Lantai 1" -> "1")
            preg_match('/\d+/', $lantai, $matches);
            $lantaiCode = $matches[0] ?? '0';
            $parts[] = 'LT'.$lantaiCode;
        }

        // Room/location code from nama
        $namaCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $namaLokasi), 0, 4));
        if (strlen($namaCode) < 2) {
            $namaCode = str_pad($namaCode, 2, 'X');
        }
        $parts[] = $namaCode;

        $baseKode = implode('-', $parts);
        $kode = $baseKode;
        $counter = 1;

        while (self::withTrashed()->where('kode_lokasi', $kode)->exists()) {
            $kode = $baseKode.'-'.str_pad($counter++, 2, '0', STR_PAD_LEFT);
        }

        return $kode;
    }

    /**
     * Get all unit barang in this lokasi.
     */
    public function unitBarang(): HasMany
    {
        return $this->hasMany(UnitBarang::class, 'lokasi_id', 'kode_lokasi');
    }

    /**
     * Get all transaksi barang targeting this lokasi.
     */
    public function transaksiBarang(): HasMany
    {
        return $this->hasMany(TransaksiBarang::class, 'lokasi_tujuan', 'kode_lokasi');
    }

    /**
     * Get all mutasi from this lokasi.
     */
    public function mutasiDari(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'lokasi_asal', 'kode_lokasi');
    }

    /**
     * Get all mutasi to this lokasi.
     */
    public function mutasiKe(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'lokasi_tujuan', 'kode_lokasi');
    }
}
