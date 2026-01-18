<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->hasPermissionTo('view_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view_users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('edit_users');
    }

    public function delete(User $user, User $model): bool
    {
        // Tidak bisa delete diri sendiri
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('delete_users');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('delete_users');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
