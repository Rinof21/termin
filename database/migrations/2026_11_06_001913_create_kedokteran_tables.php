<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel Data Dosen
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique(); // Inisial/Kode dosen: YAN, SHL, IF, dll.
            $table->string('nama'); // Nama lengkap beserta gelar
            $table->string('nip', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->timestamps();
        });

        // Tabel Data Modul Kedokteran
        Schema::create('moduls', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique(); // misal: BM1, ETN, SRF, FCP
            $table->string('nama'); // e.g. Ilmu Biomedis, Etnomedik, Saraf Jiwa, FCP
            $table->string('semester')->default('Semester Ganjil 2026/2027');
            $table->string('termin')->default('Termin 4');
            $table->string('warna', 20)->default('#0284c7'); // Kode warna hex untuk visualisasi kalender
            $table->foreignId('koordinator_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Tabel Jadwal Kuliah & Kegiatan Termin
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('moduls')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->string('nama_kuliah'); // e.g. Praktikum Histologi, Tanaman Obat Kalbar, Pleno 1, DK1P3
            $table->string('tipe_kegiatan')->default('Kuliah'); // Kuliah, Praktikum, Diskusi Kelompok (DK), TBL, Pleno, Formatif, dll.
            $table->string('kelompok')->nullable(); // A.1, A.2, Semua, dll.
            $table->string('ruangan')->nullable(); // Ruang Kuliah 1, Lab Histologi, Ruang Diskusi
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('dosen_pendamping')->nullable(); // Jika ada dosen kedua/ketiga (inisial/nama)
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
        Schema::dropIfExists('moduls');
        Schema::dropIfExists('dosens');
    }
};
