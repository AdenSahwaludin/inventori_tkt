<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\MutasiLokasi;
use App\Models\UnitBarang;

/**
 * Observer untuk UnitBarang.
 *
 * Handles:
 * 1. Tracking mutasi lokasi saat lokasi_id berubah
 * 2. Logging semua aktivitas CRUD
 * 3. Prevent actual delete (gunakan nonaktifkan() sebagai gantinya)
 */
class UnitBarangObserver
{
    /**
     * Handle the UnitBarang "created" event.
     */
    public function created(UnitBarang $unit): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_CREATE,
            "Unit barang baru dibuat: {$unit->kode_unit}",
            'unit_barang',
            $unit->kode_unit,
            ['new' => $unit->toArray()]
        );
    }

    /**
     * Handle the UnitBarang "updating" event.
     * Dijalankan SEBELUM data disimpan.
     */
    public function updating(UnitBarang $unit): void
    {
        // Cek apakah lokasi_id berubah
        if ($unit->isDirty('lokasi_id')) {
            $lokasiAsal = $unit->getOriginal('lokasi_id');
            $lokasiTujuan = $unit->lokasi_id;

            // Hanya buat mutasi jika lokasi asal ada (bukan unit baru)
            if ($lokasiAsal) {
                MutasiLokasi::create([
                    'unit_barang_id' => $unit->kode_unit,
                    'lokasi_asal' => $lokasiAsal,
                    'lokasi_tujuan' => $lokasiTujuan,
                    'tanggal_mutasi' => now()->toDateString(),
                    'user_id' => auth()->id() ?? 1,
                    'keterangan' => 'Mutasi lokasi via admin panel',
                ]);
            }
        }
    }

    /**
     * Handle the UnitBarang "updated" event.
     */
    public function updated(UnitBarang $unit): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_UPDATE,
            "Unit barang diupdate: {$unit->kode_unit}",
            'unit_barang',
            $unit->kode_unit,
            [
                'old' => $unit->getOriginal(),
                'new' => $unit->getChanges(),
            ]
        );
    }

    /**
     * Handle the UnitBarang "deleting" event.
     * PREVENT actual delete - gunakan nonaktifkan() sebagai gantinya.
     *
     * CATATAN: Ini akan mencegah delete fisik.
     * Untuk nonaktifkan unit, gunakan method $unit->nonaktifkan()
     * atau custom Filament Action "Nonaktifkan Unit".
     */
    public function deleting(UnitBarang $unit): bool
    {
        // Log attempt to delete
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Percobaan hapus unit barang: {$unit->kode_unit} - Dialihkan ke nonaktifkan",
            'unit_barang',
            $unit->kode_unit
        );

        // Nonaktifkan unit daripada delete
        $unit->status = UnitBarang::STATUS_DIHAPUS;
        $unit->is_active = false;
        $unit->saveQuietly();

        // Return false to prevent actual deletion
        return false;
    }

    /**
     * Handle the UnitBarang "deleted" event.
     * Seharusnya tidak pernah dipanggil karena deleting() return false.
     */
    public function deleted(UnitBarang $unit): void
    {
        LogAktivitas::log(
            LogAktivitas::TYPE_DELETE,
            "Unit barang dihapus: {$unit->kode_unit}",
            'unit_barang',
            $unit->kode_unit,
            ['deleted' => $unit->toArray()]
        );
    }
}
