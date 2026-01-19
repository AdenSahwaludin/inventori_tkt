<?php

namespace Database\Seeders;

use App\Models\Ruang;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruangs = [
            ['nama_ruang' => 'Gudang Utama'],
            ['nama_ruang' => 'Ruang Server'],
            ['nama_ruang' => 'Lab Komputer'],
            ['nama_ruang' => 'Kantor Admin'],
            ['nama_ruang' => 'Ruang Rapat'],
            ['nama_ruang' => 'Gudang B'],
            ['nama_ruang' => 'Workshop'],
            ['nama_ruang' => 'Laboratorium'],
            ['nama_ruang' => 'Area Produksi'],
            ['nama_ruang' => 'Loading Dock'],
        ];

        foreach ($ruangs as $ruang) {
            Ruang::firstOrCreate(
                ['nama_ruang' => $ruang['nama_ruang']],
                $ruang
            );
        }
    }
}
