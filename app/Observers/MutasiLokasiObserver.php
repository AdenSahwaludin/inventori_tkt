<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\MutasiLokasi;
use App\Models\UnitBarang;

class MutasiLokasiObserver
{
    /**
     * Handle the MutasiLokasi "created" event.
     * Ubah ruang_id unit barang sesuai mutasi yang dibuat.
     */
    public function created(MutasiLokasi $mutasiLokasi): void
    {
        // Update ruang_id unit barang ke ruang tujuan
        UnitBarang::where('kode_unit', $mutasiLokasi->unit_barang_id)
            ->update(['ruang_id' => $mutasiLokasi->ruang_tujuan_id]);

        // Log aktivitas
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Mutasi lokasi dibuat: Unit {$mutasiLokasi->unit_barang_id} berpindah dari ruang {$mutasiLokasi->ruangAsal?->nama_ruang} ke {$mutasiLokasi->ruangTujuan?->nama_ruang}",
            'mutasi_lokasi',
            (string) $mutasiLokasi->id,
            ['new' => $mutasiLokasi->toArray()]
        );
    }

    /**
     * Handle the MutasiLokasi "deleted" event.
     * Kembalikan ruang_id unit barang ke ruang asal saat mutasi dihapus.
     */
    public function deleted(MutasiLokasi $mutasiLokasi): void
    {
        // Kembalikan ruang_id unit barang ke ruang asal
        UnitBarang::where('kode_unit', $mutasiLokasi->unit_barang_id)
            ->update(['ruang_id' => $mutasiLokasi->ruang_asal_id]);

        // Log aktivitas
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Mutasi lokasi dihapus: Unit {$mutasiLokasi->unit_barang_id} dikembalikan ke ruang {$mutasiLokasi->ruangAsal?->nama_ruang}",
            'mutasi_lokasi',
            (string) $mutasiLokasi->id,
            ['deleted' => $mutasiLokasi->toArray()]
        );
    }
}
