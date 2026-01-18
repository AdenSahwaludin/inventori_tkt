<?php

namespace App\Filament\Resources\MutasiLokasis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MutasiLokasiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('unitBarang.kode_unit'),
                TextEntry::make('lokasi_asal'),
                TextEntry::make('lokasi_tujuan'),
                TextEntry::make('tanggal_mutasi')
                    ->date(),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
