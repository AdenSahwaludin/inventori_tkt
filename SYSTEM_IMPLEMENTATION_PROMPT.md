# 📋 PROMPT: Sistem Inventaris Warehouse TKT - Implementasi Final

**Document Version**: 1.0  
**Last Updated**: January 18, 2026  
**Status**: Ready for Implementation  

---

## 🎯 Objective

Membangun sistem inventaris warehouse yang robust dengan:
- **Master Barang** sebagai referensi dengan distribusi lokasi saat create
- **Unit Barang** yang auto-generate dari distribusi lokasi
- **Transaksi Masuk** untuk penambahan unit dari master yang sudah ada
- **Transaksi Keluar** untuk perpindahan/penghapusan unit
- **Pelacakan Mutasi** lokasi setiap unit barang
- **Authorization** yang ketat dengan 3 role (Admin, Kepala Sekolah, Petugas)

---

## 📊 Architecture Overview

### Database Schema (Final)

```
master_barang (Master Data - Reference)
├── kode_master (PK: NAM-KAT, e.g., LAP-ELE)
├── nama_barang
├── kategori_id (FK → kategoris.kode_kategori)
├── satuan (pcs, box, rim, dus, etc)
├── merk
├── harga_satuan (decimal)
├── reorder_point (integer, default: 0)
├── deskripsi
├── distribusi_lokasi (JSON: [{lokasi_id, jumlah}, ...])
├── created_by (FK → users.id)
├── created_at, updated_at

unit_barang (Physical Units)
├── kode_unit (PK: MASTER-XXX, e.g., LAP-ELE-001)
├── master_barang_id (FK → master_barang.kode_master)
├── lokasi_id (FK → lokasis.kode_lokasi)
├── status (enum: baik|dipinjam|rusak|maintenance|hilang|dihapus)
├── is_active (boolean, default: true)
├── catatan (text, optional)
├── created_by (FK → users.id)
├── created_at, updated_at

transaksi_barang (Transaksi Masuk - Add Units)
├── id (PK)
├── kode_transaksi (unique: TRX-MASUK-YYMMDD-XXX)
├── master_barang_id (FK → master_barang.kode_master)
├── lokasi_tujuan_id (FK → lokasis.kode_lokasi)
├── jumlah (berapa unit akan di-generate)
├── catatan
├── user_id (FK → users.id, pembuat transaksi)
├── approval_status (enum: pending|approved|rejected)
├── approved_by (FK → users.id, nullable)
├── approved_at (timestamp, nullable)
├── approval_notes (text, optional)
├── created_at, updated_at

transaksi_keluar (Transaksi Keluar - Move/Return Units)
├── id (PK)
├── kode_transaksi (unique: TRX-KELUAR-YYMMDD-XXX)
├── unit_barang_id (FK → unit_barang.kode_unit)
├── lokasi_asal_id (FK → lokasis.kode_lokasi)
├── lokasi_tujuan_id (FK → lokasis.kode_lokasi, nullable)
├── tipe (enum: pemindahan|peminjaman|penggunaan|penghapusan)
├── penerima (nama orang/kelas yang terima)
├── tujuan (keterangan tujuan)
├── catatan
├── user_id (FK → users.id)
├── approval_status (enum: pending|approved|rejected)
├── approved_by (FK → users.id, nullable)
├── approved_at (timestamp, nullable)
├── approval_notes (text, optional)
├── created_at, updated_at

mutasi_lokasi (Tracking - Location Changes)
├── id (PK)
├── unit_barang_id (FK → unit_barang.kode_unit)
├── lokasi_asal_id (FK → lokasis.kode_lokasi)
├── lokasi_tujuan_id (FK → lokasis.kode_lokasi)
├── tanggal_mutasi
├── user_id (FK → users.id)
├── tipe_mutasi (enum: create|transaksi_masuk|transaksi_keluar|manual)
├── created_at

barang_rusak (Damage Reports)
├── id (PK)
├── unit_barang_id (FK → unit_barang.kode_unit)
├── lokasi_id (FK → lokasis.kode_lokasi)
├── tanggal_rusak
├── deskripsi
├── penanggung_jawab (text, optional)
├── user_id (FK → users.id)
├── created_at, updated_at

log_aktivitas (Audit Trail)
├── id (PK)
├── user_id (FK → users.id)
├── action (string: create_master, create_unit, approve_transaksi, dll)
├── model (string: MasterBarang, UnitBarang, TransaksiBarang, dll)
├── model_id (string)
├── changes (JSON: {field: old_value, field: new_value})
├── ip_address
├── created_at
```

