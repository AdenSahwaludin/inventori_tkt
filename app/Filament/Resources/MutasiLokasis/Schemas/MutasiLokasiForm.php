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
                Select::make('ruang_asal_id')
                    ->relationship('ruangAsal', 'nama_ruang')
                    ->required(),
                Select::make('ruang_tujuan_id')
                    ->relationship('ruangTujuan', 'nama_ruang')
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
