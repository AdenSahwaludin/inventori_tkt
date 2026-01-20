<?php

namespace App\Filament\Resources\TransaksiBarangs\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransaksiBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_transaksi')
                    ->label('Kode Transaksi'),
                TextEntry::make('masterBarang.kode_master')
                    ->label('Kode Master'),
                TextEntry::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->date(),
                TextEntry::make('penanggung_jawab')
                    ->label('Penanggung Jawab'),
                TextEntry::make('keterangan')
                    ->label('Keterangan'),
                TextEntry::make('user.name')
                    ->label('Dibuat Oleh'),
                TextEntry::make('approved_by')
                    ->label('Disetujui Oleh')
                    ->numeric(),
                TextEntry::make('approved_at')
                    ->label('Tanggal Approval')
                    ->dateTime(),
                TextEntry::make('approval_status')
                    ->label('Status Approval')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('approval_notes')
                    ->label('Catatan Approval'),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Diupdate Pada')
                    ->dateTime(),
            ]);
    }
}
