<?php

namespace App\Filament\Resources\BarangRusaks\Pages;

use App\Filament\Resources\BarangRusaks\BarangRusakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBarangRusak extends ViewRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
