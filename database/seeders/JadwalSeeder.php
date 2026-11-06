<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Modul;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    /**
     * Seed data contoh/dummy Jadwal Perkuliahan S1 Kedokteran.
     * PERINGATAN: Seeder ini HANYA untuk lingkungan Development / Testing (Local),
     * TIDAK BOLEH dijalankan di Production.
     */
    public function run(): void
    {
        // Pengaman ekstra: jangan jalankan di server production
        if (app()->environment('production')) {
            $this->command?->warn('JadwalSeeder dilewati karena lingkungan aplikasi adalah PRODUCTION.');
            return;
        }

        $dosenModels = Dosen::all()->keyBy('kode');
        $modulBiomedis = Modul::where('kode', 'BM1')->first();
        $modulEtnomedik = Modul::where('kode', 'ETN')->first();
        $modulSarafJiwa = Modul::where('kode', 'SRF')->first();
        $modulFCP = Modul::where('kode', 'FCP')->first();
        $modulRespirasi = Modul::where('kode', 'RSP')->first();
        $modulKardio = Modul::where('kode', 'KRD')->first();
        $mataKuliahModels = MataKuliah::all()->keyBy('nama');

        if (!$modulBiomedis || !$modulRespirasi) {
            $this->command?->error('Modul belum tersedia. Jalankan ModulSeeder terlebih dahulu.');
            return;
        }

        // Bersihkan jadwal lama terlebih dahulu agar tidak menumpuk duplikat ratusan data
        Jadwal::query()->delete();

        // 21 Jadwal Realistis Terdistribusi Rapi 1 Minggu Penuh (Senin - Jumat, 2 - 6 November 2026)
        $jadwalData = [
            // SENIN, 2 NOVEMBER 2026 (6 sesi kuliah)
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['SHL']->id ?? null,
                'nama_kuliah' => 'Kuliah Pengantar Modul Etnomedik',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Kuliah Besar A',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '07:30',
                'jam_selesai' => '08:20',
                'dosen_pendamping' => 'RK',
                'catatan' => 'Silabus dan kontrak perkuliahan',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'nama_kuliah' => 'Praktikum Histologi-Patobiologi',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.1',
                'ruangan' => 'Lab Histologi Lantai 2',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Membawa jas lab dan atlas histologi',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['DN']->id ?? null,
                'nama_kuliah' => 'Praktikum Parasitologi',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.2',
                'ruangan' => 'Lab Parasitologi Lantai 3',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Identifikasi preparat basah protozoa usus',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'nama_kuliah' => 'Pleno 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Pleno',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Auditorium FK',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '10:10',
                'jam_selesai' => '11:50',
                'dosen_pendamping' => null,
                'catatan' => 'Klarifikasi capaian pemicu kasus 1',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'nama_kuliah' => 'Tanaman Obat di Kalimantan Barat',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.1',
                'ruangan' => 'Ruang Kuliah 201',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '13:00',
                'jam_selesai' => '14:40',
                'dosen_pendamping' => null,
                'catatan' => 'Etnofarmakologi tumbuhan lokal',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'nama_kuliah' => 'FORMATIF 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Ujian/Formatif',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'CBT Center Lantai 4',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '15:00',
                'jam_selesai' => '16:40',
                'dosen_pendamping' => null,
                'catatan' => 'Ujian daring 50 soal pilihan ganda',
            ],

            // SELASA, 3 NOVEMBER 2026 (5 sesi kuliah)
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'nama_kuliah' => 'TBL 1 (3 JP) Biomedis',
                'tipe_kegiatan' => 'Team-Based Learning (TBL)',
                'kelompok' => 'A.1 & A.2',
                'ruangan' => 'Ruang TBL Utama',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '08:20',
                'jam_selesai' => '11:00',
                'dosen_pendamping' => 'MHY, ZLF, IF',
                'catatan' => 'Individual & Group Readiness Assurance Test',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'nama_kuliah' => 'Evidence Based Medicine',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.1',
                'ruangan' => 'Ruang Kuliah 202',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Critical appraisal jurnal ilmiah',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['SHL']->id ?? null,
                'nama_kuliah' => 'Sejarah Perkembangan & Pengobatan Tradisional',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.2',
                'ruangan' => 'Ruang Kuliah 203',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Evolusi pengobatan kuno hingga modern',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['EAP']->id ?? null,
                'nama_kuliah' => 'Peran Pengobatan Tradisional dalam Komunitas',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Auditorium FK',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '11:00',
                'jam_selesai' => '11:50',
                'dosen_pendamping' => null,
                'catatan' => 'Integrasi pengobatan komplementer dengan faskes primer',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'nama_kuliah' => 'KP. Wawancara Psikiatri',
                'tipe_kegiatan' => 'Keterampilan Klinis (FCP)',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Konseling & CSL Lantai 3',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '13:00',
                'jam_selesai' => '15:00',
                'dosen_pendamping' => 'QRY',
                'catatan' => 'Simulasi anamnesis psikiatri dengan pasien standar',
            ],

            // RABU, 4 NOVEMBER 2026 (4 sesi kuliah)
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'nama_kuliah' => 'Praktikum Patologi Anatomi',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.1',
                'ruangan' => 'Lab Patologi Lantai 2',
                'tanggal' => '2026-11-04',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:10',
                'dosen_pendamping' => null,
                'catatan' => 'Pengamatan mikroskopis jaringan inflamasi',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'nama_kuliah' => 'Praktikum Fisiologi Manusia',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.2',
                'ruangan' => 'Lab Fisiologi Lantai 3',
                'tanggal' => '2026-11-04',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:10',
                'dosen_pendamping' => null,
                'catatan' => 'Uji refleks dan fungsi neuromuskular',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'nama_kuliah' => 'Kuliah Neuroanatomi & Sistem Saraf',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Kuliah 201',
                'tanggal' => '2026-11-04',
                'jam_mulai' => '09:10',
                'jam_selesai' => '11:00',
                'dosen_pendamping' => 'QRY',
                'catatan' => 'Morfologi dan traktus sensorik motorik serebrum',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'nama_kuliah' => 'Diskusi Kelompok 1 (DK1) Etnomedik',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'kelompok' => 'A.1 & A.2',
                'ruangan' => 'Ruang Diskusi Tutorial 1-4',
                'tanggal' => '2026-11-04',
                'jam_mulai' => '13:00',
                'jam_selesai' => '14:40',
                'dosen_pendamping' => 'SHL',
                'catatan' => 'Analisis kasus empiris pengobatan tanaman lokal',
            ],

            // KAMIS, 5 NOVEMBER 2026 (3 sesi kuliah)
            [
                'modul_id' => $modulFCP->id,
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'nama_kuliah' => 'Keterampilan Pemeriksaan Fisik Dasar',
                'tipe_kegiatan' => 'Keterampilan Klinis (FCP)',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang CSL Skills Lab',
                'tanggal' => '2026-11-05',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => 'WIK, TRI',
                'catatan' => 'Inspeksi, palpasi, perkusi, dan auskultasi',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['YAN']->id ?? null,
                'nama_kuliah' => 'Farmakognosi Tumbuhan Obat Lokal',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.1',
                'ruangan' => 'Ruang Kuliah 202',
                'tanggal' => '2026-11-05',
                'jam_mulai' => '10:10',
                'jam_selesai' => '11:50',
                'dosen_pendamping' => null,
                'catatan' => 'Isolasi senyawa bioaktif dan standarisasi ekstrak',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id ?? null,
                'nama_kuliah' => 'Diskusi Kasus Gangguan Ansietas & Depresi',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Kuliah 201',
                'tanggal' => '2026-11-05',
                'jam_mulai' => '13:00',
                'jam_selesai' => '14:40',
                'dosen_pendamping' => null,
                'catatan' => 'Kriteria diagnostik DSM-5 dan terapi farmakologis',
            ],

            // JUMAT, 6 NOVEMBER 2026 (2 sesi kuliah)
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['IF']->id ?? null,
                'nama_kuliah' => 'Review & Evaluasi Mingguan Biomedis 1',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Auditorium FK',
                'tanggal' => '2026-11-06',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:10',
                'dosen_pendamping' => 'ERY',
                'catatan' => 'Tanya jawab materi histologi dan patobiologi minggu 1',
            ],
            [
                'modul_id' => $modulFCP->id,
                'dosen_id' => $dosenModels['ADR']->id ?? null,
                'nama_kuliah' => 'Tutorial Pleno Kasus Klinis FCP',
                'tipe_kegiatan' => 'Pleno',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Kuliah Besar A',
                'tanggal' => '2026-11-06',
                'jam_mulai' => '09:10',
                'jam_selesai' => '11:00',
                'dosen_pendamping' => 'WIK',
                'catatan' => 'Presentasi perwakilan kelompok mahasiswa',
            ],

            // CONTOH SEMESTER GENAP (Termin 4: 02 Nov 2026 s/d 13 Des 2026)
            [
                'modul_id' => $modulRespirasi->id,
                'dosen_id' => $dosenModels['RK']->id ?? null,
                'nama_kuliah' => 'Fisiologi Sistem Pernapasan & Spirometri',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Ruang Kuliah 101',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Kuliah pengantar blok respirasi genap',
            ],
            [
                'modul_id' => $modulKardio->id,
                'dosen_id' => $dosenModels['ERY']->id ?? null,
                'nama_kuliah' => 'Praktikum Elektrokardiografi (EKG)',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.1',
                'ruangan' => 'Lab Fisiologi Lt. 3',
                'tanggal' => '2026-11-03',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Pemasangan elektroda dan interpretasi gelombang EKG',
            ],
        ];

        foreach ($jadwalData as $jadwal) {
            if (isset($mataKuliahModels[$jadwal['nama_kuliah']])) {
                $jadwal['mata_kuliah_id'] = $mataKuliahModels[$jadwal['nama_kuliah']]->id;
            }
            Jadwal::create($jadwal);
        }
    }
}

