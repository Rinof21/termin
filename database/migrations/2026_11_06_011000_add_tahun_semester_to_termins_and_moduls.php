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
        Schema::table('termins', function (Blueprint $table) {
            $table->string('tahun_semester', 20)->nullable()->after('tipe_semester')->index();
        });

        Schema::table('moduls', function (Blueprint $table) {
            $table->string('tahun_semester', 20)->nullable()->after('tipe_semester')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduls', function (Blueprint $table) {
            $table->dropColumn('tahun_semester');
        });

        Schema::table('termins', function (Blueprint $table) {
            $table->dropColumn('tahun_semester');
        });
    }
};

