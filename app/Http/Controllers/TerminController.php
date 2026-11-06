<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Modul;
use App\Models\Setting;
use App\Models\Termin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TerminController extends Controller
{
    /**
     * Tampilkan daftar jadwal termin per semester.
     */
    public function index(Request $request)
    {
        $activeSemester = Setting::getActiveSemester();
        $activeTermin = Termin::getActiveTermin();
        $tipeFilter = $request->query('tipe', $activeSemester);
        $search = $request->query('search');

        $counts = [
            'Semua' => Termin::count(),
            'Ganjil' => Termin::where('tipe_semester', 'Ganjil')->count(),
            'Genap' => Termin::where('tipe_semester', 'Genap')->count(),
        ];

        $termins = Termin::withCount('moduls')
            ->when($tipeFilter && $tipeFilter !== 'Semua', function ($q) use ($tipeFilter) {
                return $q->where('tipe_semester', $tipeFilter);
            })
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->orderBy('tipe_semester', 'desc')
            ->orderBy('nomor', 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.termins.index', compact(
            'termins',
            'activeSemester',
            'activeTermin',
            'tipeFilter',
            'counts',
            'search'
        ));
    }

    /**
     * Tampilkan halaman laporan beban mengajar dosen selama periode termin (format spreadsheet & rincian per minggu).
     */
    public function show(Request $request, Termin $termin)
    {
        $defaultTargetBeban = (int) $request->query('target_beban', 10);
        $daftarMinggu = $termin->daftar_minggu;
        $dosens = Dosen::orderBy('nama')->get();

        $startDate = $termin->tanggal_mulai ? $termin->tanggal_mulai->format('Y-m-d') : null;
        $endDate = $termin->tanggal_selesai ? $termin->tanggal_selesai->format('Y-m-d') : null;

        $jadwals = Jadwal::with(['modul', 'dosen'])
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Siapkan struktur data laporan dosen
        $laporanDosen = [];
        $weeklyTotals = [];
        foreach ($daftarMinggu as $m) {
            $weeklyTotals[$m['nomor']] = 0;
        }

        foreach ($dosens as $dosen) {
            $mingguCounts = [];
            $jadwalList = [];
            foreach ($daftarMinggu as $m) {
                $mingguCounts[$m['nomor']] = 0;
            }

            foreach ($jadwals as $j) {
                $isTeaching = ($j->dosen_id == $dosen->id);
                if (!$isTeaching && !empty($j->dosen_pendamping)) {
                    $dp = strtolower($j->dosen_pendamping);
                    $kode = strtolower(trim($dosen->kode));
                    $nama = strtolower(trim($dosen->nama));
                    if (str_contains($dp, $kode) || (!empty($nama) && str_contains($dp, $nama))) {
                        $isTeaching = true;
                    }
                }

                if ($isTeaching) {
                    $jadwalList[] = $j;
                    foreach ($daftarMinggu as $m) {
                        if ($j->tanggal >= $m['tanggal_mulai'] && $j->tanggal <= $m['tanggal_selesai']) {
                            $mingguCounts[$m['nomor']]++;
                            $weeklyTotals[$m['nomor']]++;
                            break;
                        }
                    }
                }
            }

            $totalSesi = array_sum($mingguCounts);
            $selisih = $totalSesi - $defaultTargetBeban;

            $laporanDosen[] = [
                'dosen_id' => $dosen->id,
                'nama' => $dosen->nama,
                'kode' => $dosen->kode,
                'nip' => $dosen->nip,
                'status' => 'Aktif',
                'minggu' => $mingguCounts,
                'total' => $totalSesi,
                'target' => $defaultTargetBeban,
                'selisih' => $selisih,
                'catatan' => $selisih < 0 ? 'Kurang ' . abs($selisih) . ' sesi' : ($selisih > 0 ? 'Kelebihan +' . $selisih . ' sesi' : 'Sesuai target beban'),
                'jadwals' => $jadwalList,
            ];
        }

        // Hitung ringkasan statistik
        $totalDosenAktif = count($dosens);
        $dosenMengajarCount = collect($laporanDosen)->where('total', '>', 0)->count();
        $totalSeluruhSesi = array_sum($weeklyTotals);
        $rataRataSesi = $dosenMengajarCount > 0 ? round($totalSeluruhSesi / $dosenMengajarCount, 1) : 0;
        $bebanTertinggi = collect($laporanDosen)->sortByDesc('total')->first();

        // Rincian jadwal perkuliahan per minggu untuk tab rincian
        $jadwalPerMinggu = [];
        foreach ($daftarMinggu as $m) {
            $jadwalPerMinggu[$m['nomor']] = $jadwals->filter(function ($j) use ($m) {
                return $j->tanggal >= $m['tanggal_mulai'] && $j->tanggal <= $m['tanggal_selesai'];
            })->values();
        }

        $jumlahPekanAktif = 20;

        return view('admin.termins.show', compact(
            'termin',
            'daftarMinggu',
            'laporanDosen',
            'weeklyTotals',
            'totalSeluruhSesi',
            'totalDosenAktif',
            'dosenMengajarCount',
            'rataRataSesi',
            'bebanTertinggi',
            'jadwalPerMinggu',
            'defaultTargetBeban',
            'jumlahPekanAktif'
        ));
    }

    /**
     * Export laporan beban mengajar dosen termin ke file CSV Excel.
     */
    public function export(Request $request, Termin $termin)
    {
        $defaultTargetBeban = (int) $request->query('target_beban', 10);
        $daftarMinggu = $termin->daftar_minggu;
        $dosens = Dosen::orderBy('nama')->get();

        $startDate = $termin->tanggal_mulai ? $termin->tanggal_mulai->format('Y-m-d') : null;
        $endDate = $termin->tanggal_selesai ? $termin->tanggal_selesai->format('Y-m-d') : null;

        $jadwals = Jadwal::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
        })->get();

        $filename = 'Laporan_Beban_Mengajar_' . str_replace(' ', '_', $termin->nama) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($termin, $daftarMinggu, $dosens, $jadwals, $defaultTargetBeban) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM untuk Excel

            // Baris 1: Header Dokumen
            fputcsv($file, [strtoupper($termin->nama), '', 'Total Minggu', count($daftarMinggu), 'Jumlah pekan aktif semester berjalan', 20]);
            fputcsv($file, []);

            // Baris Header Tabel
            $headerRow = ['No.', 'Nama', 'ID', 'Status'];
            foreach ($daftarMinggu as $m) {
                $headerRow[] = $m['label'];
            }
            $headerRow[] = 'Total';
            $headerRow[] = 'Kekurangan atau kelebihan Beban Termin ini';
            $headerRow[] = 'Catatan';
            fputcsv($file, $headerRow);

            $no = 1;
            $sumWeeks = array_fill(1, count($daftarMinggu), 0);
            $grandTotal = 0;

            foreach ($dosens as $dosen) {
                $weekCells = [];
                $totalDosen = 0;

                foreach ($daftarMinggu as $m) {
                    $cnt = 0;
                    foreach ($jadwals as $j) {
                        $isTeaching = ($j->dosen_id == $dosen->id);
                        if (!$isTeaching && !empty($j->dosen_pendamping)) {
                            $dp = strtolower($j->dosen_pendamping);
                            if (str_contains($dp, strtolower(trim($dosen->kode))) || str_contains($dp, strtolower(trim($dosen->nama)))) {
                                $isTeaching = true;
                            }
                        }
                        if ($isTeaching && $j->tanggal >= $m['tanggal_mulai'] && $j->tanggal <= $m['tanggal_selesai']) {
                            $cnt++;
                        }
                    }
                    $weekCells[] = $cnt > 0 ? $cnt : '-';
                    if ($cnt > 0) {
                        $sumWeeks[$m['nomor']] += $cnt;
                    }
                    $totalDosen += $cnt;
                }

                // Lewati dosen yang total beban mengajarnya 0
                if ($totalDosen <= 0 && $request->query('all') != '1') {
                    continue;
                }

                $selisih = $totalDosen - $defaultTargetBeban;
                $row = array_merge([$no++, $dosen->nama, $dosen->kode, 'Aktif'], $weekCells, [
                    $totalDosen,
                    $selisih,
                    $selisih < 0 ? 'Kurang ' . abs($selisih) . ' sesi' : ($selisih > 0 ? 'Kelebihan +' . $selisih . ' sesi' : 'Sesuai target beban')
                ]);
                $grandTotal += $totalDosen;

                fputcsv($file, $row);
            }

            // Baris Total Bawah
            $totalRow = ['', 'TOTAL KESELURUHAN', '', ''];
            foreach ($daftarMinggu as $m) {
                $totalRow[] = $sumWeeks[$m['nomor']];
            }
            $totalRow[] = $grandTotal;
            $totalRow[] = '';
            $totalRow[] = '';
            fputcsv($file, $totalRow);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Aktifkan salah satu Termin sebagai termin aktif saat ini.
     */
    public function activate(Request $request, Termin $termin)
    {
        Termin::setActive($termin);

        return redirect()->back()->with(
            'success',
            "{$termin->nama} ({$termin->tipe_semester} {$termin->tahun_ajaran}) berhasil diaktifkan sebagai jadwal termin akademik berjalan!"
        );
    }

    /**
     * Tampilkan form tambah termin baru.
     */
    public function create()
    {
        $activeSemester = Setting::getActiveSemester();
        $defaultNomor = 4;

        return view('admin.termins.create', compact('activeSemester', 'defaultNomor'));
    }

    /**
     * Simpan data termin baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor' => 'required|integer|min:1|max:20',
            'nama' => 'required|string|max:100',
            'tipe_semester' => 'required|in:Ganjil,Genap',
            'tahun_semester' => 'nullable|string|max:20',
            'tahun_ajaran' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'jumlah_minggu' => 'required|integer|min:1|max:12',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if (empty($validated['tahun_semester'])) {
            $th = substr($validated['tahun_ajaran'] ?? '2026', 0, 4);
            $validated['tahun_semester'] = $th . ($validated['tipe_semester'] === 'Genap' ? '.2' : '.1');
        }

        $jumlahMinggu = (int) $validated['jumlah_minggu'];
        $start = Carbon::parse($validated['tanggal_mulai']);

        // Jika tanggal selesai tidak diisi manual, hitung otomatis (start + N minggu - 1 hari)
        if (empty($validated['tanggal_selesai'])) {
            $validated['tanggal_selesai'] = (clone $start)->addWeeks($jumlahMinggu)->subDay()->format('Y-m-d');
        }

        $isAktif = $request->boolean('is_aktif');
        $validated['is_aktif'] = false; // default false dahulu

        $termin = Termin::create($validated);

        if ($isAktif) {
            Termin::setActive($termin);
        }

        return redirect()->route('admin.termins.index', ['tipe' => $termin->tipe_semester])
            ->with('success', "{$termin->nama} berhasil ditambahkan.");
    }

    /**
     * Tampilkan form edit termin.
     */
    public function edit(Termin $termin)
    {
        return view('admin.termins.edit', compact('termin'));
    }

    /**
     * Perbarui data termin.
     */
    public function update(Request $request, Termin $termin)
    {
        $validated = $request->validate([
            'nomor' => 'required|integer|min:1|max:20',
            'nama' => 'required|string|max:100',
            'tipe_semester' => 'required|in:Ganjil,Genap',
            'tahun_semester' => 'nullable|string|max:20',
            'tahun_ajaran' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'jumlah_minggu' => 'required|integer|min:1|max:12',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if (empty($validated['tahun_semester'])) {
            $th = substr($validated['tahun_ajaran'] ?? '2026', 0, 4);
            $validated['tahun_semester'] = $th . ($validated['tipe_semester'] === 'Genap' ? '.2' : '.1');
        }

        $jumlahMinggu = (int) $validated['jumlah_minggu'];
        $start = Carbon::parse($validated['tanggal_mulai']);

        if (empty($validated['tanggal_selesai'])) {
            $validated['tanggal_selesai'] = (clone $start)->addWeeks($jumlahMinggu)->subDay()->format('Y-m-d');
        }

        $isAktif = $request->boolean('is_aktif');
        unset($validated['is_aktif']);

        $termin->update($validated);

        if ($isAktif && !$termin->is_aktif) {
            Termin::setActive($termin);
        }

        return redirect()->route('admin.termins.index', ['tipe' => $termin->tipe_semester])
            ->with('success', "{$termin->nama} berhasil diperbarui.");
    }

    /**
     * Hapus satu termin.
     */
    public function destroy(Termin $termin)
    {
        $nama = $termin->nama;
        $tipe = $termin->tipe_semester;

        if ($termin->is_aktif) {
            return redirect()->back()->with('error', "{$nama} sedang aktif dan tidak dapat dihapus. Silakan aktifkan termin lain terlebih dahulu.");
        }

        // Lepaskan relasi termin dari modul
        Modul::where('termin_id', $termin->id)->update(['termin_id' => null]);

        $termin->delete();

        return redirect()->route('admin.termins.index', ['tipe' => $tipe])
            ->with('success', "{$nama} berhasil dihapus.");
    }

    /**
     * Hapus beberapa termin sekaligus (Bulk Delete).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:termins,id',
        ]);

        $ids = $request->input('ids');
        $activeTermin = Termin::where('is_aktif', true)->first();

        // Jangan hapus termin yang sedang aktif
        $validIds = array_filter($ids, function ($id) use ($activeTermin) {
            return !$activeTermin || $id != $activeTermin->id;
        });

        if (empty($validIds)) {
            return redirect()->back()->with('error', 'Termin yang dipilih sedang berstatus aktif dan tidak dapat dihapus.');
        }

        Modul::whereIn('termin_id', $validIds)->update(['termin_id' => null]);
        $count = Termin::whereIn('id', $validIds)->delete();

        return redirect()->back()->with('success', "Sebanyak {$count} termin berhasil dihapus.");
    }
}

