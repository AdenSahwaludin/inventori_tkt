<?php

namespace App\Filament\Resources\MasterBarangs\Pages;

use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterBarang extends CreateRecord
{
    protected static string $resource = MasterBarangResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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
            throw \Illuminate\Validation\ValidationException::withMessages([
                'distribusi_lokasi' => "Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})",
            ]);
        }
    }
}

