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
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('tanggal_kejadian')
                    ->label('Tanggal Kejadian')
                    ->required(),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                TextInput::make('penanggung_jawab')
                    ->required(),
                TextInput::make('user_id')
                    ->label('User')
                    ->default(fn () => auth()->id())
                    ->dehydrated()
                    ->hidden(),
            ]);
    }
}
