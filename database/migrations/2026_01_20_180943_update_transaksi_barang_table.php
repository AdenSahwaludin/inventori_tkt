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
        Schema::table('transaksi_barang', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign('transaksi_barang_lokasi_tujuan_foreign');
            // Drop the old columns
            $table->dropColumn(['lokasi_tujuan', 'jumlah']);
            // Add new JSON column for distribution
            $table->json('distribusi_lokasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_barang', function (Blueprint $table) {
            // Add back the old columns
            $table->string('lokasi_tujuan');
            $table->integer('jumlah');
            // Re-add the foreign key
            $table->foreign('lokasi_tujuan')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            // Drop the new column
            $table->dropColumn('distribusi_lokasi');
        });
    }
};
