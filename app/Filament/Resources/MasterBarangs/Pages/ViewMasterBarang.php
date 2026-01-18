<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterBarang extends ViewRecord
{
    protected static string $resource = MasterBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
