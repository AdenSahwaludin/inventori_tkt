<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model TransaksiBarang - Transaksi Masuk Barang.
 *
 * PENTING: Transaksi masuk berelasi ke master_barang, BUKAN unit_barang.
 * Unit barang akan di-generate otomatis saat transaksi di-approve
 * sebanyak field 'jumlah'.
 */
class TransaksiBarang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'transaksi_barang';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode_transaksi',
        'master_barang_id',
        'ruang_tujuan_id',
        'tanggal_transaksi',
        'jumlah',
        'penanggung_jawab',
        'keterangan',
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
        'jumlah' => 'integer',
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
     * Pattern: TRX-MASUK-YYYYMMDD-XXX
     */
    public static function generateKodeTransaksi(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TRX-MASUK-'.$date.'-';

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
     * Get the master barang for this transaction.
     */
    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id', 'kode_master');
    }

    /**
     * Get the target ruang for this transaction.
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
     * Accessor: Get nama barang from master.
     */
    public function getNamaBarangAttribute(): string
    {
        return $this->masterBarang?->nama_barang ?? '-';
    }

    /**
     * Accessor: Get nama ruang tujuan.
     */
    public function getNamaRuangTujuanAttribute(): string
    {
        return $this->ruangTujuan?->nama_ruang ?? '-';
    }
}
