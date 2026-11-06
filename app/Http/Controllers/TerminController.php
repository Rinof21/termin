<?php

namespace App\Http\Controllers;

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

