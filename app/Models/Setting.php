<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     */
    public static function set(string $key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Ambil semester aktif saat ini ('Ganjil' atau 'Genap').
     */
    public static function getActiveSemester(): string
    {
        return static::get('active_semester', 'Ganjil') ?: 'Ganjil';
    }

    /**
     * Ambil kode tahun semester aktif saat ini (contoh: '2026.1' untuk Ganjil, '2026.2' untuk Genap).
     */
    public static function getActiveTahunSemester(): string
    {
        $code = static::get('active_tahun_semester');
        if (!$code) {
            $semester = static::getActiveSemester();
            $code = $semester === 'Genap' ? '2026.2' : '2026.1';
            static::set('active_tahun_semester', $code);
        }
        return $code;
    }

    /**
     * Setel kode tahun semester aktif dan sinkronkan semester ('Ganjil' / 'Genap').
     */
    public static function setActiveTahunSemester(string $code): void
    {
        $semester = static::getSemesterFromCode($code);
        static::set('active_tahun_semester', $code);
        static::set('active_semester', $semester);
    }

    /**
     * Deteksi tipe semester dari kode (misal '2026.1' -> 'Ganjil', '2026.2' -> 'Genap').
     */
    public static function getSemesterFromCode(string $code): string
    {
        return str_ends_with(trim($code), '.2') ? 'Genap' : 'Ganjil';
    }

    /**
     * Format label lengkap tahun semester (misal '2026.1' -> '2026.1 - Semester Ganjil 2026/2027').
     */
    public static function formatTahunSemester(?string $code): string
    {
        if (!$code) return '-';

        $parts = explode('.', $code);
        $tahun = $parts[0] ?? '2026';
        $sub = $parts[1] ?? '1';
        $semester = $sub == '2' ? 'Genap' : 'Ganjil';
        $tahunNext = ((int) $tahun) + 1;

        return "{$code} (Semester {$semester} {$tahun}/{$tahunNext})";
    }
}
