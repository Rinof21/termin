<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    use HasFactory;

    protected $table = 'moduls';

    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'tipe_semester',
        'tahun_semester',
        'termin',
        'termin_id',
        'warna',
        'koordinator_id',
        'keterangan',
    ];

    /**
     * Kode tahun semester (misal: '2026.1' atau '2026.2').
     */
    public function getKodeTahunSemesterAttribute(): string
    {
        if ($this->tahun_semester) {
            return $this->tahun_semester;
        }
        if ($this->terminModel && $this->terminModel->tahun_semester) {
            return $this->terminModel->tahun_semester;
        }
        return '2026' . ($this->tipe_semester === 'Genap' ? '.2' : '.1');
    }

    /**
     * Relasi ke periode termin akademik.
     */
    public function terminModel()
    {
        return $this->belongsTo(Termin::class, 'termin_id');
    }

    /**
     * Scope modul berdasarkan semester aktif (Ganjil / Genap).
     */
    public function scopeSemesterTipe($query, $tipe = null)
    {
        if ($tipe) {
            return $query->where('tipe_semester', $tipe);
        }
        return $query;
    }

    /**
     * Dosen koordinator modul.
     */
    public function koordinator()
    {
        return $this->belongsTo(Dosen::class, 'koordinator_id');
    }

    /**
     * Daftar mata kuliah / materi dalam modul ini.
     */
    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class, 'modul_id')->orderBy('nama');
    }

    /**
     * Jadwal kuliah dalam modul ini.
     */
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}