---

## 🎨 Filament Resources & Forms

### 1. **MasterBarangResource** - Create Master Barang Dengan Distribusi Lokasi

**Navigation**: Inventaris → Master Barang → Buat Master Barang

**Form Structure**:
```
┌─────────────────────────────────────────────────┐
│  SECTION: Data Barang                           │
├─────────────────────────────────────────────────┤
│ Nama Barang *                                   │
│ [Input text]                                    │
│                                                 │
│ Kategori *                      Satuan *        │
│ [Select + Create] (dropdown)    [Select]        │
│                                 (pcs/box/rim)   │
│                                                 │
│ Merk                            Harga Satuan *  │
│ [Input text]                    [Input currency]│
│                                                 │
│ Reorder Point *                 Deskripsi       │
│ [Input number: 0]               [Textarea]      │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  SECTION: Distribusi Lokasi                     │
├─────────────────────────────────────────────────┤
│ Pilih lokasi dan jumlah unit yang akan dibuat   │
│                                                 │
│ Lokasi *              Jumlah *   [- Action]     │
│ [Select + Create]     [Number]                  │
│ Lokasi *              Jumlah *   [- Action]     │
│ [Select + Create]     [Number]                  │
│                                                 │
│ [+ Tambah Lokasi]                               │
│                                                 │
│ Jumlah Unit Total: 8 unit (calculated)          │
└─────────────────────────────────────────────────┘

[Buat]  [Buat & buat lainnya]  [Batal]
```

**Behavior**:
- Kategori field: Searchable, creatable inline
- Lokasi field: Searchable, creatable inline (form untuk nama_lokasi)
- Satuan field: Predefined options (pcs, box, rim, dus, unit, set, paket)
- On Submit:
  - Auto-generate kode_master (NAM-KAT)
  - Auto-generate kode_unit untuk setiap lokasi & jumlah
  - Simpan distribusi_lokasi sebagai JSON
  - Redirect ke detail master barang

**Validation**:
- nama_barang: required, unique, min:3
- kategori_id: required, exists
- satuan: required, in:[pcs, box, rim, ...]
- harga_satuan: required, numeric, >=0
- reorder_point: required, integer, >=0
- distribusi_lokasi: required, min:1, each.jumlah >= 0

---

### 2. **TransaksiBarangResource** - Transaksi Masuk (Penambahan Unit)

**Navigation**: Transaksi → Transaksi Masuk → Buat Transaksi Masuk

**Form Structure**:
```
┌─────────────────────────────────────────────────┐
│  SECTION: Data Transaksi                        │
├─────────────────────────────────────────────────┤
│ Kode Transaksi                                  │
│ TRX-MASUK-260118-001 (readonly, auto)           │
│                                                 │
│ Master Barang *                                 │
│ [Select Searchable]                             │
│ Format: "LAP-ELE - Laptop Dell Latitude 5420"   │
│                                                 │
│ Lokasi Tujuan *                                 │
│ [Select + Create] (dropdown)                    │
│                                                 │
│ Jumlah Unit *                                   │
│ [Input number: min 1]                           │
│                                                 │
│ Catatan                                         │
│ [Textarea] (supplier, nomor PO, dll)            │
│                                                 │
│ Status Approval                                 │
│ pending (readonly)                              │
└─────────────────────────────────────────────────┘

[Buat]  [Buat & buat lainnya]  [Batal]
```

**Behavior**:
- Master Barang: Required, shows current stock info when selected
- On Submit:
  - Status default: pending
  - user_id: auth()->id()
  - Kirim notifikasi ke Kepala Sekolah
  - Observer akan auto-generate unit saat approved

