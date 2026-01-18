<?php

namespace App\Filament\Resources\BarangRusaks\Pages;

use App\Filament\Resources\BarangRusaks\BarangRusakResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBarangRusak extends EditRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
