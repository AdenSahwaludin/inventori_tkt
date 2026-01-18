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
        Schema::create('unit_barang', function (Blueprint $table) {
            $table->string('kode_unit')->primary();
            $table->string('master_barang_id');
            $table->string('lokasi_id');
            $table->enum('status', ['baik', 'dipinjam', 'rusak', 'maintenance', 'hilang', 'dihapus'])
                ->default('baik');
            $table->boolean('is_active')->default(true);
            $table->date('tanggal_pembelian')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('master_barang_id')
                ->references('kode_master')
                ->on('master_barang')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('lokasi_id')
                ->references('kode_lokasi')
                ->on('lokasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Index for common queries
            $table->index(['status', 'is_active']);
            $table->index(['master_barang_id', 'is_active']);
            $table->index(['lokasi_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_barang');
    }
};
