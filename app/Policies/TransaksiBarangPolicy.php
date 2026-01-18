<?php

namespace App\Policies;

use App\Models\TransaksiBarang;
use App\Models\User;

class TransaksiBarangPolicy
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
        return $user->hasPermissionTo('view_transaksi_barangs');
    }

    public function view(User $user, TransaksiBarang $transaksi): bool
    {
        return $user->hasPermissionTo('view_transaksi_barangs');
    }

    public function create(User $user): bool
    {
        // Hanya Petugas (atau Admin via before())
        return $user->hasPermissionTo('create_transaksi_barangs');
    }

    /**
     * Aturan edit transaksi:
     * 1. Hanya pemilik transaksi (user_id === $user->id)
     * 2. Hanya saat status 'pending' (belum di-approve/reject)
     * 3. Setelah approved/rejected → READ-ONLY
     */
    public function update(User $user, TransaksiBarang $transaksi): bool
    {
        return $transaksi->approval_status === 'pending'
            && $transaksi->user_id === $user->id
            && $user->hasPermissionTo('edit_transaksi_barangs');
    }

    /**
     * Approve transaksi - hanya Kepala Sekolah (atau Admin via before()).
     */
    public function approve(User $user, TransaksiBarang $transaksi): bool
    {
        return $user->hasPermissionTo('approve_transaksi_barangs')
            && $transaksi->approval_status === 'pending';
    }

    /**
     * Tidak boleh delete transaksi (hanya Admin via before()).
     */
    public function delete(User $user, TransaksiBarang $transaksi): bool
    {
        return false;
    }

    public function restore(User $user, TransaksiBarang $transaksi): bool
    {
        return false;
    }

    public function forceDelete(User $user, TransaksiBarang $transaksi): bool
    {
        return false;
    }
}
