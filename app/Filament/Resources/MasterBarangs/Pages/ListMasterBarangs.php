<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterBarangs extends ListRecords
{
    protected static string $resource = MasterBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
