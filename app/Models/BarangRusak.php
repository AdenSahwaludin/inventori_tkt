<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model BarangRusak - Laporan Barang Rusak.
 *
 * KONSEP: 1 laporan = 1 unit barang.
 * Saat laporan dibuat, status unit_barang otomatis berubah ke 'rusak'.
 */
class BarangRusak extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'barang_rusak';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_barang_id',
        'tanggal_kejadian',
        'keterangan',
        'penanggung_jawab',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_kejadian' => 'date',
    ];

    /**
     * Get the unit barang for this report.
     */
    public function unitBarang(): BelongsTo
    {
        return $this->belongsTo(UnitBarang::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Get the user who created this report.
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
     * Accessor: Get lokasi from unit.
     */
    public function getLokasiAttribute(): string
    {
        return $this->unitBarang?->nama_lokasi ?? '-';
    }
}
