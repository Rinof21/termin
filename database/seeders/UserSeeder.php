<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed akun pengguna awal (Super Admin, Administrator, Mahasiswa).
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'Mahasiswa', 'guard_name' => 'web']);

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin Kedokteran',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        $adminUser = User::firstOrCreate(
            ['email' => 'staff@admin.com'],
            [
                'name' => 'Admin Akademik S1 Kedokteran',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$adminRole]);

        $regularUser = User::firstOrCreate(
            ['email' => 'user@admin.com'],
            [
                'name' => 'Mahasiswa S1 Kedokteran',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $regularUser->syncRoles([$userRole]);
    }
}

