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
     * Handle approval: Update status dan ruang unit_barang sesuai tipe transaksi.
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

        // Get unit barang
        $unit = UnitBarang::where('kode_unit', $transaksi->unit_barang_id)->first();
        
        if (! $unit) {
            return;
        }

        // Update berdasarkan tipe transaksi
        if ($transaksi->tipe === TransaksiKeluar::TIPE_PEMINDAHAN) {
            // Pemindahan: ubah ruang_id ke ruang tujuan
            $unit->update([
                'ruang_id' => $transaksi->ruang_tujuan_id,
                'status' => UnitBarang::STATUS_BAIK,
            ]);
            $message = "Transaksi pemindahan {$transaksi->kode_transaksi} disetujui. Unit {$transaksi->unit_barang_id} dipindahkan ke ruang {$unit->ruangTujuan?->nama_ruang}.";
        } else {
            // Peminjaman/Penggunaan/Penghapusan: ubah status, jangan ubah ruang
            $unit->update([
                'status' => UnitBarang::STATUS_DIPINJAM,
            ]);
            $message = "Transaksi {$transaksi->tipe} {$transaksi->kode_transaksi} disetujui. Unit {$transaksi->unit_barang_id} status berubah ke 'dipinjam'.";
        }

        // Log aktivitas approval
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            $message,
            'transaksi_keluar',
            (string) $transaksi->id
        );
    }

    /**
     * Handle the TransaksiKeluar "deleted" event.
     */
    public function deleted(TransaksiKeluar $transaksi): void
    {
        // Jika transaksi sudah approved (sudah punya approval_at), kembalikan unit barang
        if ($transaksi->approval_status === TransaksiKeluar::STATUS_APPROVED && $transaksi->approved_at) {
            $unit = UnitBarang::where('kode_unit', $transaksi->unit_barang_id)->first();
            
            if ($unit) {
                if ($transaksi->tipe === TransaksiKeluar::TIPE_PEMINDAHAN) {
                    // Pemindahan dihapus: kembalikan ke ruang asal
                    $unit->update([
                        'ruang_id' => $transaksi->ruang_asal_id,
                    ]);
                } else {
                    // Peminjaman/Penggunaan dihapus: kembalikan status ke baik
                    $unit->update([
                        'status' => UnitBarang::STATUS_BAIK,
                    ]);
                }
            }
        }

        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Transaksi keluar dihapus: {$transaksi->kode_transaksi}",
            'transaksi_keluar',
            (string) $transaksi->id,
            ['deleted' => $transaksi->toArray()]
        );
    }
}
