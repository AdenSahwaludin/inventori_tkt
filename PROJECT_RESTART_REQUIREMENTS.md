# 📋 Requirement Memulai Ulang Project Laravel - Sistem Inventaris Warehouse TKT

## 🎯 Tujuan Project

Membangun **Sistem Inventaris Warehouse** berbasis web untuk mengelola barang, transaksi masuk/keluar, laporan stok, dan tracking aktivitas pengguna dengan arsitektur database yang benar dan terstruktur.

---

## 📊 Stack Teknologi

### Backend

- **Laravel**: ^12.0 (Framework PHP terbaru)
- **PHP**: ^8.2 atau ^8.3
- **Database**: MySQL 8.0+ atau MariaDB 10.11+

### Frontend

- **Filament**: 4.0 (Admin Panel Framework)
- **Livewire**: 3.x (bundled dengan Filament)
- **TailwindCSS**: 4.x
- **Alpine.js**: 3.x (bundled dengan Livewire)

### Additional Packages

- **spatie/laravel-permission**: ^6.24 (Role & Permission management)

### Development Tools

- **Laravel Pint**: ^1.24 (Code formatter)
- **Laravel Boost**: ^1.8 (MCP development tools)

---

## 🗄️ Arsitektur Database yang Benar

### ⚠️ CRITICAL: Kesalahan Fatal pada Project Lama

Project lama memiliki 3 masalah desain database kritis:

1. **Tabel Barang Flat yang Salah**:
    - Tabel `barang` menyimpan data master dan unit dalam satu tabel
    - Menyebabkan duplikasi data untuk barang yang sama di lokasi berbeda
    - Sulit tracking unit individual

2. **Tabel StokLokasi Redundant**:
    - Tabel `stok_lokasi` mencoba memecah stok per lokasi
    - Menciptakan relasi yang kompleks dan ambigu
    - Menyebabkan inkonsistensi data stok

3. **Relasi Barang-Kategori-Lokasi Salah**:
    - Barang berelasi langsung ke lokasi (seharusnya unit yang berelasi)
    - TransaksiBarang berelasi ke `barang_id` bukan `unit_barang_id`
    - Kategori berelasi ke Barang yang sudah tidak terpakai

### ✅ Solusi: Arsitektur Master-Detail Pattern

Gunakan **Master-Detail Pattern** dengan pemisahan jelas:

#### 1. **Tabel `master_barang`** (Master Data)

Menyimpan informasi umum barang yang bersifat tetap:

```php
// Primary Key: kode_master (string, non-incrementing)
// Contoh: MB-ELK-001, MB-ALT-002

$table->string('kode_master')->primary();
$table->string('nama_barang');              // "Laptop Dell Latitude 5420"
$table->string('kategori_id');              // FK → kategori.kode_kategori
$table->string('satuan')->default('pcs');   // "pcs", "unit", "box"
$table->string('merk')->nullable();         // "Dell"
$table->decimal('harga_satuan', 15, 2)->nullable();
$table->integer('reorder_point')->default(0); // ✅ Batas minimum stok untuk alert
$table->text('deskripsi')->nullable();
$table->softDeletes();
$table->timestamps();

// Foreign Keys
$table->foreign('kategori_id')->references('kode_kategori')->on('kategori');
```

**Relasi:**

- BelongsTo → Kategori (kategori_id)
- HasMany → UnitBarang (kode_master)
- HasMany → TransaksiBarang (master_barang_id) ✅

---

#### 2. **Tabel `unit_barang`** (Detail/Instance)

Menyimpan setiap unit fisik barang individual:

```php
// Primary Key: kode_unit (string, auto-generated)
// Contoh: MB-ELK-001-001, MB-ELK-001-002

$table->string('kode_unit')->primary();
$table->string('master_barang_id');         // FK → master_barang.kode_master
$table->string('lokasi_id');                // FK → lokasi.kode_lokasi
$table->enum('status', ['baik', 'dipinjam', 'rusak', 'maintenance', 'hilang', 'dihapus'])
      ->default('baik');
$table->boolean('is_active')->default(true); // ✅ Pengganti soft delete
$table->date('tanggal_pembelian')->nullable();
$table->text('catatan')->nullable();
$table->timestamps();

// Foreign Keys
$table->foreign('master_barang_id')->references('kode_master')->on('master_barang');
$table->foreign('lokasi_id')->references('kode_lokasi')->on('lokasi');
```

**⚠️ PENTING: Perbedaan `status` vs `is_active`**

