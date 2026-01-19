<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add distribusi_lokasi & created_by to master_barang
        Schema::table('master_barang', function (Blueprint $table) {
            $table->json('distribusi_lokasi')->nullable()->after('deskripsi');
            $table->unsignedBigInteger('created_by')->nullable()->after('distribusi_lokasi');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // 2. Add created_by to unit_barang
        Schema::table('unit_barang', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('catatan');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // 3. Update transaksi_keluar with new fields
        Schema::table('transaksi_keluar', function (Blueprint $table) {
            $table->string('lokasi_asal_id')->nullable()->after('unit_barang_id');
            $table->string('lokasi_tujuan_id')->nullable()->after('lokasi_asal_id');
            $table->enum('tipe', ['pemindahan', 'peminjaman', 'penggunaan', 'penghapusan'])
                ->default('peminjaman')->after('lokasi_tujuan_id');
            $table->text('catatan')->nullable()->after('keterangan');

            $table->foreign('lokasi_asal_id')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('lokasi_tujuan_id')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });

        // 4. Update mutasi_lokasi with tipe_mutasi
        Schema::table('mutasi_lokasi', function (Blueprint $table) {
            // lokasi_asal should be nullable (for create)
            $table->string('lokasi_asal')->nullable()->change();
            $table->enum('tipe_mutasi', ['create', 'transaksi_masuk', 'transaksi_keluar', 'manual'])
                ->default('manual')->after('tanggal_mutasi');
        });

        // 5. Add lokasi_id to barang_rusak
        Schema::table('barang_rusak', function (Blueprint $table) {
            $table->string('lokasi_id')->nullable()->after('unit_barang_id');
            $table->date('tanggal_rusak')->nullable()->after('lokasi_id');

            $table->foreign('lokasi_id')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_rusak', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn(['lokasi_id', 'tanggal_rusak']);
        });

        Schema::table('mutasi_lokasi', function (Blueprint $table) {
            $table->dropColumn('tipe_mutasi');
        });

        Schema::table('transaksi_keluar', function (Blueprint $table) {
            $table->dropForeign(['lokasi_asal_id']);
            $table->dropForeign(['lokasi_tujuan_id']);
            $table->dropColumn(['lokasi_asal_id', 'lokasi_tujuan_id', 'tipe', 'catatan']);
        });

        Schema::table('unit_barang', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['distribusi_lokasi', 'created_by']);
        });
    }
};
