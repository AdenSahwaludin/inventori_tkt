<?php

namespace App\Policies;

use App\Models\LogAktivitas;
use App\Models\User;

class LogAktivitasPolicy
{
    /**
     * Admin bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_log_aktivitas');
    }

    /**
     * View single log:
     * - Kepala Sekolah & Admin: bisa view semua log
     * - Petugas: hanya bisa view log miliknya sendiri
     */
    public function view(User $user, LogAktivitas $log): bool
    {
        if ($user->hasRole('Kepala Sekolah')) {
            return true;
        }

        // Petugas hanya boleh melihat log milik sendiri
        return $log->user_id === $user->id
            && $user->hasPermissionTo('view_log_aktivitas');
    }

    /**
     * Log aktivitas dibuat otomatis oleh sistem.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LogAktivitas $log): bool
    {
        return false;
    }

    public function delete(User $user, LogAktivitas $log): bool
    {
        return false;
    }

    public function restore(User $user, LogAktivitas $log): bool
    {
        return false;
    }

    public function forceDelete(User $user, LogAktivitas $log): bool
    {
        return false;
    }
}
