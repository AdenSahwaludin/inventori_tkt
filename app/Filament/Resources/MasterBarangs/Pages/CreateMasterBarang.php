<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Models\MasterBarang;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateMasterBarang extends CreateRecord
{
    protected static string $resource = MasterBarangResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $this->validateTotalPesanan($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validateTotalPesanan($data);
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
                ->title('Validasi Gagal')
                ->body("Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})")
                ->danger()

                ->send();

            throw ValidationException::withMessages([
                'total_pesanan' => "Total unit ({$total}) melebihi Total Pesanan ({$totalPesanan})",
            ]);
        }
    }
}

