<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Ruang;
use App\Models\MasterBarang;
use Illuminate\Database\Seeder;

class TKSeederSekolah extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk data Sekolah TK (Taman Kanak-kanak)
     */
    public function run(): void
    {
        // 1. Create Kategori untuk TK
        $kategoris = [
            ['nama_kategori' => 'Mainan Edukatif', 'deskripsi' => 'Mainan yang mendukung perkembangan kognitif dan motorik anak.'],
            ['nama_kategori' => 'Alat Tulis & Kertas', 'deskripsi' => 'Pensil, pensil warna, krayon, kertas, buku gambar, dll.'],
            ['nama_kategori' => 'Furniture Anak', 'deskripsi' => 'Meja anak, kursi anak, lemari mainan, rak buku, dll.'],
            ['nama_kategori' => 'Peralatan Olahraga', 'deskripsi' => 'Bola, tali lompat, cone, matras, badminton, dll.'],
            ['nama_kategori' => 'Alat Musik', 'deskripsi' => 'Gitar, drum, keyboard, recorder, marakas, xylophone, dll.'],
            ['nama_kategori' => 'Perlengkapan Seni', 'deskripsi' => 'Cat, kanvas, kuas, gunting, lem, stiker, dll.'],
            ['nama_kategori' => 'Peralatan Kebersihan', 'deskripsi' => 'Sapu, pel, tempat sampah, sabun tangan, tisu, dll.'],
            ['nama_kategori' => 'Alat Pembelajaran', 'deskripsi' => 'Papan tulis, spidol, globe, jam dinding, kartu huruf/angka, dll.'],
            ['nama_kategori' => 'Perlengkapan Aman', 'deskripsi' => 'Helm mainan, bendera keselamatan, rambu-rambu, dll.'],
            ['nama_kategori' => 'Dekorasi Kelas', 'deskripsi' => 'Poster edukasi, balon, kalimat mutiara, hiasan dinding, dll.'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(
                ['nama_kategori' => $kategori['nama_kategori']],
                $kategori
            );
        }

        // 2. Create Ruang untuk TK
        $ruangs = [
            ['nama_ruang' => 'Kelompok A'],
            ['nama_ruang' => 'Kelompok B'],
            ['nama_ruang' => 'Ruang Kepala Sekolah'],
            ['nama_ruang' => 'Ruang Guru'],
            ['nama_ruang' => 'Perpustakaan Anak'],
            ['nama_ruang' => 'Area Bermain Dalam'],
            ['nama_ruang' => 'Area Bermain Luar'],
            ['nama_ruang' => 'Ruang Musik'],
            ['nama_ruang' => 'Ruang Seni'],
            ['nama_ruang' => 'Kamar Mandi Anak'],
            ['nama_ruang' => 'Ruang UKS'],
            ['nama_ruang' => 'Dapur/Kantin'],
            ['nama_ruang' => 'Gudang'],
            ['nama_ruang' => 'Ruang Olahraga'],
        ];

        foreach ($ruangs as $ruang) {
            Ruang::firstOrCreate(
                ['nama_ruang' => $ruang['nama_ruang']],
                $ruang
            );
        }

        // 3. Create Master Barang untuk TK
        // Get kode_kategori dari kategori yang sudah dibuat
        $kategoriMap = [
            'Mainan Edukatif' => Kategori::where('nama_kategori', 'Mainan Edukatif')->first()->kode_kategori,
            'Alat Tulis & Kertas' => Kategori::where('nama_kategori', 'Alat Tulis & Kertas')->first()->kode_kategori,
            'Furniture Anak' => Kategori::where('nama_kategori', 'Furniture Anak')->first()->kode_kategori,
            'Peralatan Olahraga' => Kategori::where('nama_kategori', 'Peralatan Olahraga')->first()->kode_kategori,
            'Alat Musik' => Kategori::where('nama_kategori', 'Alat Musik')->first()->kode_kategori,
            'Perlengkapan Seni' => Kategori::where('nama_kategori', 'Perlengkapan Seni')->first()->kode_kategori,
            'Peralatan Kebersihan' => Kategori::where('nama_kategori', 'Peralatan Kebersihan')->first()->kode_kategori,
            'Alat Pembelajaran' => Kategori::where('nama_kategori', 'Alat Pembelajaran')->first()->kode_kategori,
        ];

        $masterBarangs = [
            // Mainan Edukatif
            ['nama_barang' => 'Balok Kayu', 'kategori_id' => $kategoriMap['Mainan Edukatif'], 'satuan' => 'set', 'merk' => 'Generic', 'harga_satuan' => 250000, 'reorder_point' => 2, 'total_pesanan' => 5, 'deskripsi' => 'Set balok kayu untuk membangun kreativitas anak.'],
            ['nama_barang' => 'Puzzle Kayu', 'kategori_id' => $kategoriMap['Mainan Edukatif'], 'satuan' => 'buah', 'merk' => 'Playskool', 'harga_satuan' => 85000, 'reorder_point' => 3, 'total_pesanan' => 10, 'deskripsi' => 'Puzzle kayu dengan gambar hewan dan buah.'],
            ['nama_barang' => 'Lego Duplo', 'kategori_id' => $kategoriMap['Mainan Edukatif'], 'satuan' => 'set', 'merk' => 'Lego', 'harga_satuan' => 450000, 'reorder_point' => 2, 'total_pesanan' => 4, 'deskripsi' => 'Lego besar yang aman untuk anak TK.'],

            // Alat Tulis & Kertas
            ['nama_barang' => 'Pensil Warna', 'kategori_id' => $kategoriMap['Alat Tulis & Kertas'], 'satuan' => 'box', 'merk' => 'Faber-Castell', 'harga_satuan' => 65000, 'reorder_point' => 5, 'total_pesanan' => 20, 'deskripsi' => 'Pensil warna isi 12 warna yang aman untuk anak.'],
            ['nama_barang' => 'Kertas A4', 'kategori_id' => $kategoriMap['Alat Tulis & Kertas'], 'satuan' => 'rim', 'merk' => 'Copy Paper', 'harga_satuan' => 50000, 'reorder_point' => 10, 'total_pesanan' => 30, 'deskripsi' => 'Kertas A4 putih 70 gsm.'],
            ['nama_barang' => 'Crayon', 'kategori_id' => $kategoriMap['Alat Tulis & Kertas'], 'satuan' => 'box', 'merk' => 'Crayola', 'harga_satuan' => 35000, 'reorder_point' => 8, 'total_pesanan' => 25, 'deskripsi' => 'Krayon warna-warni isi 24 batang.'],

            // Furniture Anak
            ['nama_barang' => 'Meja Anak', 'kategori_id' => $kategoriMap['Furniture Anak'], 'satuan' => 'buah', 'merk' => 'Ikea', 'harga_satuan' => 350000, 'reorder_point' => 1, 'total_pesanan' => 6, 'deskripsi' => 'Meja belajar untuk anak dengan tinggi yang sesuai.'],
            ['nama_barang' => 'Kursi Anak', 'kategori_id' => $kategoriMap['Furniture Anak'], 'satuan' => 'buah', 'merk' => 'Ikea', 'harga_satuan' => 200000, 'reorder_point' => 2, 'total_pesanan' => 15, 'deskripsi' => 'Kursi anak dengan desain ergonomis dan warna ceria.'],
            ['nama_barang' => 'Lemari Mainan', 'kategori_id' => $kategoriMap['Furniture Anak'], 'satuan' => 'buah', 'merk' => 'Ikea', 'harga_satuan' => 800000, 'reorder_point' => 1, 'total_pesanan' => 3, 'deskripsi' => 'Lemari penyimpanan mainan dengan laci dan rak.'],

            // Peralatan Olahraga
            ['nama_barang' => 'Bola Karet', 'kategori_id' => $kategoriMap['Peralatan Olahraga'], 'satuan' => 'buah', 'merk' => 'Spalding', 'harga_satuan' => 75000, 'reorder_point' => 3, 'total_pesanan' => 15, 'deskripsi' => 'Bola karet berbagai ukuran untuk bermain.'],
            ['nama_barang' => 'Tali Lompat', 'kategori_id' => $kategoriMap['Peralatan Olahraga'], 'satuan' => 'buah', 'merk' => 'Generic', 'harga_satuan' => 25000, 'reorder_point' => 5, 'total_pesanan' => 20, 'deskripsi' => 'Tali lompat untuk olahraga anak.'],
            ['nama_barang' => 'Matras', 'kategori_id' => $kategoriMap['Peralatan Olahraga'], 'satuan' => 'buah', 'merk' => 'Generic', 'harga_satuan' => 150000, 'reorder_point' => 2, 'total_pesanan' => 8, 'deskripsi' => 'Matras untuk senam dan aktivitas fisik.'],

            // Alat Musik
            ['nama_barang' => 'Drum Mainan', 'kategori_id' => $kategoriMap['Alat Musik'], 'satuan' => 'buah', 'merk' => 'Fisher-Price', 'harga_satuan' => 120000, 'reorder_point' => 2, 'total_pesanan' => 8, 'deskripsi' => 'Drum mainan dengan warna-warna cerah.'],
            ['nama_barang' => 'Marakas', 'kategori_id' => $kategoriMap['Alat Musik'], 'satuan' => 'pasang', 'merk' => 'Suzuki', 'harga_satuan' => 45000, 'reorder_point' => 5, 'total_pesanan' => 15, 'deskripsi' => 'Marakas untuk mengenalkan ritme dan musik.'],

            // Perlengkapan Seni
            ['nama_barang' => 'Cat Air', 'kategori_id' => $kategoriMap['Perlengkapan Seni'], 'satuan' => 'box', 'merk' => 'Faber-Castell', 'harga_satuan' => 55000, 'reorder_point' => 5, 'total_pesanan' => 20, 'deskripsi' => 'Cat air isi 12 warna yang aman untuk anak.'],
            ['nama_barang' => 'Kuas', 'kategori_id' => $kategoriMap['Perlengkapan Seni'], 'satuan' => 'set', 'merk' => 'Generic', 'harga_satuan' => 40000, 'reorder_point' => 5, 'total_pesanan' => 15, 'deskripsi' => 'Set kuas berbagai ukuran untuk seni lukis.'],

            // Peralatan Kebersihan
            ['nama_barang' => 'Sapu Anak', 'kategori_id' => $kategoriMap['Peralatan Kebersihan'], 'satuan' => 'buah', 'merk' => 'Generic', 'harga_satuan' => 35000, 'reorder_point' => 3, 'total_pesanan' => 10, 'deskripsi' => 'Sapu berukuran anak untuk belajar membersihkan.'],
            ['nama_barang' => 'Sabun Tangan', 'kategori_id' => $kategoriMap['Peralatan Kebersihan'], 'satuan' => 'botol', 'merk' => 'Lifebuoy', 'harga_satuan' => 15000, 'reorder_point' => 10, 'total_pesanan' => 40, 'deskripsi' => 'Sabun tangan cair yang aman untuk anak.'],

            // Alat Pembelajaran
            ['nama_barang' => 'Papan Tulis Putih', 'kategori_id' => $kategoriMap['Alat Pembelajaran'], 'satuan' => 'buah', 'merk' => 'Generic', 'harga_satuan' => 180000, 'reorder_point' => 2, 'total_pesanan' => 4, 'deskripsi' => 'Papan tulis putih dengan stand untuk kelas.'],
            ['nama_barang' => 'Spidol Papan', 'kategori_id' => $kategoriMap['Alat Pembelajaran'], 'satuan' => 'box', 'merk' => 'Artline', 'harga_satuan' => 45000, 'reorder_point' => 5, 'total_pesanan' => 15, 'deskripsi' => 'Spidol papan tulis berbagai warna.'],
            ['nama_barang' => 'Kartu Huruf', 'kategori_id' => $kategoriMap['Alat Pembelajaran'], 'satuan' => 'set', 'merk' => 'Generic', 'harga_satuan' => 60000, 'reorder_point' => 2, 'total_pesanan' => 8, 'deskripsi' => 'Kartu huruf A-Z untuk belajar membaca.'],
        ];

        foreach ($masterBarangs as $barang) {
            MasterBarang::firstOrCreate(
                ['nama_barang' => $barang['nama_barang']],
                $barang
            );
        }
    }
}
