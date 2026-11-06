<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosens';

    protected $fillable = [
        'kode',
        'nama',
        'nip',
        'email',
        'no_hp',
    ];

    /**
     * Jadwal kuliah yang diampu dosen ini.
     */
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    /**
     * Modul yang dikoordinatori dosen ini.
     */
    public function modulKoordinator()
    {
        return $this->hasMany(Modul::class, 'koordinator_id');
    }
}

