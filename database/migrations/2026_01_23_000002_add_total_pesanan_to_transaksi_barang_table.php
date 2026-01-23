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
            $table->integer('total_pesanan')->default(0)->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_barang', function (Blueprint $table) {
            $table->dropColumn('total_pesanan');
        });
    }
};
