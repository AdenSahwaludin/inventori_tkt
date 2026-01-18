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
        Schema::create('master_barang', function (Blueprint $table) {
            $table->string('kode_master')->primary();
            $table->string('nama_barang');
            $table->string('kategori_id');
            $table->string('satuan')->default('pcs');
            $table->string('merk')->nullable();
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->integer('reorder_point')->default(0);
            $table->text('deskripsi')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('kategori_id')
                ->references('kode_kategori')
                ->on('kategori')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang');
    }
};
