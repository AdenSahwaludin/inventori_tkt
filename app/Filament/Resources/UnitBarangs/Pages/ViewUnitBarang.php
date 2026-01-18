<?php

namespace App\Filament\Resources\UnitBarangs\Pages;

use App\Filament\Resources\UnitBarangs\UnitBarangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUnitBarang extends ViewRecord
{
    protected static string $resource = UnitBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