| Field | Fungsi | Contoh Penggunaan |
|-------|--------|-------------------|
| `status` | Kondisi fisik/operasional unit untuk **histori & audit** | `baik`, `rusak`, `dipinjam`, `hilang`, `dihapus` |
| `is_active` | Flag operasional untuk **filtering** pada transaksi/query | `true` = aktif, `false` = nonaktif (tidak muncul di operasional) |

**Aturan Query:**
- Semua query operasional (transaksi, peminjaman, mutasi) **WAJIB** filter `is_active = true`
- Field `status` digunakan untuk laporan histori dan audit trail
- Unit dengan `is_active = false` tetap tersimpan untuk menjaga integritas histori

**Dokumentasi Model (wajib ada di kode):**
```php
/**
 * Model UnitBarang - Representasi unit fisik barang individual
 * 
 * IMPORTANT: Status vs is_active
 * - status: Kondisi fisik unit (baik, rusak, dipinjam, dll) - untuk histori
 * - is_active: Flag operasional (true/false) - untuk filtering query
 * 
 * TIDAK MENGGUNAKAN SOFT DELETE karena:
 * 1. Soft delete dapat menghilangkan histori transaksi
 * 2. Foreign key constraint bisa bermasalah
 * 3. Lebih fleksibel menggunakan is_active + status 'dihapus'
 * 
 * Untuk nonaktifkan unit, JANGAN gunakan delete()!
 * Gunakan Filament Action "Nonaktifkan Unit" yang set:
 * - status = 'dihapus'
 * - is_active = false
 */
```

**Auto-generate Kode Unit:**

```php
// Pattern: {kode_master}-{sequence}
// MB-ELK-001 → MB-ELK-001-001, MB-ELK-001-002, ...
```

**Relasi:**

- BelongsTo → MasterBarang (master_barang_id)
- BelongsTo → Lokasi (lokasi_id)
- HasMany → TransaksiKeluar (unit_barang_id)
- HasMany → BarangRusak (unit_barang_id)
- HasMany → MutasiLokasi (unit_barang_id) ✅

---

#### 3. **Tabel `kategori`** (Master Data)

```php
// Primary Key: kode_kategori (string, auto-generated)
// Contoh: ELK (Elektronik), ALT (Alat Tulis), FRN (Furniture)

$table->string('kode_kategori')->primary();
$table->string('nama_kategori');           // "Elektronik", "Alat Tulis"
$table->text('deskripsi')->nullable();
$table->softDeletes();
$table->timestamps();
```

**Auto-generate Kode:**

```php
// Ambil 3 huruf pertama dari nama_kategori
// "Elektronik" → "ELK"
// "Alat Tulis" → "ALT"
// Jika duplikat: "ELK1", "ELK2"
```

**Relasi:**

- HasMany → MasterBarang (kategori_id)

---

#### 4. **Tabel `lokasi`** (Master Data)

```php
// Primary Key: kode_lokasi (string, auto-generated)
// Contoh: GDA-LT1-R01 (Gedung A, Lantai 1, Ruang 01)

$table->string('kode_lokasi')->primary();
$table->string('nama_lokasi');             // "Ruang Server", "Gudang Utama"
$table->string('gedung')->nullable();      // "Gedung A", "Gedung B"
$table->string('lantai')->nullable();      // "Lantai 1", "Lantai 2"
$table->text('keterangan')->nullable();
$table->softDeletes();
$table->timestamps();
```

**Auto-generate Kode:**

```php
// Format: GD{X}-LT{Y}-R{ZZ}
// Gedung A, Lantai 1, Ruang 01 → "GDA-LT1-R01"
```

**Relasi:**

- HasMany → UnitBarang (lokasi_id)

---

#### 5. **Tabel `transaksi_barang`** (Transaksi Masuk)

⚠️ **PENTING**: Transaksi masuk berelasi ke `master_barang`, bukan `unit_barang`.
Unit barang akan di-generate otomatis saat transaksi di-approve.

```php
$table->id();
$table->string('kode_transaksi')->unique();  // Auto: TRX-MASUK-YYYYMMDD-XXX
$table->string('master_barang_id');          // ✅ FK → master_barang.kode_master
$table->string('lokasi_tujuan');             // ✅ FK → lokasi.kode_lokasi (lokasi penyimpanan)
$table->date('tanggal_transaksi');
$table->integer('jumlah');                   // ✅ Jumlah unit yang akan di-generate
$table->string('penanggung_jawab')->nullable();
$table->text('keterangan')->nullable();
$table->unsignedBigInteger('user_id');       // FK → users.id (pemohon)
$table->unsignedBigInteger('approved_by')->nullable(); // FK → users.id
$table->timestamp('approved_at')->nullable();
$table->enum('approval_status', ['pending', 'approved', 'rejected'])
      ->default('pending');
$table->text('approval_notes')->nullable();
$table->timestamps();

// Foreign Keys
$table->foreign('master_barang_id')->references('kode_master')->on('master_barang');
$table->foreign('lokasi_tujuan')->references('kode_lokasi')->on('lokasi');
$table->foreign('user_id')->references('id')->on('users');
$table->foreign('approved_by')->references('id')->on('users');
```

