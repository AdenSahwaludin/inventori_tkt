<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin');

        // 2. Kepala Sekolah
        $kepalaSekolah = User::firstOrCreate(
            ['email' => 'kepala@gmail.com'],
            [
                'name' => 'Kepala Sekolah TKT',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $kepalaSekolah->assignRole('Kepala Sekolah');

        // 3. Petugas Inventaris
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@gmail.com'],
            [
                'name' => 'Petugas Inventaris',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $petugas->assignRole('Petugas Inventaris');
    }
}
