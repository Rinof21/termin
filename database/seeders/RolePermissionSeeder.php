<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed Spatie Roles & Permissions (Otorisasi & Hak Akses Pengguna).
     */
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar Hak Akses (Permissions) Berbahasa Indonesia
        $permissions = [
            // Modul Pengguna
            'lihat-pengguna',
            'tambah-pengguna',
            'ubah-pengguna',
            'hapus-pengguna',

            // Modul Peran
            'lihat-peran',
            'tambah-peran',
            'ubah-peran',
            'hapus-peran',

            // Modul Izin Akses
            'lihat-izin',
            'tambah-izin',
            'ubah-izin',
            'hapus-izin',

            // Modul Kedokteran: Dosen
            'lihat-dosen',
            'tambah-dosen',
            'ubah-dosen',
            'hapus-dosen',

            // Modul Kedokteran: Modul
            'lihat-modul',
            'tambah-modul',
            'ubah-modul',
            'hapus-modul',

            // Modul Kedokteran: Jadwal Termin
            'lihat-jadwal',
            'tambah-jadwal',
            'ubah-jadwal',
            'hapus-jadwal',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat Peran (Roles)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $dosenRole = Role::firstOrCreate(['name' => 'Dosen / Pengajar', 'guard_name' => 'web']);
        $stafRole = Role::firstOrCreate(['name' => 'Staf Akademik', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'Mahasiswa', 'guard_name' => 'web']);

        // Berikan semua izin ke Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Administrator dapat mengelola semua data akademik & pengguna
        $adminRole->syncPermissions([
            'lihat-pengguna', 'tambah-pengguna', 'ubah-pengguna',
            'lihat-peran', 'lihat-izin',
            'lihat-dosen', 'tambah-dosen', 'ubah-dosen', 'hapus-dosen',
            'lihat-modul', 'tambah-modul', 'ubah-modul', 'hapus-modul',
            'lihat-jadwal', 'tambah-jadwal', 'ubah-jadwal', 'hapus-jadwal',
        ]);

        // Staf Akademik & Dosen
        $dosenRole->syncPermissions(['lihat-dosen', 'lihat-modul', 'lihat-jadwal']);
        $stafRole->syncPermissions([
            'lihat-dosen', 'tambah-dosen', 'ubah-dosen',
            'lihat-modul', 'tambah-modul', 'ubah-modul',
            'lihat-jadwal', 'tambah-jadwal', 'ubah-jadwal',
        ]);
        $userRole->syncPermissions(['lihat-jadwal', 'lihat-dosen', 'lihat-modul']);

        // 3. Panggil UserSeeder untuk akun awal
        $this->call(UserSeeder::class);
    }
}
