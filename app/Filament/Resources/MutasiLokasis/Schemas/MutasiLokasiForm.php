<?php

namespace App\Filament\Resources\MutasiLokasis\Schemas;

use App\Models\UnitBarang;
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
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $unit = UnitBarang::find($state);
                            $set('ruang_asal_id', $unit?->ruang_id);
                        }
                    }),
                Select::make('ruang_asal_id')
                    ->label('Ruang Asal')
                    ->relationship('ruangAsal', 'nama_ruang')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Otomatis terisi dari lokasi unit barang'),
                Select::make('ruang_tujuan_id')
                    ->label('Ruang Tujuan')
                    ->relationship('ruangTujuan', 'nama_ruang')
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('tanggal_mutasi')
                    ->label('Tanggal Mutasi')
                    ->required(),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                TextInput::make('user_id')
                    ->label('User')
                    ->default(fn () => auth()->id())
                    ->dehydrated()
                    ->hidden(),
            ]);
    }
}