**Permission Check**:
- Hanya Petugas & Admin bisa create
- Petugas hanya bisa edit/lihat transaksi pending miliknya
- Kepala Sekolah bisa lihat semua & approve/reject

---

### 3. **TransaksiKeluarResource** - Transaksi Keluar (Pemindahan/Penghapusan Unit)

**Navigation**: Transaksi → Transaksi Keluar → Buat Transaksi Keluar

**Form Structure**:
```
┌─────────────────────────────────────────────────┐
│  SECTION: Data Transaksi Keluar                 │
├─────────────────────────────────────────────────┤
│ Kode Transaksi                                  │
│ TRX-KELUAR-260118-001 (readonly, auto)          │
│                                                 │
│ Unit Barang *                                   │
│ [Select Searchable]                             │
│ Format: "LAP-ELE-001 - Laptop Dell - Gudang A"  │
│ Filter: status='baik', is_active=true           │
│                                                 │
│ Tipe Transaksi *                                │
│ [Select Radio/Dropdown]                         │
│ ○ Pemindahan (ke ruangan lain)                  │
│ ○ Peminjaman (dipinjam guru/kelas)              │
│ ○ Penggunaan (pakai sendiri/putus alat)         │
│ ○ Penghapusan (sudah tidak layak)               │
│                                                 │
│ Lokasi Tujuan * (visible if tipe != penghapusan)
│ [Select] (pilih ruangan)                        │
│                                                 │
│ Penerima * (visible if tipe = peminjaman)       │
│ [Input text] (nama guru/kelas)                  │
│                                                 │
│ Tujuan (visible if tipe = peminjaman)           │
│ [Textarea] (keterangan penggunaan)              │
│                                                 │
│ Catatan                                         │
│ [Textarea] (optional, keterangan tambahan)      │
│                                                 │
│ Status Approval                                 │
│ pending (readonly)                              │
└─────────────────────────────────────────────────┘

[Buat]  [Buat & buat lainnya]  [Batal]
```

**Behavior**:
- Unit Barang: Only shows active units with status 'baik'
- Tipe field: Changes field visibility (conditional)
- On Submit:
  - Status default: pending
  - user_id: auth()->id()
  - Notify Kepala Sekolah untuk approval
  - Setelah approved: Unit status berubah sesuai tipe

**Validation**:
- unit_barang_id: required, exists, status='baik'
- tipe: required, in:[pemindahan, peminjaman, penggunaan, penghapusan]
- lokasi_tujuan_id: required_unless:tipe,penghapusan
- penerima: required_if:tipe,peminjaman

---

### 4. **UnitBarangResource** - View & Manage Units

**Navigation**: Inventaris → Unit Barang → Daftar Unit

**Table Columns**:
```
┌─────────────────────────────────────────────────────────────┐
│ [☐] │ Kode Unit      │ Master Barang │ Lokasi    │ Status │
├─────────────────────────────────────────────────────────────┤
│ [☐] │ LAP-ELE-001 📋 │ Laptop Dell   │ Gudang A  │ Baik   │
│ [☐] │ LAP-ELE-002 📋 │ Laptop Dell   │ Gudang A  │ Baik   │
│ [☐] │ LAP-ELE-003 📋 │ Laptop Dell   │ Kelas 1   │ Dipinjam│
│ [☐] │ LAP-ELE-004 📋 │ Laptop Dell   │ Kelas 2   │ Rusak  │
│ [☐] │ LAP-ELE-005 📋 │ Laptop Dell   │ Gudang A  │ Maintenance │
└─────────────────────────────────────────────────────────────┘

Columns:
- Kode Unit (copyable icon)
- Master Barang (linked)
- Lokasi (badge)
- Status (colored badge)
- is_active (toggle, only for Admin)
- Actions (View, Edit, Nonaktifkan/Aktifkan)
```

