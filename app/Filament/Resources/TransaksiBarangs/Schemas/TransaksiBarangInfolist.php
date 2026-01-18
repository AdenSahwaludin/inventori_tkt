<?php

namespace App\Filament\Resources\TransaksiBarangs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransaksiBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_transaksi'),
                TextEntry::make('masterBarang.kode_master'),
                TextEntry::make('lokasi_tujuan'),
                TextEntry::make('tanggal_transaksi')
                    ->date(),
                TextEntry::make('jumlah')
                    ->numeric(),
                TextEntry::make('penanggung_jawab'),
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
