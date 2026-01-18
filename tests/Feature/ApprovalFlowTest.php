<?php

use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\MasterBarang;
use App\Models\TransaksiBarang;
use App\Models\UnitBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $adminRole = Role::create(['name' => 'Admin']);
    $kepalaRole = Role::create(['name' => 'Kepala Sekolah']);
    $petugasRole = Role::create(['name' => 'Petugas Inventaris']);

    // Create permissions
    $permissions = [
        'view_transaksi_barangs',
        'create_transaksi_barangs',
        'edit_transaksi_barangs',
        'approve_transaksi_barangs',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $adminRole->givePermissionTo(Permission::all());
    $kepalaRole->givePermissionTo(['view_transaksi_barangs', 'approve_transaksi_barangs']);
    $petugasRole->givePermissionTo(['view_transaksi_barangs', 'create_transaksi_barangs', 'edit_transaksi_barangs']);

    // Create users
    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('Admin');

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
        'kode_lokasi' => 'GDA-LT1',
        'nama_lokasi' => 'Gudang Utama',
        'gedung' => 'Gedung A',
        'lantai' => '1',
    ]);

    // Create master barang
    $this->masterBarang = MasterBarang::create([
        'kode_master' => 'MB-ELK-001',
        'nama_barang' => 'Laptop Dell',
        'kategori_id' => 'ELK',
        'satuan' => 'Unit',
        'merk' => 'Dell',
        'harga' => 10000000,
        'reorder_point' => 3,
    ]);
});

describe('Transaksi Barang Creation', function () {
    it('allows petugas to create transaksi barang', function () {
        $transaksi = TransaksiBarang::create([
            'kode_transaksi' => 'TRX-20260118-001',
            'master_barang_id' => $this->masterBarang->kode_master,
            'lokasi_tujuan' => $this->lokasi->kode_lokasi,
            'tanggal_transaksi' => now(),
            'jumlah' => 5,
            'penanggung_jawab' => 'Supplier A',
            'keterangan' => 'Pembelian laptop baru',
            'user_id' => $this->petugas->id,
            'approval_status' => 'pending',
        ]);

        expect($transaksi)->toBeInstanceOf(TransaksiBarang::class)
            ->and($transaksi->approval_status)->toBe('pending')
            ->and($transaksi->jumlah)->toBe(5);
    });

    it('creates transaksi with pending status by default', function () {
        $transaksi = TransaksiBarang::create([
            'kode_transaksi' => 'TRX-20260118-002',
            'master_barang_id' => $this->masterBarang->kode_master,
            'lokasi_tujuan' => $this->lokasi->kode_lokasi,
            'tanggal_transaksi' => now(),
            'jumlah' => 3,
            'user_id' => $this->petugas->id,
            'approval_status' => 'pending',
        ]);

        expect($transaksi->approval_status)->toBe('pending');
    });
});

describe('Approval Flow', function () {
    beforeEach(function () {
        $this->transaksi = TransaksiBarang::create([
            'kode_transaksi' => 'TRX-20260118-003',
            'master_barang_id' => $this->masterBarang->kode_master,
            'lokasi_tujuan' => $this->lokasi->kode_lokasi,
            'tanggal_transaksi' => now(),
            'jumlah' => 5,
            'penanggung_jawab' => 'Supplier A',
            'user_id' => $this->petugas->id,
            'approval_status' => 'pending',
        ]);
    });

    it('generates unit barang when transaksi is approved', function () {
        $initialUnitCount = UnitBarang::count();

        $this->transaksi->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $newUnitCount = UnitBarang::count();

        expect($newUnitCount)->toBe($initialUnitCount + 5);
    });

    it('creates unit barang with correct master barang reference', function () {
        $this->transaksi->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $units = UnitBarang::where('master_barang_id', $this->masterBarang->kode_master)->get();

        expect($units)->toHaveCount(5)
            ->and($units->first()->master_barang_id)->toBe($this->masterBarang->kode_master);
    });

    it('creates unit barang with correct lokasi from transaksi', function () {
        $this->transaksi->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $units = UnitBarang::where('master_barang_id', $this->masterBarang->kode_master)->get();

        foreach ($units as $unit) {
            expect($unit->lokasi_id)->toBe($this->lokasi->kode_lokasi);
        }
    });

    it('creates unit barang with status baik and is_active true', function () {
        $this->transaksi->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $units = UnitBarang::where('master_barang_id', $this->masterBarang->kode_master)->get();

        foreach ($units as $unit) {
            expect($unit->status)->toBe('baik')
                ->and($unit->is_active)->toBeTrue();
        }
    });

    it('generates sequential kode_unit for each unit', function () {
        $this->transaksi->update([
            'approval_status' => 'approved',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
        ]);

        $units = UnitBarang::where('master_barang_id', $this->masterBarang->kode_master)
            ->orderBy('kode_unit')
            ->get();

        expect($units[0]->kode_unit)->toBe('MB-ELK-001-001')
            ->and($units[1]->kode_unit)->toBe('MB-ELK-001-002')
            ->and($units[2]->kode_unit)->toBe('MB-ELK-001-003')
            ->and($units[3]->kode_unit)->toBe('MB-ELK-001-004')
            ->and($units[4]->kode_unit)->toBe('MB-ELK-001-005');
    });

    it('does not generate unit barang when transaksi is rejected', function () {
        $initialUnitCount = UnitBarang::count();

        $this->transaksi->update([
            'approval_status' => 'rejected',
            'approved_by' => $this->kepala->id,
            'approved_at' => now(),
            'approval_notes' => 'Data tidak lengkap',
        ]);

        $newUnitCount = UnitBarang::count();

        expect($newUnitCount)->toBe($initialUnitCount);
    });

    it('records approver information when approved', function () {
        $this->transaksi->approval_status = 'approved';
        $this->transaksi->approved_by = $this->kepala->id;
        $this->transaksi->approved_at = now();
        $this->transaksi->save();

        $this->transaksi->refresh();

        expect($this->transaksi->approved_by)->toBe($this->kepala->id)
            ->and($this->transaksi->approved_at)->not->toBeNull();
    });
});

describe('Authorization', function () {
    beforeEach(function () {
        $this->transaksi = TransaksiBarang::create([
            'kode_transaksi' => 'TRX-20260118-004',
            'master_barang_id' => $this->masterBarang->kode_master,
            'lokasi_tujuan' => $this->lokasi->kode_lokasi,
            'tanggal_transaksi' => now(),
            'jumlah' => 3,
            'user_id' => $this->petugas->id,
            'approval_status' => 'pending',
        ]);
    });

    it('allows kepala sekolah to approve transaksi', function () {
        expect($this->kepala->can('approve', $this->transaksi))->toBeTrue();
    });

    it('allows admin to approve transaksi', function () {
        expect($this->admin->can('approve', $this->transaksi))->toBeTrue();
    });

    it('prevents petugas from approving transaksi', function () {
        expect($this->petugas->can('approve', $this->transaksi))->toBeFalse();
    });

    it('allows petugas to edit own pending transaksi', function () {
        expect($this->petugas->can('update', $this->transaksi))->toBeTrue();
    });

    it('prevents petugas from editing approved transaksi', function () {
        $this->transaksi->update(['approval_status' => 'approved']);

        expect($this->petugas->can('update', $this->transaksi))->toBeFalse();
    });

    it('prevents petugas from editing other user transaksi', function () {
        $otherPetugas = User::factory()->create(['is_active' => true]);
        $otherPetugas->assignRole('Petugas Inventaris');

        expect($otherPetugas->can('update', $this->transaksi))->toBeFalse();
    });
});
