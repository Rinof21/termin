<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Modul;
use App\Models\Setting;
use App\Models\Termin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModulController extends Controller
{
    /**
     * Tampilkan daftar modul pembelajaran kedokteran.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $activeSemester = Setting::getActiveSemester();
        $tipeFilter = $request->query('tipe', $activeSemester);

        $counts = [
            'Semua' => Modul::count(),
            'Ganjil' => Modul::where('tipe_semester', 'Ganjil')->count(),
            'Genap' => Modul::where('tipe_semester', 'Genap')->count(),
        ];

        $moduls = Modul::with('koordinator')
            ->withCount('jadwals', 'mataKuliahs')
            ->when($tipeFilter && $tipeFilter !== 'Semua', function ($query) use ($tipeFilter) {
                return $query->where('tipe_semester', $tipeFilter);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode', 'like', "%{$search}%")
                      ->orWhere('termin', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.moduls.index', compact('moduls', 'search', 'activeSemester', 'tipeFilter', 'counts'));
    }

    /**
     * Tampilkan form tambah modul.
     */
    public function create()
    {
        $dosens = Dosen::orderBy('nama')->get();
        $termins = Termin::orderBy('nomor')->get();
        $activeSemester = Setting::getActiveSemester();
        $activeTahunSemester = Setting::getActiveTahunSemester();
        $activeTermin = Termin::getActiveTermin();
        return view('admin.moduls.create', compact('dosens', 'termins', 'activeSemester', 'activeTahunSemester', 'activeTermin'));
    }

    /**
     * Simpan data modul baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:moduls,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'tipe_semester' => ['required', 'in:Ganjil,Genap'],
            'tahun_semester' => ['nullable', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:100'],
            'termin' => ['nullable', 'string', 'max:50'],
            'termin_id' => ['nullable', 'exists:termins,id'],
            'warna' => ['required', 'string', 'max:20'],
            'koordinator_id' => ['nullable', 'exists:dosens,id'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $validated['kode'] = strtoupper(trim($validated['kode']));

        if (!empty($validated['termin_id'])) {
            $t = Termin::find($validated['termin_id']);
            if ($t) {
                $validated['termin'] = $t->nama;
                if (empty($validated['tahun_semester']) && $t->tahun_semester) {
                    $validated['tahun_semester'] = $t->tahun_semester;
                }
            }
        } elseif (empty($validated['termin'])) {
            $validated['termin'] = 'Termin 4';
        }

        if (empty($validated['tahun_semester'])) {
            $validated['tahun_semester'] = '2026.' . ($validated['tipe_semester'] === 'Genap' ? '2' : '1');
        }

        Modul::create($validated);

        return redirect()->route('admin.moduls.index')
            ->with('success', 'Modul kedokteran berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah modul.
     */
    public function edit(Modul $modul)
    {
        $dosens = Dosen::orderBy('nama')->get();
        $termins = Termin::orderBy('nomor')->get();
        $activeSemester = Setting::getActiveSemester();
        $activeTahunSemester = Setting::getActiveTahunSemester();
        $activeTermin = Termin::getActiveTermin();
        return view('admin.moduls.edit', compact('modul', 'dosens', 'termins', 'activeSemester', 'activeTahunSemester', 'activeTermin'));
    }

    /**
     * Perbarui data modul.
     */
    public function update(Request $request, Modul $modul)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('moduls')->ignore($modul->id)],
            'nama' => ['required', 'string', 'max:255'],
            'tipe_semester' => ['required', 'in:Ganjil,Genap'],
            'tahun_semester' => ['nullable', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:100'],
            'termin' => ['nullable', 'string', 'max:50'],
            'termin_id' => ['nullable', 'exists:termins,id'],
            'warna' => ['required', 'string', 'max:20'],
            'koordinator_id' => ['nullable', 'exists:dosens,id'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $validated['kode'] = strtoupper(trim($validated['kode']));

        if (!empty($validated['termin_id'])) {
            $t = Termin::find($validated['termin_id']);
            if ($t) {
                $validated['termin'] = $t->nama;
                if (empty($validated['tahun_semester']) && $t->tahun_semester) {
                    $validated['tahun_semester'] = $t->tahun_semester;
                }
            }
        } elseif (empty($validated['termin'])) {
            $validated['termin'] = 'Termin 4';
        }

        if (empty($validated['tahun_semester'])) {
            $validated['tahun_semester'] = '2026.' . ($validated['tipe_semester'] === 'Genap' ? '2' : '1');
        }

        $modul->update($validated);

        return redirect()->route('admin.moduls.index')
            ->with('success', 'Data modul berhasil diperbarui.');
    }

    /**
     * Ganti semester & tahun semester aktif sistem (contoh: 2026.1, 2026.2, 2027.1, 2027.2 atau Ganjil/Genap).
     */
    public function switchSemester(Request $request)
    {
        $validated = $request->validate([
            'semester' => ['nullable', 'in:Ganjil,Genap'],
            'tahun_semester' => ['nullable', 'string', 'max:20'],
        ]);

        if (!empty($validated['tahun_semester'])) {
            $kode = trim($validated['tahun_semester']);
            Setting::setActiveTahunSemester($kode);
            $tipe = Setting::getActiveSemester();
        } else {
            $tipe = $validated['semester'] ?? 'Ganjil';
            $kode = '2026.' . ($tipe === 'Genap' ? '2' : '1');
            Setting::setActiveTahunSemester($kode);
        }

        // Sinkronkan termin aktif yang sesuai
        $termin = Termin::where(function ($q) use ($kode, $tipe) {
                $q->where('tahun_semester', $kode)
                  ->orWhere('tipe_semester', $tipe);
            })
            ->where('nomor', 4)
            ->first()
            ?: Termin::where('tipe_semester', $tipe)->first();

        if ($termin) {
            Termin::setActive($termin);
        }

        $label = Setting::formatTahunSemester($kode);

        return back()->with('success', "Tahun Semester aktif berhasil diubah menjadi: {$label}. Seluruh modul & jadwal termin disesuaikan.");
    }

    /**
     * Hapus data modul.
     */
    public function destroy(Modul $modul)
    {
        if ($modul->jadwals()->count() > 0) {
            return back()->with('error', 'Modul tidak dapat dihapus karena masih memiliki jadwal kuliah yang terhubung.');
        }

        $modul->delete();

        return redirect()->route('admin.moduls.index')
            ->with('success', 'Modul berhasil dihapus.');
    }
}

