<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Termin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    /**
     * Tampilkan daftar master tahun semester.
     */
    public function index(Request $request)
    {
        $activeSemester = Setting::getActiveSemester();
        $activeTahunSemester = Setting::getActiveTahunSemester();
        $search = $request->query('search');

        $semesters = Semester::withCount('termins', 'moduls')
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode', 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.semesters.index', compact('semesters', 'search', 'activeSemester', 'activeTahunSemester'));
    }

    /**
     * Aktifkan salah satu semester di sistem.
     */
    public function activate(Semester $semester)
    {
        Semester::setActive($semester);

        // Cari termin yang khusus terhubung dengan tahun_semester ini jika ada
        $termin = Termin::where('tahun_semester', $semester->kode)->where('nomor', 4)->first()
            ?: Termin::where('tahun_semester', $semester->kode)->first();

        if ($termin) {
            Termin::setActive($termin);
        }

        return redirect()->back()->with(
            'success',
            "{$semester->nama} ({$semester->kode}) berhasil diaktifkan sebagai semester akademik berjalan!"
        );
    }

    /**
     * Form tambah semester baru.
     */
    public function create()
    {
        // Prediksi kode semester berikutnya
        $latest = Semester::orderBy('kode', 'desc')->first();
        $suggestedCode = '2027.1';
        $suggestedName = 'Semester Ganjil 2027/2028';
        $suggestedYear = '2027/2028';
        $suggestedType = 'Ganjil';

        if ($latest) {
            $parts = explode('.', $latest->kode);
            $th = (int) ($parts[0] ?? 2026);
            $sub = (int) ($parts[1] ?? 1);

            if ($sub === 1) {
                $suggestedCode = "{$th}.2";
                $suggestedType = 'Genap';
                $suggestedName = "Semester Genap {$th}/" . ($th + 1);
                $suggestedYear = "{$th}/" . ($th + 1);
            } else {
                $nextTh = $th + 1;
                $suggestedCode = "{$nextTh}.1";
                $suggestedType = 'Ganjil';
                $suggestedName = "Semester Ganjil {$nextTh}/" . ($nextTh + 1);
                $suggestedYear = "{$nextTh}/" . ($nextTh + 1);
            }
        }

        return view('admin.semesters.create', compact('suggestedCode', 'suggestedName', 'suggestedYear', 'suggestedType'));
    }

    /**
     * Simpan semester baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:semesters,kode',
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $isAktif = $request->boolean('is_aktif');
        $validated['is_aktif'] = false;

        $semester = Semester::create($validated);

        if ($isAktif) {
            Semester::setActive($semester);
        }

        return redirect()->route('admin.semesters.index')
            ->with('success', "Semester {$semester->kode} ({$semester->nama}) berhasil ditambahkan.");
    }

    /**
     * Form edit semester.
     */
    public function edit(Semester $semester)
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    /**
     * Perbarui data semester.
     */
    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('semesters')->ignore($semester->id)],
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $isAktif = $request->boolean('is_aktif');
        unset($validated['is_aktif']);

        $semester->update($validated);

        if ($isAktif && !$semester->is_aktif) {
            Semester::setActive($semester);
        }

        return redirect()->route('admin.semesters.index')
            ->with('success', "Semester {$semester->kode} berhasil diperbarui.");
    }

    /**
     * Hapus semester.
     */
    public function destroy(Semester $semester)
    {
        if ($semester->is_aktif) {
            return redirect()->back()->with('error', "Semester {$semester->kode} sedang aktif dan tidak dapat dihapus.");
        }

        $kode = $semester->kode;
        $semester->delete();

        return redirect()->route('admin.semesters.index')
            ->with('success', "Semester {$kode} berhasil dihapus.");
    }

    /**
     * Hapus beberapa semester sekaligus (Bulk Delete).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:semesters,id',
        ]);

        $ids = $request->input('ids');
        $activeCode = Setting::getActiveTahunSemester();

        $validIds = Semester::whereIn('id', $ids)
            ->where('is_aktif', false)
            ->where('kode', '!=', $activeCode)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            return redirect()->back()->with('error', 'Semester yang dipilih sedang aktif dan tidak dapat dihapus.');
        }

        $count = Semester::whereIn('id', $validIds)->delete();

        return redirect()->back()->with('success', "Sebanyak {$count} semester berhasil dihapus.");
    }
}
