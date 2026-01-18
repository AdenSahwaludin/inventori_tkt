<?php

namespace App\Filament\Widgets;

use App\Models\Lokasi;
use Filament\Widgets\ChartWidget;

class BarangPerLokasiWidget extends ChartWidget
{
    protected ?string $heading = 'Distribusi Barang per Lokasi';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $lokasis = Lokasi::withCount(['unitBarang' => function ($query) {
            $query->where('is_active', true);
        }])
            ->orderByDesc('unit_barang_count')
            ->take(8)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $lokasis->pluck('unit_barang_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',   // blue
                        'rgb(16, 185, 129)',   // green
                        'rgb(245, 158, 11)',   // yellow
                        'rgb(239, 68, 68)',    // red
                        'rgb(139, 92, 246)',   // purple
                        'rgb(236, 72, 153)',   // pink
                        'rgb(20, 184, 166)',   // teal
                        'rgb(249, 115, 22)',   // orange
                    ],
                ],
            ],
            'labels' => $lokasis->pluck('nama_lokasi')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
