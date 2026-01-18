<?php

namespace App\Filament\Resources\TransaksiKeluars\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransaksiKeluarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_transaksi')
                    ->required(),
                Select::make('unit_barang_id')
                    ->relationship('unitBarang', 'kode_unit')
                    ->required(),
                DatePicker::make('tanggal_transaksi')
                    ->required(),
                TextInput::make('penerima')
                    ->required(),
                TextInput::make('tujuan'),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('approved_by')
                    ->numeric(),
                DateTimePicker::make('approved_at'),
                Select::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                Textarea::make('approval_notes')
                    ->columnSpanFull(),
            ]);
    }
}
