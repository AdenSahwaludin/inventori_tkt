<?php

namespace App\Filament\Widgets;

use App\Models\MasterBarang;
use App\Models\TransaksiBarang;
use App\Models\TransaksiKeluar;
use App\Models\UnitBarang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalMasterBarang = MasterBarang::count();
        $totalUnitAktif = UnitBarang::active()->count();
        $totalUnitRusak = UnitBarang::where('status', 'rusak')->count();
        $totalUnitDipinjam = UnitBarang::where('status', 'dipinjam')->count();
        $transaksiPending = TransaksiBarang::where('approval_status', 'pending')->count();
        $transaksiKeluarPending = TransaksiKeluar::where('approval_status', 'pending')->count();

        return [
            Stat::make('Total Jenis Barang', $totalMasterBarang)
                ->description('Jenis barang terdaftar')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Unit Aktif', $totalUnitAktif)
                ->description('Unit barang operasional')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Unit Dipinjam', $totalUnitDipinjam)
                ->description('Sedang dipinjam')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),

            Stat::make('Unit Rusak', $totalUnitRusak)
                ->description('Perlu perbaikan/disposal')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Transaksi Masuk Pending', $transaksiPending)
                ->description('Menunggu approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($transaksiPending > 0 ? 'warning' : 'success'),

            Stat::make('Transaksi Keluar Pending', $transaksiKeluarPending)
                ->description('Menunggu approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($transaksiKeluarPending > 0 ? 'warning' : 'success'),
        ];
    }
}