**Relasi:**

- BelongsTo → MasterBarang (master_barang_id) ✅
- BelongsTo → Lokasi (lokasi_tujuan) ✅
- BelongsTo → User (user_id)
- BelongsTo → User (approved_by)

**Logic Approval:**
Saat `approval_status` berubah menjadi `approved`, sistem otomatis:

1. Generate `unit_barang` sebanyak `jumlah`
2. Set `lokasi_id` sesuai `lokasi_tujuan`
3. Set `status = 'baik'` dan `is_active = true`
4. Set `tanggal_pembelian` sesuai `tanggal_transaksi`

---

#### 6. **Tabel `transaksi_keluar`** (Transaksi Keluar)

⚠️ **KONSEP**: 1 transaksi = 1 unit barang (field `jumlah` tidak diperlukan).

```php
$table->id();
$table->string('kode_transaksi')->unique();  // Auto: TRX-KELUAR-YYYYMMDD-XXX
$table->string('unit_barang_id');            // FK → unit_barang.kode_unit
$table->date('tanggal_transaksi');
$table->string('penerima');                  // Nama penerima barang
$table->string('tujuan')->nullable();        // Tujuan penggunaan
$table->text('keterangan')->nullable();
$table->unsignedBigInteger('user_id');       // FK → users.id (pemohon)
$table->unsignedBigInteger('approved_by')->nullable();
$table->timestamp('approved_at')->nullable();
$table->enum('approval_status', ['pending', 'approved', 'rejected'])
      ->default('pending');
$table->text('approval_notes')->nullable();
$table->timestamps();

// Foreign Keys
$table->foreign('unit_barang_id')->references('kode_unit')->on('unit_barang');
$table->foreign('user_id')->references('id')->on('users');
$table->foreign('approved_by')->references('id')->on('users');
```

**Logic Approval:**
Saat `approval_status` berubah menjadi `approved`:

- Update `unit_barang.status = 'dipinjam'`
- Log aktivitas ke `log_aktivitas`

---

#### 7. **Tabel `barang_rusak`** (Laporan Barang Rusak)

⚠️ **KONSEP**: 1 laporan = 1 unit barang (field `jumlah` dihapus).

```php
$table->id();
$table->string('unit_barang_id');            // FK → unit_barang.kode_unit
$table->date('tanggal_kejadian');
$table->text('keterangan')->nullable();      // Penyebab kerusakan
$table->string('penanggung_jawab');
$table->unsignedBigInteger('user_id');       // FK → users.id (pelapor)
$table->timestamps();

// Foreign Keys
$table->foreign('unit_barang_id')->references('kode_unit')->on('unit_barang');
$table->foreign('user_id')->references('id')->on('users');
```

**Logic Observer:**
Saat record `barang_rusak` dibuat:

- Update `unit_barang.status = 'rusak'`
- Log ke `log_aktivitas`

---

#### 8. **Tabel `users`** (dengan Roles & Permissions)

```php
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->timestamp('email_verified_at')->nullable();
$table->string('password');
$table->boolean('is_active')->default(true);
$table->rememberToken();
$table->softDeletes();
$table->timestamps();
```

**Roles:**

- **Admin**: Full access ke semua fitur
- **Operator**: Buat transaksi, lihat laporan
- **Viewer**: Hanya lihat data (read-only)

**Permissions (gunakan Spatie Laravel Permission):**

```php
// Master Data
'view_kategoris', 'create_kategoris', 'edit_kategoris', 'delete_kategoris',
'view_lokasis', 'create_lokasis', 'edit_lokasis', 'delete_lokasis',

// Barang
'view_master_barangs', 'create_master_barangs', 'edit_master_barangs', 'delete_master_barangs',
'view_unit_barangs', 'create_unit_barangs', 'edit_unit_barangs', 'delete_unit_barangs',

// Transaksi
'view_transaksi_barangs', 'create_transaksi_barangs', 'approve_transaksi_barangs',
'view_transaksi_keluars', 'create_transaksi_keluars', 'approve_transaksi_keluars',

// Laporan
'view_barang_rusaks', 'create_barang_rusaks',
'view_log_aktivitas',

// User Management
'view_users', 'create_users', 'edit_users', 'delete_users',
```

