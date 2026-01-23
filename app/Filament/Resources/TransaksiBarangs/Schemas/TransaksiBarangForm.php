<?php

namespace App\Filament\Resources\TransaksiBarangs\Schemas;

use App\Models\Ruang;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransaksiBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_transaksi')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->default(fn () => 'TRX-' . strtoupper(substr(uniqid(), -6))),
                Select::make('master_barang_id')
                    ->relationship('masterBarang', 'nama_barang')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->required(),
                TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab'),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                TextInput::make('total_pesanan')
                    ->label('Total Pesanan')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Maksimal jumlah unit yang akan dipesan di semua ruang')
                    ->columnSpan(1),
                
                // Distribusi Ruang dengan Jumlah
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
                                // Get parent distribusi_lokasi array
                                $parent = $get('../..');
                                $allDistribusi = $parent['distribusi_lokasi'] ?? [];
                                
                                // Get all ruang_id values except the current row
                                $selectedRuang = collect($allDistribusi)
                                    ->reject(fn ($item) => ($item['ruang_id'] ?? null) === null)
                                    ->reject(fn ($item, $key) => $item === $state) // Exclude current row
                                    ->pluck('ruang_id')
                                    ->toArray();
                                
                                return in_array($value, $selectedRuang, true);
                            })
                            ->columnSpan(2),
                        TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->addActionLabel('+ Tambah Ruang')
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $record, callable $get) {
                        $totalPesanan = (int) $get('total_pesanan');
                        if ($totalPesanan <= 0) {
                            return;
                        }
                        
                        $currentTotal = collect($state ?? [])->sum('jumlah');
                    })
                    ->validationAttribute('Distribusi Ruang'),
                
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->dehydrated()
                    ->hidden()
                    ->default(fn () => auth()->id()),
                TextInput::make('approved_by')
                    ->numeric()
                    ->hidden()
                    ->dehydrated(false),
                DateTimePicker::make('approved_at')
                    ->hidden()
                    ->dehydrated(false),
                Select::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->hidden()
                    ->dehydrated(),
                Textarea::make('approval_notes')
                    ->columnSpanFull()
                    ->hidden()
                    ->dehydrated(false),
            ]);
    }
}
