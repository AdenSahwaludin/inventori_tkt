<?php

namespace App\Policies;

use App\Models\BarangRusak;
use App\Models\User;

class BarangRusakPolicy
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
        return $user->hasPermissionTo('view_barang_rusaks');
    }

    public function view(User $user, BarangRusak $barangRusak): bool
    {
        return $user->hasPermissionTo('view_barang_rusaks');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_barang_rusaks');
    }

    /**
     * Laporan barang rusak tidak bisa diedit setelah dibuat.
     */
    public function update(User $user, BarangRusak $barangRusak): bool
    {
        return false;
    }

    public function delete(User $user, BarangRusak $barangRusak): bool
    {
        return false;
    }

    public function restore(User $user, BarangRusak $barangRusak): bool
    {
        return false;
    }

    public function forceDelete(User $user, BarangRusak $barangRusak): bool
    {
        return false;
    }
}