---

#### 9. **Tabel `log_aktivitas`** (Audit Log)

```php
$table->id();
$table->unsignedBigInteger('user_id');
$table->enum('jenis_aktivitas', ['login', 'logout', 'create', 'update', 'delete', 'view']);
$table->string('nama_tabel')->nullable();    // 'master_barang', 'unit_barang', dll
$table->string('record_id')->nullable();     // Bisa integer atau string
$table->text('deskripsi');
$table->json('perubahan_data')->nullable();  // Before & after data
$table->string('ip_address', 45)->nullable();
$table->text('user_agent')->nullable();
$table->timestamps();

$table->foreign('user_id')->references('id')->on('users');
```

**Observer Pattern:** Gunakan Eloquent Observers untuk auto-log semua aktivitas CRUD.

---

#### 10. **Tabel `backup_logs`** (Backup Management)

```php
$table->id();
$table->unsignedBigInteger('user_id');
$table->string('filename')->nullable();
$table->string('format')->default('sql');
$table->bigInteger('file_size')->nullable(); // bytes
$table->enum('status', ['success', 'failed', 'pending'])->default('pending');
$table->text('notes')->nullable();
$table->softDeletes();
$table->timestamps();

$table->foreign('user_id')->references('id')->on('users');
```

---

#### 11. **Tabel `mutasi_lokasi`** (Tracking Perpindahan Unit) ✅

⚠️ **PENTING**: Tabel ini mencatat histori perpindahan lokasi untuk audit trail.

```php
$table->id();
$table->string('unit_barang_id');            // FK → unit_barang.kode_unit
$table->string('lokasi_asal');               // FK → lokasi.kode_lokasi
$table->string('lokasi_tujuan');             // FK → lokasi.kode_lokasi
$table->date('tanggal_mutasi');
$table->text('keterangan')->nullable();
$table->unsignedBigInteger('user_id');       // FK → users.id (yang melakukan mutasi)
$table->timestamps();

// Foreign Keys
$table->foreign('unit_barang_id')->references('kode_unit')->on('unit_barang');
$table->foreign('lokasi_asal')->references('kode_lokasi')->on('lokasi');
$table->foreign('lokasi_tujuan')->references('kode_lokasi')->on('lokasi');
$table->foreign('user_id')->references('id')->on('users');
```

**Relasi:**

- BelongsTo → UnitBarang (unit_barang_id)
- BelongsTo → Lokasi (lokasi_asal)
- BelongsTo → Lokasi (lokasi_tujuan)
- BelongsTo → User (user_id)

**Logic Observer:**
Saat `unit_barang.lokasi_id` berubah:

1. Simpan record baru ke `mutasi_lokasi`
2. Set `lokasi_asal` = lokasi lama
3. Set `lokasi_tujuan` = lokasi baru
4. Set `tanggal_mutasi` = now()
5. Log ke `log_aktivitas`

---

## 🔄 Urutan Migration (PENTING!)

**WAJIB ikuti urutan ini untuk menghindari foreign key error:**

```bash
1. 2024_01_01_000001_create_users_table.php
2. 2024_01_01_000002_create_permission_tables.php  # Spatie package
3. 2024_01_01_000003_create_kategori_table.php
4. 2024_01_01_000004_create_lokasi_table.php
5. 2024_01_01_000005_create_master_barang_table.php
6. 2024_01_01_000006_create_unit_barang_table.php
7. 2024_01_01_000007_create_transaksi_barang_table.php
8. 2024_01_01_000008_create_transaksi_keluar_table.php
9. 2024_01_01_000009_create_barang_rusak_table.php
10. 2024_01_01_000010_create_mutasi_lokasi_table.php ✅
11. 2024_01_01_000011_create_log_aktivitas_table.php
12. 2024_01_01_000012_create_backup_logs_table.php
```

---

## 📐 Entity Relationship Diagram (ERD)

