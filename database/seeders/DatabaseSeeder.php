<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Master & Authorization Seeders (Dijalankan di Production & Development)
        $this->call([
            RolePermissionSeeder::class, // Hak Akses, Peran, & Akun Dasar
            SemesterSeeder::class,       // Master Tahun Semester
            TerminSeeder::class,         // Master Jadwal Termin
            DosenSeeder::class,          // Master Data Dosen Prodi Kedokteran
            ModulSeeder::class,          // Master Modul Pembelajaran & Mata Kuliah
        ]);

        // 2. Dummy / Sample Data Seeders (HANYA dijalankan di local / testing, TIDAK di production)
        if (!app()->environment('production')) {
            $this->call([
                JadwalSeeder::class,     // Data contoh jadwal kuliah untuk testing & development
            ]);
        }
    }
}
