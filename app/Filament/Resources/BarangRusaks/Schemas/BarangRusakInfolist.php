<?php

namespace App\Filament\Resources\BarangRusaks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BarangRusakInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('unitBarang.kode_unit'),
                TextEntry::make('tanggal_kejadian')
                    ->date(),
                TextEntry::make('penanggung_jawab'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
