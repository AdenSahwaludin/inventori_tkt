<?php

namespace App\Filament\Resources\TransaksiKeluars\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransaksiKeluarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_transaksi'),
                TextEntry::make('unitBarang.kode_unit'),
                TextEntry::make('tanggal_transaksi')
                    ->date(),
                TextEntry::make('penerima'),
                TextEntry::make('tujuan'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('approved_by')
                    ->numeric(),
                TextEntry::make('approved_at')
                    ->dateTime(),
                TextEntry::make('approval_status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