**Actions**:
```
┌─────────────────────────────────────────────────┐
│ VIEW ACTION                                     │
├─────────────────────────────────────────────────┤
│ Kode Unit: LAP-ELE-001                          │
│ Master Barang: Laptop Dell Latitude 5420        │
│ Lokasi: Gudang A                                │
│ Status: Baik                                    │
│ is_active: Yes                                  │
│ Catatan: -                                      │
│ Created: 15 Jan 2026 by Petugas                 │
│                                                 │
│ [Edit] [Nonaktifkan] [Batal]                    │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ EDIT ACTION (hanya Admin)                       │
├─────────────────────────────────────────────────┤
│ Status: [Select: baik|dipinjam|rusak|...]      │
│ Catatan: [Textarea]                             │
│                                                 │
│ [Simpan] [Batal]                                │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ NONAKTIFKAN UNIT ACTION (hanya Admin)           │
├─────────────────────────────────────────────────┤
│ Yakin mau nonaktifkan unit LAP-ELE-001?         │
│ Status akan berubah menjadi 'dihapus' dan       │
│ is_active menjadi false.                        │
│                                                 │
│ [Confirm] [Cancel]                              │
│                                                 │
│ → Update: status='dihapus', is_active=false     │
│ → Log aktivitas                                 │
└─────────────────────────────────────────────────┘
```

**Filters**:
- Status: Baik, Dipinjam, Rusak, Maintenance, Hilang, Dihapus
- Lokasi: Semua lokasi
- Active: Yes, No
- Master Barang: Select

**Scopes** (Default):
- Filter: is_active = true (hanya unit aktif)
- Order: kode_unit ASC

---

## 🔄 Observer & Business Logic

### **MasterBarangObserver**

**Location**: `app/Observers/MasterBarangObserver.php`

```php
public function creating(MasterBarang $master): void
{
    // 1. Generate kode_master dari nama_barang & kategori
    $nama_code = substr(str_replace(' ', '', $master->nama_barang), 0, 3);
    $kategori_code = $master->kategori->nama_kategori;
    $kategori_code = substr(str_replace(' ', '', $kategori_code), 0, 3);
    
    $master->kode_master = strtoupper($nama_code . '-' . $kategori_code);
    
    // 2. Ensure kode_master unique, add suffix if needed
    $count = 1;
    $original_code = $master->kode_master;
    while (MasterBarang::where('kode_master', $master->kode_master)->exists()) {
        $master->kode_master = $original_code . $count++;
    }
}

public function created(MasterBarang $master): void
{
    // 3. Auto-generate UnitBarang sesuai distribusi_lokasi
    if ($master->distribusi_lokasi && is_array($master->distribusi_lokasi)) {
        $unit_counter = 1;
        
        foreach ($master->distribusi_lokasi as $distribusi) {
            $lokasi_id = $distribusi['lokasi_id'];
            $jumlah = (int) $distribusi['jumlah'];
            
            // Generate unit untuk lokasi ini
            for ($i = 0; $i < $jumlah; $i++) {
                $kode_unit = sprintf('%s-%03d', $master->kode_master, $unit_counter);
                
                UnitBarang::create([
                    'kode_unit' => $kode_unit,
                    'master_barang_id' => $master->kode_master,
                    'lokasi_id' => $lokasi_id,
                    'status' => 'baik',
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
                
                // Log mutasi lokasi untuk tracking
                MutasiLokasi::create([
                    'unit_barang_id' => $kode_unit,
                    'lokasi_asal_id' => null,
                    'lokasi_tujuan_id' => $lokasi_id,
                    'tanggal_mutasi' => now(),
                    'user_id' => auth()->id(),
                    'tipe_mutasi' => 'create',
                ]);
                
                $unit_counter++;
            }
        }
        
        // Log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'action' => 'create_master',
            'model' => 'MasterBarang',
            'model_id' => $master->kode_master,
            'changes' => [
                'nama_barang' => $master->nama_barang,
                'jumlah_unit' => $unit_counter - 1,
            ],
            'ip_address' => request()->ip(),
        ]);
    }
}
```

---

### **TransaksiBarangObserver**

**Location**: `app/Observers/TransaksiBarangObserver.php`

