<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama_kategori' => 'Elektronik', 'deskripsi' => 'Peralatan elektronik seperti laptop, komputer, printer, dll.'],
            ['nama_kategori' => 'Alat Tulis', 'deskripsi' => 'Peralatan tulis kantor seperti pensil, pulpen, penghapus, dll.'],
            ['nama_kategori' => 'Furniture', 'deskripsi' => 'Perabot kantor seperti meja, kursi, lemari, dll.'],
            ['nama_kategori' => 'Peralatan Lab', 'deskripsi' => 'Peralatan laboratorium seperti mikroskop, tabung reaksi, dll.'],
            ['nama_kategori' => 'Perlengkapan Kebersihan', 'deskripsi' => 'Perlengkapan kebersihan seperti sapu, pel, pembersih, dll.'],
            ['nama_kategori' => 'Peralatan Safety', 'deskripsi' => 'Perlengkapan keselamatan kerja seperti helm, sarung tangan, dll.'],
            ['nama_kategori' => 'Mesin', 'deskripsi' => 'Mesin-mesin produksi dan pendukung operasional.'],
            ['nama_kategori' => 'Kendaraan', 'deskripsi' => 'Kendaraan operasional seperti forklift, truk, dll.'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(
                ['nama_kategori' => $kategori['nama_kategori']],
                $kategori
            );
        }
    }
}
