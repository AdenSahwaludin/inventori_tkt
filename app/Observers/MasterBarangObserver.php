<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\MasterBarang;
use App\Models\UnitBarang;

/**
 * Observer untuk MasterBarang.
 *
 * PENTING: Saat master barang dibuat dengan distribusi_ruang,
 * sistem akan auto-generate unit_barang sesuai distribusi.
 */
class MasterBarangObserver
{
    /**
     * Handle the MasterBarang "created" event.
     * Auto-generate UnitBarang sesuai distribusi_ruang.
     */
    public function created(MasterBarang $master): void
    {
        // Auto-generate UnitBarang sesuai distribusi_ruang
        if ($master->distribusi_lokasi && is_array($master->distribusi_lokasi)) {
            $unitCounter = 1;

            foreach ($master->distribusi_lokasi as $distribusi) {
                $ruangId = $distribusi['ruang_id'] ?? null;
                $jumlah = (int) ($distribusi['jumlah'] ?? 0);

                if (! $ruangId || $jumlah <= 0) {
                    continue;
                }

                // Generate unit untuk ruang ini
                for ($i = 0; $i < $jumlah; $i++) {
                    $kodeUnit = sprintf('%s-%03d', $master->kode_master, $unitCounter);

                    $unit = UnitBarang::create([
                        'kode_unit' => $kodeUnit,
                        'master_barang_id' => $master->kode_master,
                        'ruang_id' => $ruangId,
                        'status' => UnitBarang::STATUS_BAIK,
                        'is_active' => true,
                        'tanggal_pembelian' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    $unitCounter++;
                }
            }

            // Log aktivitas
            LogAktivitas::log(
                LogAktivitas::TYPE_CREATE,
                "Master barang {$master->nama_barang} dibuat dengan ".($unitCounter - 1).' unit',
                'master_barang',
                $master->kode_master,
                [
                    'nama_barang' => $master->nama_barang,
                    'jumlah_unit' => $unitCounter - 1,
                    'distribusi_lokasi' => $master->distribusi_lokasi,
                ]
            );
        } else {
            // Log aktivitas tanpa unit
            LogAktivitas::log(
                LogAktivitas::TYPE_CREATE,
                "Master barang {$master->nama_barang} dibuat (tanpa unit)",
                'master_barang',
                $master->kode_master,
                ['nama_barang' => $master->nama_barang]
            );
        }
    }

    /**
     * Handle the MasterBarang "updated" event.
     */
    public function updated(MasterBarang $master): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Master barang {$master->nama_barang} diupdate",
            'master_barang',
            $master->kode_master,
            [
                'old' => $master->getOriginal(),
                'new' => $master->getChanges(),
            ]
        );
    }

    /**
     * Handle the MasterBarang "deleted" event.
     */
    public function deleted(MasterBarang $master): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Master barang {$master->nama_barang} dihapus (soft delete)",
            'master_barang',
            $master->kode_master,
            ['deleted' => $master->toArray()]
        );
    }
}
