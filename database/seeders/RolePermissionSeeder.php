<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Modul;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Termin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Inisialisasi Master Data Tahun Semester & Semester Aktif
        Semester::query()->delete();

        $defaultSemesters = [
            [
                'kode' => '2026.2',
                'nama' => 'Semester Genap 2026/2027',
                'tipe' => 'Genap',
                'tahun_ajaran' => '2026/2027',
                'tanggal_mulai' => '2026-11-02',
                'tanggal_selesai' => '2026-12-13',
                'is_aktif' => true,
                'keterangan' => 'Tahun Akademik 2026/2027 Semester Genap (Termin 4)',
            ],
        ];

        foreach ($defaultSemesters as $ds) {
            Semester::create($ds);
        }

        Setting::setActiveTahunSemester('2026.2');

        // 2. Daftar Hak Akses (Permissions) Berbahasa Indonesia
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

        // 3. Buat Peran (Roles)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $dosenRole = Role::firstOrCreate(['name' => 'Dosen / Pengajar', 'guard_name' => 'web']);
        $stafRole = Role::firstOrCreate(['name' => 'Staf Akademik', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'Mahasiswa', 'guard_name' => 'web']);

        // Berikan semua izin ke Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Administrator dapat mengelola semua data akademik
        $adminRole->syncPermissions([
            'lihat-pengguna', 'tambah-pengguna', 'ubah-pengguna',
            'lihat-peran', 'lihat-izin',
            'lihat-dosen', 'tambah-dosen', 'ubah-dosen', 'hapus-dosen',
            'lihat-modul', 'tambah-modul', 'ubah-modul', 'hapus-modul',
            'lihat-jadwal', 'tambah-jadwal', 'ubah-jadwal', 'hapus-jadwal',
        ]);

        // Staf & Dosen
        $dosenRole->syncPermissions(['lihat-dosen', 'lihat-modul', 'lihat-jadwal']);
        $stafRole->syncPermissions([
            'lihat-dosen', 'tambah-dosen', 'ubah-dosen',
            'lihat-modul', 'tambah-modul', 'ubah-modul',
            'lihat-jadwal', 'tambah-jadwal', 'ubah-jadwal',
        ]);
        $userRole->syncPermissions(['lihat-jadwal', 'lihat-dosen', 'lihat-modul']);

        // 4. Buat Akun Pengguna Awal
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

        // 5. Seed Data Dosen Prodi Kedokteran (47 Dosen Resmi Sesuai Gambar / Inisial)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Dosen::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $daftarDosen = [
            ['kode' => 'EMP', 'nama' => 'Emika Prastyan'],
            ['kode' => 'VN',  'nama' => 'Virhan N'],
            ['kode' => 'ANG', 'nama' => 'Agus F'],
            ['kode' => 'RPN', 'nama' => 'Rangga Putra Nugraha'],
            ['kode' => 'PUJ', 'nama' => 'Puji Astuti'],
            ['kode' => 'FJR', 'nama' => 'Fajar Sholehudin'],
            ['kode' => 'ERY', 'nama' => 'Ery Hermawati'],
            ['kode' => 'AN',  'nama' => 'An An'],
            ['kode' => 'IT',  'nama' => 'Ita Armyanti'],
            ['kode' => 'MDA', 'nama' => 'Mira Delima Asikin'],
            ['kode' => 'COS', 'nama' => 'Costan Tryono'],
            ['kode' => 'TRI', 'nama' => 'Tri Wahyudi'],
            ['kode' => 'RIN', 'nama' => 'Rini A'],
            ['kode' => 'RK',  'nama' => 'Asmaurika Pramuwidya'],
            ['kode' => 'INA', 'nama' => 'Agustina Arundina'],
            ['kode' => 'DN',  'nama' => 'Diana N'],
            ['kode' => 'MHY', 'nama' => 'Mahyarudin'],
            ['kode' => 'DHI', 'nama' => 'Mardhia'],
            ['kode' => 'AMB', 'nama' => 'Ambar R'],
            ['kode' => 'FLO', 'nama' => 'Florean Hartungi'],
            ['kode' => 'ROC', 'nama' => 'Robert Cristeven'],
            ['kode' => 'HAN', 'nama' => 'Mitra H'],
            ['kode' => 'SHL', 'nama' => 'Shella'],
            ['kode' => 'ADR', 'nama' => 'Andriani'],
            ['kode' => 'EAP', 'nama' => 'Eka AP'],
            ['kode' => 'IF',  'nama' => 'Iit Fitrianingrum'],
            ['kode' => 'DT',  'nama' => 'M. Ibnu K'],
            ['kode' => 'ARD', 'nama' => 'Ahmad Ramdhani'],
            ['kode' => 'PNR', 'nama' => 'Poppy Novitarini'],
            ['kode' => 'QRY', 'nama' => 'Qory Irsan'],
            ['kode' => 'YAN', 'nama' => 'Syf. N. Yanti RSA'],
            ['kode' => 'WH',  'nama' => 'Willy H'],
            ['kode' => 'WIK', 'nama' => 'Wiwik W'],
            ['kode' => 'DES', 'nama' => 'Desriani Lestari'],
            ['kode' => 'DR',  'nama' => 'Arif W'],
            ['kode' => 'EFI', 'nama' => 'Effiana'],
            ['kode' => 'MIS', 'nama' => 'Mistika Z'],
            ['kode' => 'ZLF', 'nama' => 'Zulfa Khairunnisa Ishan'],
            ['kode' => 'SAR', 'nama' => 'Sari Eka P'],
            ['kode' => 'DEL', 'nama' => 'Delima Fajar Liana'],
            ['kode' => 'SRY', 'nama' => 'Sari R'],
            ['kode' => 'ABR', 'nama' => 'Abror I'],
            ['kode' => 'ALX', 'nama' => 'Alex'],
            ['kode' => 'RDH', 'nama' => 'Ridha Ulfah'],
            ['kode' => 'ASR', 'nama' => 'M. Asroruddin'],
            ['kode' => 'HER', 'nama' => 'Heru Fajar Trianto'],
            ['kode' => 'INM', 'nama' => 'M. In\'am I'],
        ];

        $dosenModels = [];
        foreach ($daftarDosen as $item) {
            $dosenModels[$item['kode']] = Dosen::create($item);
        }

        // 6. Seed Data Jadwal Termin Akademik (Hanya Termin 4 Genap untuk 2026.2 aktif)
        Termin::query()->delete();

        $terminModels = [];
        $t4Genap = Termin::create([
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
        ]);
        $terminModels['Genap_4'] = $t4Genap;

        // Set active termin di setting (Termin 4 Genap untuk 2026.2)
        Setting::set('active_termin_id', $t4Genap->id);

        // 7. Seed Modul Semester Ganjil (Termin 4 Ganjil, Tahun Semester 2026.1)
        $modulBiomedis = Modul::updateOrCreate(
            ['kode' => 'BM1'],
            [
                'nama' => 'Ilmu Biomedis',
                'tipe_semester' => 'Ganjil',
                'tahun_semester' => '2026.1',
                'semester' => 'Semester Ganjil 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminModels['Ganjil_4']->id ?? null,
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
                'termin_id' => $terminModels['Ganjil_4']->id ?? null,
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
                'termin_id' => $terminModels['Ganjil_4']->id ?? null,
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
                'termin_id' => $terminModels['Ganjil_4']->id ?? null,
                'warna' => '#84cc16', // Lime Green
                'koordinator_id' => $dosenModels['ADR']->id ?? null,
                'keterangan' => 'Keterampilan klinis dasar, rekam medis, dan tutorial kelompok',
            ]
        );

        // Seed Modul Semester Genap (Sebagai contoh untuk filter semester genap 2026.2)
        $modulRespirasi = Modul::updateOrCreate(
            ['kode' => 'RSP'],
            [
                'nama' => 'Sistem Respirasi',
                'tipe_semester' => 'Genap',
                'tahun_semester' => '2026.2',
                'semester' => 'Semester Genap 2026/2027',
                'termin' => 'Termin 4',
                'termin_id' => $terminModels['Genap_4']->id ?? null,
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
                'termin_id' => $terminModels['Genap_4']->id ?? null,
                'warna' => '#ef4444', // Red
                'koordinator_id' => $dosenModels['ERY']->id ?? null,
                'keterangan' => 'Anatomi jantung, elektrokardiografi, dan sirkulasi darah',
            ]
        );

        // 8. Seed Master Data Mata Kuliah untuk Setiap Modul
        MataKuliah::query()->delete();

        $daftarMataKuliah = [
            // Modul Biomedis (BM1)
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-01',
                'nama' => 'Praktikum Histologi-Patobiologi',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['IF']->id,
                'ruangan' => 'Lab Histologi Lantai 2',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pengamatan mikroskopis struktur sel, jaringan dasar, dan perubahan patologis seluler.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-02',
                'nama' => 'Praktikum Parasitologi',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['DN']->id,
                'ruangan' => 'Lab Parasitologi Lantai 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Morfologi parasit protozoa dan cacing helmintes penyebab infeksi endemik.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-03',
                'nama' => 'TBL 1 (3 JP) Biomedis',
                'tipe_kegiatan' => 'Team-Based Learning (TBL)',
                'dosen_id' => $dosenModels['ERY']->id,
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
                'dosen_id' => $dosenModels['IF']->id,
                'ruangan' => 'Lab Patologi Lantai 2',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pengamatan preparat histopatologis inflamasi akut, kronis, dan neoplasma.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-05',
                'nama' => 'Praktikum Fisiologi Manusia',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['ERY']->id,
                'ruangan' => 'Lab Fisiologi Lantai 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pemeriksaan fungsi refleks, konduksi saraf motorik, dan elektromiografi sederhana.',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'kode' => 'BM1-06',
                'nama' => 'Review & Evaluasi Mingguan Biomedis 1',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['IF']->id,
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
                'dosen_id' => $dosenModels['SHL']->id,
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
                'dosen_id' => $dosenModels['YAN']->id,
                'ruangan' => 'Ruang Kuliah 201',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Kearifan lokal pengobatan herbal Dayak & Melayu Kalbar serta potensi farmakologisnya.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-03',
                'nama' => 'Evidence Based Medicine',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['YAN']->id,
                'ruangan' => 'Ruang Kuliah 202',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Metodologi telaah kritis jurnal ilmiah dan uji klinis sediaan bahan alam.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-04',
                'nama' => 'Sejarah Perkembangan & Pengobatan Tradisional',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['SHL']->id,
                'ruangan' => 'Ruang Kuliah 203',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Historis sistem pengobatan dunia timur dan barat menuju kedokteran modern.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-05',
                'nama' => 'Peran Pengobatan Tradisional dalam Komunitas',
                'tipe_kegiatan' => 'Kuliah',
                'dosen_id' => $dosenModels['EAP']->id,
                'ruangan' => 'Auditorium FK',
                'beban_jam' => '1 JP (50 Menit)',
                'deskripsi' => 'Pola pemanfaatan TOGA dan integrasi komplementer di Puskesmas.',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'kode' => 'ETN-06',
                'nama' => 'Diskusi Kelompok 1 (DK1) Etnomedik',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'dosen_id' => $dosenModels['YAN']->id,
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
                'dosen_id' => $dosenModels['YAN']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
                'ruangan' => 'Auditorium FK',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pleno klarifikasi kasus pemicu 1 gangguan sistem saraf pusat.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-02',
                'nama' => 'FORMATIF 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Ujian/Formatif',
                'dosen_id' => $dosenModels['AN']->id,
                'ruangan' => 'CBT Center Lantai 4',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Ujian formatif evaluasi belajar mandiri mahasiswa minggu 1.',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'kode' => 'SRF-03',
                'nama' => 'TBL 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Team-Based Learning (TBL)',
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['ADR']->id,
                'ruangan' => 'Ruang Skills Lab 1',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Standar penulisan catatan medis SOAP dan aspek medikolegal resume medis.',
            ],
            [
                'modul_id' => $modulFCP->id,
                'kode' => 'FCP-02',
                'nama' => 'DK1P3 (8) Diskusi Kelompok',
                'tipe_kegiatan' => 'Diskusi Kelompok (DK)',
                'dosen_id' => $dosenModels['ADR']->id,
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
                'dosen_id' => $dosenModels['ADR']->id,
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
                'dosen_id' => $dosenModels['ADR']->id,
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
                'dosen_id' => $dosenModels['RK']->id,
                'ruangan' => 'Ruang Kuliah 101',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Mekanika respirasi, kurva disosiasi oksihemoglobin, dan tes spirometri.',
            ],
            [
                'modul_id' => $modulKardio->id,
                'kode' => 'KRD-01',
                'nama' => 'Praktikum Elektrokardiografi (EKG)',
                'tipe_kegiatan' => 'Praktikum',
                'dosen_id' => $dosenModels['ERY']->id,
                'ruangan' => 'Lab Fisiologi Lt. 3',
                'beban_jam' => '2 JP (100 Menit)',
                'deskripsi' => 'Pemasangan elektroda 12 sadapan dan identifikasi ritme sinus normal & aritmia.',
            ],
        ];

        $mataKuliahModels = [];
        foreach ($daftarMataKuliah as $mkData) {
            $mataKuliahModels[$mkData['nama']] = MataKuliah::create($mkData);
        }

        // 8. Bersihkan jadwal lama terlebih dahulu agar tidak menumpuk duplikat ratusan data
        Jadwal::query()->delete();

        // 8. Seed 21 Jadwal Realistis Terdistribusi Rapi 1 Minggu Penuh (Senin - Jumat, 2 - 6 November 2026)
        $jadwalData = [
            // SENIN, 2 NOVEMBER 2026 (6 sesi kuliah)
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['SHL']->id,
                'nama_kuliah' => 'Kuliah Pengantar Modul Etnomedik',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.1 & A.2',
                'ruangan' => 'Ruang Kuliah Besar A',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '07:30',
                'jam_selesai' => '08:20',
                'dosen_pendamping' => 'RK',
                'catatan' => 'Kuliah pengantar pembuka termin 4',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['IF']->id,
                'nama_kuliah' => 'Praktikum Histologi-Patobiologi',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.1',
                'ruangan' => 'Lab Histologi Lantai 2',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Wajib jas lab dan preparat mikroskop',
            ],
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['DN']->id,
                'nama_kuliah' => 'Praktikum Parasitologi',
                'tipe_kegiatan' => 'Praktikum',
                'kelompok' => 'A.2',
                'ruangan' => 'Lab Parasitologi Lantai 3',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '08:20',
                'jam_selesai' => '10:00',
                'dosen_pendamping' => null,
                'catatan' => 'Identifikasi telur dan parasit helmintes',
            ],
            [
                'modul_id' => $modulEtnomedik->id,
                'dosen_id' => $dosenModels['YAN']->id,
                'nama_kuliah' => 'Tanaman Obat di Kalimantan Barat',
                'tipe_kegiatan' => 'Kuliah',
                'kelompok' => 'A.1',
                'ruangan' => 'Ruang Kuliah 201',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '10:10',
                'jam_selesai' => '11:50',
                'dosen_pendamping' => null,
                'catatan' => 'Studi fitofarmaka dan kearifan lokal kalbar',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id,
                'nama_kuliah' => 'Pleno 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Pleno',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'Auditorium FK',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '10:10',
                'jam_selesai' => '11:50',
                'dosen_pendamping' => null,
                'catatan' => 'Pembahasan kasus pemicu 1',
            ],
            [
                'modul_id' => $modulSarafJiwa->id,
                'dosen_id' => $dosenModels['AN']->id,
                'nama_kuliah' => 'FORMATIF 1 Saraf Jiwa',
                'tipe_kegiatan' => 'Ujian/Formatif',
                'kelompok' => 'Semua Kelompok',
                'ruangan' => 'CBT Center Lantai 4',
                'tanggal' => '2026-11-02',
                'jam_mulai' => '13:00',
                'jam_selesai' => '14:40',
                'dosen_pendamping' => null,
                'catatan' => 'Evaluasi formatif mingguan',
            ],

            // SELASA, 3 NOVEMBER 2026 (5 sesi kuliah)
            [
                'modul_id' => $modulBiomedis->id,
                'dosen_id' => $dosenModels['ERY']->id,
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
                'dosen_id' => $dosenModels['YAN']->id,
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
                'dosen_id' => $dosenModels['SHL']->id,
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
                'dosen_id' => $dosenModels['EAP']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['IF']->id,
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
                'dosen_id' => $dosenModels['ERY']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['YAN']->id,
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
                'dosen_id' => $dosenModels['ADR']->id,
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
                'dosen_id' => $dosenModels['YAN']->id,
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
                'dosen_id' => $dosenModels['AN']->id,
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
                'dosen_id' => $dosenModels['IF']->id,
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
                'dosen_id' => $dosenModels['ADR']->id,
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
                'dosen_id' => $dosenModels['RK']->id,
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
                'dosen_id' => $dosenModels['ERY']->id,
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
