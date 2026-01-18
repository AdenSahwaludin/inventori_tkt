<?php

namespace App\Filament\Resources\MutasiLokasis\Pages;

use App\Filament\Resources\MutasiLokasis\MutasiLokasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMutasiLokasis extends ListRecords
{
    protected static string $resource = MutasiLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