```php
public function updated(TransaksiBarang $transaksi): void
{
    // Trigger saat status_approval changed to 'approved'
    if ($transaksi->isDirty('approval_status') 
        && $transaksi->approval_status === 'approved'
    ) {
        $master = $transaksi->masterBarang;
        
        // 1. Get last unit number for this master
        $lastUnit = UnitBarang::where('master_barang_id', $master->kode_master)
            ->orderByDesc('kode_unit')
            ->first();
        
        $startNumber = $lastUnit 
            ? intval(substr($lastUnit->kode_unit, -3)) + 1 
            : 1;
        
        // 2. Generate new units
        for ($i = 0; $i < $transaksi->jumlah; $i++) {
            $kode_unit = sprintf('%s-%03d', $master->kode_master, $startNumber + $i);
            
            UnitBarang::create([
                'kode_unit' => $kode_unit,
                'master_barang_id' => $master->kode_master,
                'lokasi_id' => $transaksi->lokasi_tujuan_id,
                'status' => 'baik',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
            
            // Log mutasi lokasi
            MutasiLokasi::create([
                'unit_barang_id' => $kode_unit,
                'lokasi_asal_id' => null,
                'lokasi_tujuan_id' => $transaksi->lokasi_tujuan_id,
                'tanggal_mutasi' => now(),
                'user_id' => auth()->id(),
                'tipe_mutasi' => 'transaksi_masuk',
            ]);
        }
        
        // 3. Log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'action' => 'approve_transaksi_masuk',
            'model' => 'TransaksiBarang',
            'model_id' => $transaksi->id,
            'changes' => [
                'status' => 'pending → approved',
                'jumlah_unit_dibuat' => $transaksi->jumlah,
            ],
            'ip_address' => request()->ip(),
        ]);
        
        // 4. Notifikasi ke Petugas
        // User::find($transaksi->user_id)->notify(new TransaksiApprovedNotification($transaksi));
    }
}
```

---

### **UnitBarangObserver**

**Location**: `app/Observers/UnitBarangObserver.php`

```php
public function updating(UnitBarang $unit): void
{
    // Saat lokasi_id berubah
    if ($unit->isDirty('lokasi_id')) {
        $old_lokasi_id = $unit->getOriginal('lokasi_id');
        $new_lokasi_id = $unit->lokasi_id;
        
        // Log mutasi lokasi
        MutasiLokasi::create([
            'unit_barang_id' => $unit->kode_unit,
            'lokasi_asal_id' => $old_lokasi_id,
            'lokasi_tujuan_id' => $new_lokasi_id,
            'tanggal_mutasi' => now(),
            'user_id' => auth()->id(),
            'tipe_mutasi' => 'manual',
        ]);
    }
    
    // Saat status berubah
    if ($unit->isDirty('status')) {
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'action' => 'update_unit_status',
            'model' => 'UnitBarang',
            'model_id' => $unit->kode_unit,
            'changes' => [
                'status' => $unit->getOriginal('status') . ' → ' . $unit->status,
            ],
            'ip_address' => request()->ip(),
        ]);
    }
    
    // Saat is_active berubah
    if ($unit->isDirty('is_active')) {
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'action' => $unit->is_active ? 'activate_unit' : 'nonaktifkan_unit',
            'model' => 'UnitBarang',
            'model_id' => $unit->kode_unit,
            'changes' => [
                'is_active' => $unit->getOriginal('is_active') ? 'true' : 'false' . ' → ' . ($unit->is_active ? 'true' : 'false'),
            ],
            'ip_address' => request()->ip(),
        ]);
    }
}
```

---

## 🔐 Authorization & Scopes

### **Query Scopes** (UnitBarang Model)

```php
// app/Models/UnitBarang.php

public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

public function scopeOperational(Builder $query): Builder
{
    return $query->where('is_active', true)
                 ->whereIn('status', ['baik', 'maintenance']);
}

public function scopeAvailableForTransaction(Builder $query): Builder
{
    return $query->where('is_active', true)
                 ->where('status', 'baik');
}
```

---

### **Policy Rules**

