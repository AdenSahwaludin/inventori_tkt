<?php

namespace App\Filament\Resources\MutasiLokasis\Pages;

use App\Filament\Resources\MutasiLokasis\MutasiLokasiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMutasiLokasi extends ViewRecord
{
    protected static string $resource = MutasiLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
