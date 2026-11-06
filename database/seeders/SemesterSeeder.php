<?php

namespace Database\Seeders;

use App\Models\Semester;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Seed master data Tahun Semester.
     */
    public function run(): void
    {
        $defaultSemesters = [
            [
                'kode' => '2026.1',
                'nama' => 'Semester Ganjil 2026/2027',
                'tipe' => 'Ganjil',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-01-05',
                'tanggal_selesai' => '2026-06-21',
                'is_aktif' => false,
                'keterangan' => 'Tahun Akademik 2026/2027 Semester Ganjil',
            ],
            [
                'kode' => '2026.2',
                'nama' => 'Semester Genap 2026/2027',
                'tipe' => 'Genap',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-06-29',
                'tanggal_selesai' => '2026-12-13',
                'is_aktif' => true,
                'keterangan' => 'Tahun Akademik 2026/2027 Semester Genap (Termin 4 Aktif)',
            ],
        ];

        foreach ($defaultSemesters as $ds) {
            Semester::updateOrCreate(['kode' => $ds['kode']], $ds);
        }

        Setting::setActiveTahunSemester('2026.2');
    }
}