```
                    ┌─────────────┐
                    │   KATEGORI  │
                    └──────┬──────┘
                           │ 1:N
                           ▼
┌──────────┐        ┌─────────────┐        ┌──────────┐
│  LOKASI  │◄───┐   │MASTER_BARANG│   ┌───►│  USERS   │
└────┬─────┘    │   └──────┬──────┘   │    └────┬─────┘
     │ 1:N     │          │ 1:N      │         │
     │         │          ▼          │         │
     │         │   ┌─────────────┐   │         │
     │         └───│ UNIT_BARANG │◄──┘         │
     │             └──────┬──────┘             │
     │                    │                    │
     │         ┌──────────┼──────────┐         │
     │         │          │          │         │
     │         ▼          ▼          ▼         │
     │  ┌──────────┐ ┌─────────┐ ┌─────────┐  │
     │  │TRANSAKSI │ │TRANSAKSI│ │ BARANG_ │  │
     │  │ KELUAR   │ │  RUSAK  │ │  RUSAK  │  │
     │  └────┬─────┘ └────┬────┘ └────┬────┘  │
     │       │            │           │        │
     │       │            ▼           │        │
     │       │     ┌──────────────┐   │        │
     │       │     │ MUTASI_LOKASI│◄──┘        │
     │       │     └──────┬───────┘            │
     │       │            │                    │
     ▼       │            │                    │
┌─────────┐  │            │                    │
│TRANSAKSI│◄─┘            │                    │
│ BARANG  │               │                    │
│(MASUK)  │◄──────────────┘                    │
└────┬────┘                                    │
     │                                         │
     └─────────────────────────────────────────┘
                           │
                           ▼
                  ┌─────────────┐
                  │LOG_AKTIVITAS│
                  └─────────────┘
```

**⚠️ Catatan Penting:**

- `TRANSAKSI_BARANG` (masuk) berelasi ke `MASTER_BARANG` + `LOKASI` ✅
- `TRANSAKSI_KELUAR` berelasi ke `UNIT_BARANG` ✅
- `BARANG_RUSAK` berelasi ke `UNIT_BARANG` ✅
- `MUTASI_LOKASI` mencatat perpindahan `UNIT_BARANG` ✅

---

## 🎨 Filament Resources Structure

### Master Data Resources

1. **KategoriResource** → Manage kategori (CRUD sederhana)
2. **LokasiResource** → Manage lokasi dengan auto-generate kode
3. **MasterBarangResource** → Manage master barang dengan relasi ke kategori

### Operational Resources

4. **UnitBarangResource** →
    - Table columns: kode_unit, nama_barang (via master), kategori, lokasi, status
    - Filter: status, lokasi, kategori
    - Bulk actions: update status, pindah lokasi
    - Detail view: tracking history transaksi

5. **TransaksiBarangResource** (Transaksi Masuk) →
    - Form: pilih unit_barang, tanggal, keterangan
    - Approval flow: pending → approved/rejected
    - Auto-generate kode_transaksi

6. **TransaksiKeluarResource** →
    - Similar dengan TransaksiBarang
    - Tambahan field: penerima, tujuan

7. **BarangRusakResource** →
    - Form: pilih unit_barang, tanggal_kejadian, keterangan, penanggung_jawab
    - Auto-update status unit_barang ke 'rusak'

### Monitoring Resources

8. **MutasiLokasiResource** → ✅ Read-only, tracking perpindahan unit barang
    - Table columns: unit_barang, lokasi_asal, lokasi_tujuan, tanggal_mutasi, user
    - Filter: tanggal, lokasi
    - Tidak ada form (auto-generated via observer)

9. **LogAktivitasResource** → Read-only, with filters and search
10. **BackupLogResource** → Backup management dengan action manual backup

### User Management

10. **UserResource** →
    - Form: name, email, password, roles
    - Shield integration untuk role & permission management

---

## 📊 Dashboard Widgets

### 1. StatsOverviewWidget

```php
- Total Master Barang (count master_barang)
- Total Unit Barang (count unit_barang where status='baik')
- Unit Barang Rusak (count unit_barang where status='rusak')
- Transaksi Bulan Ini (count transaksi_barang current month)
```

### 2. BarangStokRendahWidget

```php
// Hitung unit per master barang, tampilkan yang total unit < reorder_point
// ✅ Menggunakan field reorder_point dari master_barang
SELECT
  master_barang.kode_master,
  master_barang.nama_barang,
  master_barang.reorder_point,
  COUNT(unit_barang.kode_unit) as total_unit
FROM master_barang
LEFT JOIN unit_barang ON unit_barang.master_barang_id = master_barang.kode_master
  AND unit_barang.status = 'baik'
  AND unit_barang.is_active = true
GROUP BY master_barang.kode_master
HAVING total_unit < master_barang.reorder_point AND master_barang.reorder_point > 0
ORDER BY total_unit ASC
```

### 3. TransaksiChartWidget

- Line chart transaksi masuk vs keluar per bulan (6 bulan terakhir)

### 4. BarangPerLokasiWidget

- Pie chart distribusi unit barang per lokasi

