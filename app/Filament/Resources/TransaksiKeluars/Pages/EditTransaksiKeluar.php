<?php

namespace App\Filament\Resources\TransaksiKeluars\Pages;

use App\Filament\Resources\TransaksiKeluars\TransaksiKeluarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiKeluar extends EditRecord
{
    protected static string $resource = TransaksiKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
