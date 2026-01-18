<?php

namespace App\Filament\Resources\MutasiLokasis\Pages;

use App\Filament\Resources\MutasiLokasis\MutasiLokasiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMutasiLokasi extends EditRecord
{
    protected static string $resource = MutasiLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
