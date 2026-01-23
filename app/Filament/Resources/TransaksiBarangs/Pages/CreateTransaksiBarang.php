<?php

namespace App\Filament\Resources\TransaksiBarangs\Pages;

use App\Filament\Resources\TransaksiBarangs\TransaksiBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksiBarang extends CreateRecord
{
    protected static string $resource = TransaksiBarangResource::class;

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
            throw \Illuminate\Validation\ValidationException::withMessages([
                'distribusi_lokasi' => "Total unit ({$total}) tidak boleh melebihi Total Pesanan ({$totalPesanan})",
            ]);
        }
    }
}
