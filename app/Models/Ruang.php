<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruang extends Model
{
    use SoftDeletes;

    protected $table = 'ruang';

    protected $fillable = [
        'nama_ruang',
    ];

    public function unitBarangs()
    {
        return $this->hasMany(UnitBarang::class, 'ruang_id');
    }

    public function mutasiLokasis()
    {
        return $this->hasMany(MutasiLokasi::class, 'ruang_id');
    }

    public function transaksiKeluarAsal()
    {
        return $this->hasMany(TransaksiKeluar::class, 'ruang_asal_id');
    }

    public function transaksiKeluarTujuan()
    {
        return $this->hasMany(TransaksiKeluar::class, 'ruang_tujuan_id');
    }

    public function barangRusaks()
    {
        return $this->hasMany(BarangRusak::class, 'ruang_id');
    }
}
