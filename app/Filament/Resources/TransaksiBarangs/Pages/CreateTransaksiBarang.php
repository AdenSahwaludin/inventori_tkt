<?php

namespace App\Filament\Resources\TransaksiBarangs\Pages;

use App\Filament\Resources\TransaksiBarangs\TransaksiBarangResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateTransaksiBarang extends CreateRecord
{
    protected static string $resource = TransaksiBarangResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $this->validateTotalPesanan($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validateTotalPesanan($data);
        $data['user_id'] = auth()->id();
        $data['approval_status'] = 'pending';
        return $data;
    }

    private function validateTotalPesanan(array $data): void
    {
        $totalPesanan = (int) ($data['total_pesanan'] ?? 0);
        if ($totalPesanan <= 0) {
            return;
        }

        $distribusi = collect($data['distribusi_lokasi'] ?? []);
        $total = $distribusi->sum('jumlah');

        if ($total > $totalPesanan) {
            Notification::make()
                ->title('❌ Validasi Gagal')
                ->body("Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})")
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'total_pesanan' => "Total unit ({$total}) melebihi Total Pesanan ({$totalPesanan})",
            ]);
        }
    }
}
