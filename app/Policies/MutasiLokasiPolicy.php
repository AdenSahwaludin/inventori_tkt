<?php

namespace App\Policies;

use App\Models\MutasiLokasi;
use App\Models\User;

class MutasiLokasiPolicy
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
        return $user->hasPermissionTo('view_mutasi_lokasis');
    }

    public function view(User $user, MutasiLokasi $mutasiLokasi): bool
    {
        return $user->hasPermissionTo('view_mutasi_lokasis');
    }

    /**
     * Mutasi lokasi dibuat otomatis oleh sistem (observer).
     * Tidak ada create manual.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MutasiLokasi $mutasiLokasi): bool
    {
        return false;
    }

    public function delete(User $user, MutasiLokasi $mutasiLokasi): bool
    {
        return false;
    }

    public function restore(User $user, MutasiLokasi $mutasiLokasi): bool
    {
        return false;
    }

    public function forceDelete(User $user, MutasiLokasi $mutasiLokasi): bool
    {
        return false;
    }
}
