<?php

namespace App\Policies;

use App\Models\MasterBarang;
use App\Models\User;

class MasterBarangPolicy
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
        return $user->hasPermissionTo('view_master_barangs');
    }

    public function view(User $user, MasterBarang $masterBarang): bool
    {
        return $user->hasPermissionTo('view_master_barangs');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_master_barangs');
    }

    public function update(User $user, MasterBarang $masterBarang): bool
    {
        return $user->hasPermissionTo('edit_master_barangs');
    }

    public function delete(User $user, MasterBarang $masterBarang): bool
    {
        return $user->hasPermissionTo('delete_master_barangs');
    }

    public function restore(User $user, MasterBarang $masterBarang): bool
    {
        return $user->hasPermissionTo('delete_master_barangs');
    }

    public function forceDelete(User $user, MasterBarang $masterBarang): bool
    {
        return false;
    }
}
