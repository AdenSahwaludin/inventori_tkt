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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Pindahkan data terakhir dari lokasi ke ruang jika belum
        if (Schema::hasTable('lokasi') && Schema::hasTable('ruang')) {
            $lokasis = DB::table('lokasi')->get();
            foreach ($lokasis as $lokasi) {
                $exists = DB::table('ruang')->where('nama_ruang', $lokasi->nama_lokasi)->exists();
                if (!$exists) {
                    DB::table('ruang')->insert([
                        'nama_ruang' => $lokasi->nama_lokasi,
                        'created_at' => $lokasi->created_at ?? now(),
                        'updated_at' => $lokasi->updated_at ?? now(),
                    ]);
                }
            }
        }

        // Update unit_barang: map lokasi_id ke ruang_id
        if (Schema::hasTable('unit_barang')) {
            $units = DB::table('unit_barang')->whereNotNull('lokasi_id')->get();
            foreach ($units as $unit) {
                if (!$unit->ruang_id && Schema::hasTable('lokasi')) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $unit->lokasi_id)
                        ->value('ruang.id');
                    if ($ruangId) {
                        DB::table('unit_barang')->where('kode_unit', $unit->kode_unit)->update(['ruang_id' => $ruangId]);
                    }
                }
            }

            // Drop foreign key dan kolom lokasi_id
            if (Schema::hasColumn('unit_barang', 'lokasi_id')) {
                Schema::table('unit_barang', function (Blueprint $table) {
                    $table->dropForeign(['lokasi_id']);
                    $table->dropColumn('lokasi_id');
                });
            }

            // Add foreign key untuk ruang_id
            if (Schema::hasColumn('unit_barang', 'ruang_id') && !Schema::hasTable('unit_barang_ruang_fk_check')) {
                Schema::table('unit_barang', function (Blueprint $table) {
                    $table->foreign('ruang_id')->references('id')->on('ruang')->onDelete('restrict');
                });
            }
        }

        // Update mutasi_lokasi
        if (Schema::hasTable('mutasi_lokasi') && Schema::hasColumn('mutasi_lokasi', 'lokasi_id')) {
            $mutasis = DB::table('mutasi_lokasi')->whereNotNull('lokasi_id')->get();
            foreach ($mutasis as $mutasi) {
                if (!$mutasi->ruang_id) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $mutasi->lokasi_id)
                        ->value('ruang.id');
                    if ($ruangId) {
                        DB::table('mutasi_lokasi')->where('id', $mutasi->id)->update(['ruang_id' => $ruangId]);
                    }
                }
            }

            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                $table->dropForeign(['lokasi_id']);
                $table->dropColumn('lokasi_id');
            });

            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                $table->foreign('ruang_id')->references('id')->on('ruang')->onDelete('restrict');
            });
        }

        // Update transaksi_keluar
        if (Schema::hasTable('transaksi_keluar')) {
            if (Schema::hasColumn('transaksi_keluar', 'lokasi_asal_id')) {
                $transaksis = DB::table('transaksi_keluar')->whereNotNull('lokasi_asal_id')->get();
                foreach ($transaksis as $transaksi) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $transaksi->lokasi_asal_id)
                        ->value('ruang.id');
                    if ($ruangId && !$transaksi->ruang_asal_id) {
                        DB::table('transaksi_keluar')->where('id', $transaksi->id)->update(['ruang_asal_id' => $ruangId]);
                    }
                }

                Schema::table('transaksi_keluar', function (Blueprint $table) {
                    $table->dropForeign(['lokasi_asal_id']);
                    $table->dropColumn('lokasi_asal_id');
                });

                Schema::table('transaksi_keluar', function (Blueprint $table) {
                    $table->foreign('ruang_asal_id')->references('id')->on('ruang')->onDelete('restrict');
                });
            }

            if (Schema::hasColumn('transaksi_keluar', 'lokasi_tujuan_id')) {
                $transaksis = DB::table('transaksi_keluar')->whereNotNull('lokasi_tujuan_id')->get();
                foreach ($transaksis as $transaksi) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $transaksi->lokasi_tujuan_id)
                        ->value('ruang.id');
                    if ($ruangId && !$transaksi->ruang_tujuan_id) {
                        DB::table('transaksi_keluar')->where('id', $transaksi->id)->update(['ruang_tujuan_id' => $ruangId]);
                    }
                }

                Schema::table('transaksi_keluar', function (Blueprint $table) {
                    $table->dropForeign(['lokasi_tujuan_id']);
                    $table->dropColumn('lokasi_tujuan_id');
                });

                Schema::table('transaksi_keluar', function (Blueprint $table) {
                    $table->foreign('ruang_tujuan_id')->references('id')->on('ruang')->onDelete('restrict');
                });
            }
        }

        // Update barang_rusak
        if (Schema::hasTable('barang_rusak') && Schema::hasColumn('barang_rusak', 'lokasi_id')) {
            $barangs = DB::table('barang_rusak')->whereNotNull('lokasi_id')->get();
            foreach ($barangs as $barang) {
                if (!$barang->ruang_id) {
                    $ruangId = DB::table('ruang')
                        ->join('lokasi', 'ruang.nama_ruang', '=', 'lokasi.nama_lokasi')
                        ->where('lokasi.kode_lokasi', $barang->lokasi_id)
                        ->value('ruang.id');
                    if ($ruangId) {
                        DB::table('barang_rusak')->where('id', $barang->id)->update(['ruang_id' => $ruangId]);
                    }
                }
            }

            Schema::table('barang_rusak', function (Blueprint $table) {
                $table->dropForeign(['lokasi_id']);
                $table->dropColumn('lokasi_id');
            });

            Schema::table('barang_rusak', function (Blueprint $table) {
                $table->foreign('ruang_id')->references('id')->on('ruang')->onDelete('restrict');
            });
        }

        // Update transaksi_barang
        if (Schema::hasTable('transaksi_barang') && Schema::hasColumn('transaksi_barang', 'lokasi_tujuan_id')) {
            Schema::table('transaksi_barang', function (Blueprint $table) {
                $table->dropForeign(['lokasi_tujuan_id']);
                $table->dropColumn('lokasi_tujuan_id');
            });
        }

        // Sekarang drop tabel lokasi
        Schema::dropIfExists('lokasi');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate lokasi table for rollback
        Schema::create('lokasi', function (Blueprint $table) {
            $table->string('kode_lokasi')->primary();
            $table->string('nama_lokasi');
            $table->string('gedung')->nullable();
            $table->string('lantai')->nullable();
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
