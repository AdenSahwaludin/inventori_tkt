<?php

namespace App\Filament\Resources\TransaksiBarangs\Pages;

use App\Filament\Resources\TransaksiBarangs\TransaksiBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

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
