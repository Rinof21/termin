@extends('layouts.app', ['title' => 'Ubah Jadwal Kuliah'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.jadwals.index') }}" class="text-slate-400 hover:text-slate-600">Jadwal Kuliah</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Ubah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Jadwal Kuliah</h1>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui waktu, ruangan, dosen, atau materi perkuliahan</p>
        </div>
        <a href="{{ route('admin.jadwals.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Kalender</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.jadwals.update', $jadwal->id) }}" method="POST" class="space-y-6"
              x-data="jadwalFormApp(@js($modulMataKuliahMap ?? []), '{{ old('modul_id', $jadwal->modul_id) }}', '{{ old('nama_kuliah', $jadwal->nama_kuliah) }}')">
            @csrf
            @method('PUT')

            <!-- Modul & Tipe Kegiatan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="modul_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Modul Pembelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="modul_id" id="modul_id" required
                            x-model="selectedModulId"
                            @change="onModulChange()"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('modul_id') border-rose-300 @enderror">
                        @foreach($moduls as $modul)
                            <option value="{{ $modul->id }}" {{ old('modul_id', $jadwal->modul_id) == $modul->id ? 'selected' : '' }}>
                                {{ $modul->kode }} - {{ $modul->nama }} ({{ $modul->termin }})
                            </option>
                        @endforeach
                    </select>
                    @error('modul_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tipe_kegiatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tipe / Bentuk Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe_kegiatan" id="tipe_kegiatan" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('tipe_kegiatan') border-rose-300 @enderror">
                        @foreach($tipeKegiatan as $tipe)
                            <option value="{{ $tipe }}" {{ old('tipe_kegiatan', $jadwal->tipe_kegiatan) == $tipe ? 'selected' : '' }}>
                                {{ $tipe }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipe_kegiatan')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nama Kuliah / Topik Pembelajaran -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="nama_kuliah" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nama Kuliah / Topik Materi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Bisa pilih dari data modul atau ketik topik baru</span>
                </div>

                <!-- Dropdown Pilih Dari Mata Kuliah Modul (Jika Ada) -->
                <div x-show="availableMataKuliahs.length > 0" x-cloak 
                     class="p-2.5 rounded-xl bg-brand-50/70 border border-brand-200/80 transition">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-brand-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-collection-fill text-brand-600"></i>
                            Pilih Dari Data Mata Kuliah Modul Ini (<span x-text="availableMataKuliahs.length"></span> materi):
                        </span>
                        <span class="text-[10px] text-brand-600 italic">Otomatis mengisi form jika dipilih</span>
                    </div>
                    <select x-model="selectedMataKuliahId" @change="onSelectMataKuliah($event.target.value)"
                            class="w-full px-3.5 py-2 bg-white border border-brand-300 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">-- Pilih Mata Kuliah yang Sudah Ada di Modul --</option>
                        <template x-for="mk in availableMataKuliahs" :key="mk.id">
                            <option :value="mk.id" x-text="mk.nama + ' (' + mk.tipe_kegiatan + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Input Ketik Nama Kuliah / Topik Baru -->
                <div>
                    <input type="text" name="nama_kuliah" id="nama_kuliah" required 
                           x-model="namaKuliah"
                           placeholder="Contoh: Praktikum Histologi-Patobiologi, Tanaman Obat Kalbar, TBL 1"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('nama_kuliah') border-rose-300 @enderror">
                    @error('nama_kuliah')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dosen Pengampu & Dosen Pendamping -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dosen_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Dosen Pengampu Utama
                    </label>
                    <select name="dosen_id" id="dosen_id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('dosen_id') border-rose-300 @enderror">
                        <option value="">-- Pilih Dosen Pengampu --</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id', $jadwal->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                [{{ $dosen->kode }}] {{ $dosen->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('dosen_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="dosenPendampingPicker(@js($dosens->map(fn($d) => ['id' => $d->id, 'kode' => $d->kode, 'nama' => $d->nama])), @js(old('dosen_pendamping', $jadwal->dosen_pendamping ?? '')))"
                     @set-dosen-pendamping.window="setFromCodes($event.detail)">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="dosen_pendamping_select" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Dosen Pengampu
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Bisa pilih lebih dari 1</span>
                    </div>

                    <!-- Hidden input untuk menyimpan dan mengirimkan inisial yang dipilih (contoh: "RK, ERY") -->
                    <input type="hidden" name="dosen_pendamping" :value="selected.join(', ')">

                    <div class="space-y-2">
                        <!-- Dropdown pilih data dosen -->
                        <select id="dosen_pendamping_select" @change="addDosen($event.target.value); $event.target.value = '';"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
                            <option value="">+ Tambah Dosen Pengampu dari Data Dosen...</option>
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
                                Belum ada dosen pengampu dipilih (opsional).
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanggal & Jam Perkuliahan Tetap S1 Kedokteran -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4"
                 x-data="multiSesiApp('{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}', '{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}')">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Waktu & Sesi Jam Kuliah Tetap</span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Sesuai kurikulum S1 Kedokteran (50 Menit per JP, ISHOMA 11.50 - 13.00)</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        9 Sesi Tetap
                    </span>
                </div>

                <!-- Tombol Pemilihan Sesi (Mendukung Lebih dari 1 Sesi) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">
                                Pilih Sesi Perkuliahan (Bisa Lebih dari 1 Sesi):
                            </span>
                            <span class="text-[11px] text-slate-500">
                                Klik sesi untuk memilih. Klik beberapa sesi untuk perkuliahan multi-JP (misal: Sesi 2 & 3 untuk 2 JP).
                            </span>
                        </div>
                        <div x-show="selected.length > 0">
                            <button type="button" @click="resetSesi()" 
                                    class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 underline transition">
                                Reset Pilihan
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-1.5 text-center">
                        <template x-for="s in sessions" :key="s.sesi">
                            <button type="button" @click="toggleSesi(s.sesi)"
                                    :class="isSelected(s.sesi) 
                                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm ring-2 ring-brand-400/50' 
                                        : 'bg-white hover:bg-brand-50 hover:border-brand-300 text-slate-700 border-slate-200'"
                                    class="p-2 rounded-xl border text-[11px] font-semibold transition relative group">
                                <span class="block text-[10px] font-bold"
                                      :class="isSelected(s.sesi) ? 'text-brand-100' : 'text-slate-400'">
                                    Sesi <span x-text="s.sesi"></span>
                                </span>
                                <span class="font-mono text-xs font-bold block mt-0.5" x-text="s.label"></span>
                                
                                <span x-show="isSelected(s.sesi)" 
                                      class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] shadow-xs">
                                    <i class="bi bi-check"></i>
                                </span>
                            </button>
                        </template>
                    </div>

                    <!-- Keterangan Sesi Terpilih -->
                    <div x-show="selectionSummary" class="mt-2.5 p-2.5 px-3.5 bg-brand-50 border border-brand-200 rounded-xl text-xs text-brand-900 flex items-center justify-between">
                        <div class="flex items-center gap-2 font-semibold">
                            <i class="bi bi-clock-fill text-brand-600"></i>
                            <span x-text="selectionSummary"></span>
                        </div>
                        <span class="px-2 py-0.5 bg-brand-600 text-white rounded-md text-[10px] font-mono font-bold" x-text="selected.length + ' JP'"></span>
                    </div>
                </div>

                <!-- Input Tanggal & Dropdown Jam Terpilih -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-200/60">
                    <div>
                        <label for="tanggal" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Perkuliahan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal', $jadwal->tanggal->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition @error('tanggal') border-rose-300 @enderror">
                        @error('tanggal')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>
                        <select name="jam_mulai" id="jam_mulai" required @change="onDropdownChange()"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition @error('jam_mulai') border-rose-300 @enderror">
                            @php
                                $jamMulaiList = ['07:30', '08:20', '09:10', '10:10', '11:00', '13:00', '13:50', '14:40', '15:30'];
                                $currentMulai = substr($jadwal->jam_mulai, 0, 5);
                            @endphp
                            @foreach($jamMulaiList as $jm)
                                <option value="{{ $jm }}" {{ old('jam_mulai', $currentMulai) == $jm ? 'selected' : '' }}>
                                    {{ $jm }} WIB
                                </option>
                            @endforeach
                        </select>
                        @error('jam_mulai')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>
                        <select name="jam_selesai" id="jam_selesai" required @change="onDropdownChange()"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition @error('jam_selesai') border-rose-300 @enderror">
                            @php
                                $jamSelesaiList = ['08:20', '09:10', '10:00', '11:00', '11:50', '13:50', '14:40', '15:30', '16:20'];
                                $currentSelesai = substr($jadwal->jam_selesai, 0, 5);
                            @endphp
                            @foreach($jamSelesaiList as $js)
                                <option value="{{ $js }}" {{ old('jam_selesai', $currentSelesai) == $js ? 'selected' : '' }}>
                                    {{ $js }} WIB
                                </option>
                            @endforeach
                        </select>
                        @error('jam_selesai')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Kelompok & Ruangan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="kelompok" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kelompok Mahasiswa
                    </label>
                    <input type="text" name="kelompok" id="kelompok" value="{{ old('kelompok', $jadwal->kelompok) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
                </div>

                <div>
                    <label for="ruangan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ruangan / Tempat Pelaksanaan
                    </label>
                    <input type="text" name="ruangan" id="ruangan" value="{{ old('ruangan', $jadwal->ruangan) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
                </div>
            </div>

            <!-- Catatan Kuliah -->
            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan / Instruksi Khusus
                </label>
                <textarea name="catatan" id="catatan" rows="2"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">{{ old('catatan', $jadwal->catatan) }}</textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.jadwals.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Perbarui Jadwal Kuliah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setJam(mulai, selesai) {
        document.getElementById('jam_mulai').value = mulai;
        document.getElementById('jam_selesai').value = selesai;
    }

    function jadwalFormApp(modulMataKuliahMap, initialModulId = '', initialNama = '') {
        return {
            modulMap: modulMataKuliahMap || {},
            selectedModulId: initialModulId,
            namaKuliah: initialNama,
            selectedMataKuliahId: '',

            init() {
                if (this.selectedModulId && this.namaKuliah) {
                    const list = this.availableMataKuliahs;
                    const found = list.find(mk => mk.nama.trim().toLowerCase() === this.namaKuliah.trim().toLowerCase());
                    if (found) {
                        this.selectedMataKuliahId = found.id;
                    }
                }
            },

            get availableMataKuliahs() {
                return this.modulMap[this.selectedModulId] || [];
            },

            onModulChange() {
                const list = this.availableMataKuliahs;
                const found = list.find(mk => mk.id == this.selectedMataKuliahId);
                if (!found) {
                    this.selectedMataKuliahId = '';
                }
            },

            onSelectMataKuliah(mkId) {
                this.selectedMataKuliahId = mkId;
                if (!mkId) return;

                const list = this.availableMataKuliahs;
                const mk = list.find(item => item.id == mkId);
                if (mk) {
                    this.namaKuliah = mk.nama;

                    if (mk.tipe_kegiatan) {
                        const elTipe = document.getElementById('tipe_kegiatan');
                        if (elTipe) elTipe.value = mk.tipe_kegiatan;
                    }

                    const elDosen = document.getElementById('dosen_id');
                    if (elDosen) {
                        elDosen.value = mk.dosen_id || '';
                    }

                    const elRuangan = document.getElementById('ruangan');
                    if (elRuangan) {
                        elRuangan.value = mk.ruangan || '';
                    }

                    // Dispatch event untuk update dosen pengampu secara reaktif di Alpine
                    window.dispatchEvent(new CustomEvent('set-dosen-pendamping', { 
                        detail: mk.dosen_pendamping || '' 
                    }));
                }
            }
        };
    }

    function dosenPendampingPicker(dosenList, initialValue = '') {
        const picker = {
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
            setFromCodes(codes) {
                if (!codes) {
                    this.selected = [];
                    return;
                }
                this.selected = codes.split(',').map(s => s.trim()).filter(s => s.length > 0);
            },
            getDosenName(kode) {
                const found = this.dosenList.find(d => d.kode === kode);
                return found ? found.nama : kode;
            }
        };
        window.dosenPendampingInstance = picker;
        return picker;
    }

    function multiSesiApp(initialMulai = '', initialSelesai = '') {
        const SESSIONS = [
            { sesi: 1, mulai: '07:30', selesai: '08:20', label: '07.30-08.20' },
            { sesi: 2, mulai: '08:20', selesai: '09:10', label: '08.20-09.10' },
            { sesi: 3, mulai: '09:10', selesai: '10:00', label: '09.10-10.00' },
            { sesi: 4, mulai: '10:10', selesai: '11:00', label: '10.10-11.00' },
            { sesi: 5, mulai: '11:00', selesai: '11:50', label: '11.00-11.50' },
            { sesi: 6, mulai: '13:00', selesai: '13:50', label: '13.00-13.50' },
            { sesi: 7, mulai: '13:50', selesai: '14:40', label: '13.50-14.40' },
            { sesi: 8, mulai: '14:40', selesai: '15:30', label: '14.40-15.30' },
            { sesi: 9, mulai: '15:30', selesai: '16:20', label: '15.30-16.20' },
        ];

        return {
            sessions: SESSIONS,
            jamMulai: initialMulai,
            jamSelesai: initialSelesai,
            selected: [],

            init() {
                this.syncFromInputs();
            },

            syncFromInputs() {
                if (!this.jamMulai || !this.jamSelesai) {
                    this.selected = [];
                    return;
                }
                const startSesi = this.sessions.find(s => s.mulai === this.jamMulai);
                const endSesi = this.sessions.find(s => s.selesai === this.jamSelesai);
                if (startSesi && endSesi && startSesi.sesi <= endSesi.sesi) {
                    const arr = [];
                    for (let i = startSesi.sesi; i <= endSesi.sesi; i++) {
                        arr.push(i);
                    }
                    this.selected = arr;
                } else if (startSesi) {
                    this.selected = [startSesi.sesi];
                } else {
                    this.selected = [];
                }
            },

            toggleSesi(sesiNum) {
                if (this.selected.length === 0) {
                    this.selected = [sesiNum];
                } else if (this.selected.includes(sesiNum)) {
                    if (this.selected.length === 1) {
                        this.selected = [];
                    } else if (sesiNum === Math.min(...this.selected)) {
                        this.selected = this.selected.filter(n => n !== sesiNum);
                    } else if (sesiNum === Math.max(...this.selected)) {
                        this.selected = this.selected.filter(n => n !== sesiNum);
                    } else {
                        this.selected = [sesiNum];
                    }
                } else {
                    const min = Math.min(...this.selected, sesiNum);
                    const max = Math.max(...this.selected, sesiNum);
                    const arr = [];
                    for (let i = min; i <= max; i++) {
                        arr.push(i);
                    }
                    this.selected = arr;
                }

                this.applyToInputs();
            },

            isSelected(sesiNum) {
                return this.selected.includes(sesiNum);
            },

            applyToInputs() {
                if (this.selected.length > 0) {
                    const minSesi = Math.min(...this.selected);
                    const maxSesi = Math.max(...this.selected);
                    const first = this.sessions.find(s => s.sesi === minSesi);
                    const last = this.sessions.find(s => s.sesi === maxSesi);
                    if (first && last) {
                        this.jamMulai = first.mulai;
                        this.jamSelesai = last.selesai;
                        
                        const elMulai = document.getElementById('jam_mulai');
                        const elSelesai = document.getElementById('jam_selesai');
                        if (elMulai) elMulai.value = first.mulai;
                        if (elSelesai) elSelesai.value = last.selesai;
                    }
                } else {
                    this.jamMulai = '';
                    this.jamSelesai = '';
                    const elMulai = document.getElementById('jam_mulai');
                    const elSelesai = document.getElementById('jam_selesai');
                    if (elMulai) elMulai.value = '';
                    if (elSelesai) elSelesai.value = '';
                }
            },

            onDropdownChange() {
                const elMulai = document.getElementById('jam_mulai');
                const elSelesai = document.getElementById('jam_selesai');
                if (elMulai) this.jamMulai = elMulai.value;
                if (elSelesai) this.jamSelesai = elSelesai.value;
                this.syncFromInputs();
            },

            resetSesi() {
                this.selected = [];
                this.applyToInputs();
            },

            get selectionSummary() {
                if (this.selected.length === 0 || !this.jamMulai || !this.jamSelesai) {
                    return null;
                }
                const min = Math.min(...this.selected);
                const max = Math.max(...this.selected);
                const count = this.selected.length;
                const jpText = `${count} JP (${count * 50} Menit)`;
                if (min === max) {
                    return `Sesi ${min} • 1 JP (50 Menit) • ${this.jamMulai} - ${this.jamSelesai} WIB`;
                }
                return `Sesi ${min} s/d Sesi ${max} • ${jpText} • ${this.jamMulai} - ${this.jamSelesai} WIB`;
            }
        };
    }
</script>
@endsection

