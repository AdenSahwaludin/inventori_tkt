<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lokasis = [
            ['nama_lokasi' => 'Gudang Utama', 'gedung' => 'Gedung A', 'lantai' => 'Lantai 1', 'keterangan' => 'Gudang penyimpanan utama'],
            ['nama_lokasi' => 'Ruang Server', 'gedung' => 'Gedung A', 'lantai' => 'Lantai 2', 'keterangan' => 'Ruang server dan IT'],
            ['nama_lokasi' => 'Lab Komputer', 'gedung' => 'Gedung A', 'lantai' => 'Lantai 2', 'keterangan' => 'Laboratorium komputer'],
            ['nama_lokasi' => 'Kantor Admin', 'gedung' => 'Gedung A', 'lantai' => 'Lantai 1', 'keterangan' => 'Kantor administrasi'],
            ['nama_lokasi' => 'Ruang Rapat', 'gedung' => 'Gedung A', 'lantai' => 'Lantai 3', 'keterangan' => 'Ruang rapat utama'],
            ['nama_lokasi' => 'Gudang B', 'gedung' => 'Gedung B', 'lantai' => 'Lantai 1', 'keterangan' => 'Gudang sekunder'],
            ['nama_lokasi' => 'Workshop', 'gedung' => 'Gedung B', 'lantai' => 'Lantai 1', 'keterangan' => 'Area workshop dan maintenance'],
            ['nama_lokasi' => 'Laboratorium', 'gedung' => 'Gedung B', 'lantai' => 'Lantai 2', 'keterangan' => 'Laboratorium penelitian'],
            ['nama_lokasi' => 'Area Produksi', 'gedung' => 'Gedung C', 'lantai' => 'Lantai 1', 'keterangan' => 'Area produksi utama'],
            ['nama_lokasi' => 'Loading Dock', 'gedung' => 'Gedung C', 'lantai' => 'Lantai 1', 'keterangan' => 'Area bongkar muat'],
        ];

        foreach ($lokasis as $lokasi) {
            Lokasi::firstOrCreate(
                ['nama_lokasi' => $lokasi['nama_lokasi']],
                $lokasi
            );
        }
    }
}
