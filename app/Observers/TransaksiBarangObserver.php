<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\TransaksiBarang;
use App\Models\UnitBarang;

/**
 * Observer untuk TransaksiBarang (Transaksi Masuk).
 *
 * PENTING: Saat approval_status berubah ke 'approved',
 * sistem akan auto-generate unit_barang sebanyak field 'jumlah'.
 */
class TransaksiBarangObserver
{
    /**
     * Handle the TransaksiBarang "created" event.
     */
    public function created(TransaksiBarang $transaksi): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Transaksi masuk baru dibuat: {$transaksi->kode_transaksi}",
            'transaksi_barang',
            (string) $transaksi->id,
            ['new' => $transaksi->toArray()]
        );
    }

    /**
     * Handle the TransaksiBarang "updated" event.
     */
    public function updated(TransaksiBarang $transaksi): void
    {
        // Cek apakah approval_status berubah ke 'approved'
        if ($transaksi->isDirty('approval_status') && $transaksi->approval_status === TransaksiBarang::STATUS_APPROVED) {
            $this->handleApproval($transaksi);
        }

        // Log perubahan
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Transaksi masuk diupdate: {$transaksi->kode_transaksi}",
            'transaksi_barang',
            (string) $transaksi->id,
            [
                'old' => $transaksi->getOriginal(),
                'new' => $transaksi->getChanges(),
            ]
        );
    }

    /**
     * Handle approval: Auto-generate unit_barang sebanyak jumlah per ruang.
     */
    protected function handleApproval(TransaksiBarang $transaksi): void
    {
        // Set approval metadata tanpa trigger observer lagi (jika belum di-set)
        if (! $transaksi->approved_by) {
            $transaksi->approved_by = auth()->id();
        }
        if (! $transaksi->approved_at) {
            $transaksi->approved_at = now();
        }
        $transaksi->saveQuietly();

        // Generate unit_barang untuk setiap ruang sesuai distribusi_lokasi
        $distribusiLokasi = $transaksi->distribusi_lokasi ?? [];
        
        foreach ($distribusiLokasi as $distribusi) {
            $ruangId = $distribusi['ruang_id'] ?? null;
            $jumlah = (int) ($distribusi['jumlah'] ?? 0);
            
            if (! $ruangId || $jumlah <= 0) {
                continue;
            }
            
            // Generate unit_barang sebanyak jumlah untuk ruang ini
            for ($i = 0; $i < $jumlah; $i++) {
                UnitBarang::create([
                    'master_barang_id' => $transaksi->master_barang_id,
                    'ruang_id' => $ruangId,
                    'status' => UnitBarang::STATUS_BAIK,
                    'is_active' => true,
                    'tanggal_pembelian' => $transaksi->tanggal_transaksi,
                    'catatan' => 'Auto-generated dari transaksi: '.$transaksi->kode_transaksi,
                ]);
            }
        }

        // Hitung total jumlah dari semua distribusi
        $totalJumlah = collect($distribusiLokasi)->sum('jumlah');

        // Log aktivitas approval
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Transaksi {$transaksi->kode_transaksi} disetujui. {$totalJumlah} unit barang berhasil dibuat ke berbagai ruang.",
            'transaksi_barang',
            (string) $transaksi->id
        );
    }

    /**
     * Handle the TransaksiBarang "deleted" event.
     */
    public function deleted(TransaksiBarang $transaksi): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Transaksi masuk dihapus: {$transaksi->kode_transaksi}",
            'transaksi_barang',
            (string) $transaksi->id,
            ['deleted' => $transaksi->toArray()]
        );
    }
}
