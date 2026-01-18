<?php

namespace App\Filament\Resources\LogAktivitas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LogAktivitasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('jenis_aktivitas'),
                TextEntry::make('nama_tabel'),
                TextEntry::make('record_id'),
                TextEntry::make('ip_address'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
