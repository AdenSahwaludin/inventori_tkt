<?php

namespace App\Policies;

use App\Models\Lokasi;
use App\Models\User;

class LokasiPolicy
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
        return $user->hasPermissionTo('view_lokasis');
    }

    public function view(User $user, Lokasi $lokasi): bool
    {
        return $user->hasPermissionTo('view_lokasis');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_lokasis');
    }

    public function update(User $user, Lokasi $lokasi): bool
    {
        return $user->hasPermissionTo('edit_lokasis');
    }

    public function delete(User $user, Lokasi $lokasi): bool
    {
        return $user->hasPermissionTo('delete_lokasis');
    }

    public function restore(User $user, Lokasi $lokasi): bool
    {
        return $user->hasPermissionTo('delete_lokasis');
    }

    public function forceDelete(User $user, Lokasi $lokasi): bool
    {
        return false;
    }
}
