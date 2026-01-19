<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use App\Models\Kategori;
use App\Models\Ruang;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Data Barang
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->required()
                    ->minLength(3)
                    ->columnSpan(2),
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->options(Kategori::pluck('nama_kategori', 'kode_kategori'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('satuan')
                    ->label('Satuan')
                    ->options([
                        'pcs' => 'Pcs (Pieces)',
                        'unit' => 'Unit',
                        'box' => 'Box',
                        'rim' => 'Rim',
                        'dus' => 'Dus',
                        'set' => 'Set',
                        'paket' => 'Paket',
                        'buah' => 'Buah',
                        'lembar' => 'Lembar',
                    ])
                    ->default('pcs')
                    ->required(),
                TextInput::make('merk')
                    ->label('Merk'),
                TextInput::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('reorder_point')
                    ->label('Reorder Point (Min. Stok)')
                    ->numeric()
                    ->default(0)
                    ->helperText('Alert akan muncul jika stok di bawah nilai ini'),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                // Distribusi Ruang
                Repeater::make('distribusi_lokasi')
                    ->label('Distribusi Ruang')
                    ->schema([
                        Select::make('ruang_id')
                            ->label('Ruang')
                            ->options(Ruang::pluck('nama_ruang', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disableOptionWhen(function ($value, $state, $get) {
                                // Get all items from the repeater (../.. goes up 2 levels)
                                $allDistribusi = collect($get('../../distribusi_lokasi') ?? []);
                                
                                // Filter: get ruang_id values that are NOT the current row's current state value
                                $otherSelectedRuang = $allDistribusi
                                    ->filter(fn ($item) => $item['ruang_id'] ?? null)
                                    ->pluck('ruang_id')
                                    ->filter(fn ($id) => $id !== $state) // Exclude current selection
                                    ->toArray();
                                
                                // Disable this option if it's already selected in another row
                                return in_array($value, $otherSelectedRuang, true);
                            })
                            ->columnSpan(2),
                        TextInput::make('jumlah')
                            ->label('Jumlah Unit')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->addActionLabel('+ Tambah Ruang')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ]);
    }
}


