<?php

namespace App\Filament\Resources\TransaksiKeluars\Schemas;

use App\Models\TransaksiKeluar;
use App\Models\UnitBarang;
use Filament\Forms\Components\DatePicker;
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
                    ->label('Kode Transaksi')
                    ->default(fn () => TransaksiKeluar::generateKodeTransaksi())
                    ->disabled()
                    ->dehydrated(),
                Select::make('unit_barang_id')
                    ->label('Unit Barang')
                    ->options(function () {
                        return UnitBarang::query()
                            ->where('is_active', true)
                            ->where('status', 'baik')
                            ->with(['masterBarang', 'ruang'])
                            ->get()
                            ->mapWithKeys(fn ($unit) => [
                                $unit->kode_unit => "{$unit->kode_unit} - {$unit->masterBarang?->nama_barang} ({$unit->ruang?->nama_ruang})",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $unit = UnitBarang::find($state);
                            $set('ruang_asal_id', $unit?->ruang_id);
                        }
                    }),
                Select::make('ruang_asal_id')
                    ->label('Ruang Asal')
                    ->relationship('ruangAsal', 'nama_ruang')
                    ->disabled()
                    ->dehydrated(),
                Select::make('tipe')
                    ->label('Tipe Transaksi')
                    ->options(TransaksiKeluar::TIPE_OPTIONS)
                    ->default('peminjaman')
                    ->required()
                    ->live(),
                Select::make('ruang_tujuan_id')
                    ->label('Ruang Tujuan')
                    ->relationship('ruangTujuan', 'nama_ruang')
                    ->searchable()
                    ->preload()
                    ->visible(function ($state, callable $get) {
                        return in_array($get('tipe'), ['pemindahan']);
                    })
                    ->required(function ($state, callable $get) {
                        return $get('tipe') === 'pemindahan';
                    }),
                DatePicker::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->required(),
                TextInput::make('penerima')
                    ->label('Penerima')
                    ->required(function ($state, callable $get) {
                        return in_array($get('tipe'), ['peminjaman', 'penggunaan']);
                    })
                    ->visible(function ($state, callable $get) {
                        return in_array($get('tipe'), ['peminjaman', 'penggunaan', 'penghapusan']);
                    }),
                TextInput::make('tujuan')
                    ->label('Tujuan Penggunaan')
                    ->visible(function ($state, callable $get) {
                        return in_array($get('tipe'), ['peminjaman', 'penggunaan']);
                    }),
                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                Textarea::make('catatan')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
            ]);
    }
}
