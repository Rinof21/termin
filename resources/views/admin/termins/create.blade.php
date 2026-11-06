@extends('layouts.app', ['title' => 'Tambah Jadwal Termin Baru'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.termins.index') }}" class="text-slate-400 hover:text-slate-600">Jadwal Termin</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Jadwal Termin Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Tentukan siklus 6 minggu termin perkuliahan blok kedokteran</p>
        </div>
        <a href="{{ route('admin.termins.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.termins.store') }}" method="POST" class="space-y-6" id="terminForm">
            @csrf

            <!-- Baris 1: Nomor, Nama, dan Semester -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-2">
                    <label for="nomor" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Termin <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="nomor" id="nomor" min="1" max="20" required 
                           value="{{ old('nomor', $defaultNomor ?? 4) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nomor') border-rose-300 @enderror">
                    @error('nomor')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-4">
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Termin <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" required 
                           value="{{ old('nama', 'Termin ' . ($defaultNomor ?? 4)) }}"
                           placeholder="Contoh: Termin 4, Termin 4 Ganjil"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nama') border-rose-300 @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tahun_semester" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tahun Semester <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_semester" id="tahun_semester" required 
                           value="{{ old('tahun_semester', $activeTahunSemester ?? '2026.1') }}"
                           placeholder="2026.1"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-brand-700 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tahun_semester') border-rose-300 @enderror">
                    <span class="text-[10px] text-slate-400 mt-1 block">Contoh: 2026.1, 2026.2</span>
                    @error('tahun_semester')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tipe_semester" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tipe Semester <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe_semester" id="tipe_semester" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tipe_semester') border-rose-300 @enderror">
                        <option value="Ganjil" {{ old('tipe_semester', $activeSemester) === 'Ganjil' ? 'selected' : '' }}>Ganjil (.1)</option>
                        <option value="Genap" {{ old('tipe_semester', $activeSemester) === 'Genap' ? 'selected' : '' }}>Genap (.2)</option>
                    </select>
                    @error('tipe_semester')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tahun_ajaran" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tahun Ajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran" required 
                           value="{{ old('tahun_ajaran', '2026/2027') }}"
                           placeholder="2026/2027"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tahun_ajaran') border-rose-300 @enderror">
                    @error('tahun_ajaran')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
                    <input type="text" name="tahun_ajaran" id="tahun_ajaran" required 
                           value="{{ old('tahun_ajaran', '2026/2027') }}"
                           placeholder="2026/2027"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('tahun_ajaran') border-rose-300 @enderror">
                    @error('tahun_ajaran')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Tanggal Mulai, Durasi Minggu, Tanggal Selesai -->
            <div class="p-5 bg-slate-50/70 border border-slate-200/80 rounded-2xl space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wider">
                    <i class="bi bi-calendar3-range text-brand-600 text-sm"></i>
                    <span>Pengaturan Durasi 6 Minggu Termin</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Mulai (Senin) <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required 
                               value="{{ old('tanggal_mulai') }}"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 transition @error('tanggal_mulai') border-rose-300 @enderror">
                        @error('tanggal_mulai')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jumlah_minggu" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jumlah Minggu <span class="text-rose-500">*</span>
                        </label>
                        <select name="jumlah_minggu" id="jumlah_minggu" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-brand-500 transition @error('jumlah_minggu') border-rose-300 @enderror">
                            <option value="6" {{ old('jumlah_minggu', 6) == 6 ? 'selected' : '' }}>6 Minggu (Standar Termin)</option>
                            <option value="4" {{ old('jumlah_minggu') == 4 ? 'selected' : '' }}>4 Minggu</option>
                            <option value="5" {{ old('jumlah_minggu') == 5 ? 'selected' : '' }}>5 Minggu</option>
                            <option value="7" {{ old('jumlah_minggu') == 7 ? 'selected' : '' }}>7 Minggu</option>
                            <option value="8" {{ old('jumlah_minggu') == 8 ? 'selected' : '' }}>8 Minggu</option>
                        </select>
                        @error('jumlah_minggu')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Selesai (Otomatis)
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                               value="{{ old('tanggal_selesai') }}"
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 transition @error('tanggal_selesai') border-rose-300 @enderror">
                        <span class="text-[11px] text-slate-400 mt-1 block">Dihitung otomatis 6 minggu dari tanggal mulai jika dibiarkan kosong.</span>
                        @error('tanggal_selesai')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Preview Rincian Minggu Otomatis -->
                <div id="previewMingguBox" class="hidden pt-3 border-t border-slate-200/80">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Simulasi 6 Minggu Perkuliahan:</p>
                    <div id="previewMingguList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2"></div>
                </div>
            </div>

            <!-- Checkbox Jadikan Termin Aktif -->
            <div class="flex items-start gap-3 p-4 bg-emerald-50/60 border border-emerald-200 rounded-2xl">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="is_aktif" id="is_aktif" value="1" {{ old('is_aktif') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                </div>
                <div>
                    <label for="is_aktif" class="text-xs font-bold text-emerald-900 cursor-pointer">
                        Jadikan sebagai Jadwal Termin Aktif Saat Ini
                    </label>
                    <p class="text-[11px] text-emerald-700 mt-0.5">
                        Jika dicentang, kalender perkuliahan dan modul aktif akan otomatis diarahkan ke termin ini.
                    </p>
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label for="keterangan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Keterangan / Catatan Termin (Opsional)
                </label>
                <textarea name="keterangan" id="keterangan" rows="3"
                          placeholder="Tambahkan catatan khusus, misal: Blok Biomedis 1, Ujian Blok di Minggu ke-6, dsb..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('keterangan') border-rose-300 @enderror">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.termins.index') }}" 
                   class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                    <i class="bi bi-check2"></i>
                    <span>Simpan Jadwal Termin</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('tanggal_mulai');
        const weeksInput = document.getElementById('jumlah_minggu');
        const endInput = document.getElementById('tanggal_selesai');
        const previewBox = document.getElementById('previewMingguBox');
        const previewList = document.getElementById('previewMingguList');

        function updateDates() {
            if (!startInput.value) {
                previewBox.classList.add('hidden');
                return;
            }

            const startDate = new Date(startInput.value + 'T00:00:00');
            const totalWeeks = parseInt(weeksInput.value) || 6;

            // Hitung tanggal selesai: start + (totalWeeks * 7) - 1 hari
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + (totalWeeks * 7) - 1);

            const yyyy = endDate.getFullYear();
            const mm = String(endDate.getMonth() + 1).padStart(2, '0');
            const dd = String(endDate.getDate()).padStart(2, '0');
            endInput.value = `${yyyy}-${mm}-${dd}`;

            // Render preview minggu
            previewList.innerHTML = '';
            for (let i = 1; i <= totalWeeks; i++) {
                const wStart = new Date(startDate);
                wStart.setDate(startDate.getDate() + ((i - 1) * 7));
                const wEnd = new Date(wStart);
                wEnd.setDate(wStart.getDate() + 6);

                const item = document.createElement('div');
                item.className = 'p-2 bg-white rounded-xl border border-slate-200 text-center shadow-2xs';
                item.innerHTML = `
                    <div class="text-[11px] font-bold text-brand-700">Minggu ${i}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">${wStart.toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})} - ${wEnd.toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})}</div>
                `;
                previewList.appendChild(item);
            }
            previewBox.classList.remove('hidden');
        }

        startInput.addEventListener('change', updateDates);
        weeksInput.addEventListener('change', updateDates);

        const tsInput = document.getElementById('tahun_semester');
        const tipeSelect = document.getElementById('tipe_semester');
        const thAjaranInput = document.getElementById('tahun_ajaran');

        if (tsInput) {
            tsInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (val.endsWith('.2')) {
                    tipeSelect.value = 'Genap';
                } else if (val.endsWith('.1')) {
                    tipeSelect.value = 'Ganjil';
                }
                const year = parseInt(val.split('.')[0]);
                if (year && year > 2000) {
                    thAjaranInput.value = `${year}/${year + 1}`;
                }
            });
        }

        if (startInput.value) {
            updateDates();
        }
    });
</script>
@endsection