**File**: `app/Policies/MasterBarangPolicy.php`
```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Admin')) {
        return true;
    }
    return null;
}

public function viewAny(User $user): bool
{
    return $user->hasPermissionTo('view_master_barangs');
}

public function create(User $user): bool
{
    return $user->hasPermissionTo('create_master_barangs');
}

public function update(User $user, MasterBarang $master): bool
{
    return $user->hasPermissionTo('edit_master_barangs');
}

public function delete(User $user, MasterBarang $master): bool
{
    return false; // Tidak boleh delete master barang
}
```

**File**: `app/Policies/UnitBarangPolicy.php`
```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Admin')) {
        return true;
    }
    return null;
}

public function viewAny(User $user): bool
{
    return $user->hasPermissionTo('view_unit_barangs');
}

public function update(User $user, UnitBarang $unit): bool
{
    // Petugas bisa edit via Actions, bukan form langsung
    return $user->hasPermissionTo('edit_unit_barangs');
}

public function delete(User $user, UnitBarang $unit): bool
{
    return false; // Gunakan nonaktifkan, bukan delete
}
```

**File**: `app/Policies/TransaksiBarangPolicy.php`
```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Admin')) {
        return true;
    }
    return null;
}

public function create(User $user): bool
{
    return $user->hasPermissionTo('create_transaksi_barangs');
}

public function update(User $user, TransaksiBarang $transaksi): bool
{
    // Hanya bisa edit transaksi pending milik sendiri
    return $transaksi->approval_status === 'pending'
        && $transaksi->user_id === $user->id
        && $user->hasPermissionTo('edit_transaksi_barangs');
}

public function approve(User $user, TransaksiBarang $transaksi): bool
{
    return $user->hasPermissionTo('approve_transaksi_barangs')
        && $transaksi->approval_status === 'pending';
}

public function delete(User $user, TransaksiBarang $transaksi): bool
{
    return false; // Tidak boleh delete transaksi
}
```

---

## 🎯 Role & Permission Matrix

| Role | Master Barang | Unit Barang | Transaksi Masuk | Transaksi Keluar | Approve |
|------|:---:|:---:|:---:|:---:|:---:|
| **Admin** | CRUD | CRUD | CRUD | CRUD | ✅ |
| **Petugas Inventaris** | C/R/U | R | C/R/U* | C/R/U* | ❌ |
| **Kepala Sekolah** | R | R | R | R | ✅ |

**Keterangan**:
- C = Create, R = Read, U = Update, D = Delete
- * = Hanya pending & milik user sendiri

---

## ✅ Implementation Checklist

### Phase 1: Database & Models
- [ ] Review/update MasterBarang migration (add distribusi_lokasi JSON)
- [ ] Review/update UnitBarang migration
- [ ] Create/update models: MasterBarang, UnitBarang, TransaksiBarang, TransaksiKeluar, MutasiLokasi, BarangRusak
- [ ] Create MasterBarangObserver (auto-generate kode & unit)
- [ ] Create UnitBarangObserver (log mutasi, log aktivitas)
- [ ] Update TransaksiBarangObserver (auto-generate unit on approval)
- [ ] Register observers di AppServiceProvider

### Phase 2: Filament Resources
- [ ] Create/update MasterBarangResource:
  - [ ] Form dengan distribusi lokasi Repeater
  - [ ] Create option untuk Kategori & Lokasi
  - [ ] Table dengan actions
- [ ] Create/update TransaksiBarangResource:
  - [ ] Form dengan master barang + lokasi tujuan
  - [ ] Create option untuk Lokasi
  - [ ] Approval actions (Approve, Reject)
- [ ] Create/update TransaksiKeluarResource:
  - [ ] Form dengan unit barang spesifik
  - [ ] Conditional visibility (lokasi_tujuan hanya jika pemindahan)
  - [ ] Unit barang filter (status='baik', is_active=true)
- [ ] Create/update UnitBarangResource:
  - [ ] Table dengan filter & sort
  - [ ] View/Edit actions
  - [ ] Nonaktifkan/Aktifkan actions (hanya Admin)

