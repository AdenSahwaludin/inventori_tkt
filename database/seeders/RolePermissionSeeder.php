<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Kategori
            'view_kategoris',
            'create_kategoris',
            'edit_kategoris',
            'delete_kategoris',

            // Ruang
            'view_ruangs',
            'create_ruangs',
            'edit_ruangs',
            'delete_ruangs',

            // Master Barang
            'view_master_barangs',
            'create_master_barangs',
            'edit_master_barangs',
            'delete_master_barangs',

            // Unit Barang
            'view_unit_barangs',
            'create_unit_barangs',
            'edit_unit_barangs',
            'nonaktifkan_unit_barangs',

            // Transaksi Barang (Masuk)
            'view_transaksi_barangs',
            'create_transaksi_barangs',
            'edit_transaksi_barangs',
            'approve_transaksi_barangs',

            // Transaksi Keluar
            'view_transaksi_keluars',
            'create_transaksi_keluars',
            'edit_transaksi_keluars',
            'approve_transaksi_keluars',

            // Barang Rusak
            'view_barang_rusaks',
            'create_barang_rusaks',

            // Mutasi Lokasi
            'view_mutasi_lokasis',
            'create_mutasi_lokasis',
            'edit_mutasi_lokasis',
            'delete_mutasi_lokasis',

            // Log Aktivitas
            'view_log_aktivitas',

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Laporan
            'generate_laporan',
            'export_data',

            // System
            'backup_database',
            'system_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Role: Admin (Full Access)
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Role: Kepala Sekolah (Supervisor)
        $kepalaSekolahRole = Role::firstOrCreate(['name' => 'Kepala Sekolah']);
        $kepalaSekolahRole->givePermissionTo([
            // View All
            'view_kategoris',
            'view_ruangs',
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
        $petugasRole = Role::firstOrCreate(['name' => 'Petugas Inventaris']);
        $petugasRole->givePermissionTo([
            // View
            'view_kategoris',
            'view_ruangs',
            'view_master_barangs',
            'view_unit_barangs',
            'view_transaksi_barangs',
            'view_transaksi_keluars',
            'view_barang_rusaks',
            'view_mutasi_lokasis',
            'view_log_aktivitas',

            // Create/Edit Master Data
            'create_kategoris',
            'edit_kategoris',
            'delete_kategoris',
            'create_ruangs',
            'edit_ruangs',
            'delete_ruangs',
            'create_master_barangs',
            'edit_master_barangs',
            'create_unit_barangs',
            'edit_unit_barangs',

            // Transaksi
            'create_transaksi_barangs',
            'edit_transaksi_barangs',
            'create_transaksi_keluars',
            'edit_transaksi_keluars',

            // Mutasi Lokasi
            'create_mutasi_lokasis',
            'edit_mutasi_lokasis',
            'delete_mutasi_lokasis',

            // Barang Rusak
            'create_barang_rusaks',
        ]);
    }
}
