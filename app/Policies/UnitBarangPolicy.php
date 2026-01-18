<?php

namespace App\Policies;

use App\Models\UnitBarang;
use App\Models\User;

class UnitBarangPolicy
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
        return $user->hasPermissionTo('view_unit_barangs');
    }

    public function view(User $user, UnitBarang $unitBarang): bool
    {
        return $user->hasPermissionTo('view_unit_barangs');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_unit_barangs');
    }

    public function update(User $user, UnitBarang $unitBarang): bool
    {
        return $user->hasPermissionTo('edit_unit_barangs');
    }

    /**
     * Unit barang tidak boleh dihapus, hanya dinonaktifkan.
     * Hanya Admin yang bisa nonaktifkan via before().
     */
    public function delete(User $user, UnitBarang $unitBarang): bool
    {
        return false;
    }

    /**
     * Nonaktifkan unit barang (soft delete alternative).
     */
    public function nonaktifkan(User $user, UnitBarang $unitBarang): bool
    {
        return $user->hasPermissionTo('nonaktifkan_unit_barangs');
    }

    public function restore(User $user, UnitBarang $unitBarang): bool
    {
        return false;
    }

    public function forceDelete(User $user, UnitBarang $unitBarang): bool
    {
        return false;
    }
}
