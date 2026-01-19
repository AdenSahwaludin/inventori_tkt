<?php

namespace App\Filament\Resources\Ruangs\Pages;

use App\Filament\Resources\Ruangs\RuangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRuang extends ViewRecord
{
    protected static string $resource = RuangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()->can('delete_ruangs')),
        ];
    }
}
