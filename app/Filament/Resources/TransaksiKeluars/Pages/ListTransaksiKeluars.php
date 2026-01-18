<?php

namespace App\Filament\Resources\TransaksiKeluars\Pages;

use App\Filament\Resources\TransaksiKeluars\TransaksiKeluarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiKeluars extends ListRecords
{
    protected static string $resource = TransaksiKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
