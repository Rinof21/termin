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
        Schema::create('termins', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor'); // 1, 2, 3, dst.
            $table->string('nama'); // misal: "Termin 1", "Termin 4"
            $table->string('tipe_semester', 20)->default('Ganjil'); // 'Ganjil' atau 'Genap'
            $table->string('tahun_ajaran', 30)->default('2026/2027');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai'); // rentang 6 minggu
            $table->integer('jumlah_minggu')->default(6);
            $table->boolean('is_aktif')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Hubungkan modul ke termin_id
        Schema::table('moduls', function (Blueprint $table) {
            $table->foreignId('termin_id')->nullable()->after('termin')->constrained('termins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduls', function (Blueprint $table) {
            $table->dropForeign(['termin_id']);
            $table->dropColumn('termin_id');
        });

        Schema::dropIfExists('termins');
    }
};

