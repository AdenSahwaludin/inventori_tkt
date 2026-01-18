<?php

namespace App\Filament\Resources\UnitBarangs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('master_barang_id')
                    ->relationship('masterBarang', 'kode_master')
                    ->required(),
                Select::make('lokasi_id')
                    ->relationship('lokasi', 'kode_lokasi')
                    ->required(),
                Select::make('status')
                    ->options([
                        'baik' => 'Baik',
                        'dipinjam' => 'Dipinjam',
                        'rusak' => 'Rusak',
                        'maintenance' => 'Maintenance',
                        'hilang' => 'Hilang',
                        'dihapus' => 'Dihapus',
                    ])
                    ->default('baik')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                DatePicker::make('tanggal_pembelian'),
                Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }
}
