<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model UnitBarang - Representasi unit fisik barang individual.
 *
 * IMPORTANT: Status vs is_active
 * - status: Kondisi fisik unit (baik, rusak, dipinjam, dll) - untuk histori
 * - is_active: Flag operasional (true/false) - untuk filtering query
 *
 * TIDAK MENGGUNAKAN SOFT DELETE karena:
 * 1. Soft delete dapat menghilangkan histori transaksi
 * 2. Foreign key constraint bisa bermasalah
 * 3. Lebih fleksibel menggunakan is_active + status 'dihapus'
 *
 * Untuk nonaktifkan unit, JANGAN gunakan delete()!
 * Gunakan Filament Action "Nonaktifkan Unit" yang set:
 * - status = 'dihapus'
 * - is_active = false
 */
class UnitBarang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'unit_barang';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kode_unit';

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
        'kode_unit',
        'master_barang_id',
        'lokasi_id',
        'status',
        'is_active',
        'tanggal_pembelian',
        'catatan',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_pembelian' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Available status options.
     */
    public const STATUS_BAIK = 'baik';

    public const STATUS_DIPINJAM = 'dipinjam';

    public const STATUS_RUSAK = 'rusak';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_HILANG = 'hilang';

    public const STATUS_DIHAPUS = 'dihapus';

    public const STATUSES = [
        self::STATUS_BAIK => 'Baik',
        self::STATUS_DIPINJAM => 'Dipinjam',
        self::STATUS_RUSAK => 'Rusak',
        self::STATUS_MAINTENANCE => 'Maintenance',
        self::STATUS_HILANG => 'Hilang',
        self::STATUS_DIHAPUS => 'Dihapus',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            if (empty($unit->kode_unit)) {
                $unit->kode_unit = self::generateKodeUnit($unit->master_barang_id);
            }
        });
    }

    /**
     * Generate kode unit from master barang.
     * Pattern: {KODE_MASTER}-{SEQUENCE}
     * Example: MB-ELK-001-001, MB-ELK-001-002
     */
    public static function generateKodeUnit(string $masterBarangId): string
    {
        $prefix = $masterBarangId.'-';

        $lastKode = self::where('kode_unit', 'LIKE', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(kode_unit, -3) AS UNSIGNED) DESC')
            ->first();

        if (! $lastKode) {
            return $prefix.'001';
        }

        $lastNumber = (int) substr($lastKode->kode_unit, -3);

        return $prefix.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk unit yang aktif (is_active = true).
     * WAJIB digunakan untuk semua query operasional.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk unit yang siap dioperasikan (aktif dan status baik).
     * Gunakan untuk transaksi keluar, peminjaman, mutasi.
     */
    public function scopeOperational($query)
    {
        return $query->where('is_active', true)
            ->where('status', self::STATUS_BAIK);
    }

    /**
     * Scope untuk unit berdasarkan status tertentu.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the master barang that owns this unit.
     */
    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id', 'kode_master');
    }

    /**
     * Get the lokasi where this unit is stored.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id', 'kode_lokasi');
    }

    /**
     * Get all transaksi keluar for this unit.
     */
    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(TransaksiKeluar::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Get all barang rusak reports for this unit.
     */
    public function barangRusak(): HasMany
    {
        return $this->hasMany(BarangRusak::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Get all mutasi lokasi for this unit.
     */
    public function mutasiLokasi(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Accessor: Get nama barang from master.
     */
    public function getNamaBarangAttribute(): string
    {
        return $this->masterBarang?->nama_barang ?? '-';
    }

    /**
     * Accessor: Get kategori name from master.
     */
    public function getKategoriAttribute(): ?string
    {
        return $this->masterBarang?->kategori?->nama_kategori;
    }

    /**
     * Accessor: Get lokasi name.
     */
    public function getNamaLokasiAttribute(): string
    {
        return $this->lokasi?->nama_lokasi ?? '-';
    }

    /**
     * Accessor: Get formatted status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nonaktifkan unit (soft delete alternative).
     * Mengubah status ke 'dihapus' dan is_active ke false.
     */
    public function nonaktifkan(?string $catatan = null): bool
    {
        $this->status = self::STATUS_DIHAPUS;
        $this->is_active = false;

        if ($catatan) {
            $this->catatan = $catatan;
        }

        return $this->save();
    }

    /**
     * Aktifkan kembali unit yang sudah dinonaktifkan.
     */
    public function aktifkan(string $status = self::STATUS_BAIK): bool
    {
        $this->status = $status;
        $this->is_active = true;

        return $this->save();
    }
}
