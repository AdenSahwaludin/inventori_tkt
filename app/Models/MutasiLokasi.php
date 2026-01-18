<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MutasiLokasi - Tracking Perpindahan Unit Barang.
 *
 * Tabel ini mencatat histori perpindahan lokasi untuk audit trail.
 * Record dibuat otomatis oleh UnitBarangObserver saat lokasi_id berubah.
 */
class MutasiLokasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mutasi_lokasi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_barang_id',
        'lokasi_asal',
        'lokasi_tujuan',
        'tanggal_mutasi',
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
     * Get the origin lokasi.
     */
    public function lokasiAsal(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal', 'kode_lokasi');
    }

    /**
     * Get the destination lokasi.
     */
    public function lokasiTujuanRelation(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan', 'kode_lokasi');
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
     * Accessor: Get nama lokasi asal.
     */
    public function getNamaLokasiAsalAttribute(): string
    {
        return $this->lokasiAsal?->nama_lokasi ?? '-';
    }

    /**
     * Accessor: Get nama lokasi tujuan.
     */
    public function getNamaLokasiTujuanAttribute(): string
    {
        return $this->lokasiTujuanRelation?->nama_lokasi ?? '-';
    }
}
