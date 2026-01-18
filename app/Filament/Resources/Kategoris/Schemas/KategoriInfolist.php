<?php

namespace App\Filament\Resources\Kategoris\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KategoriInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_kategori'),
                TextEntry::make('nama_kategori'),
                TextEntry::make('deleted_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
