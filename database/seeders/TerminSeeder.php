<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Termin;
use Illuminate\Database\Seeder;

class TerminSeeder extends Seeder
{
    /**
     * Seed master data Jadwal Termin Perkuliahan (Siklus 4 Termin per Semester).
     */
    public function run(): void
    {
        $allTerminsData = [
            // Semester Genap 2026.2
            [
                'nomor' => 1,
                'nama' => 'Termin 1 Genap',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-06-29',
                'tanggal_selesai' => '2026-08-09',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 1 Genap (6 Minggu Berjalan: 29 Jun 2026 s/d 09 Ags 2026)',
            ],
            [
                'nomor' => 2,
                'nama' => 'Termin 2 Genap',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-08-10',
                'tanggal_selesai' => '2026-09-20',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 2 Genap (6 Minggu Berjalan: 10 Ags 2026 s/d 20 Sep 2026)',
            ],
            [
                'nomor' => 3,
                'nama' => 'Termin 3 Genap',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-09-21',
                'tanggal_selesai' => '2026-11-01',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 3 Genap (6 Minggu Berjalan: 21 Sep 2026 s/d 01 Nov 2026)',
            ],
            [
                'nomor' => 4,
                'nama' => 'Termin 4 Genap',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-11-02',
                'tanggal_selesai' => '2026-12-13',
                'jumlah_minggu' => 6,
                'is_aktif' => true,
                'keterangan' => 'Blok Pembelajaran Termin 4 Genap (6 Minggu Berjalan: 02 Nov 2026 s/d 13 Des 2026)',
            ],
            // Semester Ganjil 2026.1
            [
                'nomor' => 1,
                'nama' => 'Termin 1 Ganjil',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-01-05',
                'tanggal_selesai' => '2026-02-15',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 1 Ganjil (6 Minggu Berjalan: 05 Jan 2026 s/d 15 Feb 2026)',
            ],
            [
                'nomor' => 2,
                'nama' => 'Termin 2 Ganjil',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-02-16',
                'tanggal_selesai' => '2026-03-29',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 2 Ganjil (6 Minggu Berjalan: 16 Feb 2026 s/d 29 Mar 2026)',
            ],
            [
                'nomor' => 3,
                'nama' => 'Termin 3 Ganjil',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-03-30',
                'tanggal_selesai' => '2026-05-10',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 3 Ganjil (6 Minggu Berjalan: 30 Mar 2026 s/d 10 Mei 2026)',
            ],
            [
                'nomor' => 4,
                'nama' => 'Termin 4 Ganjil',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-05-11',
                'tanggal_selesai' => '2026-06-21',
                'jumlah_minggu' => 6,
                'is_aktif' => false,
                'keterangan' => 'Blok Pembelajaran Termin 4 Ganjil (6 Minggu Berjalan: 11 Mei 2026 s/d 21 Jun 2026)',
            ],
        ];

        foreach ($allTerminsData as $td) {
            $termin = Termin::updateOrCreate(
                [
                    'nomor' => $td['nomor'],
                    'tipe_semester' => $td['tipe_semester'],
                    'tahun_semester' => $td['tahun_semester'],
                ],
                $td
            );

            if ($td['is_aktif']) {
                Setting::set('active_termin_id', $termin->id);
            }
        }
    }
}

