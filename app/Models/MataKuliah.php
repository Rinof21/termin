<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliahs';

    protected $fillable = [
        'modul_id',
        'kode',
        'nama',
        'tipe_kegiatan',
        'dosen_id',
        'dosen_pendamping',
        'beban_jam',
        'ruangan',
        'deskripsi',
    ];

    /**
     * Modul induk mata kuliah ini.
     */
    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    /**
     * Dosen pengampu utama.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Jadwal yang telah di-plot menggunakan mata kuliah ini.
     */
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}

