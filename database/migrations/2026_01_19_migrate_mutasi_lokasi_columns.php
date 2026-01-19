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
        // Migrate mutasi_lokasi from lokasi_asal/tujuan (string) to ruang_asal_id/tujuan_id (integer FK)
        if (Schema::hasTable('mutasi_lokasi')) {
            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                // Check if old columns exist and add new columns if they don't
                if (Schema::hasColumn('mutasi_lokasi', 'lokasi_asal') && !Schema::hasColumn('mutasi_lokasi', 'ruang_asal_id')) {
                    $table->unsignedBigInteger('ruang_asal_id')->nullable();
                }
                if (Schema::hasColumn('mutasi_lokasi', 'lokasi_tujuan') && !Schema::hasColumn('mutasi_lokasi', 'ruang_tujuan_id')) {
                    $table->unsignedBigInteger('ruang_tujuan_id')->nullable();
                }
            });

            // Migrate data from lokasi to ruang
            if (Schema::hasColumn('mutasi_lokasi', 'lokasi_asal') && Schema::hasColumn('mutasi_lokasi', 'ruang_asal_id')) {
                $mutasis = DB::table('mutasi_lokasi')->get();
                foreach ($mutasis as $mutasi) {
                    $ruangAsalId = null;
                    $ruangTujuanId = null;

                    // Map lokasi_asal to ruang_asal_id
                    if ($mutasi->lokasi_asal) {
                        $ruangAsalId = DB::table('ruang')
                            ->where('nama_ruang', DB::table('lokasi')
                                ->where('kode_lokasi', $mutasi->lokasi_asal)
                                ->value('nama_lokasi'))
                            ->value('id');
                    }

                    // Map lokasi_tujuan to ruang_tujuan_id
                    if ($mutasi->lokasi_tujuan) {
                        $ruangTujuanId = DB::table('ruang')
                            ->where('nama_ruang', DB::table('lokasi')
                                ->where('kode_lokasi', $mutasi->lokasi_tujuan)
                                ->value('nama_lokasi'))
                            ->value('id');
                    }

                    DB::table('mutasi_lokasi')
                        ->where('id', $mutasi->id)
                        ->update([
                            'ruang_asal_id' => $ruangAsalId,
                            'ruang_tujuan_id' => $ruangTujuanId,
                        ]);
                }
            }

            // Drop old columns and add foreign key constraints
            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                if (Schema::hasColumn('mutasi_lokasi', 'lokasi_asal')) {
                    $table->dropForeign(['lokasi_asal']);
                    $table->dropColumn('lokasi_asal');
                }
                if (Schema::hasColumn('mutasi_lokasi', 'lokasi_tujuan')) {
                    $table->dropForeign(['lokasi_tujuan']);
                    $table->dropColumn('lokasi_tujuan');
                }
            });

            // Add foreign key constraints for new columns
            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                if (Schema::hasColumn('mutasi_lokasi', 'ruang_asal_id')) {
                    $table->foreign('ruang_asal_id')->references('id')->on('ruang')->onDelete('restrict');
                }
                if (Schema::hasColumn('mutasi_lokasi', 'ruang_tujuan_id')) {
                    $table->foreign('ruang_tujuan_id')->references('id')->on('ruang')->onDelete('restrict');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mutasi_lokasi')) {
            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                if (Schema::hasColumn('mutasi_lokasi', 'ruang_asal_id')) {
                    $table->dropForeign(['ruang_asal_id']);
                    $table->dropColumn('ruang_asal_id');
                }
                if (Schema::hasColumn('mutasi_lokasi', 'ruang_tujuan_id')) {
                    $table->dropForeign(['ruang_tujuan_id']);
                    $table->dropColumn('ruang_tujuan_id');
                }
            });

            // Recreate old columns
            Schema::table('mutasi_lokasi', function (Blueprint $table) {
                $table->string('lokasi_asal')->nullable();
                $table->string('lokasi_tujuan')->nullable();

                $table->foreign('lokasi_asal')
                    ->references('kode_lokasi')
                    ->on('lokasi')
                    ->onDelete('restrict');

                $table->foreign('lokasi_tujuan')
                    ->references('kode_lokasi')
                    ->on('lokasi')
                    ->onDelete('restrict');
            });
        }
    }
};
