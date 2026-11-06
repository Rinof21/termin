<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Modul;
use App\Models\Termin;
use Illuminate\Database\Seeder;

class ModulSeeder extends Seeder
{
    /**
     * Seed master data Modul & Mata Kuliah Prodi Kedokteran.
     */
    public function run(): void
    {
        $dosenModels = Dosen::all()->keyBy('kode');
        $terminGanjil4 = Termin::where('tipe_semester', 'Ganjil')->where('nomor', 4)->first();
        $terminGenap4 = Termin::where('tipe_semester', 'Genap')->where('nomor', 4)->first();

        // 1. Modul Semester Ganjil (Tahun Semester 2026.1)
        $modulBiomedis = Modul::updateOrCreate(
            ['kode' => 'BM1'],
            [
                'nama' => 'Ilmu Biomedis',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'semester' => 'Semester Ganjil 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGanjil4?->id,
                'warna' => '#06b6d4', // Cyan / Turquoise
                'koordinator_id' => $dosenModels['IF']->id ?? null,
                'keterangan' => 'Mencakup Histologi, Patobiologi, dan Parasitologi',
            ]
        );

        $modulEtnomedik = Modul::updateOrCreate(
            ['kode' => 'ETN'],
            [
                'nama' => 'Etnomedik',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'semester' => 'Semester Ganjil 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGanjil4?->id,
                'warna' => '#ec4899', // Pink
                'koordinator_id' => $dosenModels['YAN']->id ?? null,
                'keterangan' => 'Pengobatan tradisional, tanaman obat kalbar, dan komunitas',
            ]
        );

        $modulSarafJiwa = Modul::updateOrCreate(
            ['kode' => 'SRF'],
            [
                'nama' => 'Saraf Jiwa',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'semester' => 'Semester Ganjil 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGanjil4?->id,
                'warna' => '#f97316', // Orange
                'koordinator_id' => $dosenModels['AN']->id ?? null,
                'keterangan' => 'Sistem saraf, psikiatri, dan kesehatan mental',
            ]
        );

        $modulFCP = Modul::updateOrCreate(
            ['kode' => 'FCP'],
            [
                'nama' => 'Fundamental Clinical Practice (FCP)',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'semester' => 'Semester Ganjil 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGanjil4?->id,
                'warna' => '#84cc16', // Lime Green
                'koordinator_id' => $dosenModels['ADR']->id ?? null,
                'keterangan' => 'Keterampilan klinis dasar, rekam medis, dan tutorial kelompok',
            ]
        );

        // 2. Modul Semester Genap (Tahun Semester 2026.2)
        $modulRespirasi = Modul::updateOrCreate(
            ['kode' => 'RSP'],
            [
                'nama' => 'Sistem Respirasi',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'semester' => 'Semester Genap 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGenap4?->id,
                'warna' => '#3b82f6', // Blue
                'koordinator_id' => $dosenModels['RK']->id ?? null,
                'keterangan' => 'Sistem pernapasan, fisiologi paru, dan penyakit paru obstruktif',
            ]
        );

        $modulKardio = Modul::updateOrCreate(
            ['kode' => 'KRD'],
            [
                'nama' => 'Sistem Kardiovaskular',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'semester' => 'Semester Genap 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminGenap4?->id,
                'warna' => '#ef4444', // Red
                'koordinator_id' => $dosenModels['ERY']->id ?? null,
                'keterangan' => 'Anatomi jantung, elektrokardiografi, dan sirkulasi darah',
            ]
        );

        // 3. Master Data Mata Kuliah untuk Setiap Modul
        $daftarMataKuliah = [
            // Modul Biomedis (BM1)
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-01',
                'nama' => 'Praktikum Histologi-Patobiologi',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'ruangan' => 'Lab Histologi Lantai 2',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pengamatan mikroskopis struktur sel, jaringan dasar, dan perubahan patologis seluler.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-02',
                'nama' => 'Praktikum Parasitologi',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['DN']->id ?? null,
                'ruangan' => 'Lab Parasitologi Lantai 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Morfologi parasit protozoa dan cacing helmintes penyebab infeksi endemik.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-03',
                'nama' => 'TBL 1 (3 JP) Biomedis',
                'tipe_kegiatan' => 'Team-Based Learning (TBL)',
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'dosen_pendamping' => 'MHY, ZLF, IF',
                'ruangan' => 'Ruang TBL Utama',
                'beban_jam' => '3 JP (150 Menit)',
                'deskripsi' => 'Pembelajaran berbasis tim membahas integrasi fungsi organ dan biokimia klinis.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-04',
                'nama' => 'Praktikum Patologi Anatomi',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'ruangan' => 'Lab Patologi Lantai 2',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pengamatan preparat histopatologis inflamasi akut, kronis, dan neoplasma.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-05',
                'nama' => 'Praktikum Fisiologi Manusia',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'ruangan' => 'Lab Fisiologi Lantai 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pemeriksaan fungsi refleks, konduksi saraf motorik, dan elektromiografi sederhana.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-06',
                'nama' => 'Review & Evaluasi Mingguan Biomedis 1',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'dosen_pendamping' => 'ERY',
                'ruangan' => 'Auditorium FK',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Kuliah sintesis dan klarifikasi konsep biomedis minggu pertama.',
            ],

            // Modul Etnomedik (ETN)
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-01',
                'nama' => 'Kuliah Pengantar Modul Etnomedik',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['SHL']->id ?? null,
                'dosen_pendamping' => 'RK',
                'ruangan' => 'Ruang Kuliah Besar A',
                'beban_jam' => '1 JP (50 Menit)',
                'deskripsi' => 'Silabus, capaian pembelajaran, dan kontrak perkuliahan etnomedik.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-02',
                'nama' => 'Tanaman Obat di Kalimantan Barat',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'ruangan' => 'Ruang Kuliah 201',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Kearifan lokal pengobatan herbal Dayak & Melayu Kalbar serta potensi farmakologisnya.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-03',
                'nama' => 'Evidence Based Medicine',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'ruangan' => 'Ruang Kuliah 202',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Metodologi telaah kritis jurnal ilmiah dan uji klinis sediaan bahan alam.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-04',
                'nama' => 'Sejarah Perkembangan & Pengobatan Tradisional',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['SHL']->id ?? null,
                'ruangan' => 'Ruang Kuliah 203',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Historis sistem pengobatan dunia timur dan barat menuju kedokteran modern.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-05',
                'nama' => 'Peran Pengobatan Tradisional dalam Komunitas',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['EAP']->id ?? null,
                'ruangan' => 'Auditorium FK',
                'beban_jam' => '1 JP (50 Menit)',
                'deskripsi' => 'Pola pemanfaatan TOGA dan integrasi komplementer di Puskesmas.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-06',
                'nama' => 'Diskusi Kelompok 1 (DK1) Etnomedik',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'dosen_pendamping' => 'SHL',
                'ruangan' => 'Ruang Diskusi Tutorial 1-4',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Problem-Based Learning kasus keracunan jamu tradisional versus fitofarmaka terstandar.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-07',
                'nama' => 'Farmakognosi Tumbuhan Obat Lokal',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'ruangan' => 'Ruang Kuliah 202',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Identifikasi simplisia, senyawa metabolit sekunder flavonoid, terpenoid, dan alkaloid.',
            ],

