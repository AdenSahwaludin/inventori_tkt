<?php

namespace App\Filament\Widgets;

use App\Models\Ruang;
use Filament\Widgets\ChartWidget;

class BarangPerLokasiWidget extends ChartWidget
{
    protected ?string $heading = 'Distribusi Barang per Ruang';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $ruangs = Ruang::withCount(['unitBarangs' => function ($query) {
            $query->where('is_active', true);
        }])
            ->orderByDesc('unit_barangs_count')
            ->take(8)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $ruangs->pluck('unit_barangs_count')->toArray(),
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
            'labels' => $ruangs->pluck('nama_ruang')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