---

## 🧪 Testing Requirements (Pest)

### Feature Tests

```php
// tests/Feature/MasterBarangTest.php
- ✓ Admin can create master barang with auto-generated kode
- ✓ Validation works for required fields
- ✓ Kategori relationship works correctly

// tests/Feature/UnitBarangTest.php
- ✓ Unit barang auto-generates kode from master
- ✓ Unit can be moved to different lokasi
- ✓ Status updates correctly

// tests/Feature/TransaksiTest.php
- ✓ Transaksi masuk creates unit_barang record
- ✓ Approval flow works (pending → approved → rejected)
- ✓ Only Admin/Operator can approve

// tests/Feature/BarangRusakTest.php
- ✓ Creating barang_rusak updates unit status to 'rusak'
- ✓ Operator can create barang_rusak report

// tests/Feature/LogAktivitasTest.php
- ✓ Observers auto-log all CRUD operations
- ✓ Log includes IP and user agent
```

### Unit Tests

```php
// tests/Unit/MasterBarangTest.php
- ✓ Kode master auto-generation logic
- ✓ Relationship methods return correct data

// tests/Unit/UnitBarangTest.php
- ✓ Kode unit generation follows pattern
- ✓ Accessor methods work (nama_barang, kategori)
```

---

## 🔐 Authorization & Security

### Policies

Buat policy untuk setiap model dengan `before()` method:

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Admin')) {
        return true; // Admin bypass all checks
    }
    return null;
}
```

### Middleware

- **LogLoginActivity**: Log setiap login dengan IP & user agent
- **EnsureUserIsActive**: Cek `users.is_active` sebelum akses
- **RoleMiddleware**: Spatie permission middleware

---

## 📦 Seeders

### 1. RolePermissionSeeder

```php
// Create roles
Admin, Operator, Viewer

// Assign permissions
- Admin: all permissions
- Operator: view + create transaksi, view laporan
- Viewer: view only
```

### 2. UserSeeder

```php
// Default users
- admin@example.com (Admin)
- operator@example.com (Operator)
- viewer@example.com (Viewer)
```

### 3. KategoriSeeder

```php
Elektronik, Alat Tulis, Furniture, Peralatan Lab, dll
```

### 4. LokasiSeeder

```php
Gedung A - Lantai 1 - Ruang 01
Gedung A - Lantai 2 - Lab Komputer
Gudang Utama, dll
```

### 5. MasterBarangSeeder (dengan UnitBarangSeeder)

```php
// Buat 10-20 master barang
// Setiap master barang, buat 3-5 unit barang di lokasi berbeda
```

---

## 🚀 Setup Instructions

### 1. Create New Laravel Project

```bash
composer create-project laravel/laravel tkt-warehouse
cd tkt-warehouse
```

### 2. Install Dependencies

```bash
# Install Filament
composer require filament/filament:"^4.0" -W

# Install Spatie Permission
composer require spatie/laravel-permission

# Install DOMPDF
composer require barryvdh/laravel-dompdf

# Install Dev Dependencies
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev laravel/pint
composer require --dev laravel/boost

# Init Pest
./vendor/bin/pest --init
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate

# Set database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tkt_warehouse
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install Filament

```bash
php artisan filament:install --panels

# Create Filament user
php artisan make:filament-user
```

### 5. Publish Spatie Permission

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 6. Create Migrations (dalam urutan yang benar!)

```bash
# Buat migrations sesuai urutan di atas
php artisan make:migration create_kategori_table
php artisan make:migration create_lokasi_table
php artisan make:migration create_master_barang_table
php artisan make:migration create_unit_barang_table
# ... dst
```

### 7. Run Migrations & Seeders

```bash
php artisan migrate:fresh --seed
```

### 8. Create Filament Resources

```bash
php artisan make:filament-resource Kategori --generate
php artisan make:filament-resource Lokasi --generate
php artisan make:filament-resource MasterBarang --generate
php artisan make:filament-resource UnitBarang --generate
# ... dst
```

### 9. Create Policies

```bash
php artisan make:policy MasterBarangPolicy --model=MasterBarang
php artisan make:policy UnitBarangPolicy --model=UnitBarang
# ... dst
```

### 10. Create Observers

```bash
php artisan make:observer MasterBarangObserver --model=MasterBarang
php artisan make:observer UnitBarangObserver --model=UnitBarang

# Register di AppServiceProvider:
MasterBarang::observe(MasterBarangObserver::class);
UnitBarang::observe(UnitBarangObserver::class);
```

### 11. Run Dev Server

