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
        Schema::create('barang_rusak', function (Blueprint $table) {
            $table->id();
            $table->string('unit_barang_id');
            $table->date('tanggal_kejadian');
            $table->text('keterangan')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('unit_barang_id')
                ->references('kode_unit')
                ->on('unit_barang')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Index for common queries
            $table->index('tanggal_kejadian');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_rusak');
    }
};
