@extends('layouts.app', ['title' => 'Tambah Tahun Semester Baru'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.semesters.index') }}" class="text-slate-400 hover:text-slate-600">Tahun Semester</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Tahun Semester Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan periode semester akademik baru (contoh: 2027.1, 2027.2, 2028.1)</p>
        </div>
        <a href="{{ route('admin.semesters.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.semesters.store') }}" method="POST" class="space-y-6" id="semesterForm">
            @csrf

            <!-- Baris 1: Kode Semester, Nama Semester, Tipe, dan Tahun Ajaran -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3">
                    <label for="kode" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode Semester <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode" id="kode" required 
                           value="{{ old('kode', $suggestedCode ?? '2027.1') }}"
                           placeholder="Contoh: 2027.1"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-brand-700 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('kode') border-rose-300 @enderror">
                    <span class="text-[11px] text-slate-400 mt-1 block">.1 Ganjil, .2 Genap</span>
                    @error('kode')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-5">
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Semester <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" required 
                           value="{{ old('nama', $suggestedName ?? 'Semester Ganjil 2027/2028') }}"
                           placeholder="Contoh: Semester Ganjil 2027/2028"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nama') border-rose-300 @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tipe" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tipe Semester <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe" id="tipe" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tipe') border-rose-300 @enderror">
                        <option value="Ganjil" {{ old('tipe', $suggestedType ?? 'Ganjil') === 'Ganjil' ? 'selected' : '' }}>Ganjil (.1)</option>
                        <option value="Genap" {{ old('tipe', $suggestedType ?? 'Ganjil') === 'Genap' ? 'selected' : '' }}>Genap (.2)</option>
                    </select>
                    @error('tipe')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tahun_ajaran" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tahun Ajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran" required 
                           value="{{ old('tahun_ajaran', $suggestedYear ?? '2027/2028') }}"
                           placeholder="2027/2028"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tahun_ajaran') border-rose-300 @enderror">
                    @error('tahun_ajaran')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Tanggal Mulai & Tanggal Selesai Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Mulai Semester (Opsional)
                    </label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                           value="{{ old('tanggal_mulai') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="tanggal_selesai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Selesai Semester (Opsional)
                    </label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                           value="{{ old('tanggal_selesai') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Checkbox Jadikan Semester Aktif -->
            <div class="flex items-start gap-3 p-4 bg-emerald-50/60 border border-emerald-200 rounded-2xl">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="is_aktif" id="is_aktif" value="1" {{ old('is_aktif') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                </div>
                <div>
                    <label for="is_aktif" class="text-xs font-bold text-emerald-900 cursor-pointer">
                        Jadikan sebagai Tahun Semester Aktif Saat Ini
                    </label>
                    <p class="text-[11px] text-emerald-700 mt-0.5">
                        Jika dicentang, seluruh data modul dan kalender perkuliahan akan otomatis diarahkan ke semester ini.
                    </p>
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label for="keterangan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Keterangan (Opsional)
                </label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          placeholder="Tambahkan catatan khusus periode semester..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.semesters.index') }}" 
                   class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                    <i class="bi bi-check2"></i>
                    <span>Simpan Semester</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodeInput = document.getElementById('kode');
        const namaInput = document.getElementById('nama');
        const tipeSelect = document.getElementById('tipe');
        const tahunAjaranInput = document.getElementById('tahun_ajaran');

        kodeInput.addEventListener('input', function () {
            const val = this.value.trim();
            const parts = val.split('.');
            const year = parseInt(parts[0]);
            const sub = parts[1];

            if (sub === '2') {
                tipeSelect.value = 'Genap';
            } else if (sub === '1') {
                tipeSelect.value = 'Ganjil';
            }

            if (year && year > 2000) {
                const nextYear = year + 1;
                tahunAjaranInput.value = `${year}/${nextYear}`;
                namaInput.value = `Semester ${tipeSelect.value} ${year}/${nextYear}`;
            }
        });

        tipeSelect.addEventListener('change', function () {
            const val = kodeInput.value.trim();
            const year = parseInt(val.split('.')[0]) || 2027;
            const nextYear = year + 1;
            namaInput.value = `Semester ${this.value} ${year}/${nextYear}`;
        });
    });
</script>
@endsection

