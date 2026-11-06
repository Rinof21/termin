<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Modul;
use App\Models\Setting;
use App\Models\Termin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicScheduleController extends Controller
{
    /**
     * Dashboard Publik: Menampilkan Kalender Termin & Matriks Jadwal Perkuliahan.
     */
    public function index(Request $request)
    {
        \Carbon\Carbon::setLocale('id');
        app()->setLocale('id');

        $activeSemester = Setting::getActiveSemester();
        $semesterFilter = $request->query('semester', $activeSemester);
        $currentView = $request->query('view', 'calendar'); // 'calendar' (default homepage) atau 'matrix'
        $terminId = $request->query('termin_id');
        // Default tercentang: Pisah kolom per kelompok A.1 / A.2
        $splitKelompok = $request->boolean('split_kelompok', true);

        // Ambil Termin yang sesuai
        if ($terminId) {
            $currentTermin = Termin::find($terminId);
        } else {
            $currentTermin = Termin::where('tipe_semester', $semesterFilter)
                ->where('is_aktif', true)
                ->first()
                ?: Termin::where('tipe_semester', $semesterFilter)->first();
        }

        $termins = Termin::where('tipe_semester', $semesterFilter)->orderBy('nomor')->get();

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

        // Tentukan hari aktif: jika user memilih hari, pakai pilihan user.
        // Jika tidak ada di query, dan minggu terpilih adalah minggu hari ini, maka langsung buka HARI INI!
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

                // Cari termin yang mencakup tanggal ini jika ada
                $matchingTermin = Termin::where('tipe_semester', $semesterFilter)
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

                // Jika user tidak memilih 'hari' secara spesifik, gunakan hari dari tanggal kalender
                if (!$request->has('hari')) {
                    $hariSlug = $daySlugMap[$targetDate->dayOfWeek] ?? 'senin';
                }
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
        $dosens = Dosen::orderBy('nama')->get();
        $kelompoks = Jadwal::whereHas('modul', function ($q) use ($semesterFilter) {
                $q->where('tipe_semester', $semesterFilter);
            })
            ->whereNotNull('kelompok')
            ->distinct()
            ->pluck('kelompok')
            ->all();

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

        // Ambil Jadwal pada tanggal terpilih untuk Matriks
        $jadwalsOnDate = Jadwal::with(['modul', 'dosen', 'mataKuliah'])
            ->whereDate('tanggal', $selectedDate)
            ->get();

        // Pemetaan Grid Matriks: $grid[slot_idx][col_idx]
        $grid = [];
        $occupied = [];

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

                $matchedJadwal = null;
                foreach ($jadwalsOnDate as $j) {
                    if ($j->modul_id != $col['modul_id']) {
                        continue;
                    }

                    if (!empty($col['kelompok'])) {
                        $jKelompok = trim($j->kelompok ?? '');
                        if (!empty($jKelompok) && 
                            $jKelompok !== 'Semua Kelompok' && 
                            $jKelompok !== 'Semua' && 
                            stripos($jKelompok, $col['kelompok']) === false) {
                            continue;
                        }
                    }

                    $jMulai = substr($j->jam_mulai, 0, 5);
                    $jSelesai = substr($j->jam_selesai, 0, 5);

                    if ($jMulai < $slotSelesai && $jSelesai > $slotMulai) {
                        $matchedJadwal = $j;
                        break;
                    }
                }

                if ($matchedJadwal) {
                    $rowspan = 1;
                    $maxSlotIndex = ($s < 5) ? 4 : 8; // Batasi sebelum ISHOMA
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

        // Statistik singkat untuk portal publik
        $totalModulsCount = $moduls->count();
        $totalDosensCount = $dosens->count();
        $totalJadwalsCount = Jadwal::whereHas('modul', function ($q) use ($semesterFilter) {
            $q->where('tipe_semester', $semesterFilter);
        })->count();

        return view('public.dashboard', compact(
            'activeSemester',
            'semesterFilter',
            'currentView',
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
            'kelompoks',
            'splitKelompok',
            'totalModulsCount',
            'totalDosensCount',
            'totalJadwalsCount'
        ));
    }

    /**
     * JSON Endpoint Publik untuk Kalender FullCalendar.js.
     */
    public function events(Request $request)
    {
        $activeSemester = Setting::getActiveSemester();
        $semesterFilter = $request->query('semester', $activeSemester);
        $modulId = $request->query('modul_id');
        $dosenId = $request->query('dosen_id');
        $kelompok = $request->query('kelompok');

        $jadwals = Jadwal::with('modul', 'dosen')
            ->whereHas('modul', function ($q) use ($semesterFilter) {
                if ($semesterFilter && $semesterFilter !== 'Semua') {
                    $q->where('tipe_semester', $semesterFilter);
                }
            })
            ->when($modulId, function ($query, $modulId) {
                return $query->where('modul_id', $modulId);
            })
            ->when($dosenId, function ($query, $dosenId) {
                return $query->forDosen($dosenId);
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

            $titlePrefix = '';
            if ($dosenInisial) {
                $titlePrefix .= "[{$dosenInisial}] ";
            }
            if ($j->kelompok && $j->kelompok !== 'Semua Kelompok') {
                $titlePrefix .= "[{$j->kelompok}] ";
            }

            return [
                'id' => $j->id,
                'title' => $titlePrefix . $j->nama_kuliah,
                'start' => $start,
                'end' => $end,
                'backgroundColor' => $warna,
                'borderColor' => $warna,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'nama_kuliah' => $j->nama_kuliah,
                    'tipe_kegiatan' => $j->tipe_kegiatan,
                    'modul_id' => $j->modul_id,
                    'modul_nama' => $j->modul->nama ?? '-',
                    'modul_kode' => $j->modul->kode ?? '-',
                    'modul_warna' => $warna,
                    'dosen_id' => $j->dosen_id,
                    'dosen' => $dosenLabel,
                    'dosen_utama' => $dosenUtama,
                    'dosen_pendamping' => $pendampingList,
                    'dosen_all_labels' => $allLabels,
                    'dosen_inisial' => $dosenInisial,
                    'ruangan' => $j->ruangan ?? '-',
                    'kelompok' => $j->kelompok ?? 'Semua Kelompok',
                    'catatan' => $j->catatan ?? '-',
                    'jam' => substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5) . ' WIB',
                    'tanggal_indo' => $j->tanggal->translatedFormat('l, d F Y'),
                ]
            ];
        });

        return response()->json($events);
    }
}

