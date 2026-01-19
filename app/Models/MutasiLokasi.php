<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MutasiLokasi - Tracking Perpindahan Unit Barang.
 *
 * Tabel ini mencatat histori perpindahan ruang untuk audit trail.
 * Record dibuat otomatis oleh UnitBarangObserver saat ruang_id berubah.
 */
class MutasiLokasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mutasi_lokasi';

    /**
     * Tipe mutasi constants.
     */
    public const TIPE_CREATE = 'create';

    public const TIPE_TRANSAKSI_MASUK = 'transaksi_masuk';

    public const TIPE_TRANSAKSI_KELUAR = 'transaksi_keluar';

    public const TIPE_MANUAL = 'manual';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_barang_id',
        'ruang_asal_id',
        'ruang_tujuan_id',
        'tanggal_mutasi',
        'tipe_mutasi',
        'keterangan',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    /**
     * Get the unit barang for this mutation.
     */
    public function unitBarang(): BelongsTo
    {
        return $this->belongsTo(UnitBarang::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Get the origin ruang.
     */
    public function ruangAsal(): BelongsTo
    {
        return $this->belongsTo(Ruang::class, 'ruang_asal_id', 'id');
    }

    /**
     * Get the destination ruang.
     */
    public function ruangTujuan(): BelongsTo
    {
        return $this->belongsTo(Ruang::class, 'ruang_tujuan_id', 'id');
    }

    /**
     * Get the user who performed this mutation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: Get kode unit barang.
     */
    public function getKodeUnitAttribute(): string
    {
        return $this->unitBarang?->kode_unit ?? '-';
    }

    /**
     * Accessor: Get nama barang from unit.
     */
    public function getNamaBarangAttribute(): string
    {
        return $this->unitBarang?->nama_barang ?? '-';
    }

    /**
     * Accessor: Get nama ruang asal.
     */
    public function getNamaRuangAsalAttribute(): string
    {
        return $this->ruangAsal?->nama_ruang ?? '-';
    }

    /**
     * Accessor: Get nama ruang tujuan.
     */
    public function getNamaRuangTujuanAttribute(): string
    {
        return $this->ruangTujuan?->nama_ruang ?? '-';
    }
}
