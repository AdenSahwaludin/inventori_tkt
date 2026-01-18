# 👥 Dokumentasi Role & Permission - Sistem Inventaris TKT

## 📋 Daftar Isi

1. [Overview Role](#overview-role)
2. [Role: Admin](#role-admin)
3. [Role: Kepala Sekolah](#role-kepala-sekolah)
4. [Role: Petugas Inventaris](#role-petugas-inventaris)
5. [Matriks Permission](#matriks-permission)
6. [Workflow & Use Case](#workflow--use-case)
7. [Implementasi di Filament](#implementasi-di-filament)

---

## 📊 Overview Role

Sistem Inventaris TKT memiliki 3 role utama dengan hierarki dan tanggung jawab yang jelas:

```
┌─────────────────────────────────────────────────────┐
│                      ADMIN                          │
│  (Superadmin - Full Access ke Seluruh Sistem)      │
└─────────────────────────────────────────────────────┘
                        │
        ┌───────────────┴───────────────┐
        ▼                               ▼
┌──────────────────┐         ┌──────────────────────┐
│ KEPALA SEKOLAH   │         │  PETUGAS INVENTARIS  │
│   (Supervisor)   │         │     (Operator)       │
└──────────────────┘         └──────────────────────┘
```

**Prinsip Desain:**

- **Separation of Concern**: Setiap role memiliki tanggung jawab spesifik
- **Least Privilege**: User hanya mendapat akses sesuai kebutuhan pekerjaannya
- **Audit Trail**: Semua aktivitas tercatat di `log_aktivitas`
- **Approval Flow**: Transaksi penting memerlukan approval dari Kepala Sekolah

---

## 🔴 Role: Admin

### Deskripsi

**Admin** adalah superuser sistem dengan akses penuh ke seluruh fitur. Role ini ditujukan untuk **teknisi IT atau developer** yang mengelola sistem secara teknis.

### Karakteristik

- **Level Akses**: Full Control (100%)
- **Jumlah User**: 1-2 orang (sangat terbatas)
- **Fokus Kerja**: Maintenance sistem, user management, konfigurasi
- **Tidak Terlibat**: Operasional harian inventaris

### Full Permissions

```php
// ✅ Semua permission tanpa exception
'*' // Wildcard - Admin bypass semua policy checks
```

### Detail Akses

#### 1. User Management

- ✅ View semua user
- ✅ Create user baru (Admin, Kepala Sekolah, Petugas)
- ✅ Edit profil, role, dan permission user
- ✅ Nonaktifkan/aktifkan user (`is_active`)
- ✅ Reset password user
- ✅ Assign/revoke roles

#### 2. Master Data (Full CRUD)

- ✅ Kategori: create, read, update, delete
- ✅ Lokasi: create, read, update, delete
- ✅ Master Barang: create, read, update, delete
- ✅ Unit Barang: create, read, update, delete, nonaktifkan

#### 3. Transaksi

- ✅ View semua transaksi (masuk & keluar)
- ✅ Create transaksi masuk/keluar
- ✅ **Approve/reject transaksi** (bypass approval flow)
- ✅ Edit transaksi (dengan batasan tertentu)
- ✅ Cancel transaksi

#### 4. Laporan & Monitoring

- ✅ View log aktivitas (semua user)
- ✅ View backup logs
- ✅ Generate laporan (PDF/Excel)
- ✅ Dashboard analytics lengkap
- ✅ Mutasi lokasi histori

#### 5. System Configuration

- ✅ Manual backup database
- ✅ System settings
- ✅ Permission management
- ✅ Access control

### Use Case Admin

**Skenario 1: Setup Awal Sistem**

1. Install dan konfigurasi aplikasi
2. Buat role & permission
3. Buat user Kepala Sekolah
4. Buat user Petugas Inventaris (1-2 orang)
5. Setup kategori dan lokasi awal
6. Import data master barang (jika ada)

**Skenario 2: User Management**

1. Petugas resign → Admin nonaktifkan user lama
2. Petugas baru → Admin buat user baru dengan role "Petugas Inventaris"
3. Kepala Sekolah berganti → Admin ubah role atau buat user baru

**Skenario 3: Troubleshooting**

1. User lupa password → Admin reset password
2. Error data → Admin akses langsung database/log
3. Bug report → Admin debug dan fix

### Policy Implementation

```php
// app/Policies/[Any]Policy.php
public function before(User $user, string $ability): ?bool
{
    // Admin bypass semua policy checks
    if ($user->hasRole('Admin')) {
        return true;
    }
    return null;
}
```

### ⚠️ Batasan & Tanggung Jawab

❌ **Tidak Boleh:**

- Mengubah data transaksi yang sudah approved tanpa alasan kuat
- Menghapus log aktivitas
- Memberikan akses Admin ke user operasional

✅ **Harus:**

- Backup database secara berkala
- Monitor log aktivitas mencurigakan
- Maintain user management dengan hati-hati
- Dokumentasi setiap perubahan sistem

---

## 🔵 Role: Kepala Sekolah

### Deskripsi

**Kepala Sekolah** adalah supervisor dengan fokus pada **monitoring, approval, dan pelaporan**. Role ini memiliki akses read-only luas dan authority untuk approve/reject transaksi.

### Karakteristik

- **Level Akses**: Supervisor (70%)
- **Jumlah User**: 1 orang (Kepala Sekolah)
- **Fokus Kerja**: Monitoring inventaris, approval transaksi, laporan manajemen
- **Tidak Boleh**: Create/edit master data operasional

### Permissions

```php
// View (Read-Only)
'view_kategoris',
'view_lokasis',
'view_master_barangs',
'view_unit_barangs',
'view_transaksi_barangs',
'view_transaksi_keluars',
'view_barang_rusaks',
'view_mutasi_lokasis',
'view_log_aktivitas',

// Approval Authority
'approve_transaksi_barangs',    // ✅ Approve transaksi masuk

// Reporting
'generate_laporan',             // ✅ Generate laporan PDF/Excel
'export_data',                  // ✅ Export data
```

### Detail Akses

#### 1. Master Data (Read-Only)

- ✅ View kategori
- ✅ View lokasi
- ✅ View master barang
- ✅ View unit barang
- ❌ Create/edit/delete (hanya Petugas & Admin)

#### 2. Transaksi (View + Approve)

- ✅ View semua transaksi masuk
- ✅ View semua transaksi keluar
- ✅ **Approve transaksi masuk** (generate unit otomatis)
- ✅ **Approve transaksi keluar**
- ✅ **Reject transaksi** dengan approval notes
- ❌ Create transaksi (hanya Petugas)
- ❌ Edit detail transaksi

#### 3. Laporan Barang Rusak

- ✅ View laporan barang rusak
- ✅ Filter berdasarkan periode, penanggung jawab
- ❌ Create laporan (hanya Petugas)

#### 4. Monitoring & Laporan

- ✅ Dashboard analytics (widget stok rendah, grafik transaksi)
- ✅ View log aktivitas (semua user)
- ✅ View mutasi lokasi histori
- ✅ Generate laporan:
    - Laporan Stok per Kategori/Lokasi
    - Laporan Transaksi Masuk/Keluar (periode)
    - Laporan Barang Rusak
    - Laporan Nilai Inventaris
- ✅ Export ke PDF/Excel

#### 5. User Management (Limited)

- ✅ View daftar user & role
- ❌ Create/edit/delete user

### Use Case Kepala Sekolah

**Skenario 1: Approval Transaksi Masuk Barang**

1. Petugas buat transaksi masuk 5 laptop (status: `pending`)
2. Kepala Sekolah login → notifikasi transaksi pending
3. Kepala Sekolah review detail transaksi
4. Kepala Sekolah approve → sistem generate 5 unit barang otomatis
5. Petugas terima notifikasi "Transaksi Approved"

**Skenario 2: Approval Transaksi Keluar (Peminjaman)**

1. Petugas buat transaksi keluar laptop untuk Guru A (status: `pending`)
2. Kepala Sekolah review: penerima, tujuan, keterangan
3. Kepala Sekolah approve → unit barang status berubah `dipinjam`
4. Guru A dapat notifikasi peminjaman approved

**Skenario 3: Reject Transaksi**

1. Petugas buat transaksi keluar proyektor tanpa tujuan jelas
2. Kepala Sekolah reject dengan notes: "Tolong lengkapi tujuan penggunaan"
3. Petugas dapat notifikasi → perbaiki data → submit ulang

**Skenario 4: Monitoring Rutin Bulanan**

1. Setiap akhir bulan, Kepala Sekolah generate laporan:
    - Transaksi masuk/keluar bulan ini
    - Barang rusak bulan ini
    - Stok per lokasi
2. Export laporan ke PDF
3. Presentasi ke yayasan/dinas pendidikan

**Skenario 5: Cek Barang Stok Rendah**

1. Kepala Sekolah buka dashboard
2. Widget "Barang Stok Rendah" muncul (merah)
3. Kepala Sekolah instruksikan Petugas untuk pengadaan barang

### Policy Implementation

```php
// app/Policies/TransaksiBarangPolicy.php
public function approve(User $user, TransaksiBarang $transaksi): bool
{
    // Hanya Kepala Sekolah (atau Admin) yang bisa approve
    return $user->hasRole('Kepala Sekolah') || $user->hasRole('Admin');
}

public function create(User $user): bool
{
    // Kepala Sekolah TIDAK bisa create transaksi
    return $user->hasRole('Petugas Inventaris') || $user->hasRole('Admin');
}
```

### Filament Resource Customization

```php
// app/Filament/Resources/TransaksiBarangResource.php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([
            Tables\Actions\ViewAction::make(),

            // Action Approve (hanya untuk Kepala Sekolah & Admin)
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) =>
                    $record->approval_status === 'pending'
                    && auth()->user()->can('approve', $record)
                )
                ->action(function ($record) {
                    $record->update([
                        'approval_status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }),

            // Action Reject
            Tables\Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('approval_notes')
                        ->label('Alasan Reject')
                        ->required(),
                ])
                ->visible(fn ($record) =>
                    $record->approval_status === 'pending'
                    && auth()->user()->can('approve', $record)
                )
                ->action(function ($record, array $data) {
                    $record->update([
                        'approval_status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'approval_notes' => $data['approval_notes'],
                    ]);
                }),
        ]);
}
```

### ⚠️ Batasan & Tanggung Jawab

❌ **Tidak Boleh:**

- Create/edit master data (kategori, lokasi, barang)
- Create transaksi (delegasi ke Petugas)
- Edit data transaksi yang sudah approved
- Nonaktifkan unit barang
- Hapus data apapun

✅ **Harus:**

- Review dan approve/reject transaksi dalam 1-2 hari kerja
- Monitor dashboard dan stok rendah secara berkala
- Generate laporan bulanan untuk manajemen
- Berikan approval notes yang jelas saat reject

---

## 🟢 Role: Petugas Inventaris

### Deskripsi

**Petugas Inventaris** adalah operator harian sistem dengan fokus pada **input data, transaksi, dan maintenance inventaris**. Role ini adalah user paling aktif dalam sistem.

### Karakteristik

- **Level Akses**: Operator (60%)
- **Jumlah User**: 1-3 orang (staff inventaris)
- **Fokus Kerja**: Input data barang, transaksi masuk/keluar, laporan barang rusak
- **Tidak Boleh**: Approve transaksi sendiri, hapus data, user management

### Permissions

```php
// Master Data
'view_kategoris',
'view_lokasis',
'view_master_barangs',
'create_master_barangs',    // ✅ Tambah master barang baru
'edit_master_barangs',      // ✅ Edit info master barang
'view_unit_barangs',
'create_unit_barangs',      // ✅ Generate unit manual (rare case)
'edit_unit_barangs',        // ✅ Edit lokasi, catatan unit

// Transaksi Masuk
'view_transaksi_barangs',
'create_transaksi_barangs', // ✅ Buat transaksi masuk (pending)
'edit_transaksi_barangs',   // ✅ Edit transaksi pending (belum approved)

// Transaksi Keluar
'view_transaksi_keluars',
'create_transaksi_keluars', // ✅ Buat transaksi keluar (pending)
'edit_transaksi_keluars',   // ✅ Edit transaksi pending

// Barang Rusak
'view_barang_rusaks',
'create_barang_rusaks',     // ✅ Laporkan barang rusak

// Mutasi Lokasi
'view_mutasi_lokasis',      // ✅ View histori perpindahan

// Monitoring
'view_log_aktivitas',       // ✅ View log aktivitas sendiri
                            // ⚠️  PENTING: Pembatasan akses dilakukan di QUERY level, bukan permission.
                            //     Petugas hanya bisa melihat log miliknya sendiri via filter user_id.
                            //     Lihat LogAktivitasPolicy atau Resource untuk implementasinya.
```

### Detail Akses

#### 1. Master Data

- ✅ View kategori & lokasi (read-only, dikelola Admin)
- ✅ **Create master barang** (saat barang baru datang)
- ✅ **Edit master barang** (update harga, merk, deskripsi)
- ✅ View unit barang
- ✅ **Create unit barang** (⚠️ **HANYA untuk kondisi khusus**:
    - Barang hibah satuan (tanpa transaksi masuk formal)
    - Koreksi data legacy (migrasi data lama)
    - **CATATAN**: Pembuatan unit manual TIDAK menggantikan transaksi masuk.
      Sebagian besar unit barang dibuat otomatis via approval transaksi masuk.)
- ✅ **Edit unit barang** (update lokasi, catatan)
- ❌ Delete kategori/lokasi/master barang

#### 2. Transaksi Masuk (Barang Datang)

- ✅ **Create transaksi masuk**:
    - Pilih master barang (atau buat baru)
    - Tentukan jumlah unit
    - Tentukan lokasi penyimpanan
    - Isi keterangan (supplier, tanggal beli, dll)
    - Submit → status `pending` (tunggu approval)
- ✅ **Edit transaksi pending** (⚠️ **Aturan Edit**:
    - Hanya bisa edit **transaksi milik sendiri** (`user_id === auth()->id()`)
    - Hanya saat status masih **`pending`**
    - Setelah `approved` atau `rejected` → **READ-ONLY**, tidak bisa diedit lagi
    - Logic ini ditegakkan di **Policy**, bukan UI saja)
- ✅ View semua transaksi masuk
- ❌ Approve transaksi sendiri
- ❌ Edit transaksi yang sudah approved/rejected

#### 3. Transaksi Keluar (Peminjaman)

- ✅ **Create transaksi keluar**:
    - Pilih unit barang spesifik (yang status `baik`)
    - Isi penerima (nama guru/kelas/departemen)
    - Isi tujuan penggunaan
    - Isi keterangan
    - Submit → status `pending`
- ✅ **Edit transaksi pending** (⚠️ **Aturan sama dengan transaksi masuk**:
    - Hanya transaksi milik sendiri + status `pending`
    - Setelah approved/rejected → READ-ONLY)
- ✅ View semua transaksi keluar
- ❌ Approve transaksi sendiri

#### 4. Laporan Barang Rusak

- ✅ **Create laporan barang rusak**:
    - Pilih unit barang
    - Tanggal kejadian
    - Keterangan kerusakan
    - Penanggung jawab (jika ada)
    - Submit → unit status otomatis `rusak`
- ✅ View semua laporan barang rusak

#### 5. Mutasi Lokasi

- ✅ **Pindahkan unit barang**:
    - Edit unit barang → ubah lokasi
    - Sistem auto-log ke tabel `mutasi_lokasi`
- ✅ View histori mutasi lokasi

#### 6. Monitoring

- ✅ Dashboard (widget stok rendah, transaksi pending)
- ✅ **View log aktivitas sendiri** (⚠️ **Pembatasan di Query Level**:
    - Permission: `view_log_aktivitas` ✅ (granted)
    - **Data dibatasi di Policy/Resource**: `$query->where('user_id', auth()->id())`
    - Petugas TIDAK bisa lihat log user lain
    - Admin & Kepala Sekolah bisa lihat semua log)
- ❌ Generate laporan (hanya Kepala Sekolah & Admin)

### Use Case Petugas Inventaris

**Skenario 1: Barang Baru Datang (Transaksi Masuk)**

1. Supplier kirim 10 laptop Dell Latitude 5420
2. Petugas cek: Master barang "Laptop Dell Latitude 5420" belum ada
3. Petugas create master barang baru:
    - Nama: "Laptop Dell Latitude 5420"
    - Kategori: Elektronik
    - Satuan: Unit
    - Merk: Dell
    - Harga: Rp 8.000.000
    - Reorder Point: 3
4. Petugas create transaksi masuk:
    - Master Barang: Laptop Dell Latitude 5420
    - Jumlah: 10
    - Lokasi Tujuan: Gudang Utama
    - Penanggung Jawab: Pak Budi (supplier)
    - Keterangan: "Pembelian via PO-2024-001"
    - Submit → status `pending`
5. Notifikasi ke Kepala Sekolah untuk approval
6. Setelah approved → 10 unit barang ter-generate otomatis

**Skenario 2: Guru Pinjam Proyektor (Transaksi Keluar)**

1. Guru A minta pinjam proyektor untuk kelas
2. Petugas cek unit proyektor yang tersedia (status `baik`)
3. Petugas create transaksi keluar:
    - Unit Barang: MB-ELK-002-003 (Proyektor Epson)
    - Penerima: Ibu Ani (Guru Kelas 3A)
    - Tujuan: Pembelajaran Kelas 3A
    - Keterangan: "Pinjam 1 minggu untuk pelajaran IPA"
    - Submit → status `pending`
4. Kepala Sekolah approve
5. Unit proyektor status berubah `dipinjam`

**Skenario 3: Laporan Kursi Rusak**

1. Petugas cek gudang → temukan 2 kursi rusak
2. Petugas create laporan barang rusak:
    - Unit Barang: MB-FRN-001-005 (Kursi Kayu)
    - Tanggal Kejadian: 2024-01-15
    - Keterangan: "Kaki kursi patah, tidak bisa diperbaiki"
    - Penanggung Jawab: (kosong, kerusakan natural)
3. Submit → unit status otomatis `rusak`
4. Kepala Sekolah dapat notifikasi ada barang rusak

**Skenario 4: Pindah Barang ke Lokasi Lain**

1. Petugas pindahkan 5 meja dari Gudang ke Kelas 1A
2. Petugas edit setiap unit barang:
    - Ubah Lokasi: dari "Gudang Utama" → "Kelas 1A"
    - Save
3. Sistem auto-log mutasi lokasi (5 record di `mutasi_lokasi`)

**Skenario 5: Cek Transaksi Pending**

1. Petugas buka dashboard
2. Widget "Transaksi Pending" → 3 transaksi masuk menunggu approval
3. Petugas follow-up ke Kepala Sekolah via WA/email

### Policy Implementation

```php
// app/Policies/TransaksiBarangPolicy.php
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
    // Petugas TIDAK bisa approve
    return false;
}
```

### Filament Resource Customization

```php
// app/Filament/Resources/UnitBarangResource.php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([
            // Filter hanya unit aktif untuk operasional
            Tables\Filters\Filter::make('active')
                ->query(fn (Builder $query) => $query->where('is_active', true))
                ->default(), // Default ON

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'baik' => 'Baik',
                    'dipinjam' => 'Dipinjam',
                    'rusak' => 'Rusak',
                    'maintenance' => 'Maintenance',
                ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),

            // Nonaktifkan (hanya Admin)
            Tables\Actions\Action::make('nonaktifkan')
                ->visible(fn () => auth()->user()->hasRole('Admin')),
        ]);
}

// app/Filament/Resources/TransaksiBarangResource.php (Form)
public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Select::make('master_barang_id')
            ->relationship('masterBarang', 'nama_barang')
            ->searchable()
            ->required()
            ->createOptionForm([
                // Inline create master barang baru
                Forms\Components\TextInput::make('nama_barang')->required(),
                Forms\Components\Select::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori'),
                // ... fields lain
            ]),

        Forms\Components\Select::make('lokasi_tujuan')
            ->relationship('lokasiTujuan', 'nama_lokasi')
            ->required(),

        Forms\Components\TextInput::make('jumlah')
            ->numeric()
            ->minValue(1)
            ->required(),

        Forms\Components\DatePicker::make('tanggal_transaksi')
            ->default(now())
            ->required(),

        Forms\Components\TextInput::make('penanggung_jawab'),

        Forms\Components\Textarea::make('keterangan')
            ->rows(3),

        // approval_status readonly (dikelola sistem)
        Forms\Components\Placeholder::make('approval_status')
            ->content(fn ($record) => $record?->approval_status ?? 'pending')
            ->visible(fn ($operation) => $operation === 'edit'),
    ]);
}
```

### ⚠️ Batasan & Tanggung Jawab

❌ **Tidak Boleh:**

- Approve transaksi sendiri (conflict of interest)
- Delete master data apapun
- Nonaktifkan unit barang (hanya Admin)
- Edit transaksi yang sudah approved
- Access user management
- Generate laporan resmi (hanya Kepala Sekolah)

✅ **Harus:**

- Input data dengan akurat dan lengkap
- Submit transaksi segera setelah barang datang/keluar
- Update lokasi unit saat ada perpindahan
- Laporkan barang rusak dengan detail jelas
- Follow-up transaksi pending ke Kepala Sekolah

---

## 📊 Matriks Permission

| Permission                      | Admin | Kepala Sekolah | Petugas Inventaris |
| ------------------------------- | :---: | :------------: | :----------------: |
| **Master Data - Kategori**      |       |                |                    |
| view_kategoris                  |  ✅   |       ✅       |         ✅         |
| create_kategoris                |  ✅   |       ❌       |         ❌         |
| edit_kategoris                  |  ✅   |       ❌       |         ❌         |
| delete_kategoris                |  ✅   |       ❌       |         ❌         |
| **Master Data - Lokasi**        |       |                |                    |
| view_lokasis                    |  ✅   |       ✅       |         ✅         |
| create_lokasis                  |  ✅   |       ❌       |         ❌         |
| edit_lokasis                    |  ✅   |       ❌       |         ❌         |
| delete_lokasis                  |  ✅   |       ❌       |         ❌         |
| **Master Data - Master Barang** |       |                |                    |
| view_master_barangs             |  ✅   |       ✅       |         ✅         |
| create_master_barangs           |  ✅   |       ❌       |         ✅         |
| edit_master_barangs             |  ✅   |       ❌       |         ✅         |
| delete_master_barangs           |  ✅   |       ❌       |         ❌         |
| **Unit Barang**                 |       |                |                    |
| view_unit_barangs               |  ✅   |       ✅       |         ✅         |
| create_unit_barangs             |  ✅   |       ❌       |         ✅         |
| edit_unit_barangs               |  ✅   |       ❌       |         ✅         |
| nonaktifkan_unit_barangs        |  ✅   |       ❌       |         ❌         |
| **Transaksi Masuk**             |       |                |                    |
| view_transaksi_barangs          |  ✅   |       ✅       |         ✅         |
| create_transaksi_barangs        |  ✅   |       ❌       |         ✅         |
| edit_transaksi_barangs          |  ✅   |       ❌       |        ✅\*        |
| approve_transaksi_barangs       |  ✅   |       ✅       |         ❌         |
| **Transaksi Keluar**            |       |                |                    |
| view_transaksi_keluars          |  ✅   |       ✅       |         ✅         |
| create_transaksi_keluars        |  ✅   |       ❌       |         ✅         |
| edit_transaksi_keluars          |  ✅   |       ❌       |        ✅\*        |
| approve_transaksi_keluars       |  ✅   |       ✅       |         ❌         |
| **Barang Rusak**                |       |                |                    |
| view_barang_rusaks              |  ✅   |       ✅       |         ✅         |
| create_barang_rusaks            |  ✅   |       ❌       |         ✅         |
| **Mutasi Lokasi**               |       |                |                    |
| view_mutasi_lokasis             |  ✅   |       ✅       |         ✅         |
| **Log Aktivitas**               |       |                |                    |
| view_log_aktivitas              |  ✅   |       ✅       |       ✅\*\*       |
| **User Management**             |       |                |                    |
| view_users                      |  ✅   |       ✅       |         ❌         |
| create_users                    |  ✅   |       ❌       |         ❌         |
| edit_users                      |  ✅   |       ❌       |         ❌         |
| delete_users                    |  ✅   |       ❌       |         ❌         |
| **Laporan**                     |       |                |                    |
| generate_laporan                |  ✅   |       ✅       |         ❌         |
| export_data                     |  ✅   |       ✅       |         ❌         |
| **System**                      |       |                |                    |
| backup_database                 |  ✅   |       ❌       |         ❌         |
| system_settings                 |  ✅   |       ❌       |         ❌         |

**Keterangan:**

- ✅ = Full Access
- ✅\* = Limited (hanya transaksi pending milik sendiri)
    - Pembatasan via **Policy**: `$transaksi->user_id === $user->id && $transaksi->approval_status === 'pending'`
- ✅\*\* = Limited (hanya log aktivitas sendiri)
    - Pembatasan via **Query Level**: `$query->where('user_id', auth()->id())`
    - Permission tetap granted, tapi data difilter di Resource/Policy
- ❌ = No Access

**Catatan Penting:**

- Permission `view_log_aktivitas` untuk Petugas **tetap granted**, tapi pembatasan dilakukan di **query level**.
- Permission `create_unit_barangs` untuk Petugas **hanya untuk kondisi khusus** (hibah satuan, koreksi legacy), bukan untuk operasional normal.

---

## 🔄 Workflow & Use Case

### Workflow 1: Barang Baru Masuk (Purchase/Donation)

```
┌─────────────────────────────────────────────────────────┐
│  1. Barang Datang ke Sekolah                            │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  2. PETUGAS: Create Master Barang (jika belum ada)      │
│     - Input nama, kategori, harga, dll                  │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  3. PETUGAS: Create Transaksi Masuk                     │
│     - Pilih master barang                               │
│     - Input jumlah unit                                 │
│     - Tentukan lokasi penyimpanan                       │
│     - Status: pending                                   │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  4. SISTEM: Notifikasi ke Kepala Sekolah               │
│     "Transaksi masuk menunggu approval"                 │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  5. KEPALA SEKOLAH: Review & Approve/Reject             │
│     - Cek detail transaksi                              │
│     - Jika OK: Approve                                  │
│     - Jika ada masalah: Reject + notes                  │
└─────────────────────────────────────────────────────────┘
                        │
                ┌───────┴────────┐
                ▼                ▼
          [APPROVED]        [REJECTED]
                │                │
                ▼                ▼
┌──────────────────────┐  ┌──────────────────┐
│ 6a. SISTEM:          │  │ 6b. PETUGAS:     │
│ - Generate unit      │  │ - Perbaiki data  │
│   barang otomatis    │  │ - Submit ulang   │
│ - Set lokasi sesuai  │  └──────────────────┘
│   transaksi          │
│ - Status unit: baik  │
│ - Log aktivitas      │
└──────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  7. SELESAI: Unit barang siap digunakan                 │
└─────────────────────────────────────────────────────────┘
```

### Workflow 2: Peminjaman Barang (Transaksi Keluar)

```
┌─────────────────────────────────────────────────────────┐
│  1. Guru/Kelas Minta Pinjam Barang                      │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  2. PETUGAS: Cek Ketersediaan Unit                      │
│     - Filter: is_active = true, status = 'baik'         │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  3. PETUGAS: Create Transaksi Keluar                    │
│     - Pilih unit barang spesifik                        │
│     - Input penerima                                    │
│     - Input tujuan penggunaan                           │
│     - Status: pending                                   │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  4. KEPALA SEKOLAH: Approve/Reject                      │
└─────────────────────────────────────────────────────────┘
                        │
                ┌───────┴────────┐
                ▼                ▼
          [APPROVED]        [REJECTED]
                │                │
                ▼                ▼
┌──────────────────────┐  ┌──────────────────┐
│ 5a. SISTEM:          │  │ 5b. PETUGAS:     │
│ - Update unit status │  │ - Follow-up      │
│   ke 'dipinjam'      │  │   dengan alasan  │
│ - Log aktivitas      │  └──────────────────┘
└──────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  6. Barang Dipinjam (tracking via status unit)          │
└─────────────────────────────────────────────────────────┘
```

### Workflow 3: Pengembalian Barang

```
┌─────────────────────────────────────────────────────────┐
│  1. Peminjam Kembalikan Barang                          │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  2. PETUGAS: Cek Kondisi Barang                         │
└─────────────────────────────────────────────────────────┘
                        │
                ┌───────┴────────┐
                ▼                ▼
          [BAIK]            [RUSAK]
                │                │
                ▼                ▼
┌──────────────────────┐  ┌──────────────────────┐
│ 3a. PETUGAS:         │  │ 3b. PETUGAS:         │
│ - Edit unit barang   │  │ - Create laporan     │
│ - Update status:     │  │   barang rusak       │
│   'baik'             │  │ - Sistem auto-update │
│                      │  │   status: 'rusak'    │
└──────────────────────┘  └──────────────────────┘
```

### Workflow 4: Laporan Bulanan (Kepala Sekolah)

```
┌─────────────────────────────────────────────────────────┐
│  1. KEPALA SEKOLAH: Buka Menu Laporan                   │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  2. Pilih Jenis Laporan & Periode                       │
│     - Laporan Stok per Kategori (Jan 2024)              │
│     - Laporan Transaksi Masuk (Jan 2024)                │
│     - Laporan Transaksi Keluar (Jan 2024)               │
│     - Laporan Barang Rusak (Jan 2024)                   │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  3. SISTEM: Generate Laporan PDF/Excel                  │
└─────────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│  4. KEPALA SEKOLAH: Download & Share ke Manajemen       │
└─────────────────────────────────────────────────────────┘
```

---

## 💻 Implementasi di Filament

### 1. Seeder untuk Role & Permission

```php
// database/seeders/RolePermissionSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Kategori
            'view_kategoris', 'create_kategoris', 'edit_kategoris', 'delete_kategoris',

            // Lokasi
            'view_lokasis', 'create_lokasis', 'edit_lokasis', 'delete_lokasis',

            // Master Barang
            'view_master_barangs', 'create_master_barangs', 'edit_master_barangs', 'delete_master_barangs',

            // Unit Barang
            'view_unit_barangs', 'create_unit_barangs', 'edit_unit_barangs', 'nonaktifkan_unit_barangs',

            // Transaksi Barang (Masuk)
            'view_transaksi_barangs', 'create_transaksi_barangs', 'edit_transaksi_barangs', 'approve_transaksi_barangs',

            // Transaksi Keluar
            'view_transaksi_keluars', 'create_transaksi_keluars', 'edit_transaksi_keluars', 'approve_transaksi_keluars',

            // Barang Rusak
            'view_barang_rusaks', 'create_barang_rusaks',

            // Mutasi Lokasi
            'view_mutasi_lokasis',

            // Log Aktivitas
            'view_log_aktivitas',

            // User Management
            'view_users', 'create_users', 'edit_users', 'delete_users',

            // Laporan
            'generate_laporan', 'export_data',

            // System
            'backup_database', 'system_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Role: Admin (Full Access)
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Role: Kepala Sekolah (Supervisor)
        $kepalaSekolahRole = Role::create(['name' => 'Kepala Sekolah']);
        $kepalaSekolahRole->givePermissionTo([
            // View All
            'view_kategoris',
            'view_lokasis',
            'view_master_barangs',
            'view_unit_barangs',
            'view_transaksi_barangs',
            'view_transaksi_keluars',
            'view_barang_rusaks',
            'view_mutasi_lokasis',
            'view_log_aktivitas',
            'view_users',

            // Approval
            'approve_transaksi_barangs',
            'approve_transaksi_keluars',

            // Reporting
            'generate_laporan',
            'export_data',
        ]);

        // 3. Role: Petugas Inventaris (Operator)
        $petugasRole = Role::create(['name' => 'Petugas Inventaris']);
        $petugasRole->givePermissionTo([
            // View
            'view_kategoris',
            'view_lokasis',
            'view_master_barangs',
            'view_unit_barangs',
            'view_transaksi_barangs',
            'view_transaksi_keluars',
            'view_barang_rusaks',
            'view_mutasi_lokasis',
            'view_log_aktivitas',

            // Create/Edit Master Data
            'create_master_barangs',
            'edit_master_barangs',
            'create_unit_barangs',
            'edit_unit_barangs',

            // Transaksi
            'create_transaksi_barangs',
            'edit_transaksi_barangs',
            'create_transaksi_keluars',
            'edit_transaksi_keluars',

            // Barang Rusak
            'create_barang_rusaks',
        ]);
    }
}
```

### 2. Seeder untuk User Default

```php
// database/seeders/UserSeeder.php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@tkt.sch.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        // 2. Kepala Sekolah
        $kepalaSekolah = User::create([
            'name' => 'Kepala Sekolah TKT',
            'email' => 'kepala@tkt.sch.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $kepalaSekolah->assignRole('Kepala Sekolah');

        // 3. Petugas Inventaris
        $petugas = User::create([
            'name' => 'Petugas Inventaris',
            'email' => 'petugas@tkt.sch.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $petugas->assignRole('Petugas Inventaris');
    }
}
```

### 3. Policy dengan Role Check

#### A. Policy untuk Transaksi (dengan aturan edit pending)

```php
// app/Policies/TransaksiBarangPolicy.php
<?php

namespace App\Policies;

use App\Models\TransaksiBarang;
use App\Models\User;

class TransaksiBarangPolicy
{
    /**
     * Admin bypass all checks
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_transaksi_barangs');
    }

    public function view(User $user, TransaksiBarang $transaksi): bool
    {
        return $user->hasPermissionTo('view_transaksi_barangs');
    }

    public function create(User $user): bool
    {
        // Hanya Petugas (atau Admin via before())
        return $user->hasPermissionTo('create_transaksi_barangs');
    }

    public function update(User $user, TransaksiBarang $transaksi): bool
    {
        // ⚠️ ATURAN EDIT TRANSAKSI:
        // 1. Hanya pemilik transaksi (user_id === $user->id)
        // 2. Hanya saat status 'pending' (belum di-approve/reject)
        // 3. Setelah approved/rejected → READ-ONLY (tidak bisa diedit)
        // 4. User harus punya permission 'edit_transaksi_barangs'
        return $transaksi->approval_status === 'pending'
            && $transaksi->user_id === $user->id
            && $user->hasPermissionTo('edit_transaksi_barangs');
    }

    public function approve(User $user, TransaksiBarang $transaksi): bool
    {
        // Hanya Kepala Sekolah (atau Admin via before())
        return $user->hasPermissionTo('approve_transaksi_barangs')
            && $transaksi->approval_status === 'pending';
    }

    public function delete(User $user, TransaksiBarang $transaksi): bool
    {
        // Tidak boleh delete transaksi (hanya Admin via before())
        return false;
    }
}
```

#### B. Policy untuk Log Aktivitas (pembatasan query level)

```php
// app/Policies/LogAktivitasPolicy.php
<?php

namespace App\Policies;

use App\Models\LogAktivitas;
use App\Models\User;

class LogAktivitasPolicy
{
    /**
     * Admin bypass all checks
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_log_aktivitas');
    }

    public function view(User $user, LogAktivitas $log): bool
    {
        // ⚠️ PEMBATASAN QUERY LEVEL:
        // - Kepala Sekolah & Admin: bisa view semua log
        // - Petugas: hanya bisa view log miliknya sendiri
        if ($user->hasRole('Kepala Sekolah')) {
            return true;
        }

        // Petugas hanya boleh melihat log milik sendiri
        return $log->user_id === $user->id
            && $user->hasPermissionTo('view_log_aktivitas');
    }
}
```

**Implementasi di Filament Resource:**

```php
// app/Filament/Resources/LogAktivitasResource.php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // ⚠️ PEMBATASAN QUERY: Petugas hanya lihat log sendiri
    if (!auth()->user()->hasRole(['Admin', 'Kepala Sekolah'])) {
        $query->where('user_id', auth()->id());
    }

    return $query;
}
```

### 4. Filament Resource dengan Authorization

```php
// app/Filament/Resources/MasterBarangResource.php
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class MasterBarangResource extends Resource
{
    protected static ?string $model = MasterBarang::class;

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', MasterBarang::class);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update', $record);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete', $record);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->user()->can('update', $record)),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => auth()->user()->can('delete', $record)),
            ]);
    }
}
```

---

## 📝 Checklist Implementasi

### Setup Awal

- [ ] Install Spatie Laravel Permission: `composer require spatie/laravel-permission`
- [ ] Publish migration: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- [ ] Run migration: `php artisan migrate`
- [ ] Buat RolePermissionSeeder
- [ ] Buat UserSeeder (3 user default)
- [ ] Run seeder: `php artisan db:seed --class=RolePermissionSeeder`
- [ ] Run seeder: `php artisan db:seed --class=UserSeeder`

### Policy Implementation

- [ ] Buat policy untuk setiap model: `php artisan make:policy [Model]Policy --model=[Model]`
- [ ] Tambahkan `before()` method di semua policy untuk Admin bypass
- [ ] Implementasi permission check di setiap policy method

### Filament Resource

- [ ] Update semua Resource dengan authorization check (`canCreate()`, `canEdit()`, `canDelete()`)
- [ ] Hide/show actions berdasarkan permission
- [ ] Tambahkan Action "Approve/Reject" di TransaksiBarangResource & TransaksiKeluarResource
- [ ] Filter default `is_active = true` di UnitBarangResource

### Observer & Notification

- [ ] Observer untuk auto-log aktivitas
- [ ] Notifikasi ke Kepala Sekolah saat ada transaksi pending
- [ ] Email/notifikasi ke Petugas saat transaksi di-approve/reject

### Testing

- [ ] Test login sebagai Admin → cek full access
- [ ] Test login sebagai Kepala Sekolah → cek approval flow
- [ ] Test login sebagai Petugas → cek create transaksi
- [ ] Test policy enforcement (Petugas tidak bisa approve, dll)

---

**Dokumentasi dibuat untuk Sistem Inventaris TKT**
**Last Updated: January 18, 2026**
