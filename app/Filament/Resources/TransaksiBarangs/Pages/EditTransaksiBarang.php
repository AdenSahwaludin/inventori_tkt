<?php

namespace App\Filament\Resources\TransaksiBarangs\Pages;

use App\Filament\Resources\TransaksiBarangs\TransaksiBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTransaksiBarang extends EditRecord
{
    protected static string $resource = TransaksiBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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
