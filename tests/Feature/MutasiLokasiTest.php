<?php

use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\MasterBarang;
use App\Models\MutasiLokasi;
use App\Models\UnitBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create minimal roles
    Role::create(['name' => 'Admin']);
    Role::create(['name' => 'Petugas Inventaris']);

    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('Admin');

    // Create kategori
    $this->kategori = Kategori::create([
        'kode_kategori' => 'FRN',
        'nama_kategori' => 'Furniture',
    ]);

    // Create lokasi
    $this->lokasiAsal = Lokasi::create([
        'kode_lokasi' => 'GDA-LT1-GUDA',
        'nama_lokasi' => 'Gudang Utama',
        'gedung' => 'Gedung A',
        'lantai' => '1',
    ]);

    $this->lokasiTujuan = Lokasi::create([
        'kode_lokasi' => 'GDA-LT2-KL3A',
        'nama_lokasi' => 'Kelas 3A',
        'gedung' => 'Gedung A',
        'lantai' => '2',
    ]);

    // Create master barang
    $this->masterBarang = MasterBarang::create([
        'kode_master' => 'MB-FRN-001',
        'nama_barang' => 'Meja Kayu',
        'kategori_id' => 'FRN',
        'satuan' => 'Unit',
        'harga' => 500000,
        'reorder_point' => 5,
    ]);

    // Create unit barang
    $this->unitBarang = UnitBarang::create([
        'kode_unit' => 'MB-FRN-001-001',
        'master_barang_id' => 'MB-FRN-001',
        'lokasi_id' => $this->lokasiAsal->kode_lokasi,
        'status' => 'baik',
        'is_active' => true,
        'tanggal_pembelian' => now()->subMonths(6),
    ]);
});

describe('Mutasi Lokasi Tracking', function () {
    it('creates mutasi record when unit lokasi changes', function () {
        $initialMutasiCount = MutasiLokasi::count();

        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        $newMutasiCount = MutasiLokasi::count();

        expect($newMutasiCount)->toBe($initialMutasiCount + 1);
    });

    it('records correct lokasi asal in mutasi', function () {
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        $mutasi = MutasiLokasi::latest()->first();

        expect($mutasi->lokasi_asal)->toBe($this->lokasiAsal->kode_lokasi);
    });

    it('records correct lokasi tujuan in mutasi', function () {
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        $mutasi = MutasiLokasi::latest()->first();

        expect($mutasi->lokasi_tujuan)->toBe($this->lokasiTujuan->kode_lokasi);
    });

    it('records correct unit barang reference in mutasi', function () {
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        $mutasi = MutasiLokasi::latest()->first();

        expect($mutasi->unit_barang_id)->toBe($this->unitBarang->kode_unit);
    });

    it('records tanggal mutasi as current date', function () {
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        $mutasi = MutasiLokasi::latest()->first();

        expect($mutasi->tanggal_mutasi->toDateString())->toBe(now()->toDateString());
    });

    it('does not create mutasi when lokasi stays the same', function () {
        $initialMutasiCount = MutasiLokasi::count();

        $this->unitBarang->update([
            'catatan' => 'Update catatan saja',
        ]);

        $newMutasiCount = MutasiLokasi::count();

        expect($newMutasiCount)->toBe($initialMutasiCount);
    });

    it('creates multiple mutasi records for multiple location changes', function () {
        $lokasiKetiga = Lokasi::create([
            'kode_lokasi' => 'GDA-LT3-RVSR',
            'nama_lokasi' => 'Ruang Server',
            'gedung' => 'Gedung A',
            'lantai' => '3',
        ]);

        // First move
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        // Second move
        $this->unitBarang->update([
            'lokasi_id' => $lokasiKetiga->kode_lokasi,
        ]);

        $mutasiCount = MutasiLokasi::where('unit_barang_id', $this->unitBarang->kode_unit)->count();

        expect($mutasiCount)->toBe(2);
    });

    it('tracks complete history of unit movements', function () {
        $lokasiKetiga = Lokasi::create([
            'kode_lokasi' => 'GDB-LT1-RVMU',
            'nama_lokasi' => 'Ruang Musik',
            'gedung' => 'Gedung B',
            'lantai' => '1',
        ]);

        // First move: Gudang -> Kelas 3A
        $this->unitBarang->update([
            'lokasi_id' => $this->lokasiTujuan->kode_lokasi,
        ]);

        // Second move: Kelas 3A -> Ruang Musik
        $this->unitBarang->update([
            'lokasi_id' => $lokasiKetiga->kode_lokasi,
        ]);

        $mutasis = MutasiLokasi::where('unit_barang_id', $this->unitBarang->kode_unit)
            ->orderBy('tanggal_mutasi')
            ->get();

        expect($mutasis[0]->lokasi_asal)->toBe($this->lokasiAsal->kode_lokasi)
            ->and($mutasis[0]->lokasi_tujuan)->toBe($this->lokasiTujuan->kode_lokasi)
            ->and($mutasis[1]->lokasi_asal)->toBe($this->lokasiTujuan->kode_lokasi)
            ->and($mutasis[1]->lokasi_tujuan)->toBe($lokasiKetiga->kode_lokasi);
    });
});

