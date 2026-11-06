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
        $status = $request->query('status', 'active');

        // Jumlah jadwal aktif dan yang berada di kotak sampah (soft deleted)
        $activeCount = Jadwal::when($semesterFilter && $semesterFilter !== 'Semua', function ($query) use ($semesterFilter) {
            return $query->whereHas('modul', function ($q) use ($semesterFilter) {
                $q->where('tipe_semester', $semesterFilter);
            });
        })->count();

        $trashCount = Jadwal::onlyTrashed()
            ->when($semesterFilter && $semesterFilter !== 'Semua', function ($query) use ($semesterFilter) {
                return $query->whereHas('modul', function ($q) use ($semesterFilter) {
                    $q->where('tipe_semester', $semesterFilter);
                });
            })->count();

        $baseQuery = $status === 'trash'
            ? Jadwal::onlyTrashed()->with('modul', 'dosen')
            : Jadwal::with('modul', 'dosen');

        $jadwals = $baseQuery
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
            ->orderBy($status === 'trash' ? 'deleted_at' : 'tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(15)
            ->withQueryString();

        $moduls = Modul::when($semesterFilter && $semesterFilter !== 'Semua', function ($q) use ($semesterFilter) {
                return $q->where('tipe_semester', $semesterFilter);
            })
            ->orderBy('nama')
            ->get();

        $dosens = Dosen::orderBy('nama')->get();

        return view('admin.jadwals.index', compact(
            'jadwals', 'moduls', 'dosens', 'search', 'modulId', 'dosenId', 'tanggal', 
            'activeSemester', 'semesterFilter', 'status', 'activeCount', 'trashCount'
        ));
    }

    /**
     * Tampilan Matriks Jadwal Kuliah Spreadsheet (Baris atas Nama Modul / Kelompok).
     */
    public function matrix(Request $request)
    {
        \Carbon\Carbon::setLocale('id');
        app()->setLocale('id');

        $activeSemester = Setting::getActiveSemester();
        $semesterFilter = $request->query('semester', $activeSemester);
        $terminId = $request->query('termin_id');
        // Default tercentang: Pisah kolom per kelompok A.1 / A.2
        $splitKelompok = $request->boolean('split_kelompok', true);

        // Ambil Termin yang sesuai
        if ($terminId) {
            $currentTermin = \App\Models\Termin::find($terminId);
        } else {
            $currentTermin = \App\Models\Termin::where('tipe_semester', $semesterFilter)
                ->where('is_aktif', true)
                ->first()
                ?: \App\Models\Termin::where('tipe_semester', $semesterFilter)->first();
        }

        $termins = \App\Models\Termin::where('tipe_semester', $semesterFilter)->orderBy('nomor')->get();

        // Ambil Daftar Minggu
        $daftarMinggu = $currentTermin ? $currentTermin->daftar_minggu : [];
        if (empty($daftarMinggu)) {
            $startFallback = Carbon::now()->startOfWeek(Carbon::MONDAY);
            for ($i = 1; $i <= 6; $i++) {
                $ws = (clone $startFallback)->addWeeks($i - 1);
                $we = (clone $ws)->addDays(5);
                $daftarMinggu[] = [
                    'nomor' => $i,
                    'label' => 'Minggu ' . $i,
                    'tanggal_mulai' => $ws->format('Y-m-d'),
                    'tanggal_selesai' => $we->format('Y-m-d'),
                    'rentang' => $ws->translatedFormat('d M') . ' - ' . $we->translatedFormat('d M Y'),
                ];
            }
        }

        // --- DETEKSI TANGGAL HARI INI (TODAY) SECARA OTOMATIS ---
        $today = Carbon::today();
        $todayDateStr = $today->format('Y-m-d');

        $daySlugMap = [
            Carbon::MONDAY => 'senin',
            Carbon::TUESDAY => 'selasa',
            Carbon::WEDNESDAY => 'rabu',
            Carbon::THURSDAY => 'kamis',
            Carbon::FRIDAY => 'jumat',
            Carbon::SATURDAY => 'sabtu',
            Carbon::SUNDAY => 'senin',
        ];

        $defaultMingguNomor = 1;
        $defaultHariSlug = 'senin';
        $todayInTermin = false;

        foreach ($daftarMinggu as $m) {
            if ($todayDateStr >= $m['tanggal_mulai'] && $todayDateStr <= $m['tanggal_selesai']) {
                $defaultMingguNomor = (int) $m['nomor'];
                $defaultHariSlug = $daySlugMap[$today->dayOfWeek] ?? 'senin';
                $todayInTermin = true;
                break;
            }
        }

        // Tentukan nomor minggu aktif
        $mingguNomor = $request->has('minggu') ? (int) $request->query('minggu') : $defaultMingguNomor;

        // Tentukan hari aktif: jika ada di request pakai itu, jika tidak ada dan ini minggu hari ini pakai hari ini
        if ($request->has('hari')) {
            $hariSlug = $request->query('hari');
        } elseif ($mingguNomor === $defaultMingguNomor && $todayInTermin) {
            $hariSlug = $defaultHariSlug;
        } else {
            $hariSlug = 'senin';
        }

        // --- DUKUNGAN KLIK KOTAK TANGGAL DARI KALENDER: ?tanggal=YYYY-MM-DD ---
        if ($request->has('tanggal') && !empty($request->query('tanggal'))) {
            try {
                $targetDate = Carbon::parse($request->query('tanggal'));
                $targetDateStr = $targetDate->format('Y-m-d');

                $matchingTermin = \App\Models\Termin::where('tipe_semester', $semesterFilter)
                    ->where('tanggal_mulai', '<=', $targetDateStr)
                    ->where('tanggal_selesai', '>=', $targetDateStr)
                    ->first();
                if ($matchingTermin) {
                    $currentTermin = $matchingTermin;
                    $daftarMinggu = $currentTermin->daftar_minggu;
                }

                foreach ($daftarMinggu as $m) {
                    if ($targetDateStr >= $m['tanggal_mulai'] && $targetDateStr <= $m['tanggal_selesai']) {
                        $mingguNomor = (int) $m['nomor'];
                        break;
                    }
                }

                $hariSlug = $daySlugMap[$targetDate->dayOfWeek] ?? 'senin';
            } catch (\Exception $e) {
                // Abaikan jika format tidak valid
            }
        }

        $selectedMinggu = collect($daftarMinggu)->firstWhere('nomor', $mingguNomor) ?: $daftarMinggu[0];
        $weekStartDate = Carbon::parse($selectedMinggu['tanggal_mulai']);

        // 6 Hari Perkuliahan (Senin s/d Sabtu)
        $days = [];
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
        foreach ($dayNames as $idx => $dName) {
            $dateObj = (clone $weekStartDate)->addDays($idx);
            $slug = strtolower(str_replace("'", "", $dName));
            $isToday = ($dateObj->format('Y-m-d') === $todayDateStr);
            $days[] = [
                'index' => $idx,
                'slug' => $slug,
                'nama' => $dName,
                'date' => $dateObj->format('Y-m-d'),
                'formatted' => $dateObj->translatedFormat('l, d F Y'),
                'short_date' => $dateObj->translatedFormat('d M'),
                'is_today' => $isToday,
            ];
        }

        $selectedDay = collect($days)->firstWhere('slug', $hariSlug) ?: $days[0];
        $selectedDate = $selectedDay['date'];

        // Ambil modul-modul pada semester terpilih
        $moduls = Modul::where('tipe_semester', $semesterFilter)->orderBy('id')->get();

        // Susun Kolom Matriks: 1 Modul = 1 Kolom Murni (Persis sesuai jumlah data modul di aplikasi)
        $columns = [];
        foreach ($moduls as $m) {
            if ($splitKelompok) {
                // Hanya jika opsi 'Pisah Kolom' dicentang oleh user
                $kelompoksInDb = Jadwal::where('modul_id', $m->id)
                    ->whereNotNull('kelompok')
                    ->whereNotIn('kelompok', ['Semua Kelompok', 'Semua', ''])
                    ->distinct()
                    ->pluck('kelompok')
                    ->all();

                if (!empty($kelompoksInDb)) {
                    if (in_array('A.1', $kelompoksInDb) && !in_array('A.2', $kelompoksInDb)) {
                        $kelompoksInDb[] = 'A.2';
                    }
                    sort($kelompoksInDb);
                    foreach ($kelompoksInDb as $grp) {
                        $columns[] = [
                            'id' => 'modul_' . $m->id . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $grp),
                            'modul_id' => $m->id,
                            'modul' => $m,
                            'kelompok' => $grp,
                            'nama' => $m->nama . ' (' . $grp . ')',
                            'kode' => $m->kode,
                            'warna' => $m->warna ?: '#4f46e5',
                        ];
                    }
                    continue;
                }
            }

            // Default: 1 Modul = 1 Kolom Murni
            $columns[] = [
                'id' => 'modul_' . $m->id,
                'modul_id' => $m->id,
                'modul' => $m,
                'kelompok' => null,
                'nama' => $m->nama,
                'kode' => $m->kode,
                'warna' => $m->warna ?: '#4f46e5',
            ];
        }

        // 9 Sesi Standar Kurikulum S1 Kedokteran
        $timeSlots = [
            ['sesi' => 1, 'mulai' => '07:30', 'selesai' => '08:20', 'label' => '07.30 - 08.20'],
            ['sesi' => 2, 'mulai' => '08:20', 'selesai' => '09:10', 'label' => '08.20 - 09.10'],
            ['sesi' => 3, 'mulai' => '09:10', 'selesai' => '10:00', 'label' => '09.10 - 10.00'],
            ['sesi' => 4, 'mulai' => '10:10', 'selesai' => '11:00', 'label' => '10.10 - 11.00'],
            ['sesi' => 5, 'mulai' => '11:00', 'selesai' => '11:50', 'label' => '11.00 - 11.50'],
            ['sesi' => 6, 'mulai' => '13:00', 'selesai' => '13:50', 'label' => '13.00 - 13.50'],
            ['sesi' => 7, 'mulai' => '13:50', 'selesai' => '14:40', 'label' => '13.50 - 14.40'],
            ['sesi' => 8, 'mulai' => '14:40', 'selesai' => '15:30', 'label' => '14.40 - 15.30'],
            ['sesi' => 9, 'mulai' => '15:30', 'selesai' => '16:20', 'label' => '15.30 - 16.20'],
        ];

        // Ambil Jadwal pada tanggal terpilih
        $jadwalsOnDate = Jadwal::with(['modul', 'dosen', 'mataKuliah'])
            ->whereDate('tanggal', $selectedDate)
            ->get();

        // Pemetaan Grid: $grid[slot_idx][col_idx]
        $grid = [];
        $occupied = []; // [slot_idx][col_idx] => true jika sudah di-cover oleh rowspan/colspan sebelumnya

        $numSlots = count($timeSlots);
        $numCols = count($columns);

        for ($s = 0; $s < $numSlots; $s++) {
            $slot = $timeSlots[$s];
            $slotMulai = substr($slot['mulai'], 0, 5);
            $slotSelesai = substr($slot['selesai'], 0, 5);

            for ($c = 0; $c < $numCols; $c++) {
                if (isset($occupied[$s][$c])) {
                    $grid[$s][$c] = [
                        'type' => 'spanned',
                    ];
                    continue;
                }

                $col = $columns[$c];

                // Cari jadwal yang cocok dengan kolom ini dan slot ini
                $matchedJadwal = null;
                foreach ($jadwalsOnDate as $j) {
                    if ($j->modul_id != $col['modul_id']) {
                        continue;
                    }

                    // Cocokkan kelompok
                    if (!empty($col['kelompok'])) {
                        $jKelompok = trim($j->kelompok ?? '');
                        if (!empty($jKelompok) && 
                            $jKelompok !== 'Semua Kelompok' && 
                            $jKelompok !== 'Semua' && 
                            stripos($jKelompok, $col['kelompok']) === false) {
                            continue;
                        }
                    }

                    // Cocokkan waktu (overlap)
                    $jMulai = substr($j->jam_mulai, 0, 5);
                    $jSelesai = substr($j->jam_selesai, 0, 5);

                    if ($jMulai < $slotSelesai && $jSelesai > $slotMulai) {
                        $matchedJadwal = $j;
                        break;
                    }
                }

                if ($matchedJadwal) {
                    // Hitung rowspan
                    $rowspan = 1;
                    $maxSlotIndex = ($s < 5) ? 4 : 8; // Jangan tembus ISHOMA
                    for ($nextS = $s + 1; $nextS <= $maxSlotIndex; $nextS++) {
                        $nextSlot = $timeSlots[$nextS];
                        $nextMulai = substr($nextSlot['mulai'], 0, 5);
                        $nextSelesai = substr($nextSlot['selesai'], 0, 5);

                        $jMulai = substr($matchedJadwal->jam_mulai, 0, 5);
                        $jSelesai = substr($matchedJadwal->jam_selesai, 0, 5);

                        if ($jMulai < $nextSelesai && $jSelesai > $nextMulai) {
                            $rowspan++;
                            $occupied[$nextS][$c] = true;
                        } else {
                            break;
                        }
                    }

                    // Cek colspan (jika kolom berikutnya milik modul yang sama dan memiliki jadwal yang sama)
                    $colspan = 1;
                    if ($c + 1 < $numCols) {
                        $nextCol = $columns[$c + 1];
                        if ($nextCol['modul_id'] == $col['modul_id']) {
                            $jKelompok = trim($matchedJadwal->kelompok ?? '');
                            if (empty($jKelompok) || $jKelompok === 'Semua Kelompok' || stripos($jKelompok, '&') !== false) {
                                $colspan = 2;
                                $occupied[$s][$c + 1] = true;
                                for ($k = 1; $k < $rowspan; $k++) {
                                    $occupied[$s + $k][$c + 1] = true;
                                }
                            }
                        }
                    }

                    $grid[$s][$c] = [
                        'type' => 'event',
                        'jadwal' => $matchedJadwal,
                        'rowspan' => $rowspan,
                        'colspan' => $colspan,
                        'modul' => $col['modul'],
                    ];
                } else {
                    $grid[$s][$c] = [
                        'type' => 'empty',
                        'rowspan' => 1,
                        'colspan' => 1,
                        'modul' => $col['modul'],
                        'kelompok' => $col['kelompok'],
                        'slot' => $slot,
                    ];
                }
            }
        }

        $dosens = Dosen::orderBy('nama')->get();

        return view('admin.jadwals.matrix', compact(
            'activeSemester',
            'semesterFilter',
            'currentTermin',
            'termins',
            'daftarMinggu',
            'selectedMinggu',
            'mingguNomor',
            'days',
            'selectedDay',
            'hariSlug',
            'selectedDate',
            'columns',
            'timeSlots',
            'grid',
            'jadwalsOnDate',
            'moduls',
            'dosens',
            'splitKelompok'
        ));
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
    public function create(Request $request)
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

        // 9 Sesi Standar Kurikulum S1 Kedokteran
        $standardSlots = [
            1 => ['mulai' => '07:30', 'selesai' => '08:20'],
            2 => ['mulai' => '08:20', 'selesai' => '09:10'],
            3 => ['mulai' => '09:10', 'selesai' => '10:00'],
            4 => ['mulai' => '10:10', 'selesai' => '11:00'],
            5 => ['mulai' => '11:00', 'selesai' => '11:50'],
            6 => ['mulai' => '13:00', 'selesai' => '13:50'],
            7 => ['mulai' => '13:50', 'selesai' => '14:40'],
            8 => ['mulai' => '14:40', 'selesai' => '15:30'],
            9 => ['mulai' => '15:30', 'selesai' => '16:20'],
        ];

        $sesi = (int) $request->query('sesi', 0);
        $jamMulai = $request->query('jam_mulai', '');
        $jamSelesai = $request->query('jam_selesai', '');

        if ($sesi && isset($standardSlots[$sesi])) {
            if (!$jamMulai) {
                $jamMulai = $standardSlots[$sesi]['mulai'];
            }
            if (!$jamSelesai) {
                $jamSelesai = $standardSlots[$sesi]['selesai'];
            }
        } elseif ($jamMulai && !$sesi) {
            $jmClean = substr($jamMulai, 0, 5);
            foreach ($standardSlots as $sNum => $sData) {
                if ($sData['mulai'] === $jmClean) {
                    $sesi = $sNum;
                    if (!$jamSelesai) {
                        $jamSelesai = $sData['selesai'];
                    }
                    break;
                }
            }
        }

        if ($jamMulai) {
            $jamMulai = substr($jamMulai, 0, 5);
        }
        if ($jamSelesai) {
            $jamSelesai = substr($jamSelesai, 0, 5);
        }

        $preset = [
            'modul_id' => $request->query('modul_id', ''),
            'tanggal' => $request->query('tanggal', \Carbon\Carbon::today()->format('Y-m-d')),
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kelompok' => $request->query('kelompok', ''),
            'sesi' => $sesi,
            'from_matrix' => $request->has('tanggal') || $request->has('sesi') || $request->has('jam_mulai'),
        ];

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

        return view('admin.jadwals.create', compact('moduls', 'dosens', 'tipeKegiatan', 'slotJam', 'activeSemester', 'modulMataKuliahMap', 'preset'));
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
     * Hapus jadwal (Soft Delete).
     */
    public function destroy(Jadwal $jadwal)
    {
        $nama = $jadwal->nama_kuliah;
        $jadwal->delete();

        return back()->with('success', "Jadwal '{$nama}' berhasil dipindahkan ke kotak sampah (soft delete).");
    }

    /**
     * Hapus banyak jadwal sekaligus (Bulk Soft Delete).
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:jadwals,id'],
        ]);

        $count = Jadwal::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Sebanyak {$count} jadwal perkuliahan berhasil dipindahkan ke kotak sampah (soft delete).");
    }

    /**
     * Pulihkan jadwal dari kotak sampah (Restore).
     */
    public function restore($id)
    {
        $jadwal = Jadwal::onlyTrashed()->findOrFail($id);
        $jadwal->restore();

        return back()->with('success', "Jadwal '{$jadwal->nama_kuliah}' berhasil dipulihkan dari kotak sampah.");
    }

    /**
     * Hapus permanen jadwal dari database (Force Delete).
     */
    public function forceDelete($id)
    {
        $jadwal = Jadwal::withTrashed()->findOrFail($id);
        $nama = $jadwal->nama_kuliah;
        $jadwal->forceDelete();

        return back()->with('success', "Jadwal '{$nama}' berhasil dihapus secara permanen.");
    }

    /**
     * Pulihkan banyak jadwal sekaligus dari kotak sampah (Bulk Restore).
     */
    public function bulkRestore(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $count = Jadwal::onlyTrashed()->whereIn('id', $validated['ids'])->restore();

        return back()->with('success', "Sebanyak {$count} jadwal perkuliahan berhasil dipulihkan.");
    }

    /**
     * Hapus permanen banyak jadwal sekaligus dari kotak sampah (Bulk Force Delete).
     */
    public function bulkForceDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $jadwals = Jadwal::withTrashed()->whereIn('id', $validated['ids'])->get();
        $count = 0;
        foreach ($jadwals as $j) {
            $j->forceDelete();
            $count++;
        }

        return back()->with('success', "Sebanyak {$count} jadwal perkuliahan berhasil dihapus secara permanen.");
    }

    /**
     * Kosongkan seluruh isi kotak sampah.
     */
    public function emptyTrash()
    {
        $jadwals = Jadwal::onlyTrashed()->get();
        $count = 0;
        foreach ($jadwals as $j) {
            $j->forceDelete();
            $count++;
        }

        return back()->with('success', "Kotak sampah berhasil dikosongkan ({$count} data dihapus permanen).");
    }
}

