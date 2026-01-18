<?php

namespace App\Observers;

use App\Models\BarangRusak;
use App\Models\LogAktivitas;
use App\Models\UnitBarang;

/**
 * Observer untuk BarangRusak.
 *
 * PENTING: Saat laporan barang rusak dibuat,
 * status unit_barang otomatis berubah ke 'rusak'.
 */
class BarangRusakObserver
{
    /**
     * Handle the BarangRusak "created" event.
     */
    public function created(BarangRusak $barangRusak): void
    {
        // Update status unit_barang ke 'rusak'
        UnitBarang::where('kode_unit', $barangRusak->unit_barang_id)
            ->update(['status' => UnitBarang::STATUS_RUSAK]);

        // Log aktivitas
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Laporan barang rusak dibuat untuk unit: {$barangRusak->unit_barang_id}",
            'barang_rusak',
            (string) $barangRusak->id,
            ['new' => $barangRusak->toArray()]
        );

        // Log perubahan status unit
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Status unit {$barangRusak->unit_barang_id} berubah ke 'rusak' (laporan kerusakan)",
            'unit_barang',
            $barangRusak->unit_barang_id
        );
    }

    /**
     * Handle the BarangRusak "updated" event.
     */
    public function updated(BarangRusak $barangRusak): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Laporan barang rusak diupdate: {$barangRusak->id}",
            'barang_rusak',
            (string) $barangRusak->id,
            [
                'old' => $barangRusak->getOriginal(),
                'new' => $barangRusak->getChanges(),
            ]
        );
    }

    /**
     * Handle the BarangRusak "deleted" event.
     */
    public function deleted(BarangRusak $barangRusak): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Laporan barang rusak dihapus: {$barangRusak->id}",
            'barang_rusak',
            (string) $barangRusak->id,
            ['deleted' => $barangRusak->toArray()]
        );
    }
}
