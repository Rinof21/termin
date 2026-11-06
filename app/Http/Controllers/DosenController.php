<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DosenController extends Controller
{
    /**
     * Tampilkan daftar dosen pengampu.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $dosens = Dosen::withCount('jadwals', 'modulKoordinator')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dosens.index', compact('dosens', 'search'));
    }

    /**
     * Tampilkan form tambah dosen.
     */
    public function create()
    {
        return view('admin.dosens.create');
    }

    /**
     * Simpan data dosen baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:dosens,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['kode'] = strtoupper(trim($validated['kode']));

        Dosen::create($validated);

        return redirect()->route('admin.dosens.index')
            ->with('success', 'Data Dosen berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah data dosen.
     */
    public function edit(Dosen $dosen)
    {
        return view('admin.dosens.edit', compact('dosen'));
    }

    /**
     * Perbarui data dosen.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('dosens')->ignore($dosen->id)],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['kode'] = strtoupper(trim($validated['kode']));

        $dosen->update($validated);

        return redirect()->route('admin.dosens.index')
            ->with('success', 'Data Dosen berhasil diperbarui.');
    }

    /**
     * Hapus data dosen.
     */
    public function destroy(Dosen $dosen)
    {
        if ($dosen->jadwals()->count() > 0) {
            return back()->with('error', 'Dosen tidak dapat dihapus karena masih terdaftar pada jadwal kuliah aktif.');
        }

        $dosen->delete();

        return redirect()->route('admin.dosens.index')
            ->with('success', 'Data Dosen berhasil dihapus.');
    }

    /**
     * Hapus banyak dosen sekaligus (Bulk Delete).
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:dosens,id'],
        ]);

        $dosensWithJadwal = Dosen::whereIn('id', $validated['ids'])
            ->whereHas('jadwals')
            ->pluck('nama')
            ->toArray();

        if (!empty($dosensWithJadwal)) {
            $namaList = implode(', ', array_slice($dosensWithJadwal, 0, 3));
            if (count($dosensWithJadwal) > 3) {
                $namaList .= ' dan ' . (count($dosensWithJadwal) - 3) . ' dosen lainnya';
            }
            return back()->with('error', "Gagal menghapus: Dosen ($namaList) masih memiliki jadwal perkuliahan aktif. Hapus atau alihkan jadwal kuliah dosen tersebut terlebih dahulu.");
        }

        $count = Dosen::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Sebanyak {$count} data dosen berhasil dihapus sekaligus.");
    }

    /**
     * Daftarkan dosen luar / tidak tetap secara cepat via AJAX dari formulir jadwal perkuliahan.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20'],
        ]);

        $nama = trim($validated['nama']);
        $kode = !empty($validated['kode']) ? strtoupper(trim($validated['kode'])) : null;

        if (!$kode) {
            $kode = $this->generateUniqueKode($nama);
        } else {
            if (Dosen::where('kode', $kode)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Inisial / Kode Dosen '{$kode}' sudah digunakan oleh dosen lain. Silakan ubah inisialnya.",
                ], 422);
            }
        }

        $dosen = Dosen::create([
            'kode' => $kode,
            'nama' => $nama,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Dosen luar '{$nama}' [{$kode}] berhasil didaftarkan.",
            'dosen' => [
                'id' => $dosen->id,
                'kode' => $dosen->kode,
                'nama' => $dosen->nama,
            ],
        ]);
    }

    /**
     * Generate inisial unik dari nama dosen luar (misal: "dr. Bambang Wijaya" -> "BW" atau "BMW").
     */
    protected function generateUniqueKode(string $nama): string
    {
        // Bersihkan gelar kedokteran dan akademis umum
        $clean = preg_replace('/\b(dr|drg|prof|ir|sp|subsp|m\.kes|m\.ked|m\.sc|ph\.d|s\.ked|m\.biomed|aiv|k)\b/i', '', $nama);
        $clean = preg_replace('/[^a-zA-Z\s]/', '', $clean);
        $words = array_values(array_filter(explode(' ', trim($clean))));

        $candidate = '';
        if (count($words) >= 3) {
            $candidate = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        } elseif (count($words) === 2) {
            $candidate = substr($words[0], 0, 2) . substr($words[1], 0, 1);
        } elseif (count($words) === 1) {
            $candidate = substr($words[0], 0, 3);
        }

        $candidate = strtoupper($candidate);
        if (strlen($candidate) < 2) {
            $candidate = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 3));
        }
        if (strlen($candidate) < 2) {
            $candidate = 'DL';
        }

        $baseCandidate = $candidate;
        $counter = 1;
        while (Dosen::where('kode', $candidate)->exists()) {
            $candidate = substr($baseCandidate, 0, 16) . $counter;
            $counter++;
        }

        return $candidate;
    }
}

