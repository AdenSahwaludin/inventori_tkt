<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_barang')
                    ->required(),
                Select::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori')
                    ->required(),
                TextInput::make('satuan')
                    ->required()
                    ->default('pcs'),
                TextInput::make('merk'),
                TextInput::make('harga_satuan')
                    ->numeric(),
                TextInput::make('reorder_point')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }
}