```bash
php artisan serve
# or
composer run dev  # if using Sail/Valet
```

---

## 📝 Key Implementation Notes

### 1. Auto-generate Kode Pattern

**Kategori:**

```php
protected static function boot() {
    parent::boot();
    static::creating(function ($kategori) {
        if (empty($kategori->kode_kategori)) {
            $kategori->kode_kategori = self::generateKodeKategori($kategori->nama_kategori);
        }
    });
}

public static function generateKodeKategori(string $nama): string {
    $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nama), 0, 3));
    $counter = 1;
    $kode = $prefix;
    while (self::where('kode_kategori', $kode)->exists()) {
        $kode = $prefix . $counter++;
    }
    return $kode;
}
```

**MasterBarang:**

```php
// Pattern: MB-{KATEGORI}-{SEQUENCE}
// MB-ELK-001, MB-ELK-002, MB-ALT-001

public static function generateKodeMaster(string $kategoriId): string {
    $kategori = Kategori::find($kategoriId);
    $prefix = 'MB-' . $kategori->kode_kategori . '-';

    $lastKode = self::where('kode_master', 'LIKE', $prefix . '%')
        ->orderBy('kode_master', 'desc')
        ->first();

    if (!$lastKode) {
        return $prefix . '001';
    }

    $lastNumber = (int) substr($lastKode->kode_master, -3);
    return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
}
```

**UnitBarang:**

```php
// Pattern: {KODE_MASTER}-{SEQUENCE}
// MB-ELK-001-001, MB-ELK-001-002

public static function generateKodeUnit(string $masterBarangId): string {
    $prefix = $masterBarangId . '-';

    $lastKode = self::where('kode_unit', 'LIKE', $prefix . '%')
        ->orderBy('kode_unit', 'desc')
        ->first();

    if (!$lastKode) {
        return $prefix . '001';
    }

    $lastNumber = (int) substr($lastKode->kode_unit, -3);
    return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
}
```

### 2. Transaksi Masuk: Auto-Generate Unit Barang Saat Approval ✅

```php
// Di TransaksiBarangResource form:
Forms\Components\Select::make('master_barang_id')
    ->relationship('masterBarang', 'nama_barang')
    ->required(),

Forms\Components\Select::make('lokasi_tujuan')
    ->relationship('lokasiTujuan', 'nama_lokasi')
    ->required(),

Forms\Components\TextInput::make('jumlah')
    ->numeric()
    ->minValue(1)
    ->required()
    ->helperText('Jumlah unit yang akan di-generate'),

Forms\Components\Select::make('approval_status')
    ->options([
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ])
    ->default('pending')
    ->disabled(fn () => !auth()->user()->hasRole('Admin'))
    ->reactive(),

// Di TransaksiBarangObserver:
public function updated(TransaksiBarang $transaksi) {
    if ($transaksi->isDirty('approval_status') && $transaksi->approval_status === 'approved') {
        // ✅ Auto-generate unit_barang sebanyak jumlah
        for ($i = 0; $i < $transaksi->jumlah; $i++) {
            UnitBarang::create([
                'master_barang_id' => $transaksi->master_barang_id,
                'lokasi_id' => $transaksi->lokasi_tujuan,
                'status' => 'baik',
                'is_active' => true,
                'tanggal_pembelian' => $transaksi->tanggal_transaksi,
                'catatan' => 'Auto-generated dari transaksi: ' . $transaksi->kode_transaksi,
            ]);
        }

        // Set approval metadata
        $transaksi->approved_by = auth()->id();
        $transaksi->approved_at = now();
        $transaksi->saveQuietly(); // Avoid infinite loop
    }
}
```

### 3. Status Management & Mutasi Lokasi ✅

```php
// Saat BarangRusak dibuat, update status unit:
public function created(BarangRusak $barangRusak) {
    UnitBarang::where('kode_unit', $barangRusak->unit_barang_id)
        ->update(['status' => 'rusak']);
}

// Saat TransaksiKeluar approved, update status:
public function updated(TransaksiKeluar $transaksi) {
    if ($transaksi->approval_status === 'approved') {
        UnitBarang::where('kode_unit', $transaksi->unit_barang_id)
            ->update(['status' => 'dipinjam']);
    }
}

// ✅ Tracking Mutasi Lokasi di UnitBarangObserver:
public function updating(UnitBarang $unit) {
    // Cek apakah lokasi_id berubah
    if ($unit->isDirty('lokasi_id')) {
        $lokasiAsal = $unit->getOriginal('lokasi_id');
        $lokasiTujuan = $unit->lokasi_id;

        // Simpan ke tabel mutasi_lokasi
        MutasiLokasi::create([
            'unit_barang_id' => $unit->kode_unit,
            'lokasi_asal' => $lokasiAsal,
            'lokasi_tujuan' => $lokasiTujuan,
            'tanggal_mutasi' => now(),
            'user_id' => auth()->id(),
            'keterangan' => 'Mutasi lokasi via admin panel',
        ]);
    }
}

// ✅ Soft "Delete" dengan status:
public function deleting(UnitBarang $unit) {
    // Jangan benar-benar delete, ubah status dan is_active
    $unit->status = 'dihapus';
    $unit->is_active = false;
    $unit->saveQuietly();

    // Cancel the actual delete
    return false;
}
```

