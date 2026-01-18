<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiBarang;
use App\Models\TransaksiKeluar;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransaksiChartWidget extends ChartWidget
{
    protected ?string $heading = 'Transaksi 6 Bulan Terakhir';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = collect();
        $transaksiMasuk = collect();
        $transaksiKeluar = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->translatedFormat('M Y'));

            $transaksiMasuk->push(
                TransaksiBarang::where('approval_status', 'approved')
                    ->whereYear('tanggal_transaksi', $date->year)
                    ->whereMonth('tanggal_transaksi', $date->month)
                    ->sum('jumlah')
            );

            $transaksiKeluar->push(
                TransaksiKeluar::where('approval_status', 'approved')
                    ->whereYear('tanggal_transaksi', $date->year)
                    ->whereMonth('tanggal_transaksi', $date->month)
                    ->count()
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk (unit)',
                    'data' => $transaksiMasuk->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Barang Keluar (transaksi)',
                    'data' => $transaksiKeluar->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