### Phase 3: Authorization
- [ ] Create/update policies: MasterBarang, UnitBarang, TransaksiBarang, TransaksiKeluar
- [ ] Register policies di AuthServiceProvider
- [ ] Update RolePermissionSeeder:
  - [ ] Add permissions: create_lokasis, edit_lokasis untuk Petugas
  - [ ] Create TestUserSeeder
- [ ] Add `before()` method di semua policy untuk Admin bypass

### Phase 4: Testing
- [ ] Test auto-generate kode_master (format NAM-KAT)
- [ ] Test auto-generate unit barang dari distribusi lokasi
- [ ] Test unit numbering continues from last
- [ ] Test transaksi masuk generate new units on approval
- [ ] Test mutasi lokasi tracking on unit lokasi change
- [ ] Test authorization untuk semua roles
- [ ] Test soft-delete alternative (is_active + status)
- [ ] Test Filament form validations

---

## 🧪 Test Requirements

### MasterBarang Tests
```php
it('generates kode_master with format NAM-KAT', function () {
    $kategori = Kategori::factory()->create(['nama_kategori' => 'Elektronik']);
    
    $master = MasterBarang::create([
        'nama_barang' => 'Laptop',
        'kategori_id' => $kategori->kode_kategori,
        'satuan' => 'unit',
        'merk' => 'Dell',
        'harga_satuan' => 8000000,
        'reorder_point' => 5,
        'distribusi_lokasi' => [
            ['lokasi_id' => 1, 'jumlah' => 0],
        ],
    ]);
    
    expect($master->kode_master)->toMatch('/^[A-Z]{3}-[A-Z]{3}$/');
});

it('auto-generates units from distribusi_lokasi', function () {
    $kategori = Kategori::factory()->create();
    $lokasi1 = Lokasi::factory()->create();
    $lokasi2 = Lokasi::factory()->create();
    
    $master = MasterBarang::create([
        'nama_barang' => 'Laptop',
        'kategori_id' => $kategori->kode_kategori,
        'satuan' => 'unit',
        'merk' => 'Dell',
        'harga_satuan' => 8000000,
        'reorder_point' => 0,
        'distribusi_lokasi' => [
            ['lokasi_id' => $lokasi1->kode_lokasi, 'jumlah' => 3],
            ['lokasi_id' => $lokasi2->kode_lokasi, 'jumlah' => 2],
        ],
    ]);
    
    expect($master->unitBarang())->toHaveCount(5);
    expect($master->unitBarang()->where('lokasi_id', $lokasi1->kode_lokasi))->toHaveCount(3);
    expect($master->unitBarang()->where('lokasi_id', $lokasi2->kode_lokasi))->toHaveCount(2);
});

it('distributes units correctly across locations', function () {
    // Same as above, verify each unit has correct lokasi_id
});
```

### TransaksiBarang Tests
```php
it('generates new units on approval', function () {
    $master = MasterBarang::factory()
        ->has(UnitBarang::factory()->count(4))
        ->create();
    
    $transaksi = TransaksiBarang::factory()->create([
        'master_barang_id' => $master->kode_master,
        'jumlah' => 2,
        'approval_status' => 'pending',
    ]);
    
    $transaksi->update(['approval_status' => 'approved']);
    
    expect($master->fresh()->unitBarang()->count())->toBe(6);
});

it('continues unit numbering from last', function () {
    // Create master with 4 units (001-004)
    // Create transaksi untuk 2 units
    // Verify new units are 005-006 (bukan 001-002)
});
```

### Authorization Tests
```php
it('petugas cannot approve transaksi', function () {
    $petugas = User::factory()->create()->assignRole('Petugas Inventaris');
    
    $transaksi = TransaksiBarang::factory()->create([
        'approval_status' => 'pending',
    ]);
    
    expect($petugas->can('approve', $transaksi))->toBeFalse();
});

it('kepala sekolah can approve transaksi', function () {
    $kepala = User::factory()->create()->assignRole('Kepala Sekolah');
    
    $transaksi = TransaksiBarang::factory()->create([
        'approval_status' => 'pending',
    ]);
    
    expect($kepala->can('approve', $transaksi))->toBeTrue();
});

it('petugas can only edit own pending transaksi', function () {
    $petugas1 = User::factory()->create()->assignRole('Petugas Inventaris');
    $petugas2 = User::factory()->create()->assignRole('Petugas Inventaris');
    
    $transaksi = TransaksiBarang::factory()->create([
        'user_id' => $petugas1->id,
        'approval_status' => 'pending',
    ]);
    
    expect($petugas1->can('update', $transaksi))->toBeTrue();
    expect($petugas2->can('update', $transaksi))->toBeFalse();
});
```

