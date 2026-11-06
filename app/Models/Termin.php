<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    use HasFactory;

    protected $table = 'termins';

    protected $fillable = [
        'nomor',
        'nama',
        'tipe_semester',
        'tahun_semester',
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_minggu',
        'is_aktif',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_aktif' => 'boolean',
            'nomor' => 'integer',
            'jumlah_minggu' => 'integer',
        ];
    }

    /**
     * Modul pembelajaran yang berjalan pada termin ini.
     */
    public function moduls()
    {
        return $this->hasMany(Modul::class);
    }

    /**
     * Seluruh jadwal perkuliahan dalam termin ini melalui modul.
     */
    public function jadwals()
    {
        return $this->hasManyThrough(Jadwal::class, Modul::class);
    }

    /**
     * Format rentang tanggal termin.
     */
    public function getRentangTanggalAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }
        return $this->tanggal_mulai->translatedFormat('d M Y') . ' s/d ' . $this->tanggal_selesai->translatedFormat('d M Y');
    }

    /**
     * Hitung dan sediakan rincian 6 minggu dalam termin ini.
     */
    public function getDaftarMingguAttribute(): array
    {
        $mingguList = [];
        $start = Carbon::parse($this->tanggal_mulai);
        $totalMinggu = $this->jumlah_minggu ?: 6;

        for ($i = 1; $i <= $totalMinggu; $i++) {
            $weekStart = (clone $start)->addWeeks($i - 1)->startOfWeek(Carbon::MONDAY);
            $weekEnd = (clone $weekStart)->addDays(6);

            $mingguList[] = [
                'nomor' => $i,
                'label' => 'Minggu ' . $i,
                'tanggal_mulai' => $weekStart->format('Y-m-d'),
                'tanggal_selesai' => $weekEnd->format('Y-m-d'),
                'rentang' => $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M Y'),
            ];
        }

        return $mingguList;
    }

    /**
     * Kode singkatan tahun semester (contoh: '2026.1', '2026.2', '2027.1').
     */
    public function getKodeTahunSemesterAttribute(): string
    {
        if ($this->tahun_semester) {
            return $this->tahun_semester;
        }

        $tahun = substr($this->tahun_ajaran ?: '2026', 0, 4);
        return $tahun . ($this->tipe_semester === 'Genap' ? '.2' : '.1');
    }

    /**
     * Ambil Termin yang sedang aktif di sistem.
     */
    public static function getActiveTermin(): ?self
    {
        $activeTahunSemester = Setting::getActiveTahunSemester();
        $activeSemester = Setting::getActiveSemester();

        // Cari termin yang aktif dan cocok dengan tahun_semester atau semester aktif
        $termin = static::where('is_aktif', true)
            ->where(function ($q) use ($activeTahunSemester, $activeSemester) {
                $q->where('tahun_semester', $activeTahunSemester)
                  ->orWhere('tipe_semester', $activeSemester);
            })
            ->first();

        // Jika tidak ada yang aktif pada semester tersebut, ambil Termin 4 (atau termin pertama)
        if (!$termin) {
            $termin = static::where('tipe_semester', $activeSemester)
                ->where('nomor', 4)
                ->first()
                ?: static::where('tipe_semester', $activeSemester)->first();

            if ($termin) {
                static::setActive($termin);
            }
        }

        return $termin;
    }

    /**
     * Setel termin tertentu sebagai aktif.
     */
    public static function setActive(self $termin): void
    {
        $kodeTahunSem = $termin->tahun_semester ?: (substr($termin->tahun_ajaran ?: '2026', 0, 4) . ($termin->tipe_semester === 'Genap' ? '.2' : '.1'));

        // Nonaktifkan semua termin lain di sistem
        static::where('id', '!=', $termin->id)->update(['is_aktif' => false]);

        $termin->update([
            'is_aktif' => true,
            'tahun_semester' => $kodeTahunSem,
        ]);

        // Pastikan semester aktif sistem sinkron dengan tipe_semester dan tahun_semester termin ini
        Setting::setActiveTahunSemester($kodeTahunSem);
        Setting::set('active_termin_id', $termin->id);
    }
}

