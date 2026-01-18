<?php

namespace App\Filament\Widgets;

use App\Models\MasterBarang;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BarangStokRendahWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ Barang Stok Rendah';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MasterBarang::query()
                    ->withCount(['unitBarang' => function (Builder $query) {
                        $query->where('is_active', true)
                            ->where('status', 'baik');
                    }])
                    ->whereRaw('(SELECT COUNT(*) FROM unit_barang WHERE unit_barang.master_barang_id = master_barang.kode_master AND unit_barang.is_active = 1 AND unit_barang.status = ?) <= master_barang.reorder_point', ['baik'])
                    ->orderByRaw('(SELECT COUNT(*) FROM unit_barang WHERE unit_barang.master_barang_id = master_barang.kode_master AND unit_barang.is_active = 1 AND unit_barang.status = ?) ASC', ['baik'])
            )
            ->columns([
                TextColumn::make('kode_master')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('unit_barang_count')
                    ->label('Stok Tersedia')
                    ->alignCenter()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('reorder_point')
                    ->label('Min. Stok')
                    ->alignCenter(),
            ])
            ->paginated([5]);
    }
}
