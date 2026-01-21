<?php

namespace App\Filament\Resources\BarangRusaks\Pages;

use App\Filament\Resources\BarangRusaks\BarangRusakResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangRusak extends CreateRecord
{
    protected static string $resource = BarangRusakResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
