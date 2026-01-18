<?php

namespace App\Filament\Resources\TransaksiBarangs\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransaksiBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_transaksi')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('masterBarang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lokasiTujuan.nama_lokasi')
                    ->label('Lokasi Tujuan')
                    ->searchable(),
                TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('penanggung_jawab')
                    ->label('P. Jawab')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label('Tgl Approval')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->approval_status === 'pending'
                        && $record->user_id === auth()->id()),

                // Action Approve
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transaksi')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui transaksi ini? Unit barang akan di-generate otomatis.')
                    ->visible(fn ($record) => $record->approval_status === 'pending'
                        && auth()->user()->can('approve', $record))
                    ->action(function ($record) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),

                // Action Reject
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Transaksi')
                    ->form([
                        Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'pending'
                        && auth()->user()->can('approve', $record))
                    ->action(function ($record, array $data) {
                        $record->update([
                            'approval_status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'],
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Admin')),
                ]),
            ]);
    }
}
