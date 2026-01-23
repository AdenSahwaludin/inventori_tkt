<?php

namespace App\Filament\Resources\TransaksiBarangs\Pages;

use App\Filament\Resources\TransaksiBarangs\TransaksiBarangResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateTransaksiBarang extends CreateRecord
{
    protected static string $resource = TransaksiBarangResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
        
        $data['user_id'] = auth()->id();
        $data['approval_status'] = 'pending';
        return $data;
    }
}
