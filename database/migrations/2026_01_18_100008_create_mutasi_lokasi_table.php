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
        Schema::create('mutasi_lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('unit_barang_id');
            $table->string('lokasi_asal');
            $table->string('lokasi_tujuan');
            $table->date('tanggal_mutasi');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('unit_barang_id')
                ->references('kode_unit')
                ->on('unit_barang')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('lokasi_asal')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('lokasi_tujuan')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Index for common queries
            $table->index('tanggal_mutasi');
            $table->index('unit_barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_lokasi');
    }
};
