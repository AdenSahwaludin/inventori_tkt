<?php

namespace App\Filament\Resources\UnitBarangs\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UnitBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_unit')
                    ->label('Kode Unit')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('masterBarang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lokasi.nama_lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'dipinjam' => 'warning',
                        'rusak' => 'danger',
                        'maintenance' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('tanggal_pembelian')
                    ->label('Tgl Beli')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('masterBarang.harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua'),
                SelectFilter::make('status')
                    ->label('Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'dipinjam' => 'Dipinjam',
                        'rusak' => 'Rusak',
                        'maintenance' => 'Maintenance',
                    ]),
                SelectFilter::make('lokasi_id')
                    ->label('Lokasi')
                    ->relationship('lokasi', 'nama_lokasi')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()->can('edit_unit_barangs')),

                // Action Nonaktifkan (hanya Admin)
                Action::make('nonaktifkan')
                    ->label('Nonaktifkan')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Unit Barang')
                    ->modalDescription('Unit barang yang dinonaktifkan tidak akan muncul di operasional. Data tetap tersimpan untuk histori.')
                    ->form([
                        Textarea::make('alasan')
                            ->label('Alasan Nonaktifkan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->is_active
                        && auth()->user()->can('nonaktifkan', $record))
                    ->action(function ($record, array $data) {
                        $record->nonaktifkan($data['alasan']);
                    }),

                // Action Aktifkan Kembali (hanya Admin)
                Action::make('aktifkan')
                    ->label('Aktifkan')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Unit Barang')
                    ->visible(fn ($record) => ! $record->is_active
                        && auth()->user()->hasRole('Admin'))
                    ->action(function ($record) {
                        $record->update(['is_active' => true, 'status' => 'baik']);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // No delete bulk action for unit barang
                ]),
            ]);
    }
}
