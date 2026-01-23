<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMasterBarang extends EditRecord
{
    protected static string $resource = MasterBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalPesanan = (int) ($data['total_pesanan'] ?? 0);
        if ($totalPesanan > 0) {
            $distribusi = collect($data['distribusi_lokasi'] ?? []);
            $total = $distribusi->sum('jumlah');

            if ($total > $totalPesanan) {
                Notification::make()
                    ->title('Validasi Gagal')
                    ->body("Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})")
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    'distribusi_lokasi' => "Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})",
                ]);
            }
        }
        return $data;
    }
}
