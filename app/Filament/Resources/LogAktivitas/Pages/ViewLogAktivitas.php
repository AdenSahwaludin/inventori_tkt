<?php

namespace App\Filament\Resources\LogAktivitas\Pages;

use App\Filament\Resources\LogAktivitas\LogAktivitasResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLogAktivitas extends ViewRecord
{
    protected static string $resource = LogAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
