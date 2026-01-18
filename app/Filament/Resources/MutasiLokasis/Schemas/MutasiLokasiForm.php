<?php

namespace App\Filament\Resources\MutasiLokasis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MutasiLokasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_barang_id')
                    ->relationship('unitBarang', 'kode_unit')
                    ->required(),
                TextInput::make('lokasi_asal')
                    ->required(),
                TextInput::make('lokasi_tujuan')
                    ->required(),
                DatePicker::make('tanggal_mutasi')
                    ->required(),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
