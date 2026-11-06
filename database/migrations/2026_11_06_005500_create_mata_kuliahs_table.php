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
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('moduls')->cascadeOnDelete();
            $table->string('kode', 30)->nullable();
            $table->string('nama');
            $table->string('tipe_kegiatan')->default('Kuliah');
            $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->string('dosen_pendamping')->nullable();
            $table->string('beban_jam', 50)->nullable(); // e.g. "2 JP (100 Menit)"
            $table->string('ruangan', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Hubungkan jadwal ke mata_kuliah_id jika ada
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('mata_kuliah_id')->nullable()->after('dosen_id')->constrained('mata_kuliahs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['mata_kuliah_id']);
            $table->dropColumn('mata_kuliah_id');
        });

        Schema::dropIfExists('mata_kuliahs');
    }
};

