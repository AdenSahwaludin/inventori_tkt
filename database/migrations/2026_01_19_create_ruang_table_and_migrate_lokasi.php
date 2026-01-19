<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Buat tabel ruang baru
        Schema::create('ruang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruang');
            $table->softDeletes();
            $table->timestamps();
        });

        // Pindahkan data dari lokasi ke ruang jika tabel lokasi ada
        if (Schema::hasTable('lokasi')) {
            $lokasis = DB::table('lokasi')->get();
            foreach ($lokasis as $lokasi) {
                DB::table('ruang')->insert([
                    'nama_ruang' => $lokasi->nama_lokasi,
                    'created_at' => $lokasi->created_at ?? now(),
                    'updated_at' => $lokasi->updated_at ?? now(),
                ]);
            }

            // Tambahkan kolom ruang_id terlebih dahulu ke tabel yang butuh
            if (Schema::hasTable('unit_barang') && !Schema::hasColumn('unit_barang', 'ruang_id')) {
                Schema::table('unit_barang', function (Blueprint $table) {
                    $table->unsignedBigInteger('ruang_id')->nullable()->after('lokasi_id');
                });
            }

            if (Schema::hasTable('mutasi_lokasi') && !Schema::hasColumn('mutasi_lokasi', 'ruang_id')) {
                Schema::table('mutasi_lokasi', function (Blueprint $table) {
                    $table->unsignedBigInteger('ruang_id')->nullable();
                });
            }

            if (Schema::hasTable('transaksi_keluar') && !Schema::hasColumn('transaksi_keluar', 'ruang_asal_id')) {
                Schema::table('transaksi_keluar', function (Blueprint $table) {
                    $table->unsignedBigInteger('ruang_asal_id')->nullable();
                    $table->unsignedBigInteger('ruang_tujuan_id')->nullable();
                });
            }

            if (Schema::hasTable('barang_rusak') && !Schema::hasColumn('barang_rusak', 'ruang_id')) {
                Schema::table('barang_rusak', function (Blueprint $table) {
                    $table->unsignedBigInteger('ruang_id')->nullable();
                });
            }

            // Update distribusi_lokasi JSON di master_barang
            if (Schema::hasTable('master_barang') && Schema::hasColumn('master_barang', 'distribusi_lokasi')) {
                $masterBarangs = DB::table('master_barang')->get();
                foreach ($masterBarangs as $mb) {
                    if ($mb->distribusi_lokasi) {
                        $distribusi = json_decode($mb->distribusi_lokasi, true);
                        foreach ($distribusi as &$item) {
                            // Cari mapping dari kode_lokasi ke id ruang
                            if (isset($item['lokasi_id'])) {
                                $namaLokasi = DB::table('lokasi')->where('kode_lokasi', $item['lokasi_id'])->value('nama_lokasi');
                                if ($namaLokasi) {
                                    $ruangId = DB::table('ruang')->where('nama_ruang', $namaLokasi)->value('id');
                                    if ($ruangId) {
                                        $item['ruang_id'] = $ruangId;
                                        unset($item['lokasi_id']);
                                    }
                                }
                            }
                        }
                        DB::table('master_barang')->where('kode_master', $mb->kode_master)->update([
                            'distribusi_lokasi' => json_encode($distribusi),
                        ]);
                    }
                }
            }

            // Update data ke kolom ruang_id dari lokasi
            if (Schema::hasTable('unit_barang') && Schema::hasColumn('unit_barang', 'lokasi_id')) {
                $unitBarangs = DB::table('unit_barang')->get();
                foreach ($unitBarangs as $ub) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $ub->lokasi_id)
                        ->value('ruang.id');
                    if ($ruangId) {
                        DB::table('unit_barang')->where('kode_unit', $ub->kode_unit)->update(['ruang_id' => $ruangId]);
                    }
                }
            }

            // Note: Keeping lokasi table for backward compatibility with seeders and existing foreign keys
            // Tabel lokasi tetap ada untuk compatibility
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate lokasi table
        Schema::create('lokasi', function (Blueprint $table) {
            $table->string('kode_lokasi')->primary();
            $table->string('nama_lokasi');
            $table->string('gedung')->nullable();
            $table->string('lantai')->nullable();
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Restore data from ruang
        $ruangs = DB::table('ruang')->get();
        foreach ($ruangs as $ruang) {
            DB::table('lokasi')->insert([
                'kode_lokasi' => strtoupper(substr($ruang->nama_ruang, 0, 3)),
                'nama_lokasi' => $ruang->nama_ruang,
                'created_at' => $ruang->created_at,
                'updated_at' => $ruang->updated_at,
            ]);
        }

        Schema::dropIfExists('ruang');
    }
};
