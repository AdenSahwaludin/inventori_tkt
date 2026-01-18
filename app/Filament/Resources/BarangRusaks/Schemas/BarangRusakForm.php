<?php

namespace App\Filament\Resources\BarangRusaks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BarangRusakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_barang_id')
                    ->relationship('unitBarang', 'kode_unit')
                    ->required(),
                DatePicker::make('tanggal_kejadian')
                    ->required(),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                TextInput::make('penanggung_jawab')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
