<?php

namespace App\Filament\Resources\UnitBarangs\Pages;

use App\Filament\Resources\UnitBarangs\UnitBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUnitBarang extends EditRecord
{
    protected static string $resource = UnitBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