            // Modul Saraf Jiwa (SRF)
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-01',
                'nama' => 'Pleno 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Pleno',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'ruangan' => 'Auditorium FK',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pleno klarifikasi kasus pemicu 1 gangguan sistem saraf pusat.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-02',
                'nama' => 'FORMATIF 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Ujian/Formatif',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'ruangan' => 'CBT Center Lantai 4',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Ujian formatif evaluasi belajar mandiri mahasiswa minggu 1.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-03',
                'nama' => 'TBL 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Team-Based Learning (TBL)',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'dosen_pendamping' => 'ARD, QRY, IF',
                'ruangan' => 'Ruang TBL 2',
                'beban_jam' => '3 JP (150 Menit)',
                'deskripsi' => 'Diskusi tim kasus klinis stroke iskemik versus hemoragik.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-04',
                'nama' => 'KP. Wawancara Psikiatri',
                'tipe_kegiatan' => 'Keterampilan Klinis (FCP)',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'dosen_pendamping' => 'QRY',
                'ruangan' => 'Ruang Konseling & CSL Lantai 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Latihan teknik anamnesis psikiatri, alloanamnesis, dan evaluasi status mental.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-05',
                'nama' => 'Kuliah Neuroanatomi & Sistem Saraf',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'dosen_pendamping' => 'QRY',
                'ruangan' => 'Ruang Kuliah 201',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Struktur korteks serebri, ganglia basalis, medula spinalis, dan vaskularisasi SSP.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-06',
                'nama' => 'Diskusi Kasus Gangguan Ansietas & Depresi',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'ruangan' => 'Ruang Kuliah 201',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Penegakan diagnosis gangguan panik, GAD, depresi unipolar berdasarkan PPDGJ/DSM-5.',
            ],

            // Modul FCP
            [
                'modul_id' => $modulFCP->id,
                'kode' => 'FCP-01',
                'nama' => 'K. Penulisan Rekam Medis',
                'tipe_kegiatan' => 'Keterampilan Klinis (FCP)',
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'ruangan' => 'Ruang Skills Lab 1',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Standar penulisan catatan medis SOAP dan aspek medikolegal resume medis.',
            ],
            [
                'modul_id' => $modulFCP->id,
                'kode' => 'FCP-02',
                'nama' => 'DK1P3 (8) Diskusi Kelompok',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'dosen_pendamping' => 'WIK, MIS, TRI, ZLF, AMR, HAN, EFI',
                'ruangan' => 'Ruang Tutorial 1-8',
                'beban_jam' => '3 JP (150 Menit)',
                'deskripsi' => 'Tutorial kelompok kecil skenario klinis batuk kronis dan demam berkepanjangan.',
            ],
            [
                'modul_id' => $modulFCP->id,
                'kode' => 'FCP-03',
                'nama' => 'Keterampilan Pemeriksaan Fisik Dasar',
                'tipe_kegiatan' => 'Keterampilan Klinis (FCP)',
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'dosen_pendamping' => 'WIK, TRI',
                'ruangan' => 'Ruang CSL Skills Lab',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Latihan pemeriksaan tanda vital, pemeriksaan kepala-leher, dan thoraks.',
            ],
            [
                'modul_id' => $modulFCP->id,
                'kode' => 'FCP-04',
                'nama' => 'Tutorial Pleno Kasus Klinis FCP',
                'tipe_kegiatan' => 'Pleno',
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'dosen_pendamping' => 'WIK',
                'ruangan' => 'Ruang Kuliah Besar A',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pleno penutup diskusi kasus klinis modul fundamental.',
            ],

            // Modul Semester Genap
            [
                'modul_id' => $modulRespirasi->id,
                'kode' => 'RSP-01',
                'nama' => 'Fisiologi Sistem Pernapasan & Spirometri',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['RK']->id ?? null,
                'ruangan' => 'Ruang Kuliah 101',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Mekanika respirasi, kurva disosiasi oksihemoglobin, dan tes spirometri.',
            ],
            [
                'modul_id' => $modulKardio->id,
                'kode' => 'KRD-01',
                'nama' => 'Praktikum Elektrokardiografi (EKG)',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'ruangan' => 'Lab Fisiologi Lt. 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pemasangan elektroda 12 sadapan dan identifikasi ritme sinus normal & aritmia.',
            ],
        ];

        foreach ($daftarMataKuliah as $mkData) {
            MataKuliah::updateOrCreate(
                ['modul_id' => $mkData['modul_id'], 'nama' => $mkData['nama']],
                $mkData
            );
        }
    }
}