describe('Unit Barang Status', function () {
    it('can be set to rusak', function () {
        $this->unitBarang->update(['status' => 'rusak']);

        expect($this->unitBarang->status)->toBe('rusak');
    });

    it('can be set to dipinjam', function () {
        $this->unitBarang->update(['status' => 'dipinjam']);

        expect($this->unitBarang->status)->toBe('dipinjam');
    });

    it('can be set to maintenance', function () {
        $this->unitBarang->update(['status' => 'maintenance']);

        expect($this->unitBarang->status)->toBe('maintenance');
    });

    it('can be deactivated using nonaktifkan method', function () {
        $this->unitBarang->nonaktifkan('Barang sudah usang');
        $this->unitBarang->refresh();

        expect($this->unitBarang->is_active)->toBeFalse()
            ->and(str_contains($this->unitBarang->catatan ?? '', 'Barang sudah usang'))->toBeTrue();
    });
});

describe('Unit Barang Scopes', function () {
    beforeEach(function () {
        // Create additional units with different statuses
        UnitBarang::create([
            'kode_unit' => 'MB-FRN-001-002',
            'master_barang_id' => 'MB-FRN-001',
            'lokasi_id' => $this->lokasiAsal->kode_lokasi,
            'status' => 'rusak',
            'is_active' => true,
        ]);

        UnitBarang::create([
            'kode_unit' => 'MB-FRN-001-003',
            'master_barang_id' => 'MB-FRN-001',
            'lokasi_id' => $this->lokasiAsal->kode_lokasi,
            'status' => 'baik',
            'is_active' => false,
        ]);

        UnitBarang::create([
            'kode_unit' => 'MB-FRN-001-004',
            'master_barang_id' => 'MB-FRN-001',
            'lokasi_id' => $this->lokasiAsal->kode_lokasi,
            'status' => 'dipinjam',
            'is_active' => true,
        ]);
    });

    it('scope active returns only active units', function () {
        $activeUnits = UnitBarang::active()->get();

        foreach ($activeUnits as $unit) {
            expect($unit->is_active)->toBeTrue();
        }
    });

    it('scope operational returns only active units with status baik', function () {
        $operationalUnits = UnitBarang::operational()->get();

        foreach ($operationalUnits as $unit) {
            expect($unit->is_active)->toBeTrue()
                ->and($unit->status)->toBe('baik');
        }
    });

    it('scope operational excludes rusak units', function () {
        $operationalUnits = UnitBarang::operational()->get();

        $rusakCount = $operationalUnits->where('status', 'rusak')->count();

        expect($rusakCount)->toBe(0);
    });

    it('scope operational excludes inactive units', function () {
        $operationalUnits = UnitBarang::operational()->get();

        $inactiveCount = $operationalUnits->where('is_active', false)->count();

        expect($inactiveCount)->toBe(0);
    });
});