---

## 🎯 Success Criteria

Project dianggap berhasil jika:

✅ **Database:**

- Semua tabel terimplementasi dengan relasi yang benar
- Foreign keys tidak error
- Auto-generate kode berfungsi tanpa duplikat

✅ **Functionality:**

- CRUD lengkap untuk semua resources
- Approval flow berjalan dengan baik
- Status tracking akurat (baik, rusak, hilang)
- Log aktivitas tercatat otomatis

✅ **Authorization:**

- Role & permission berfungsi
- Admin full access
- Operator hanya bisa create transaksi & view laporan
- Viewer read-only

✅ **Testing:**

- Minimal 20 feature tests pass
- Coverage >= 70%
- Manual testing checklist terpenuhi

✅ **UI/UX:**

- Dashboard widgets informatif
- Table sortable, filterable, searchable
- Form validation user-friendly
- Mobile responsive

---

## 📚 References & Documentation

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Filament 4 Documentation](https://filamentphp.com/docs/4.x)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6)
- [Pest Testing Framework](https://pestphp.com/)
- [TailwindCSS 4](https://tailwindcss.com/)

---

## 🐛 Common Pitfalls to Avoid

❌ **Jangan:**

1. Buat tabel `barang` flat tanpa pemisahan master-detail
2. Gunakan `stok_lokasi` sebagai pivot table
3. Buat foreign key sebelum tabel parent ada
4. Hardcode kode barang di seeder tanpa pattern
5. Skip validation di form
6. Lupa update status unit saat transaksi approved
7. ✅ Gunakan soft delete di `unit_barang` (gunakan `is_active` + status `dihapus`)
8. Skip observer untuk log aktivitas
9. ✅ Relasikan transaksi masuk ke `unit_barang` (harus ke `master_barang`)
10. ✅ Lupa generate unit saat transaksi masuk di-approve
11. ✅ Gunakan `return false` di observer `deleting()` (gunakan Filament Action)
12. ✅ Izinkan delete fisik pada `unit_barang` (gunakan nonaktifasi)
13. ✅ Lupa filter `is_active = true` pada query operasional

✅ **Lakukan:**

1. Ikuti Master-Detail pattern dengan ketat
2. Auto-generate semua kode dengan pattern konsisten
3. Gunakan Eloquent relationships, jangan raw SQL
4. Validasi di Form Request, bukan di controller
5. Test setiap fitur dengan Pest
6. Gunakan Observer untuk side effects (log, status update, mutasi lokasi)
7. Format code dengan Pint sebelum commit
8. Dokumentasi setiap perubahan logic kompleks
9. ✅ Transaksi masuk → `master_barang`, transaksi keluar → `unit_barang`
10. ✅ Auto-generate `unit_barang` saat approval transaksi masuk
11. ✅ Track mutasi lokasi di tabel `mutasi_lokasi`
12. ✅ Gunakan `is_active` + status `dihapus` daripada soft delete untuk unit
13. ✅ Set `reorder_point` di `master_barang` untuk alert stok rendah
14. ✅ Buat Filament Action khusus "Nonaktifkan" daripada DeleteAction
15. ✅ Tambahkan scope `active()` dan `operational()` di UnitBarang model
16. ✅ Dokumentasikan perbedaan `status` vs `is_active` di docblock model

---

## 🎬 Next Steps After Project Setup

1. **Week 1**: Setup project, migrations, models, seeders
2. **Week 2**: Filament resources, policies, observers
3. **Week 3**: Dashboard widgets, approval flow
4. **Week 4**: Testing, bug fixing, UI polish
5. **Week 5**: Documentation, deployment preparation

---

**Good luck! 🚀**

Dokumentasi ini memberikan panduan lengkap untuk memulai ulang project dengan arsitektur yang benar. Ikuti setiap step dengan teliti untuk menghindari masalah yang sama.
