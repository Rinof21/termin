@extends('layouts.app', ['title' => 'Tambah Mata Kuliah - ' . $modul->nama])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.moduls.index') }}" class="text-slate-400 hover:text-slate-600">Data Modul</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" class="text-slate-400 hover:text-slate-600">{{ $modul->kode }}</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah Mata Kuliah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-lg"
                      style="background-color: {{ $modul->warna }}18; color: {{ $modul->warna }}; border: 1px solid {{ $modul->warna }}40;">
                    {{ $modul->kode }}
                </span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Mata Kuliah Baru</h1>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Menambahkan topik/materi perkuliahan baru ke dalam modul <strong>{{ $modul->nama }}</strong></p>
        </div>
        <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.moduls.mata-kuliahs.store', $modul->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Kode & Nama Mata Kuliah -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="kode" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode / No. Materi
                    </label>
                    <input type="text" name="kode" id="kode" value="{{ old('kode') }}"
                           placeholder="Contoh: {{ $modul->kode }}-01"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('kode') border-rose-300 @enderror">
                    @error('kode')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Mata Kuliah / Topik Pembelajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                           placeholder="Contoh: Praktikum Histologi Jaringan Dasar, Evidence Based Medicine, TBL Kasus 1"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nama') border-rose-300 @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tipe Kegiatan & Beban Jam -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tipe_kegiatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tipe Kegiatan Pembelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe_kegiatan" id="tipe_kegiatan" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tipe_kegiatan') border-rose-300 @enderror">
                        @foreach($tipeKegiatan as $tipe)
                            <option value="{{ $tipe }}" {{ old('tipe_kegiatan', 'Kuliah') === $tipe ? 'selected' : '' }}>
                                {{ $tipe }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="beban_jam" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Beban Jam / Durasi
                    </label>
                    <input type="text" name="beban_jam" id="beban_jam" value="{{ old('beban_jam', '2 JP (100 Menit)') }}"
                           placeholder="Contoh: 1 JP (50 Menit), 2 JP (100 Menit), 3 JP (150 Menit)"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Dosen Pengampu & Dosen Pendamping -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="dosen_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Dosen Pengampu Utama
                    </label>
                    <select name="dosen_id" id="dosen_id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="">-- Pilih Dosen Pengampu --</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                [{{ $dosen->kode }}] {{ $dosen->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-data="dosenPendampingPicker(@js($dosens->map(fn($d) => ['id' => $d->id, 'kode' => $d->kode, 'nama' => $d->nama])), @js(old('dosen_pendamping', '')))">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="dosen_pendamping_select" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Dosen Pendamping / Tutor Tim <span class="text-slate-400 font-normal lowercase">(data dosen)</span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Bisa pilih lebih dari 1</span>
                    </div>

                    <!-- Hidden input untuk menyimpan dan mengirimkan inisial yang dipilih (contoh: "RK, ERY") -->
                    <input type="hidden" name="dosen_pendamping" :value="selected.join(', ')">

                    <div class="space-y-2">
                        <!-- Dropdown pilih data dosen -->
                        <select id="dosen_pendamping_select" @change="addDosen($event.target.value); $event.target.value = '';"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                            <option value="">+ Tambah Dosen Pendamping dari Data Dosen...</option>
                            <template x-for="d in availableDosens" :key="d.kode">
                                <option :value="d.kode" x-text="'[' + d.kode + '] ' + d.nama"></option>
                            </template>
                        </select>

                        <!-- Daftar Badge Dosen Terpilih -->
                        <div class="flex flex-wrap items-center gap-1.5 min-h-[38px] p-2 bg-slate-50/70 border border-dashed border-slate-200 rounded-xl">
                            <template x-for="(code, idx) in selected" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-semibold shadow-2xs">
                                    <span class="font-mono font-bold" x-text="'[' + code + ']'"></span>
                                    <span class="text-[11px] font-medium text-slate-600 truncate max-w-[150px]" x-text="getDosenName(code)"></span>
                                    <button type="button" @click="removeDosen(idx)" class="text-indigo-400 hover:text-rose-600 transition ml-0.5" title="Hapus">
                                        <i class="bi bi-x-circle-fill text-xs"></i>
                                    </button>
                                </span>
                            </template>
                            <span x-show="selected.length === 0" class="text-xs text-slate-400 italic px-1">
                                Belum ada dosen pendamping dipilih (opsional).
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ruangan Default -->
            <div>
                <label for="ruangan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Ruangan / Lokasi Default
                </label>
                <input type="text" name="ruangan" id="ruangan" value="{{ old('ruangan') }}"
                       placeholder="Contoh: Ruang Kuliah 201, Lab Histologi Lt. 2, Auditorium FK"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Deskripsi / Capaian Pembelajaran -->
            <div>
                <label for="deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Deskripsi Materi / Sasaran Pembelajaran
                </label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                          placeholder="Ringkasan materi, capaian pembelajaran (LO), atau instruksi praktikum..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">{{ old('deskripsi') }}</textarea>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-semibold text-sm rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="bi bi-check-lg text-base"></i>
                    <span>Simpan Mata Kuliah</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function dosenPendampingPicker(dosenList, initialValue = '') {
        return {
            dosenList: dosenList || [],
            selected: initialValue ? initialValue.split(',').map(s => s.trim()).filter(s => s.length > 0) : [],
            get availableDosens() {
                return this.dosenList.filter(d => !this.selected.includes(d.kode));
            },
            addDosen(kode) {
                if (kode && !this.selected.includes(kode)) {
                    this.selected.push(kode);
                }
            },
            removeDosen(idx) {
                this.selected.splice(idx, 1);
            },
            getDosenName(kode) {
                const found = this.dosenList.find(d => d.kode === kode);
                return found ? found.nama : kode;
            }
        };
    }
</script>
@endsection

