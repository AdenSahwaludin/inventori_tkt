<?php

namespace App\Filament\Resources\Ruangs\Pages;

use App\Filament\Resources\Ruangs\RuangResource;
use App\Filament\Resources\UnitBarangs\UnitBarangResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRuang extends ViewRecord
{
    protected static string $resource = RuangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lihat_barang')
                ->label('Lihat Barang')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => UnitBarangResource::getUrl('index', ['tableFilters[ruang_id][value]' => $this->record->id])),
            DeleteAction::make()
                ->visible(fn () => auth()->user()->can('delete_ruangs')),
        ];
    }
}
