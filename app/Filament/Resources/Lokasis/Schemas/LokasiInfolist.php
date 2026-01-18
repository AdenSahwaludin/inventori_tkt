<?php

namespace App\Filament\Resources\Lokasis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LokasiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_lokasi'),
                TextEntry::make('nama_lokasi'),
                TextEntry::make('gedung'),
                TextEntry::make('lantai'),
                TextEntry::make('deleted_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
