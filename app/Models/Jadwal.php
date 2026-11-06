<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwals';

    protected $fillable = [
        'modul_id',
        'dosen_id',
        'mata_kuliah_id',
        'nama_kuliah',
        'tipe_kegiatan',
        'kelompok',
        'ruangan',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'dosen_pendamping',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Modul pembelajaran terkait.
     */
    public function modul()
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }

    /**
     * Dosen utama pengampu kuliah.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Data master mata kuliah / topik.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Mendapatkan daftar rincian dosen pendamping (inisial / kode & nama lengkap).
     */
    public function getDosenPendampingDetailsAttribute(): array
    {
        if (empty($this->dosen_pendamping)) {
            return [];
        }

        $kodes = array_filter(array_map('trim', explode(',', $this->dosen_pendamping)));
        if (empty($kodes)) {
            return [];
        }

        static $dosenCache = null;
        if ($dosenCache === null) {
            $dosenCache = Dosen::all()->keyBy('kode');
        }

        $result = [];
        foreach ($kodes as $kode) {
            $d = $dosenCache->get($kode);
            $nama = $d ? $d->nama : '';
            $result[] = [
                'kode' => $kode,
                'nama' => $nama,
                'label' => $nama ? "[{$kode}] {$nama}" : "[{$kode}]",
            ];
        }

        return $result;
    }

    /**
     * Scope untuk menyaring jadwal berdasarkan dosen (baik sebagai Dosen Utama maupun Dosen Pengampu Pendamping).
     */
    public function scopeForDosen($query, $dosenId)
    {
        if (empty($dosenId)) {
            return $query;
        }

        $dosen = is_numeric($dosenId) ? Dosen::find($dosenId) : Dosen::where('kode', $dosenId)->first();

        return $query->where(function ($q) use ($dosen, $dosenId) {
            if ($dosen) {
                $kode = trim($dosen->kode);
                $q->where('dosen_id', $dosen->id)
                  ->orWhere('dosen_pendamping', $kode)
                  ->orWhere('dosen_pendamping', 'like', "{$kode},%")
                  ->orWhere('dosen_pendamping', 'like', "%, {$kode},%")
                  ->orWhere('dosen_pendamping', 'like', "%,{$kode},%")
                  ->orWhere('dosen_pendamping', 'like', "%, {$kode}")
                  ->orWhere('dosen_pendamping', 'like', "%,{$kode}")
                  ->orWhere('dosen_pendamping', 'like', "%{$kode}%");

                if (!empty($dosen->nama)) {
                    $q->orWhere('dosen_pendamping', 'like', "%{$dosen->nama}%");
                }
            } else {
                $q->where('dosen_id', $dosenId)
                  ->orWhere('dosen_pendamping', 'like', "%{$dosenId}%");
            }
        });
    }
}

