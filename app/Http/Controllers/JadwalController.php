<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Modul;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Tampilan Kalender FullCalendar.js interaktif.
     */
    public function index(Request $request)
    {
        $activeSemester = Setting::getActiveSemester();
        $moduls = Modul::where('tipe_semester', $activeSemester)->orderBy('nama')->get();
        $dosens = Dosen::orderBy('nama')->get();
        $kelompoks = Jadwal::whereHas('modul', function ($q) use ($activeSemester) {
                $q->where('tipe_semester', $activeSemester);
            })
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->all();

        return view('admin.jadwals.calendar', compact('moduls', 'dosens', 'kelompoks', 'activeSemester'));
    }

    /**
     * Tampilan Tabel / List Jadwal Lengkap dengan Paginasi & Pencarian.
     */
    public function table(Request $request)
    {
        $search = $request->query('search');
        $modulId = $request->query('modul_id');
        $dosenId = $request->query('dosen_id');
        $tanggal = $request->query('tanggal');
        $activeSemester = Setting::getActiveSemester();
        $semesterFilter = $request->query('semester', $activeSemester);

        $jadwals = Jadwal::with('modul', 'dosen')
            ->when($semesterFilter && $semesterFilter !== 'Semua', function ($query) use ($semesterFilter) {
                return $query->whereHas('modul', function ($q) use ($semesterFilter) {
                    $q->where('tipe_semester', $semesterFilter);
                });
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_kuliah', 'like', "%{$search}%")
                      ->orWhere('ruangan', 'like', "%{$search}%")
                      ->orWhere('kelompok', 'like', "%{$search}%")
                      ->orWhere('dosen_pendamping', 'like', "%{$search}%");
                });
            })
            ->when($modulId, function ($query, $modulId) {
                return $query->where('modul_id', $modulId);
            })
            ->when($dosenId, function ($query, $dosenId) {
                return $query->where('dosen_id', $dosenId);
            })
            ->when($tanggal, function ($query, $tanggal) {
                return $query->whereDate('tanggal', $tanggal);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(15)
            ->withQueryString();

        $moduls = Modul::when($semesterFilter && $semesterFilter !== 'Semua', function ($q) use ($semesterFilter) {
                return $q->where('tipe_semester', $semesterFilter);
            })
            ->orderBy('nama')
            ->get();

        $dosens = Dosen::orderBy('nama')->get();

        return view('admin.jadwals.index', compact('jadwals', 'moduls', 'dosens', 'search', 'modulId', 'dosenId', 'tanggal', 'activeSemester', 'semesterFilter'));
    }

    /**
     * JSON Endpoint untuk FullCalendar.js
     */
    public function events(Request $request)
    {
        $activeSemester = Setting::getActiveSemester();
        $modulId = $request->query('modul_id');
        $dosenId = $request->query('dosen_id');
        $kelompok = $request->query('kelompok');

        $jadwals = Jadwal::with('modul', 'dosen')
            ->whereHas('modul', function ($q) use ($activeSemester) {
                $q->where('tipe_semester', $activeSemester);
            })
            ->when($modulId, function ($query, $modulId) {
                return $query->where('modul_id', $modulId);
            })
            ->when($dosenId, function ($query, $dosenId) {
                return $query->where('dosen_id', $dosenId);
            })
            ->when($kelompok, function ($query, $kelompok) {
                return $query->where('kelompok', $kelompok);
            })
            ->get();

        $events = $jadwals->map(function ($j) {
            $dateStr = $j->tanggal->format('Y-m-d');
            $start = $dateStr . 'T' . substr($j->jam_mulai, 0, 5) . ':00';
            $end = $dateStr . 'T' . substr($j->jam_selesai, 0, 5) . ':00';

            $warna = $j->modul->warna ?? '#3b82f6';
            $dosenInisial = $j->dosen ? $j->dosen->kode : '';
            if ($j->dosen_pendamping) {
                $dosenInisial = $dosenInisial ? "{$dosenInisial}/{$j->dosen_pendamping}" : $j->dosen_pendamping;
            }

            $dosenUtama = $j->dosen ? [
                'id' => $j->dosen->id,
                'kode' => $j->dosen->kode,
                'nama' => $j->dosen->nama,
                'label' => "[{$j->dosen->kode}] {$j->dosen->nama}",
            ] : null;

            $pendampingList = $j->dosen_pendamping_details;

            $dosenLabel = $dosenUtama ? $dosenUtama['label'] : 'Dosen Belum Ditentukan';
            if (!empty($pendampingList)) {
                $pendampingLabels = array_column($pendampingList, 'label');
                $dosenLabel .= ' (Pendamping: ' . implode(', ', $pendampingLabels) . ')';
            }

            $allLabels = [];
            if ($dosenUtama) {
                $allLabels[] = $dosenUtama['label'];
            }
            foreach ($pendampingList as $p) {
                $allLabels[] = $p['label'];
            }
            $dosenLengkapText = implode(', ', $allLabels);
            $totalDosenCount = ($dosenUtama ? 1 : 0) + count($pendampingList);

            $prefix = '';
            if ($j->kelompok) {
                $prefix .= '[' . $j->kelompok . '] ';
            }
            if ($dosenInisial) {
                $prefix .= '[' . $dosenInisial . '] ';
            }

            return [
                'id' => $j->id,
                'title' => $prefix . $j->nama_kuliah,
                'start' => $start,
                'end' => $end,
                'backgroundColor' => $warna,
                'borderColor' => $warna,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'id' => $j->id,
                    'nama_kuliah' => $j->nama_kuliah,
                    'modul_nama' => $j->modul->nama ?? '-',
                    'modul_kode' => $j->modul->kode ?? '-',
                    'tipe_kegiatan' => $j->tipe_kegiatan,
                    'kelompok' => $j->kelompok ?? '-',
                    'ruangan' => $j->ruangan ?? 'Belum ditentukan',
                    'dosen' => $dosenLabel,
                    'dosen_kode' => $j->dosen->kode ?? '',
                    'dosen_nama' => $j->dosen->nama ?? '',
                    'dosen_inisial' => $dosenInisial,
                    'dosen_utama' => $dosenUtama,
                    'pendamping_list' => $pendampingList,
                    'dosen_lengkap_text' => $dosenLengkapText,
                    'total_dosen_count' => $totalDosenCount,
                    'jam' => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5) . ' WIB',
                    'tanggal_format' => $j->tanggal->translatedFormat('l, d F Y'),
                    'catatan' => $j->catatan ?? '-',
                    'edit_url' => route('admin.jadwals.edit', $j->id),
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Daftar slot jam perkuliahan standar S1 Kedokteran (50 Menit per JP).
     */
    public static function getSlotJam()
    {
        return [
            ['sesi' => 1, 'mulai' => '07:30', 'selesai' => '08:20', 'label' => 'Sesi 1: 07.30 - 08.20'],
            ['sesi' => 2, 'mulai' => '08:20', 'selesai' => '09:10', 'label' => 'Sesi 2: 08.20 - 09.10'],
            ['sesi' => 3, 'mulai' => '09:10', 'selesai' => '10:00', 'label' => 'Sesi 3: 09.10 - 10.00'],
            ['sesi' => 4, 'mulai' => '10:10', 'selesai' => '11:00', 'label' => 'Sesi 4: 10.10 - 11.00'],
            ['sesi' => 5, 'mulai' => '11:00', 'selesai' => '11:50', 'label' => 'Sesi 5: 11.00 - 11.50'],
            ['sesi' => 6, 'mulai' => '13:00', 'selesai' => '13:50', 'label' => 'Sesi 6: 13.00 - 13.50'],
            ['sesi' => 7, 'mulai' => '13:50', 'selesai' => '14:40', 'label' => 'Sesi 7: 13.50 - 14.40'],
            ['sesi' => 8, 'mulai' => '14:40', 'selesai' => '15:30', 'label' => 'Sesi 8: 14.40 - 15.30'],
            ['sesi' => 9, 'mulai' => '15:30', 'selesai' => '16:20', 'label' => 'Sesi 9: 15.30 - 16.20'],
        ];
    }

    /**
     * Tampilkan form buat jadwal baru.
     */
    public function create()
    {
        $activeSemester = Setting::getActiveSemester();
        $moduls = Modul::where('tipe_semester', $activeSemester)
            ->with(['mataKuliahs' => function ($q) {
                $q->orderBy('nama');
            }])
            ->orderBy('nama')
            ->get();
        $dosens = Dosen::orderBy('nama')->get();
        $slotJam = self::getSlotJam();

        $tipeKegiatan = [
            'Kuliah',
            'Praktikum',
            'Diskusi Kelompok (DK)',
            'Pleno',
            'Team-Based Learning (TBL)',
            'Ujian/Formatif',
            'Keterampilan Klinis (FCP)',
        ];

        $modulMataKuliahMap = $moduls->mapWithKeys(function ($m) {
            return [
                $m->id => $m->mataKuliahs->map(function ($mk) {
                    return [
                        'id' => $mk->id,
                        'nama' => $mk->nama,
                        'tipe_kegiatan' => $mk->tipe_kegiatan,
                        'dosen_id' => $mk->dosen_id,
                        'dosen_pendamping' => $mk->dosen_pendamping,
                        'ruangan' => $mk->ruangan,
                    ];
                })
            ];
        });

        return view('admin.jadwals.create', compact('moduls', 'dosens', 'tipeKegiatan', 'slotJam', 'activeSemester', 'modulMataKuliahMap'));
    }

    /**
     * Memeriksa apakah terdapat bentrok jadwal untuk Dosen Pengampu Utama maupun Dosen Pendamping.
     * Mengembalikan pesan error jika ditemukan bentrok, atau null jika tidak ada bentrok.
     */
    protected function checkDosenConflict(
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $dosenId,
        ?string $dosenPendamping,
        ?int $excludeJadwalId = null
    ): ?string {
        $jamMulai = substr($jamMulai, 0, 5);
        $jamSelesai = substr($jamSelesai, 0, 5);

        // Kumpulkan semua dosen yang terlibat dalam jadwal ini
        $assignedDosenMap = [];
        $allDosens = Dosen::all();
        $dosensById = $allDosens->keyBy('id');
        $dosensByKode = $allDosens->keyBy(fn($d) => strtoupper(trim($d->kode)));

        if ($dosenId && ($d = $dosensById->get($dosenId))) {
            $kodeUpper = strtoupper(trim($d->kode));
            $assignedDosenMap[$kodeUpper] = [
                'id' => $d->id,
                'kode' => $d->kode,
                'nama' => $d->nama,
                'label' => "[{$d->kode}] {$d->nama}",
                'role' => 'Dosen Pengampu Utama',
            ];
        }

        if (!empty($dosenPendamping)) {
            $coCodes = array_filter(array_map('trim', explode(',', $dosenPendamping)));
            foreach ($coCodes as $code) {
                $codeUpper = strtoupper($code);
                if (!isset($assignedDosenMap[$codeUpper])) {
                    $d = $dosensByKode->get($codeUpper);
                    $nama = $d ? $d->nama : '';
                    $assignedDosenMap[$codeUpper] = [
                        'id' => $d ? $d->id : null,
                        'kode' => $d ? $d->kode : $code,
                        'nama' => $nama,
                        'label' => $nama ? "[{$code}] {$nama}" : "[{$code}]",
                        'role' => 'Dosen Pengampu',
                    ];
                }
            }
        }

        // Jika tidak ada dosen yang dipilih sama sekali, tidak ada bentrok dosen
        if (empty($assignedDosenMap)) {
            return null;
        }

        // Cari jadwal lain pada tanggal yang sama
        $otherJadwals = Jadwal::with(['dosen', 'modul'])
            ->whereDate('tanggal', $tanggal)
            ->when($excludeJadwalId, function ($q, $excludeId) {
                return $q->where('id', '!=', $excludeId);
            })
            ->get();

        foreach ($otherJadwals as $other) {
            $otherMulai = substr($other->jam_mulai, 0, 5);
            $otherSelesai = substr($other->jam_selesai, 0, 5);

            // Cek irisan waktu: overlap jika startA < endB && endA > startB
            if ($otherMulai < $jamSelesai && $otherSelesai > $jamMulai) {
                // Kumpulkan dosen pada jadwal lain ini
                $otherCodes = [];

                if ($other->dosen) {
                    $otherCodes[strtoupper(trim($other->dosen->kode))] = [
                        'label' => "[{$other->dosen->kode}] {$other->dosen->nama}",
                        'role' => 'Dosen Pengampu Utama',
                    ];
                } elseif ($other->dosen_id && ($od = $dosensById->get($other->dosen_id))) {
                    $otherCodes[strtoupper(trim($od->kode))] = [
                        'label' => "[{$od->kode}] {$od->nama}",
                        'role' => 'Dosen Pengampu Utama',
                    ];
                }

                if (!empty($other->dosen_pendamping)) {
                    $otherCo = array_filter(array_map('trim', explode(',', $other->dosen_pendamping)));
                    foreach ($otherCo as $oc) {
                        $ocUpper = strtoupper($oc);
                        if (!isset($otherCodes[$ocUpper])) {
                            $d = $dosensByKode->get($ocUpper);
                            $otherCodes[$ocUpper] = [
                                'label' => $d ? "[{$oc}] {$d->nama}" : "[{$oc}]",
                                'role' => 'Dosen Pengampu',
                            ];
                        }
                    }
                }

                // Cek apakah ada dosen yang bentrok
                foreach ($assignedDosenMap as $codeUpper => $assignedInfo) {
                    if (isset($otherCodes[$codeUpper])) {
                        $dosenLabel = $assignedInfo['label'];
                        $modulNama = $other->modul ? " (Modul: {$other->modul->nama})" : '';
                        $formattedDate = Carbon::parse($tanggal)->translatedFormat('d F Y');

                        return "Jadwal bentrok! Dosen {$dosenLabel} sudah memiliki jadwal '{$other->nama_kuliah}'{$modulNama} pada hari {$formattedDate} jam {$otherMulai} - {$otherSelesai} WIB.";
                    }
                }
            }
        }

        return null;
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'modul_id' => ['required', 'exists:moduls,id'],
            'dosen_id' => ['nullable', 'exists:dosens,id'],
            'nama_kuliah' => ['required', 'string', 'max:255'],
            'tipe_kegiatan' => ['required', 'string', 'max:100'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'dosen_pendamping' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        // Bersihkan dosen pendamping jika ada duplikasi dengan dosen pengampu utama
        if (!empty($validated['dosen_id']) && !empty($validated['dosen_pendamping'])) {
            $primaryDosen = Dosen::find($validated['dosen_id']);
            if ($primaryDosen) {
                $coList = array_filter(array_map('trim', explode(',', $validated['dosen_pendamping'])));
                $coList = array_values(array_filter($coList, fn($c) => strtoupper($c) !== strtoupper($primaryDosen->kode)));
                $validated['dosen_pendamping'] = !empty($coList) ? implode(', ', $coList) : null;
            }
        }

        // Validasi bentrok jadwal dosen
        $conflict = $this->checkDosenConflict(
            $validated['tanggal'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $validated['dosen_id'] ?? null,
            $validated['dosen_pendamping'] ?? null
        );

        if ($conflict) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_mulai' => $conflict,
                    'dosen_id' => $conflict,
                ])
                ->with('error', $conflict);
        }

        $modul = Modul::findOrFail($validated['modul_id']);

        // Hitung beban jam perkuliahan berdasarkan jam mulai dan jam selesai
        $bebanJam = null;
        try {
            $start = Carbon::createFromFormat('H:i', substr($validated['jam_mulai'], 0, 5));
            $end = Carbon::createFromFormat('H:i', substr($validated['jam_selesai'], 0, 5));
            $diffMinutes = $start->diffInMinutes($end);
            $jp = (int) round($diffMinutes / 50);
            $bebanJam = $jp > 0 ? "{$jp} JP ({$diffMinutes} Menit)" : "{$diffMinutes} Menit";
        } catch (\Exception $e) {
            $bebanJam = null;
        }

        // Cari atau buat Mata Kuliah di bawah modul ini agar otomatis masuk ke Data Modul
        $mataKuliah = MataKuliah::firstOrCreate(
            [
                'modul_id' => $modul->id,
                'nama' => trim($validated['nama_kuliah']),
            ],
            [
                'kode' => $modul->kode,
                'tipe_kegiatan' => $validated['tipe_kegiatan'],
                'dosen_id' => $validated['dosen_id'] ?? null,
                'dosen_pendamping' => $validated['dosen_pendamping'] ?? null,
                'beban_jam' => $bebanJam,
                'ruangan' => $validated['ruangan'] ?? null,
                'deskripsi' => $validated['catatan'] ?? null,
            ]
        );

        if (!$mataKuliah->wasRecentlyCreated) {
            $mataKuliahUpdates = [];
            if (!$mataKuliah->dosen_id && !empty($validated['dosen_id'])) {
                $mataKuliahUpdates['dosen_id'] = $validated['dosen_id'];
            }
            if (!$mataKuliah->dosen_pendamping && !empty($validated['dosen_pendamping'])) {
                $mataKuliahUpdates['dosen_pendamping'] = $validated['dosen_pendamping'];
            }
            if (!$mataKuliah->ruangan && !empty($validated['ruangan'])) {
                $mataKuliahUpdates['ruangan'] = $validated['ruangan'];
            }
            if (!empty($mataKuliahUpdates)) {
                $mataKuliah->update($mataKuliahUpdates);
            }
        }

        $validated['mata_kuliah_id'] = $mataKuliah->id;

        Jadwal::create($validated);

        return redirect()->route('admin.jadwals.index')
            ->with('success', "Jadwal kuliah berhasil ditambahkan ke kalender dan tersinkronkan ke Data Modul {$modul->nama}.");
    }

    /**
     * Tampilkan form ubah jadwal.
     */
    public function edit(Jadwal $jadwal)
    {
        $activeSemester = Setting::getActiveSemester();
        $moduls = Modul::where('tipe_semester', $activeSemester)
            ->orWhere('id', $jadwal->modul_id)
            ->with(['mataKuliahs' => function ($q) {
                $q->orderBy('nama');
            }])
            ->orderBy('nama')
            ->get();
        $dosens = Dosen::orderBy('nama')->get();
        $slotJam = self::getSlotJam();

        $tipeKegiatan = [
            'Kuliah',
            'Praktikum',
            'Diskusi Kelompok (DK)',
            'Pleno',
            'Team-Based Learning (TBL)',
            'Ujian/Formatif',
            'Keterampilan Klinis (FCP)',
        ];

        $modulMataKuliahMap = $moduls->mapWithKeys(function ($m) {
            return [
                $m->id => $m->mataKuliahs->map(function ($mk) {
                    return [
                        'id' => $mk->id,
                        'nama' => $mk->nama,
                        'tipe_kegiatan' => $mk->tipe_kegiatan,
                        'dosen_id' => $mk->dosen_id,
                        'dosen_pendamping' => $mk->dosen_pendamping,
                        'ruangan' => $mk->ruangan,
                    ];
                })
            ];
        });

        return view('admin.jadwals.edit', compact('jadwal', 'moduls', 'dosens', 'tipeKegiatan', 'slotJam', 'activeSemester', 'modulMataKuliahMap'));
    }

    /**
     * Perbarui jadwal.
     */
    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'modul_id' => ['required', 'exists:moduls,id'],
            'dosen_id' => ['nullable', 'exists:dosens,id'],
            'nama_kuliah' => ['required', 'string', 'max:255'],
            'tipe_kegiatan' => ['required', 'string', 'max:100'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required'],
            'dosen_pendamping' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        // Bersihkan dosen pendamping jika ada duplikasi dengan dosen pengampu utama
        if (!empty($validated['dosen_id']) && !empty($validated['dosen_pendamping'])) {
            $primaryDosen = Dosen::find($validated['dosen_id']);
            if ($primaryDosen) {
                $coList = array_filter(array_map('trim', explode(',', $validated['dosen_pendamping'])));
                $coList = array_values(array_filter($coList, fn($c) => strtoupper($c) !== strtoupper($primaryDosen->kode)));
                $validated['dosen_pendamping'] = !empty($coList) ? implode(', ', $coList) : null;
            }
        }

        // Validasi bentrok jadwal dosen (kecualikan ID jadwal ini)
        $conflict = $this->checkDosenConflict(
            $validated['tanggal'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $validated['dosen_id'] ?? null,
            $validated['dosen_pendamping'] ?? null,
            $jadwal->id
        );

        if ($conflict) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_mulai' => $conflict,
                    'dosen_id' => $conflict,
                ])
                ->with('error', $conflict);
        }

        $modul = Modul::findOrFail($validated['modul_id']);

        // Hitung beban jam perkuliahan
        $bebanJam = null;
        try {
            $start = Carbon::createFromFormat('H:i', substr($validated['jam_mulai'], 0, 5));
            $end = Carbon::createFromFormat('H:i', substr($validated['jam_selesai'], 0, 5));
            $diffMinutes = $start->diffInMinutes($end);
            $jp = (int) round($diffMinutes / 50);
            $bebanJam = $jp > 0 ? "{$jp} JP ({$diffMinutes} Menit)" : "{$diffMinutes} Menit";
        } catch (\Exception $e) {
            $bebanJam = null;
        }

        // Cari atau buat Mata Kuliah di bawah modul ini agar otomatis masuk ke Data Modul
        $mataKuliah = MataKuliah::firstOrCreate(
            [
                'modul_id' => $modul->id,
                'nama' => trim($validated['nama_kuliah']),
            ],
            [
                'kode' => $modul->kode,
                'tipe_kegiatan' => $validated['tipe_kegiatan'],
                'dosen_id' => $validated['dosen_id'] ?? null,
                'dosen_pendamping' => $validated['dosen_pendamping'] ?? null,
                'beban_jam' => $bebanJam,
                'ruangan' => $validated['ruangan'] ?? null,
                'deskripsi' => $validated['catatan'] ?? null,
            ]
        );

        $validated['mata_kuliah_id'] = $mataKuliah->id;

        $jadwal->update($validated);

        return redirect()->route('admin.jadwals.index')
            ->with('success', 'Jadwal kuliah berhasil diperbarui dan tersinkronkan ke Data Modul.');
    }

    /**
     * Hapus jadwal.
     */
    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal kuliah berhasil dihapus.');
    }

    /**
     * Hapus banyak jadwal sekaligus (Bulk Delete).
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:jadwals,id'],
        ]);

        $count = Jadwal::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Sebanyak {$count} jadwal perkuliahan berhasil dihapus sekaligus.");
    }
}

