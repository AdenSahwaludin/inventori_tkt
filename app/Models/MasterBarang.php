<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterBarang extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'master_barang';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kode_master';

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
        'kode_master',
        'nama_barang',
        'kategori_id',
        'satuan',
        'merk',
        'harga_satuan',
        'reorder_point',
        'total_pesanan',
        'deskripsi',
        'distribusi_lokasi',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'reorder_point' => 'integer',
        'total_pesanan' => 'integer',
        'distribusi_lokasi' => 'array',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($masterBarang) {
            if (empty($masterBarang->kode_master)) {
                $masterBarang->kode_master = self::generateKodeMaster(
                    $masterBarang->nama_barang,
                    $masterBarang->kategori_id
                );
            }
            if (empty($masterBarang->created_by)) {
                $masterBarang->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate kode master from nama_barang & kategori.
     * Pattern: NAM-KAT (3 huruf nama + 3 huruf kategori)
     * Example: LAP-ELE, KUR-KAY, MES-OFI
     */
    public static function generateKodeMaster(string $namaBarang, string $kategoriId): string
    {
        // Ambil 3 huruf pertama dari nama barang (tanpa spasi)
        $namaCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $namaBarang), 0, 3));
        if (strlen($namaCode) < 3) {
            $namaCode = str_pad($namaCode, 3, 'X');
        }

        // Ambil 3 huruf pertama dari kode kategori
        $kategoriCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $kategoriId), 0, 3));
        if (strlen($kategoriCode) < 3) {
            $kategoriCode = str_pad($kategoriCode, 3, 'X');
        }

        $baseKode = $namaCode.'-'.$kategoriCode;
        $kode = $baseKode;
        $counter = 1;

        // Jika duplikat, tambah suffix: LAP-ELE1, LAP-ELE2, dst
        while (self::withTrashed()->where('kode_master', $kode)->exists()) {
            $kode = $baseKode.$counter++;
        }

        return $kode;
    }

    /**
     * Get the kategori that owns this master barang.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'kode_kategori');
    }

    /**
     * Get all unit barang for this master.
     */
    public function unitBarang(): HasMany
    {
        return $this->hasMany(UnitBarang::class, 'master_barang_id', 'kode_master');
    }

    /**
     * Get all transaksi barang for this master.
     */
    public function transaksiBarang(): HasMany
    {
        return $this->hasMany(TransaksiBarang::class, 'master_barang_id', 'kode_master');
    }

    /**
     * Get count of active units with 'baik' status.
     */
    public function getStokTersediaAttribute(): int
    {
        return $this->unitBarang()
            ->where('is_active', true)
            ->where('status', 'baik')
            ->count();
    }

    /**
     * Get total count of active units.
     */
    public function getTotalUnitAttribute(): int
    {
        return $this->unitBarang()
            ->where('is_active', true)
            ->count();
    }

    /**
     * Check if stock is below reorder point.
     */
    public function getIsStokRendahAttribute(): bool
    {
        if ($this->reorder_point <= 0) {
            return false;
        }

        return $this->stok_tersedia < $this->reorder_point;
    }
}