### UnitBarang Tests
```php
it('creates mutasi_lokasi record on lokasi change', function () {
    $unit = UnitBarang::factory()->create();
    $new_lokasi = Lokasi::factory()->create();
    
    $unit->update(['lokasi_id' => $new_lokasi->kode_lokasi]);
    
    expect(MutasiLokasi::where('unit_barang_id', $unit->kode_unit)->count())->toBeGreaterThan(0);
});

it('filters active units correctly', function () {
    UnitBarang::factory()->count(5)->create(['is_active' => true]);
    UnitBarang::factory()->count(3)->create(['is_active' => false]);
    
    expect(UnitBarang::active()->count())->toBe(5);
});

it('returns operational units', function () {
    UnitBarang::factory()->create(['status' => 'baik', 'is_active' => true]);
    UnitBarang::factory()->create(['status' => 'rusak', 'is_active' => true]);
    
    expect(UnitBarang::operational()->count())->toBe(1);
});
```

---

## 📝 Implementation Notes

### 1. **ID Generation Strategy**
- **Master Barang**: NAM-KAT (3 huruf nama + 3 huruf kategori)
  - Contoh: LAP-ELE, KUR-KAY, MES-OFI
  - Jika duplikat, tambah suffix: LAP-ELE1, LAP-ELE2, dst
  
- **Unit Barang**: MASTER-XXX (kode master + nomor urut 3 digit)
  - Contoh: LAP-ELE-001, LAP-ELE-002, LAP-ELE-003
  - Nomor otomatis increment berdasarkan existing units
  - Saat transaksi masuk, continue dari unit terakhir

### 2. **Distribusi Lokasi - Timing**
- **Create Master**: User pilih lokasi & jumlah saat create
- **Auto-generate**: Langsung setelah master created
- **Transaksi Masuk**: Untuk penambahan unit kemudian

### 3. **Status Unit Barang**
```
baik          → Unit dalam kondisi baik, siap digunakan
dipinjam      → Unit sedang dipinjam user lain
rusak         → Unit mengalami kerusakan
maintenance   → Unit sedang dalam perbaikan
hilang        → Unit hilang/tidak ketemu
dihapus       → Unit sudah tidak aktif (soft delete)
```

### 4. **Permission & Policy Layer**
- **Permission**: Granted/Denied di role level
- **Policy**: Additional logic untuk fine-grained control
  - Contoh: Petugas bisa edit, tapi hanya transaksi pending miliknya
  - Semua controller harus call `$this->authorize()` atau `@can`

### 5. **Audit & Compliance**
- **LogAktivitas**: Setiap create/update/delete unit
- **MutasiLokasi**: Track perpindahan unit antar lokasi
- **IP Logging**: Catat IP address user untuk security

### 6. **No Hard Delete**
- Unit barang tidak pernah di-delete dari database
- Gunakan `is_active = false` + `status = 'dihapus'`
- All queries default: `where('is_active', true)`

---

## 🚀 Ready to Implement!

**Estimated Timeline**:
- Phase 1 (Database & Models): 2-3 jam
- Phase 2 (Filament Resources): 3-4 jam
- Phase 3 (Authorization): 1-2 jam
- Phase 4 (Testing): 2-3 jam
- **Total**: ~10 jam kerja

**Next Steps**:
1. Review prompt ini & clarify jika ada yang kurang
2. Start dari Phase 1: database & models
3. Run migrations & seeders
4. Test auto-generate logic
5. Implement Filament resources
6. Write & run tests
7. Run Pint formatter
8. Final review & deployment

---

**Document Status**: ✅ Ready  
**Last Reviewed**: January 18, 2026  
**Version**: 1.0
