<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('moduls', function (Blueprint $table) {
            $table->string('tipe_semester', 20)->default('Ganjil')->after('semester');
        });

        // Set existing moduls with semester containing Genap to 'Genap', else 'Ganjil'
        DB::table('moduls')->where('semester', 'like', '%Genap%')->update(['tipe_semester' => 'Genap']);
        DB::table('moduls')->where('semester', 'like', '%Ganjil%')->update(['tipe_semester' => 'Ganjil']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduls', function (Blueprint $table) {
            $table->dropColumn('tipe_semester');
        });
    }
};

