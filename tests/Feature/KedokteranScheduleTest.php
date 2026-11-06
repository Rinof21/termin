<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Modul;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Termin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

class KedokteranScheduleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (User::where('email', 'admin@admin.com')->doesntExist() || Jadwal::count() == 0 || Semester::where('kode', '2026.2')->doesntExist()) {
            $this->seed(RolePermissionSeeder::class);
        }
    }

    public function test_calendar_page_and_events_api(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Kalender view
        $response = $this->actingAs($admin)->get('/admin/jadwals');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Termin S1 Kedokteran');
        $response->assertSee('FullCalendar');

        // Events JSON endpoint
        $response = $this->actingAs($admin)->get('/admin/jadwals/events');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'start',
                'end',
                'backgroundColor',
                'extendedProps' => [
                    'nama_kuliah',
                    'modul_nama',
                    'dosen',
                    'ruangan',
                    'jam',
                ]
            ]
        ]);
    }

    public function test_dosen_crud(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Index
        $response = $this->actingAs($admin)->get('/admin/dosens');
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($admin)->post('/admin/dosens', [
            'kode' => 'KUS',
            'nama' => 'dr. Kusnadi, Sp.B',
            'nip' => '198901012015011001',
            'email' => 'kusnadi@fk.ac.id',
        ]);
        $response->assertRedirect('/admin/dosens');
        $this->assertDatabaseHas('dosens', ['kode' => 'KUS']);

        $dosen = Dosen::where('kode', 'KUS')->first();

        // Update
        $response = $this->actingAs($admin)->put("/admin/dosens/{$dosen->id}", [
            'kode' => 'KUS',
            'nama' => 'dr. Kusnadi, Sp.B, Subsp.Ped(K)',
        ]);
        $response->assertRedirect('/admin/dosens');
        $this->assertDatabaseHas('dosens', ['nama' => 'dr. Kusnadi, Sp.B, Subsp.Ped(K)']);

        // Destroy
        $response = $this->actingAs($admin)->delete("/admin/dosens/{$dosen->id}");
        $response->assertRedirect('/admin/dosens');
        $this->assertDatabaseMissing('dosens', ['id' => $dosen->id]);
    }

    public function test_modul_crud(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Index
        $response = $this->actingAs($admin)->get('/admin/moduls');
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($admin)->post('/admin/moduls', [
            'kode' => 'KARDIO',
            'nama' => 'Sistem Kardiovaskular',
            'tipe_semester' => 'Ganjil',
            'termin' => 'Termin 4',
            'semester' => 'Semester Ganjil 2026/2027',
            'warna' => '#ef4444',
        ]);
        $response->assertRedirect('/admin/moduls');
        $this->assertDatabaseHas('moduls', ['kode' => 'KARDIO', 'tipe_semester' => 'Ganjil']);

        $modul = Modul::where('kode', 'KARDIO')->first();

        // Destroy
        $response = $this->actingAs($admin)->delete("/admin/moduls/{$modul->id}");
        $response->assertRedirect('/admin/moduls');
        $this->assertDatabaseMissing('moduls', ['id' => $modul->id]);
    }

    public function test_jadwal_crud(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $modul = Modul::first();
        $dosen = Dosen::first();

        // Store
        $response = $this->actingAs($admin)->post('/admin/jadwals', [
            'modul_id' => $modul->id,
            'dosen_id' => $dosen->id,
            'nama_kuliah' => 'Kuliah Anatomi Jantung',
            'tipe_kegiatan' => 'Kuliah',
            'kelompok' => 'A.1',
            'ruangan' => 'R. Kuliah 101',
            'tanggal' => '2026-11-04',
            'jam_mulai' => '08:20',
            'jam_selesai' => '10:00',
        ]);
        $response->assertRedirect('/admin/jadwals');
        $this->assertDatabaseHas('jadwals', ['nama_kuliah' => 'Kuliah Anatomi Jantung']);
        $this->assertDatabaseHas('mata_kuliahs', [
            'modul_id' => $modul->id,
            'nama' => 'Kuliah Anatomi Jantung',
        ]);

        $jadwal = Jadwal::where('nama_kuliah', 'Kuliah Anatomi Jantung')->first();

        // Destroy
        $response = $this->actingAs($admin)->delete("/admin/jadwals/{$jadwal->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('jadwals', ['id' => $jadwal->id]);
    }

    public function test_jadwal_bulk_delete(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $jadwalIds = Jadwal::take(2)->pluck('id')->toArray();

        $response = $this->actingAs($admin)->post('/admin/jadwals/bulk-delete', [
            'ids' => $jadwalIds,
        ]);

        $response->assertRedirect();
        foreach ($jadwalIds as $id) {
            $this->assertDatabaseMissing('jadwals', ['id' => $id]);
        }
    }

    public function test_dosen_bulk_delete(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Buat 2 dosen tanpa jadwal aktif
        $d1 = Dosen::create(['kode' => 'TST1', 'nama' => 'Dosen Test 1']);
        $d2 = Dosen::create(['kode' => 'TST2', 'nama' => 'Dosen Test 2']);

        $response = $this->actingAs($admin)->post('/admin/dosens/bulk-delete', [
            'ids' => [$d1->id, $d2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('dosens', ['id' => $d1->id]);
        $this->assertDatabaseMissing('dosens', ['id' => $d2->id]);
    }

    public function test_semester_activation_and_filtering(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Bersihkan dulu jika ada record sisa
        Modul::whereIn('kode', ['MDL_GANJIL', 'MDL_GENAP'])->delete();

        // 1. Buat modul Ganjil dan Genap
        $modulGanjil = Modul::create([
            'kode' => 'MDL_GANJIL',
            'nama' => 'Modul Semester Ganjil',
            'tipe_semester' => 'Ganjil',
            'termin' => 'Termin 1',
            'semester' => 'Semester Ganjil 2026/2027',
            'warna' => '#4f46e5',
        ]);

        $modulGenap = Modul::create([
            'kode' => 'MDL_GENAP',
            'nama' => 'Modul Semester Genap',
            'tipe_semester' => 'Genap',
            'termin' => 'Termin 2',
            'semester' => 'Semester Genap 2026/2027',
            'warna' => '#10b981',
        ]);

        // 2. Buat jadwal untuk masing-masing modul
        $jadwalGanjil = Jadwal::create([
            'modul_id' => $modulGanjil->id,
            'nama_kuliah' => 'Kuliah Khusus Ganjil',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-09',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:10',
        ]);

        $jadwalGenap = Jadwal::create([
            'modul_id' => $modulGenap->id,
            'nama_kuliah' => 'Kuliah Khusus Genap',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-10',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:10',
        ]);

        try {
            // 3. Admin aktifkan semester Ganjil
            $response = $this->actingAs($admin)->post('/admin/settings/semester', [
                'semester' => 'Ganjil',
            ]);
            $response->assertRedirect();
            $this->assertEquals('Ganjil', \App\Models\Setting::getActiveSemester());

            // Di endpoint events FullCalendar, hanya jadwal semester ganjil yang muncul
            $responseEventsGanjil = $this->actingAs($admin)->getJson('/admin/jadwals/events');
            $responseEventsGanjil->assertStatus(200);
            $eventTitlesGanjil = collect($responseEventsGanjil->json())->pluck('title');
            $this->assertTrue($eventTitlesGanjil->contains('Kuliah Khusus Ganjil'));
            $this->assertFalse($eventTitlesGanjil->contains('Kuliah Khusus Genap'));

            // 4. Admin aktifkan semester Genap via kode tahun_semester 2026.2
            $response = $this->actingAs($admin)->post('/admin/settings/semester', [
                'tahun_semester' => '2026.2',
            ]);
            $response->assertRedirect();
            $this->assertEquals('Genap', \App\Models\Setting::getActiveSemester());
            $this->assertEquals('2026.2', \App\Models\Setting::getActiveTahunSemester());

            // Di endpoint events FullCalendar, sekarang hanya jadwal semester genap yang muncul
            $responseEventsGenap = $this->actingAs($admin)->getJson('/admin/jadwals/events');
            $responseEventsGenap->assertStatus(200);
            $eventTitlesGenap = collect($responseEventsGenap->json())->pluck('title');
            $this->assertFalse($eventTitlesGenap->contains('Kuliah Khusus Ganjil'));
            $this->assertTrue($eventTitlesGenap->contains('Kuliah Khusus Genap'));
        } finally {
            // Bersihkan data uji & kembalikan ke 2026.2 Genap
            $jadwalGanjil->delete();
            $jadwalGenap->delete();
            $modulGanjil->delete();
            $modulGenap->delete();
            \App\Models\Setting::setActiveTahunSemester('2026.2');
        }
    }

    public function test_mata_kuliah_crud(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $modul = Modul::first();
        $dosen = Dosen::first();

        // 1. Index
        $response = $this->actingAs($admin)->get("/admin/moduls/{$modul->id}/mata-kuliahs");
        $response->assertStatus(200);
        $response->assertSee($modul->nama);

        // 2. Create Page
        $response = $this->actingAs($admin)->get("/admin/moduls/{$modul->id}/mata-kuliahs/create");
        $response->assertStatus(200);

        // 3. Store
        $response = $this->actingAs($admin)->post("/admin/moduls/{$modul->id}/mata-kuliahs", [
            'kode' => 'MK-TEST-01',
            'nama' => 'Mata Kuliah Uji Coba',
            'tipe_kegiatan' => 'Praktikum',
            'dosen_id' => $dosen->id,
            'ruangan' => 'Lab Komputer',
            'beban_jam' => '2 JP',
            'deskripsi' => 'Deskripsi materi uji coba',
        ]);
        $response->assertRedirect("/admin/moduls/{$modul->id}/mata-kuliahs");
        $this->assertDatabaseHas('mata_kuliahs', [
            'modul_id' => $modul->id,
            'nama' => 'Mata Kuliah Uji Coba',
        ]);

        $mk = MataKuliah::where('nama', 'Mata Kuliah Uji Coba')->first();

        // 4. Update
        $response = $this->actingAs($admin)->put("/admin/moduls/{$modul->id}/mata-kuliahs/{$mk->id}", [
            'kode' => 'MK-TEST-01-REV',
            'nama' => 'Mata Kuliah Uji Coba Direvisi',
            'tipe_kegiatan' => 'Kuliah',
        ]);
        $response->assertRedirect("/admin/moduls/{$modul->id}/mata-kuliahs");
        $this->assertDatabaseHas('mata_kuliahs', ['nama' => 'Mata Kuliah Uji Coba Direvisi']);

        // 5. Destroy
        $response = $this->actingAs($admin)->delete("/admin/moduls/{$modul->id}/mata-kuliahs/{$mk->id}");
        $response->assertRedirect("/admin/moduls/{$modul->id}/mata-kuliahs");
        $this->assertDatabaseMissing('mata_kuliahs', ['id' => $mk->id]);
    }

    public function test_termin_crud_and_activation(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // 1. Index termin
        $response = $this->actingAs($admin)->get('/admin/termins');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Jadwal Termin Perkuliahan');
        $response->assertSee('Termin 4 Genap');

        // 2. Create page
        $response = $this->actingAs($admin)->get('/admin/termins/create');
        $response->assertStatus(200);
        $response->assertSee('Tambah Jadwal Termin Baru');

        // 3. Store new termin
        $response = $this->actingAs($admin)->post('/admin/termins', [
            'nomor' => 9,
            'nama' => 'Termin 9 Uji Coba',
            'tipe_semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2027-01-25',
            'jumlah_minggu' => 6,
            'is_aktif' => 0,
            'keterangan' => 'Termin remedial/khusus',
        ]);
        $response->assertRedirect('/admin/termins?tipe=Ganjil');
        $this->assertDatabaseHas('termins', ['nama' => 'Termin 9 Uji Coba']);

        $termin = Termin::where('nama', 'Termin 9 Uji Coba')->first();
        $this->assertEquals(6, $termin->jumlah_minggu);
        $this->assertCount(6, $termin->daftar_minggu);

        // 4. Activate termin
        $response = $this->actingAs($admin)->post("/admin/termins/{$termin->id}/activate");
        $response->assertStatus(302);
        $this->assertTrue($termin->fresh()->is_aktif);
        $this->assertEquals('Ganjil', Setting::getActiveSemester());

        // 5. Update termin
        $response = $this->actingAs($admin)->put("/admin/termins/{$termin->id}", [
            'nomor' => 9,
            'nama' => 'Termin 9 Uji Coba Direvisi',
            'tipe_semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2027-01-25',
            'jumlah_minggu' => 6,
        ]);
        $response->assertRedirect('/admin/termins?tipe=Ganjil');
        $this->assertDatabaseHas('termins', ['nama' => 'Termin 9 Uji Coba Direvisi']);

        // 6. Reactivate Termin 4 so system stays on standard active termin
        $termin4 = Termin::where('nomor', 4)->where('tipe_semester', 'Genap')->first();
        Termin::setActive($termin4);

        // 7. Destroy non-active termin
        $response = $this->actingAs($admin)->delete("/admin/termins/{$termin->id}");
        $response->assertRedirect('/admin/termins?tipe=Ganjil');
        $this->assertDatabaseMissing('termins', ['id' => $termin->id]);
    }

    public function test_semester_crud_and_activation(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Semester::where('kode', '2028.1')->delete();

        // 1. Index semester
        $response = $this->actingAs($admin)->get('/admin/semesters');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Tahun Semester Akademik');
        $response->assertSee('2026.2');

        // 2. Create page
        $response = $this->actingAs($admin)->get('/admin/semesters/create');
        $response->assertStatus(200);
        $response->assertSee('Tambah Tahun Semester Baru');

        // 3. Store new semester
        $response = $this->actingAs($admin)->post('/admin/semesters', [
            'kode' => '2028.1',
            'nama' => 'Semester Ganjil 2028/2029',
            'tipe' => 'Ganjil',
            'tahun_ajaran' => '2028/2029',
            'is_aktif' => 0,
        ]);
        $response->assertRedirect('/admin/semesters');
        $this->assertDatabaseHas('semesters', ['kode' => '2028.1']);

        $semester = Semester::where('kode', '2028.1')->first();

        // 4. Activate semester
        $response = $this->actingAs($admin)->post("/admin/semesters/{$semester->id}/activate");
        $response->assertStatus(302);
        $this->assertEquals('2028.1', Setting::getActiveTahunSemester());
        $this->assertEquals('Ganjil', Setting::getActiveSemester());

        // 5. Update semester
        $response = $this->actingAs($admin)->put("/admin/semesters/{$semester->id}", [
            'kode' => '2028.1',
            'nama' => 'Semester Ganjil 2028/2029 (Revisi)',
            'tipe' => 'Ganjil',
            'tahun_ajaran' => '2028/2029',
        ]);
        $response->assertRedirect('/admin/semesters');
        $this->assertDatabaseHas('semesters', ['nama' => 'Semester Ganjil 2028/2029 (Revisi)']);

        // 6. Reactivate 2026.2 or other semester
        $otherSem = Semester::where('kode', '2026.2')->first() ?: Semester::where('kode', '!=', '2028.1')->first();
        if ($otherSem) {
            Semester::setActive($otherSem);
        }

        // 7. Delete non-active semester
        $response = $this->actingAs($admin)->delete("/admin/semesters/{$semester->id}");
        $response->assertRedirect('/admin/semesters');
        $this->assertDatabaseMissing('semesters', ['id' => $semester->id]);
    }

    public function test_jadwal_dosen_conflict_validation(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $modul = Modul::first();
        
        $dosen1 = Dosen::firstOrCreate(['kode' => 'DS1'], ['nama' => 'dr. Dosen Satu, Sp.A']);
        $dosen2 = Dosen::firstOrCreate(['kode' => 'DS2'], ['nama' => 'dr. Dosen Dua, Sp.PD']);

        // 1. Buat jadwal pertama: 08:20 - 10:00 dengan Dosen 1 sebagai utama
        $response = $this->actingAs($admin)->post('/admin/jadwals', [
            'modul_id' => $modul->id,
            'dosen_id' => $dosen1->id,
            'nama_kuliah' => 'Kuliah Sesi Pagi 1',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-25',
            'jam_mulai' => '08:20',
            'jam_selesai' => '10:00',
        ]);
        $response->assertRedirect('/admin/jadwals');
        $this->assertDatabaseHas('jadwals', ['nama_kuliah' => 'Kuliah Sesi Pagi 1']);

        // 2. Coba buat jadwal kedua dengan waktu bertubrukan (09:10 - 11:00) dan dosen utama sama (Dosen 1)
        $responseConflict1 = $this->actingAs($admin)->post('/admin/jadwals', [
            'modul_id' => $modul->id,
            'dosen_id' => $dosen1->id,
            'nama_kuliah' => 'Kuliah Bentrok Utama',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-25',
            'jam_mulai' => '09:10',
            'jam_selesai' => '11:00',
        ]);
        $responseConflict1->assertSessionHas('error');
        $responseConflict1->assertSessionHasErrors(['jam_mulai']);
        $this->assertDatabaseMissing('jadwals', ['nama_kuliah' => 'Kuliah Bentrok Utama']);

        // 3. Coba buat jadwal dengan Dosen 1 sebagai dosen pendamping pada waktu bertubrukan (08:20 - 09:10)
        $responseConflict2 = $this->actingAs($admin)->post('/admin/jadwals', [
            'modul_id' => $modul->id,
            'dosen_id' => $dosen2->id,
            'dosen_pendamping' => 'DS1',
            'nama_kuliah' => 'Kuliah Bentrok Pendamping',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-25',
            'jam_mulai' => '08:20',
            'jam_selesai' => '09:10',
        ]);
        $responseConflict2->assertSessionHas('error');
        $this->assertDatabaseMissing('jadwals', ['nama_kuliah' => 'Kuliah Bentrok Pendamping']);

        // 4. Jadwal lanjutan tidak bertubrukan (10:10 - 11:00) untuk Dosen 1 harus berhasil
        $responseSuccess = $this->actingAs($admin)->post('/admin/jadwals', [
            'modul_id' => $modul->id,
            'dosen_id' => $dosen1->id,
            'nama_kuliah' => 'Kuliah Sesi Berikutnya Aman',
            'tipe_kegiatan' => 'Kuliah',
            'tanggal' => '2026-11-25',
            'jam_mulai' => '10:10',
            'jam_selesai' => '11:00',
        ]);
        $responseSuccess->assertRedirect('/admin/jadwals');
        $this->assertDatabaseHas('jadwals', ['nama_kuliah' => 'Kuliah Sesi Berikutnya Aman']);

        // Bersihkan data tes
        Jadwal::whereIn('nama_kuliah', ['Kuliah Sesi Pagi 1', 'Kuliah Sesi Berikutnya Aman'])->delete();
        $dosen1->delete();
        $dosen2->delete();
    }

    public function test_modul_and_mata_kuliah_duplicate(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        
        // 1. Buat modul sumber
        $modul = Modul::create([
            'kode' => 'ORGDUP',
            'nama' => 'Modul Asli Uji Duplikasi',
            'semester' => 'Semester Genap 2026/2027',
            'tipe_semester' => 'Genap',
            'warna' => '#10b981',
        ]);

        // Buat materi mata kuliah di bawah modul sumber
        $mk = MataKuliah::create([
            'modul_id' => $modul->id,
            'kode' => 'ORGDUP',
            'nama' => 'Mata Kuliah Uji Duplikat',
            'tipe_kegiatan' => 'Kuliah',
        ]);

        // 2. Duplikasi Modul
        $response = $this->actingAs($admin)->post("/admin/moduls/{$modul->id}/duplicate");
        $response->assertRedirect('/admin/moduls');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('moduls', [
            'nama' => 'Modul Asli Uji Duplikasi (Salinan)',
            'kode' => 'ORGDUP_COPY',
        ]);

        $duplicatedModul = Modul::where('kode', 'ORGDUP_COPY')->first();
        $this->assertNotNull($duplicatedModul);

        // Pastikan mata kuliah di dalamnya ikut terduplikasi
        $this->assertDatabaseHas('mata_kuliahs', [
            'modul_id' => $duplicatedModul->id,
            'nama' => 'Mata Kuliah Uji Duplikat',
        ]);

        // 3. Duplikasi Mata Kuliah mandiri
        $responseMk = $this->actingAs($admin)->post("/admin/moduls/{$modul->id}/mata-kuliahs/{$mk->id}/duplicate");
        $responseMk->assertRedirect("/admin/moduls/{$modul->id}/mata-kuliahs");
        $responseMk->assertSessionHas('success');

        $this->assertDatabaseHas('mata_kuliahs', [
            'modul_id' => $modul->id,
            'nama' => 'Mata Kuliah Uji Duplikat (Salinan)',
        ]);

        // Bersihkan data
        $duplicatedModul->mataKuliahs()->delete();
        $duplicatedModul->delete();
        $modul->mataKuliahs()->delete();
        $modul->delete();
    }
}

