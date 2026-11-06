@extends('layouts.app', ['title' => 'Ubah Modul Kedokteran'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.moduls.index') }}" class="text-slate-400 hover:text-slate-600">Data Modul</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Ubah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Data Modul</h1>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui informasi modul {{ $modul->nama }}</p>
        </div>
        <a href="{{ route('admin.moduls.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.moduls.update', $modul->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Kode & Nama Modul -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="kode" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode Modul <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode" id="kode" required value="{{ old('kode', $modul->kode) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('kode') border-rose-300 @enderror">
                    @error('kode')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Modul Kedokteran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama', $modul->nama) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nama') border-rose-300 @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tipe Semester (Ganjil / Genap) -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tipe Semester <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-slate-50 border-slate-200 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                        <input type="radio" name="tipe_semester" value="Ganjil" {{ old('tipe_semester', $modul->tipe_semester) === 'Ganjil' ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-sun-fill text-indigo-600"></i>
                                <span>Semester Ganjil</span>
                            </span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Termin perkuliahan semester ganjil (Termin 1 - 4)</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-slate-50 border-slate-200 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                        <input type="radio" name="tipe_semester" value="Genap" {{ old('tipe_semester', $modul->tipe_semester) === 'Genap' ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="bi bi-moon-stars-fill text-emerald-600"></i>
                                <span>Semester Genap</span>
                            </span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Termin perkuliahan semester genap (Termin 1 - 4)</span>
                        </div>
                    </label>
                </div>
                @error('tipe_semester')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Semester, Tahun Semester & Termin -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    @php
                        $currentSemester = old('tipe_semester', $modul->tipe_semester);
                        $defaultTerminId = old('termin_id', $modul->termin_id);
                        if (!$defaultTerminId) {
                            $activeTerminObj = $termins->where('tipe_semester', $currentSemester)->where('is_aktif', true)->first()
                                ?: $termins->where('tipe_semester', $currentSemester)->where('nomor', 4)->first()
                                ?: $termins->where('tipe_semester', $currentSemester)->first();
                            $defaultTerminId = $activeTerminObj->id ?? null;
                        }
                    @endphp
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="termin_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Pilih Jadwal Termin <span class="text-rose-500">*</span>
                        </label>
                        <span id="terminBadgeAktif" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded-full">
                            Tersedia di Semester
                        </span>
                    </div>
                    <select name="termin_id" id="termin_id" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        @foreach($termins as $t)
                            <option value="{{ $t->id }}" 
                                    data-semester="{{ $t->tipe_semester }}"
                                    data-tahun-semester="{{ $t->kode_tahun_semester }}"
                                    data-nomor="{{ $t->nomor }}"
                                    data-is-aktif="{{ $t->is_aktif ? '1' : '0' }}"
                                    data-nama="{{ $t->nama }}"
                                    {{ $defaultTerminId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama }} ({{ $t->kode_tahun_semester }}){{ $t->is_aktif ? ' • AKTIF' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tahun_semester" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tahun Semester <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_semester" id="tahun_semester" required value="{{ old('tahun_semester', $modul->kode_tahun_semester) }}"
                           placeholder="Contoh: 2026.1, 2026.2"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-brand-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="semester" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Semester Akademik <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="semester" id="semester" required value="{{ old('semester', $modul->semester) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Warna Tampilan & Koordinator -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="warna" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Warna Event Kalender <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="warna" id="warna" value="{{ old('warna', $modul->warna) }}"
                               class="w-12 h-10 p-1 rounded-xl border border-slate-200 cursor-pointer">
                        <input type="text" id="warnaHex" readonly value="{{ $modul->warna }}"
                               class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-700">
                    </div>
                </div>

                <div>
                    <label for="koordinator_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Dosen Koordinator Modul
                    </label>
                    <select name="koordinator_id" id="koordinator_id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="">-- Pilih Dosen Koordinator --</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('koordinator_id', $modul->koordinator_id) == $dosen->id ? 'selected' : '' }}>
                                [{{ $dosen->kode }}] {{ $dosen->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Keterangan Modul -->
            <div>
                <label for="keterangan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Keterangan Singkat
                </label>
                <textarea name="keterangan" id="keterangan" rows="2"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('keterangan', $modul->keterangan) }}</textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.moduls.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Perbarui Modul
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioGanjil = document.querySelector('input[name="tipe_semester"][value="Ganjil"]');
    const radioGenap = document.querySelector('input[name="tipe_semester"][value="Genap"]');
    const terminSelect = document.getElementById('termin_id');
    const tahunSemesterInput = document.getElementById('tahun_semester');
    const semesterInput = document.getElementById('semester');
    const badgeAktif = document.getElementById('terminBadgeAktif');
    const warnaInput = document.getElementById('warna');
    const warnaHex = document.getElementById('warnaHex');

    if (warnaInput && warnaHex) {
        warnaInput.addEventListener('input', function() {
            warnaHex.value = this.value;
        });
    }

    function getSelectedSemester() {
        const checked = document.querySelector('input[name="tipe_semester"]:checked');
        return checked ? checked.value : 'Ganjil';
    }

    function filterTermins(preferSelectedId = null) {
        if (!terminSelect) return;
        const currentSemester = getSelectedSemester();
        const options = Array.from(terminSelect.options);

        let firstVisible = null;
        let activeOption = null;
        let termin4Option = null;
        let matchingOption = null;

        options.forEach(opt => {
            const optSemester = opt.getAttribute('data-semester');
            const isMatch = (optSemester === currentSemester);
            
            if (isMatch) {
                opt.hidden = false;
                opt.disabled = false;
                if (!firstVisible) firstVisible = opt;
                if (opt.getAttribute('data-is-aktif') === '1') {
                    activeOption = opt;
                }
                if (opt.getAttribute('data-nomor') === '4') {
                    termin4Option = opt;
                }
                if (preferSelectedId && opt.value == preferSelectedId) {
                    matchingOption = opt;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
                if (opt.selected) {
                    opt.selected = false;
                }
            }
        });

        // Tentukan option mana yang dipilih
        let toSelect = matchingOption || activeOption || termin4Option || firstVisible;
        if (toSelect) {
            terminSelect.value = toSelect.value;
        }

        updateLinkedFields();
    }

    function updateLinkedFields() {
        if (!terminSelect) return;
        const selected = terminSelect.selectedOptions[0];
        if (!selected) return;

        const currentSemester = getSelectedSemester();
        const tahunSemester = selected.getAttribute('data-tahun-semester') || (currentSemester === 'Genap' ? '2026.2' : '2026.1');
        const isAktif = selected.getAttribute('data-is-aktif') === '1';

        if (tahunSemesterInput) {
            tahunSemesterInput.value = tahunSemester;
        }
        if (semesterInput) {
            semesterInput.value = currentSemester === 'Genap' ? 'Semester Genap 2026/2027' : 'Semester Ganjil 2026/2027';
        }
        if (badgeAktif) {
            if (isAktif) {
                badgeAktif.className = 'text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1';
                badgeAktif.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Termin Aktif';
            } else {
                badgeAktif.className = 'text-[10px] font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1';
                badgeAktif.innerHTML = 'Tersedia di Semester';
            }
        }
    }

    // Initial run with existing selected value
    const initialSelectedId = terminSelect ? terminSelect.value : null;
    filterTermins(initialSelectedId);

    // Event listeners
    if (radioGanjil) {
        radioGanjil.addEventListener('change', () => filterTermins());
    }
    if (radioGenap) {
        radioGenap.addEventListener('change', () => filterTermins());
    }
    if (terminSelect) {
        terminSelect.addEventListener('change', updateLinkedFields);
    }
});
</script>
@endpush
@endsection

