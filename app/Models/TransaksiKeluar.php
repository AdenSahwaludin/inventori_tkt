<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model TransaksiKeluar - Transaksi Keluar Barang.
 *
 * KONSEP: 1 transaksi = 1 unit barang.
 * Transaksi keluar berelasi langsung ke unit_barang.
 */
class TransaksiKeluar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'transaksi_keluar';

    /**
     * Tipe transaksi constants.
     */
    public const TIPE_PEMINDAHAN = 'pemindahan';

    public const TIPE_PEMINJAMAN = 'peminjaman';

    public const TIPE_PENGGUNAAN = 'penggunaan';

    public const TIPE_PENGHAPUSAN = 'penghapusan';

    public const TIPE_OPTIONS = [
        self::TIPE_PEMINDAHAN => 'Pemindahan',
        self::TIPE_PEMINJAMAN => 'Peminjaman',
        self::TIPE_PENGGUNAAN => 'Penggunaan',
        self::TIPE_PENGHAPUSAN => 'Penghapusan',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode_transaksi',
        'unit_barang_id',
        'ruang_asal_id',
        'ruang_tujuan_id',
        'tipe',
        'tanggal_transaksi',
        'penerima',
        'tujuan',
        'keterangan',
        'catatan',
        'user_id',
        'approved_by',
        'approved_at',
        'approval_status',
        'approval_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_transaksi' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Approval status constants.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const APPROVAL_STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaksi) {
            if (empty($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi = self::generateKodeTransaksi();
            }
        });
    }

    /**
     * Generate kode transaksi.
     * Pattern: TRX-KELUAR-YYYYMMDD-XXX
     */
    public static function generateKodeTransaksi(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TRX-KELUAR-'.$date.'-';

        $lastKode = self::where('kode_transaksi', 'LIKE', $prefix.'%')
            ->orderBy('kode_transaksi', 'desc')
            ->first();

        if (! $lastKode) {
            return $prefix.'001';
        }

        $lastNumber = (int) substr($lastKode->kode_transaksi, -3);

        return $prefix.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk transaksi dengan status tertentu.
     */
    public function scopeWithApprovalStatus($query, string $status)
    {
        return $query->where('approval_status', $status);
    }

    /**
     * Scope untuk transaksi pending.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk transaksi yang sudah disetujui.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    /**
     * Get the unit barang for this transaction.
     */
    public function unitBarang(): BelongsTo
    {
        return $this->belongsTo(UnitBarang::class, 'unit_barang_id', 'kode_unit');
    }

    /**
     * Get the ruang asal for this transaction.
     */
    public function ruangAsal(): BelongsTo
    {
        return $this->belongsTo(Ruang::class, 'ruang_asal_id', 'id');
    }

    /**
     * Get the ruang tujuan for this transaction.
     */
    public function ruangTujuan(): BelongsTo
    {
        return $this->belongsTo(Ruang::class, 'ruang_tujuan_id', 'id');
    }

    /**
     * Get the user who created this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved this transaction.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->approval_status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    /**
     * Check if transaction is rejected.
     */
    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    /**
     * Accessor: Get formatted approval status.
     */
    public function getApprovalStatusLabelAttribute(): string
    {
        return self::APPROVAL_STATUSES[$this->approval_status] ?? $this->approval_status;
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
}
