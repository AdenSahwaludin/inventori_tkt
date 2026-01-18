<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\TransaksiKeluar;
use App\Models\UnitBarang;

/**
 * Observer untuk TransaksiKeluar.
 *
 * PENTING: Saat approval_status berubah ke 'approved',
 * status unit_barang otomatis berubah ke 'dipinjam'.
 */
class TransaksiKeluarObserver
{
    /**
     * Handle the TransaksiKeluar "created" event.
     */
    public function created(TransaksiKeluar $transaksi): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Transaksi keluar baru dibuat: {$transaksi->kode_transaksi}",
            'transaksi_keluar',
            (string) $transaksi->id,
            ['new' => $transaksi->toArray()]
        );
    }

    /**
     * Handle the TransaksiKeluar "updated" event.
     */
    public function updated(TransaksiKeluar $transaksi): void
    {
        // Cek apakah approval_status berubah ke 'approved'
        if ($transaksi->isDirty('approval_status') && $transaksi->approval_status === TransaksiKeluar::STATUS_APPROVED) {
            $this->handleApproval($transaksi);
        }

        // Log perubahan
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Transaksi keluar diupdate: {$transaksi->kode_transaksi}",
            'transaksi_keluar',
            (string) $transaksi->id,
            [
                'old' => $transaksi->getOriginal(),
                'new' => $transaksi->getChanges(),
            ]
        );
    }

    /**
     * Handle approval: Update status unit_barang ke 'dipinjam'.
     */
    protected function handleApproval(TransaksiKeluar $transaksi): void
    {
        // Set approval metadata tanpa trigger observer lagi (jika belum di-set)
        if (! $transaksi->approved_by) {
            $transaksi->approved_by = auth()->id();
        }
        if (! $transaksi->approved_at) {
            $transaksi->approved_at = now();
        }
        $transaksi->saveQuietly();

        // Update status unit_barang
        UnitBarang::where('kode_unit', $transaksi->unit_barang_id)
            ->update(['status' => UnitBarang::STATUS_DIPINJAM]);

        // Log aktivitas approval
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Transaksi keluar {$transaksi->kode_transaksi} disetujui. Unit {$transaksi->unit_barang_id} status berubah ke 'dipinjam'.",
            'transaksi_keluar',
            (string) $transaksi->id
        );
    }

    /**
     * Handle the TransaksiKeluar "deleted" event.
     */
    public function deleted(TransaksiKeluar $transaksi): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Transaksi keluar dihapus: {$transaksi->kode_transaksi}",
            'transaksi_keluar',
            (string) $transaksi->id,
            ['deleted' => $transaksi->toArray()]
        );
    }
}
