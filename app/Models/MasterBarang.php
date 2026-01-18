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
        'deskripsi',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'reorder_point' => 'integer',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($masterBarang) {
            if (empty($masterBarang->kode_master)) {
                $masterBarang->kode_master = self::generateKodeMaster($masterBarang->kategori_id);
            }
        });
    }

    /**
     * Generate kode master from kategori.
     * Pattern: MB-{KATEGORI}-{SEQUENCE}
     * Example: MB-ELK-001, MB-ELK-002
     */
    public static function generateKodeMaster(string $kategoriId): string
    {
        $prefix = 'MB-'.$kategoriId.'-';

        $lastKode = self::withTrashed()
            ->where('kode_master', 'LIKE', $prefix.'%')
            ->orderBy('kode_master', 'desc')
            ->first();

        if (! $lastKode) {
            return $prefix.'001';
        }

        $lastNumber = (int) substr($lastKode->kode_master, -3);

        return $prefix.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
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
