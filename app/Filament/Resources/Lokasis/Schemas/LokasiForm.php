<?php

namespace App\Filament\Resources\Lokasis\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LokasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_lokasi')
                    ->required(),
                TextInput::make('gedung'),
                TextInput::make('lantai'),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
