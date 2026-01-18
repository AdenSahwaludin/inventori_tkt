<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model LogAktivitas - Audit Log.
 *
 * Menyimpan semua aktivitas CRUD untuk audit trail.
 * Record dibuat otomatis oleh Observers pada setiap model.
 */
class LogAktivitas extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'log_aktivitas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'jenis_aktivitas',
        'nama_tabel',
        'record_id',
        'deskripsi',
        'perubahan_data',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'perubahan_data' => 'array',
    ];

    /**
     * Activity type constants.
     */
    public const TYPE_LOGIN = 'login';

    public const TYPE_LOGOUT = 'logout';

    public const TYPE_CREATE = 'create';

    public const TYPE_UPDATE = 'update';

    public const TYPE_DELETE = 'delete';

    public const TYPE_VIEW = 'view';

    public const ACTIVITY_TYPES = [
        self::TYPE_LOGIN => 'Login',
        self::TYPE_LOGOUT => 'Logout',
        self::TYPE_CREATE => 'Buat',
        self::TYPE_UPDATE => 'Update',
        self::TYPE_DELETE => 'Hapus',
        self::TYPE_VIEW => 'Lihat',
    ];

    /**
     * Get the user who performed this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: Get formatted jenis aktivitas.
     */
    public function getJenisAktivitasLabelAttribute(): string
    {
        return self::ACTIVITY_TYPES[$this->jenis_aktivitas] ?? $this->jenis_aktivitas;
    }

    /**
     * Scope untuk filter berdasarkan jenis aktivitas.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('jenis_aktivitas', $type);
    }

    /**
     * Scope untuk filter berdasarkan tabel.
     */
    public function scopeForTable($query, string $table)
    {
        return $query->where('nama_tabel', $table);
    }

    /**
     * Scope untuk filter berdasarkan user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /**
     * Helper method untuk membuat log aktivitas.
     */
    public static function log(
        string $jenisAktivitas,
        string $deskripsi,
        ?string $namaTabel = null,
        ?string $recordId = null,
        ?array $perubahanData = null
    ): self {
        return self::create([
            'user_id' => auth()->id() ?? 1,
            'jenis_aktivitas' => $jenisAktivitas,
            'nama_tabel' => $namaTabel,
            'record_id' => $recordId,
            'deskripsi' => $deskripsi,
            'perubahan_data' => $perubahanData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
