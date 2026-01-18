<?php

namespace App\Filament\Resources\UnitBarangs\Pages;

use App\Filament\Resources\UnitBarangs\UnitBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnitBarangs extends ListRecords
{
    protected static string $resource = UnitBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
