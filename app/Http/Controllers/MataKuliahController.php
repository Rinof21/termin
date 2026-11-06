<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Modul;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    /**
     * Tampilkan daftar mata kuliah / topik dalam suatu modul.
     */
    public function index(Request $request, Modul $modul)
    {
        $search = $request->query('search');
        $tipe = $request->query('tipe');

        $mataKuliahs = $modul->mataKuliahs()
            ->with('dosen')
            ->withCount('jadwals')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode', 'like', "%{$search}%")
                      ->orWhere('ruangan', 'like', "%{$search}%");
                });
            })
            ->when($tipe, function ($query, $tipe) {
                return $query->where('tipe_kegiatan', $tipe);
            })
            ->paginate(15)
            ->withQueryString();

        $dosens = Dosen::orderBy('nama')->get();

        $tipeKegiatan = [
            'Kuliah',
            'Praktikum',
            'Diskusi Kelompok (DK)',
            'Pleno',
            'Team-Based Learning (TBL)',
            'Ujian/Formatif',
            'Keterampilan Klinis (FCP)',
        ];

        return view('admin.mata_kuliahs.index', compact('modul', 'mataKuliahs', 'dosens', 'tipeKegiatan', 'search', 'tipe'));
    }

    /**
     * Tampilkan form tambah mata kuliah.
     */
    public function create(Modul $modul)
    {
        $dosens = Dosen::orderBy('nama')->get();
        $tipeKegiatan = [
            'Kuliah',
            'Praktikum',
            'Diskusi Kelompok (DK)',
            'Pleno',
            'Team-Based Learning (TBL)',
            'Ujian/Formatif',
            'Keterampilan Klinis (FCP)',
        ];

        return view('admin.mata_kuliahs.create', compact('modul', 'dosens', 'tipeKegiatan'));
    }

    /**
     * Simpan data mata kuliah baru ke dalam modul.
     */
    public function store(Request $request, Modul $modul)
    {
        $validated = $request->validate([
            'kode' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:255'],
            'tipe_kegiatan' => ['required', 'string', 'max:100'],
            'dosen_id' => ['nullable', 'exists:dosens,id'],
            'dosen_pendamping' => ['nullable', 'string', 'max:255'],
            'beban_jam' => ['nullable', 'string', 'max:50'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        if (!empty($validated['kode'])) {
            $validated['kode'] = strtoupper(trim($validated['kode']));
        }

        $modul->mataKuliahs()->create($validated);

        return redirect()->route('admin.moduls.mata-kuliahs.index', $modul->id)
            ->with('success', "Mata kuliah '{$validated['nama']}' berhasil ditambahkan ke modul {$modul->nama}.");
    }

    /**
     * Tampilkan form ubah mata kuliah.
     */
    public function edit(Modul $modul, MataKuliah $mataKuliah)
    {
        $dosens = Dosen::orderBy('nama')->get();
        $tipeKegiatan = [
            'Kuliah',
            'Praktikum',
            'Diskusi Kelompok (DK)',
            'Pleno',
            'Team-Based Learning (TBL)',
            'Ujian/Formatif',
            'Keterampilan Klinis (FCP)',
        ];

        return view('admin.mata_kuliahs.edit', compact('modul', 'mataKuliah', 'dosens', 'tipeKegiatan'));
    }

    /**
     * Perbarui data mata kuliah.
     */
    public function update(Request $request, Modul $modul, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'kode' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:255'],
            'tipe_kegiatan' => ['required', 'string', 'max:100'],
            'dosen_id' => ['nullable', 'exists:dosens,id'],
            'dosen_pendamping' => ['nullable', 'string', 'max:255'],
            'beban_jam' => ['nullable', 'string', 'max:50'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        if (!empty($validated['kode'])) {
            $validated['kode'] = strtoupper(trim($validated['kode']));
        }

        $mataKuliah->update($validated);

        return redirect()->route('admin.moduls.mata-kuliahs.index', $modul->id)
            ->with('success', "Mata kuliah '{$mataKuliah->nama}' berhasil diperbarui.");
    }

    /**
     * Duplikat satu mata kuliah di dalam modul.
     */
    public function duplicate(Modul $modul, MataKuliah $mataKuliah)
    {
        $newMk = $mataKuliah->replicate();
        $newMk->nama = $mataKuliah->nama . ' (Salinan)';
        $newMk->save();

        return redirect()->route('admin.moduls.mata-kuliahs.index', $modul->id)
            ->with('success', "Mata kuliah '{$newMk->nama}' berhasil diduplikat.");
    }

    /**
     * Hapus data mata kuliah.
     */
    public function destroy(Modul $modul, MataKuliah $mataKuliah)
    {
        $nama = $mataKuliah->nama;
        $mataKuliah->delete();

        return redirect()->route('admin.moduls.mata-kuliahs.index', $modul->id)
            ->with('success', "Mata kuliah '{$nama}' berhasil dihapus dari modul.");
    }

    /**
     * Hapus massal (Bulk Delete) mata kuliah terpilih.
     */
    public function bulkDelete(Request $request, Modul $modul)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:mata_kuliahs,id'],
        ]);

        $count = $modul->mataKuliahs()->whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Sebanyak {$count} mata kuliah berhasil dihapus sekaligus.");
    }
}

