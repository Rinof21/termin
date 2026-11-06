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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->comment('Contoh: 2026.1, 2026.2, 2027.1');
            $table->string('nama', 100)->comment('Contoh: Semester Ganjil 2026/2027');
            $table->string('tipe', 20)->default('Ganjil')->comment('Ganjil atau Genap');
            $table->string('tahun_ajaran', 20)->default('2026/2027');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

