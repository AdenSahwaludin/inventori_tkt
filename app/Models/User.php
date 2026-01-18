<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all transaksi barang created by this user.
     */
    public function transaksiBarang(): HasMany
    {
        return $this->hasMany(TransaksiBarang::class, 'user_id');
    }

    /**
     * Get all transaksi barang approved by this user.
     */
    public function approvedTransaksiBarang(): HasMany
    {
        return $this->hasMany(TransaksiBarang::class, 'approved_by');
    }

    /**
     * Get all transaksi keluar created by this user.
     */
    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(TransaksiKeluar::class, 'user_id');
    }

    /**
     * Get all log aktivitas for this user.
     */
    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }

    /**
     * Get all backup logs for this user.
     */
    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class, 'user_id');
    }
}
