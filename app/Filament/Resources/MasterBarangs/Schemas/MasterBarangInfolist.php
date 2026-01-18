<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MasterBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_master'),
                TextEntry::make('nama_barang'),
                TextEntry::make('kategori.kode_kategori'),
                TextEntry::make('satuan'),
                TextEntry::make('merk'),
                TextEntry::make('harga_satuan')
                    ->numeric(),
                TextEntry::make('reorder_point')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
