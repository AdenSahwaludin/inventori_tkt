<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'kategori';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kode_kategori';

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
        'kode_kategori',
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (empty($kategori->kode_kategori)) {
                $kategori->kode_kategori = self::generateKodeKategori($kategori->nama_kategori);
            }
        });
    }

    /**
     * Generate kode kategori from nama_kategori.
     * Pattern: 3 huruf pertama (uppercase), jika duplikat tambah angka.
     */
    public static function generateKodeKategori(string $nama): string
    {
        // Remove non-alphabetic characters and get first 3 letters
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nama), 0, 3));

        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        $counter = 1;
        $kode = $prefix;

        while (self::withTrashed()->where('kode_kategori', $kode)->exists()) {
            $kode = $prefix.$counter++;
        }

        return $kode;
    }

    /**
     * Get all master barang for this kategori.
     */
    public function masterBarang(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'kategori_id', 'kode_kategori');
    }
}
