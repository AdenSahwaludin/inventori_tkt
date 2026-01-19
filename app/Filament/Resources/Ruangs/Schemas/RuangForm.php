<?php

namespace App\Filament\Resources\Ruangs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RuangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_ruang')
                    ->label('Nama Ruang')
                    ->placeholder('Contoh: Gudang Utama, Lab Komputer')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
