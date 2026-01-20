<?php

namespace App\Filament\Resources\Ruangs\Tables;

use App\Filament\Resources\UnitBarangs\UnitBarangResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RuangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('nama_ruang')
                    ->label('Nama Ruang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('lihat_barang')
                    ->label('Lihat Barang')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => UnitBarangResource::getUrl('index', ['search' => $record->nama_ruang])),
                EditAction::make()
                    ->visible(fn () => auth()->user()->can('edit_ruangs')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete_ruangs')),
                ]),
            ]);
    }
}
