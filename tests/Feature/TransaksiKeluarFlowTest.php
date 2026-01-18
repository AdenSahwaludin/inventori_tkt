<?php

use App\Models\BarangRusak;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\MasterBarang;
use App\Models\TransaksiKeluar;
use App\Models\UnitBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'Admin']);
    $kepalaRole = Role::create(['name' => 'Kepala Sekolah']);
    $petugasRole = Role::create(['name' => 'Petugas Inventaris']);

    // Create permissions
    $permissions = [
        'view_transaksi_keluars',
        'create_transaksi_keluars',
        'edit_transaksi_keluars',
        'approve_transaksi_keluars',
        'view_barang_rusaks',
        'create_barang_rusaks',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $kepalaRole->givePermissionTo(['view_transaksi_keluars', 'approve_transaksi_keluars', 'view_barang_rusaks']);
    $petugasRole->givePermissionTo(['view_transaksi_keluars', 'create_transaksi_keluars', 'edit_transaksi_keluars', 'view_barang_rusaks', 'create_barang_rusaks']);

    // Create users
    $this->kepala = User::factory()->create(['is_active' => true]);
    $this->kepala->assignRole('Kepala Sekolah');

    $this->petugas = User::factory()->create(['is_active' => true]);
    $this->petugas->assignRole('Petugas Inventaris');

    // Create kategori dan lokasi
    $this->kategori = Kategori::create([
        'kode_kategori' => 'ELK',
        'nama_kategori' => 'Elektronik',
    ]);

    $this->lokasi = Lokasi::create([
        'kode_lokasi' => 'GDA-LT1-GUDA',
        'nama_lokasi' => 'Gudang Utama',
        'gedung' => 'Gedung A',
        'lantai' => '1',
    ]);

    // Create master barang
    $this->masterBarang = MasterBarang::create([
        'kode_master' => 'MB-ELK-001',
        'nama_barang' => 'Proyektor Epson',
        'kategori_id' => 'ELK',
        'satuan' => 'Unit',
        'harga' => 5000000,
        'reorder_point' => 2,
    ]);

    // Create unit barang
    $this->unitBarang = UnitBarang::create([
        'kode_unit' => 'MB-ELK-001-001',
        'master_barang_id' => 'MB-ELK-001',
        'lokasi_id' => $this->lokasi->kode_lokasi,
        'status' => 'baik',
        'is_active' => true,
        'tanggal_pembelian' => now()->subMonths(3),
    ]);
});

describe('Transaksi Keluar Approval Flow', function () {
    beforeEach(function () {
        $this->transaksiKeluar = TransaksiKeluar::create([
            'kode_transaksi' => 'TRK-20260118-001',
            'unit_barang_id' => $this->unitBarang->kode_unit,
            'tanggal_transaksi' => now(),
            'penerima' => 'Ibu Ani - Guru Kelas 3A',
            'tujuan' => 'Presentasi di kelas',
            'keterangan' => 'Pinjam proyektor untuk pembelajaran IPA',
            'user_id' => $this->petugas->id,
            'approval_status' => 'pending',
        ]);
    });

    it('updates unit status to dipinjam when approved', function () {
        $this->transaksiKeluar->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $this->unitBarang->refresh();

        expect($this->unitBarang->status)->toBe('dipinjam');
    });

    it('keeps unit status unchanged when rejected', function () {
        $originalStatus = $this->unitBarang->status;

        $this->transaksiKeluar->update([
            'approval_status' => 'rejected',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
            'approval_notes' => 'Proyektor sudah dijadwalkan untuk kegiatan lain',
        ]);

        $this->unitBarang->refresh();

        expect($this->unitBarang->status)->toBe($originalStatus);
    });

    it('records approval information correctly', function () {
        $this->transaksiKeluar->approval_status = 'approved';
        $this->transaksiKeluar->approved_by = $this->kepala->id;
        $this->transaksiKeluar->approved_at = now();
        $this->transaksiKeluar->save();

        $this->transaksiKeluar->refresh();

        expect($this->transaksiKeluar->approved_by)->toBe($this->kepala->id)
            ->and($this->transaksiKeluar->approved_at)->not->toBeNull()
            ->and($this->transaksiKeluar->approval_status)->toBe('approved');
    });
});

describe('Barang Rusak Flow', function () {
    it('updates unit status to rusak when barang rusak is created', function () {
        BarangRusak::create([
            'unit_barang_id' => $this->unitBarang->kode_unit,
            'tanggal_kejadian' => now(),
            'keterangan' => 'Lampu proyektor mati total',
            'penanggung_jawab' => null,
            'user_id' => $this->petugas->id,
        ]);

        $this->unitBarang->refresh();

        expect($this->unitBarang->status)->toBe('rusak');
    });

    it('creates barang rusak record with correct information', function () {
        $barangRusak = BarangRusak::create([
            'unit_barang_id' => $this->unitBarang->kode_unit,
            'tanggal_kejadian' => now(),
            'keterangan' => 'Layar proyektor retak',
            'penanggung_jawab' => 'Murid kelas 5B',
            'user_id' => $this->petugas->id,
        ]);

        expect($barangRusak->unit_barang_id)->toBe($this->unitBarang->kode_unit)
            ->and($barangRusak->keterangan)->toBe('Layar proyektor retak')
            ->and($barangRusak->penanggung_jawab)->toBe('Murid kelas 5B');
    });

    it('can link to unit barang relationship', function () {
        $barangRusak = BarangRusak::create([
            'unit_barang_id' => $this->unitBarang->kode_unit,
            'tanggal_kejadian' => now(),
            'keterangan' => 'Kerusakan',
            'user_id' => $this->petugas->id,
        ]);

        expect($barangRusak->unitBarang)->toBeInstanceOf(UnitBarang::class)
            ->and($barangRusak->unitBarang->kode_unit)->toBe($this->unitBarang->kode_unit);
    });
});
