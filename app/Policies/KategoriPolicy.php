<?php

namespace App\Policies;

use App\Models\Kategori;
use App\Models\User;

class KategoriPolicy
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
        return $user->hasPermissionTo('view_kategoris');
    }

    public function view(User $user, Kategori $kategori): bool
    {
        return $user->hasPermissionTo('view_kategoris');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_kategoris');
    }

    public function update(User $user, Kategori $kategori): bool
    {
        return $user->hasPermissionTo('edit_kategoris');
    }

    public function delete(User $user, Kategori $kategori): bool
    {
        return $user->hasPermissionTo('delete_kategoris');
    }

    public function restore(User $user, Kategori $kategori): bool
    {
        return $user->hasPermissionTo('delete_kategoris');
    }

    public function forceDelete(User $user, Kategori $kategori): bool
    {
        return false;
    }
}
