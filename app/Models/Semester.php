<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Seluruh termin yang terhubung dengan semester ini.
     */
    public function termins()
    {
        return $this->hasMany(Termin::class, 'tahun_semester', 'kode');
    }

    /**
     * Seluruh modul pembelajaran dalam semester ini.
     */
    public function moduls()
    {
        return $this->hasMany(Modul::class, 'tahun_semester', 'kode');
    }

    /**
     * Format rentang tanggal semester.
     */
    public function getRentangTanggalAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }
        return $this->tanggal_mulai->translatedFormat('d M Y') . ' s/d ' . $this->tanggal_selesai->translatedFormat('d M Y');
    }

    /**
     * Ambil semester yang saat ini berstatus aktif.
     */
    public static function getActive(): ?self
    {
        $activeCode = Setting::getActiveTahunSemester();
        $sem = static::where('is_aktif', true)->first();

        if (!$sem || $sem->kode !== $activeCode) {
            $sem = static::where('kode', $activeCode)->first() ?: static::first();
            if ($sem && !$sem->is_aktif) {
                static::setActive($sem);
            }
        }

        return $sem;
    }

    /**
     * Aktifkan salah satu semester.
     */
    public static function setActive(self $semester): void
    {
        static::query()->update(['is_aktif' => false]);
        $semester->update(['is_aktif' => true]);

        Setting::setActiveTahunSemester($semester->kode);
    }
}

