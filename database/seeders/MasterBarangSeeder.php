<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\MasterBarang;
use App\Models\UnitBarang;
use Illuminate\Database\Seeder;

class MasterBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all kategoris and lokasis
        $kategoris = Kategori::all();
        $lokasis = Lokasi::all();

        if ($kategoris->isEmpty() || $lokasis->isEmpty()) {
            $this->command->warn('Please run KategoriSeeder and LokasiSeeder first!');

            return;
        }

        // Define master barang data
        $masterBarangs = [
            // Elektronik
            ['nama_barang' => 'Laptop Dell Latitude 5420', 'kategori' => 'Elektronik', 'satuan' => 'unit', 'merk' => 'Dell', 'harga_satuan' => 15000000, 'reorder_point' => 5],
            ['nama_barang' => 'Printer HP LaserJet Pro', 'kategori' => 'Elektronik', 'satuan' => 'unit', 'merk' => 'HP', 'harga_satuan' => 3500000, 'reorder_point' => 3],
            ['nama_barang' => 'Monitor LG 24 inch', 'kategori' => 'Elektronik', 'satuan' => 'unit', 'merk' => 'LG', 'harga_satuan' => 2500000, 'reorder_point' => 5],
            ['nama_barang' => 'Keyboard Logitech K120', 'kategori' => 'Elektronik', 'satuan' => 'unit', 'merk' => 'Logitech', 'harga_satuan' => 150000, 'reorder_point' => 10],
            ['nama_barang' => 'Mouse Wireless Logitech', 'kategori' => 'Elektronik', 'satuan' => 'unit', 'merk' => 'Logitech', 'harga_satuan' => 200000, 'reorder_point' => 10],

            // Alat Tulis
            ['nama_barang' => 'Pulpen Pilot G-2', 'kategori' => 'Alat Tulis', 'satuan' => 'box', 'merk' => 'Pilot', 'harga_satuan' => 75000, 'reorder_point' => 20],
            ['nama_barang' => 'Kertas HVS A4 70gsm', 'kategori' => 'Alat Tulis', 'satuan' => 'rim', 'merk' => 'Sinar Dunia', 'harga_satuan' => 45000, 'reorder_point' => 50],
            ['nama_barang' => 'Stapler Besar', 'kategori' => 'Alat Tulis', 'satuan' => 'unit', 'merk' => 'Kangaro', 'harga_satuan' => 85000, 'reorder_point' => 5],

            // Furniture
            ['nama_barang' => 'Meja Kerja 120x60', 'kategori' => 'Furniture', 'satuan' => 'unit', 'merk' => 'Lunar', 'harga_satuan' => 1500000, 'reorder_point' => 3],
            ['nama_barang' => 'Kursi Kantor Ergonomis', 'kategori' => 'Furniture', 'satuan' => 'unit', 'merk' => 'Ergotec', 'harga_satuan' => 2000000, 'reorder_point' => 5],
            ['nama_barang' => 'Lemari Arsip 4 Laci', 'kategori' => 'Furniture', 'satuan' => 'unit', 'merk' => 'Lunar', 'harga_satuan' => 1800000, 'reorder_point' => 2],

            // Peralatan Lab
            ['nama_barang' => 'Mikroskop Digital', 'kategori' => 'Peralatan Lab', 'satuan' => 'unit', 'merk' => 'Olympus', 'harga_satuan' => 25000000, 'reorder_point' => 2],
            ['nama_barang' => 'Timbangan Analitik', 'kategori' => 'Peralatan Lab', 'satuan' => 'unit', 'merk' => 'AND', 'harga_satuan' => 15000000, 'reorder_point' => 2],

            // Perlengkapan Kebersihan
            ['nama_barang' => 'Sapu Lantai', 'kategori' => 'Perlengkapan Kebersihan', 'satuan' => 'unit', 'merk' => 'Lion Star', 'harga_satuan' => 35000, 'reorder_point' => 10],
            ['nama_barang' => 'Ember Pel', 'kategori' => 'Perlengkapan Kebersihan', 'satuan' => 'unit', 'merk' => 'Lion Star', 'harga_satuan' => 45000, 'reorder_point' => 10],

            // Peralatan Safety
            ['nama_barang' => 'Helm Safety', 'kategori' => 'Peralatan Safety', 'satuan' => 'unit', 'merk' => 'MSA', 'harga_satuan' => 250000, 'reorder_point' => 20],
            ['nama_barang' => 'Sarung Tangan Safety', 'kategori' => 'Peralatan Safety', 'satuan' => 'pasang', 'merk' => 'Krisbow', 'harga_satuan' => 35000, 'reorder_point' => 50],
            ['nama_barang' => 'Sepatu Safety', 'kategori' => 'Peralatan Safety', 'satuan' => 'pasang', 'merk' => 'Cheetah', 'harga_satuan' => 450000, 'reorder_point' => 15],
        ];

        foreach ($masterBarangs as $data) {
            // Find kategori
            $kategori = $kategoris->where('nama_kategori', $data['kategori'])->first();
            if (! $kategori) {
                continue;
            }

            // Create master barang
            $masterBarang = MasterBarang::firstOrCreate(
                ['nama_barang' => $data['nama_barang']],
                [
                    'kategori_id' => $kategori->kode_kategori,
                    'satuan' => $data['satuan'],
                    'merk' => $data['merk'],
                    'harga_satuan' => $data['harga_satuan'],
                    'reorder_point' => $data['reorder_point'],
                    'deskripsi' => 'Deskripsi untuk '.$data['nama_barang'],
                ]
            );

            // Create 3-5 unit barang for each master in different lokasi
            $unitCount = rand(3, 5);
            $selectedLokasis = $lokasis->random(min($unitCount, $lokasis->count()));

            foreach ($selectedLokasis as $lokasi) {
                UnitBarang::create([
                    'master_barang_id' => $masterBarang->kode_master,
                    'lokasi_id' => $lokasi->kode_lokasi,
                    'status' => UnitBarang::STATUS_BAIK,
                    'is_active' => true,
                    'tanggal_pembelian' => now()->subDays(rand(30, 365)),
                    'catatan' => 'Seeder generated unit',
                ]);
            }
        }
    }
}
