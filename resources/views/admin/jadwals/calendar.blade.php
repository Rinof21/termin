@extends('layouts.app', ['title' => 'Kalender Jadwal Termin S1 Kedokteran'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Kalender Termin</span>
@endsection

@section('content')
<!-- FullCalendar Stylesheet & Script -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>

<style>
    .fc {
        --fc-border-color: #f1f5f9;
        --fc-button-bg-color: #ffffff;
        --fc-button-border-color: #e2e8f0;
        --fc-button-text-color: #334155;
        --fc-button-hover-bg-color: #f8fafc;
        --fc-button-hover-border-color: #cbd5e1;
        --fc-button-active-bg-color: #4f46e5;
        --fc-button-active-border-color: #4f46e5;
        --fc-button-active-text-color: #ffffff;
        --fc-today-bg-color: #f8fafc;
        font-family: inherit;
    }
    .fc .fc-toolbar-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .fc .fc-button {
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 0.75rem !important;
        padding: 0.4rem 0.75rem !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.15s ease-in-out;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e2e8f0;
    }
    .fc-col-header-cell {
        background-color: #f8fafc;
        padding: 0.6rem 0 !important;
        font-weight: 600;
        font-size: 0.8rem;
        color: #475569;
    }
    .fc-timegrid-slot {
        height: 2.25rem !important;
    }
    .fc-event {
        border-radius: 0.5rem !important;
        padding: 2px 5px !important;
        cursor: pointer;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.08);
        transition: transform 0.1s ease, box-shadow 0.1s ease;
        border-width: 1px !important;
    }
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15);
    }
    .fc-event-main {
        font-size: 0.725rem;
        font-weight: 600;
        line-height: 1.25;
    }
    .fc-timegrid-now-indicator-line {
        border-color: #ef4444;
    }
    /* Memastikan tabel kalender selalu 100% dan kolom hari Minggu (Min) tidak terpotong */
    #calendar, .fc {
        width: 100% !important;
        max-width: 100% !important;
    }
    .fc-view-harness {
        width: 100% !important;
        max-width: 100% !important;
    }
    .fc-scrollgrid {
        width: 100% !important;
        table-layout: fixed !important;
    }
    .fc-scrollgrid-sync-table, .fc-col-header {
        width: 100% !important;
        table-layout: fixed !important;
    }
    .fc-daygrid colgroup col {
        width: 14.2857% !important;
    }
    .fc-daygrid-body {
        width: 100% !important;
    }
    .fc-daygrid-day-top {
        display: flex !important;
        flex-direction: row-reverse !important;
        padding: 3px 6px 0 6px !important;
    }
    .fc-daygrid-day-number {
        padding: 1px 4px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        white-space: nowrap !important;
    }
    .fc-col-header-cell {
        padding: 0.6rem 0 !important;
        text-align: center !important;
    }
    /* Styling Tampilan Bulanan (dayGridMonth) */
    .fc-daygrid-event {
        border-radius: 0.375rem !important;
        padding: 2.5px 6px !important;
        margin-top: 2px !important;
        font-size: 0.725rem !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .fc-daygrid-event-dot {
        display: none !important;
    }
    .fc-daygrid-dot-event {
        color: #ffffff !important;
    }
    .fc-daygrid-dot-event .fc-event-time,
    .fc-daygrid-dot-event .fc-event-title {
        color: #ffffff !important;
        font-weight: 600 !important;
    }
    .fc-daygrid-event-harness-hidden {
        display: none !important;
    }
    .fc-daygrid-day-bottom {
        margin-top: 2px !important;
        padding-bottom: 2px !important;
    }
    .fc-more-link {
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        color: #4f46e5 !important;
        padding: 2px 4px !important;
    }
    .fc-popover {
        border-radius: 1rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        z-index: 40 !important;
    }
    .fc-popover-header {
        background-color: #f8fafc !important;
        padding: 0.5rem 0.75rem !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
    }
    .fc-daygrid-day {
        cursor: pointer;
    }
    .fc-daygrid-day-frame {
        transition: background-color 0.15s ease;
    }
    .fc-daygrid-day-frame:hover {
        background-color: #f1f5f9 !important;
    }
</style>

<div class="space-y-6" x-data="calendarApp()">
    <!-- Header Halaman & Aksi Cepat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Jadwal Termin S1 Kedokteran</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($activeSemester ?? 'Ganjil') === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    Semester {{ $activeSemester ?? 'Ganjil' }} (Aktif)
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Menampilkan jadwal kuliah untuk modul <strong>Semester {{ $activeSemester ?? 'Ganjil' }}</strong>. Klik kegiatan di kalender untuk rincian lengkap.</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- 3 Mode Tampilan: Kalender (Aktif), Matriks, Tabel -->
            <a href="{{ route('admin.jadwals.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-brand-600 text-white border border-brand-700 rounded-xl text-xs font-bold shadow-xs transition"
               title="Mode Tampilan Kalender (Aktif)">
                <i class="bi bi-calendar3 text-sm"></i>
                <span>Tampilan Kalender</span>
            </a>
            <a href="{{ route('admin.jadwals.matrix') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition"
               title="Beralih ke Tampilan Matriks">
                <i class="bi bi-grid-3x3 text-sm text-emerald-600"></i>
                <span>Tampilan Matriks</span>
            </a>
            <a href="{{ route('admin.jadwals.table') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition"
               title="Beralih ke Tampilan Tabel">
                <i class="bi bi-table text-sm text-slate-500"></i>
                <span>Tampilan Tabel</span>
            </a>
        </div>
    </div>

    <!-- Layout 2 Kolom: Kolom 1 (Navigasi & Kalender) + Kolom 2 (Panel Kanan Filter & Detail) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM 1: Navigasi Minggu & Kalender (7 Kolom pada Desktop) -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-5 min-w-0">
            <!-- Navigasi Cepat 6 Minggu Termin Berjalan -->
            @if($activeTermin)
                <div class="bg-white rounded-2xl p-2.5 sm:p-3 border border-slate-200/80 shadow-xs flex items-center gap-2 overflow-x-auto">
                    <button type="button" @click="goToMonthView()"
                            :class="activeWeekTab === 'month' ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
                        <i class="bi bi-calendar-month text-sm"></i>
                        <span>Bulan Penuh</span>
                    </button>

                    @foreach($activeTermin->daftar_minggu as $m)
                        <button type="button" @click="goToWeek('{{ $m['tanggal_mulai'] }}', {{ $m['nomor'] }})"
                                :class="activeWeekTab === {{ $m['nomor'] }} ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="flex-1 min-w-[95px] px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition flex flex-col items-center leading-tight">
                            <span>{{ $m['label'] }}</span>
                            <span class="text-[10px] font-normal opacity-80">{{ \Carbon\Carbon::parse($m['tanggal_mulai'])->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($m['tanggal_selesai'])->translatedFormat('d M') }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Kontainer Kalender FullCalendar -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- KOLOM 2: Panel Kanan (Filter & Detail Jadwal Kuliah) -->
        <div id="panel-detail-kolom" class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-20 flex flex-col gap-5">
            
            <!-- Filter Bar Interaktif di Panel Kanan -->
            <div x-show="!hasSelectedEvent" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="bi bi-funnel text-brand-600 text-sm"></i>
                        <span>Filter Jadwal</span>
                    </span>
                    <button type="button" 
                            x-show="filterModul || filterDosen || filterKelompok || dosenSearchQuery" 
                            @click="resetAllFilters()"
                            class="text-[11px] font-semibold text-brand-600 hover:text-brand-800 transition">
                        Reset Filter
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Filter Modul -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Modul</label>
                        <select x-model="filterModul" @change="refetchCalendar()" 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                            <option value="">Semua Modul</option>
                            @foreach($moduls as $m)
                                <option value="{{ $m->id }}">{{ $m->kode }} - {{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Dosen & Kelompok (2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Filter Dosen -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Dosen Pengampu</label>
                            <div class="relative" @click.away="closeDosenDropdown()">
                                <div class="relative">
                                    <input type="text"
                                           x-model="dosenSearchQuery"
                                           @focus="isDosenDropdownOpen = true"
                                           @input="isDosenDropdownOpen = true"
                                           @keydown.escape="closeDosenDropdown()"
                                           @keydown.enter.prevent="selectFirstFilteredDosen()"
                                           placeholder="Ketik nama atau kode dosen..."
                                           autocomplete="off"
                                           class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-search text-[11px]"></i>
                                    </div>
                                    <button type="button"
                                            x-show="filterDosen || dosenSearchQuery"
                                            @click.stop="clearDosenFilter()"
                                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                                            title="Hapus pilihan dosen">
                                        <i class="bi bi-x-circle-fill text-xs"></i>
                                    </button>
                                    <div x-show="!filterDosen && !dosenSearchQuery" class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-chevron-expand text-xs"></i>
                                    </div>
                                </div>

                                <!-- Dropdown List -->
                                <div x-show="isDosenDropdownOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto py-1 divide-y divide-slate-50"
                                     style="display: none;">
                                    
                                    <button type="button"
                                            @click="selectDosenFilter('', '')"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-brand-50 hover:text-brand-700 flex items-center justify-between transition"
                                            :class="!filterDosen ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-700 font-medium'">
                                        <span>Semua Dosen</span>
                                        <span x-show="!filterDosen" class="text-brand-600"><i class="bi bi-check2"></i></span>
                                    </button>

                                    <template x-for="d in filteredDosenList" :key="d.id">
                                        <button type="button"
                                                @click="selectDosenFilter(d.id, d.label)"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-brand-50 hover:text-brand-700 flex items-center justify-between gap-2 transition"
                                                :class="filterDosen == d.id ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-700 font-medium'">
                                            <div class="truncate">
                                                <span class="font-mono text-[11px] text-slate-500 font-normal mr-1" x-text="'[' + d.kode + ']'"></span>
                                                <span x-text="d.nama"></span>
                                            </div>
                                            <span x-show="filterDosen == d.id" class="text-brand-600 shrink-0"><i class="bi bi-check2"></i></span>
                                        </button>
                                    </template>

                                    <div x-show="filteredDosenList.length === 0" class="px-3 py-3 text-center text-xs text-slate-400">
                                        Dosen tidak ditemukan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Kelompok -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kelompok</label>
                            <select x-model="filterKelompok" @change="refetchCalendar()" 
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                                <option value="">Semua Kelompok</option>
                                @foreach($kelompoks as $kel)
                                    <option value="{{ $kel }}">{{ $kel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Legenda Warna Modul -->
                    <div class="pt-2.5 border-t border-slate-100">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Legenda Modul:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($moduls as $modul)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-50 border border-slate-200/80 cursor-pointer hover:bg-slate-100 transition"
                                      :class="filterModul === '{{ $modul->id }}' ? 'ring-2 ring-brand-500 bg-brand-50 border-brand-300' : ''"
                                      @click="filterModul = (filterModul === '{{ $modul->id }}' ? '' : '{{ $modul->id }}'); refetchCalendar()">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $modul->warna }}"></span>
                                    <span class="text-slate-700 font-semibold">{{ $modul->kode }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Keadaan 1: Ada Jadwal yang Diklik -->
            <div x-cloak x-show="hasSelectedEvent" 
                 class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5 transition-all">
                
                <!-- Header Panel Detail -->
                <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="space-y-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white shadow-xs"
                              :style="'background-color: ' + (activeEvent.backgroundColor || '#4f46e5')">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            <span x-text="activeEvent.modul_nama"></span>
                        </span>
                        <h2 class="text-lg font-bold text-slate-900 leading-snug" x-text="activeEvent.nama_kuliah"></h2>
                        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold uppercase tracking-wider text-[10px]" x-text="activeEvent.tipe_kegiatan"></span>
                            <span x-show="activeEvent.kelompok && activeEvent.kelompok !== '-'" class="text-slate-400">&bull;</span>
                            <span x-show="activeEvent.kelompok && activeEvent.kelompok !== '-'" class="text-brand-600 font-semibold" x-text="'Kelompok: ' + activeEvent.kelompok"></span>
                        </div>
                    </div>
                    <button @click="hasSelectedEvent = false" 
                            title="Tutup Detail" 
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <!-- Informasi Rincian Kegiatan -->
                <div class="space-y-3.5 text-xs">
                    <!-- Tanggal & Jam -->
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 text-base">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Waktu Perkuliahan</span>
                            <div class="font-bold text-slate-900 text-sm mt-0.5 font-mono" x-text="activeEvent.jam"></div>
                            <div class="text-[11px] text-slate-500 mt-0.5 font-medium" x-text="activeEvent.tanggal_format"></div>
                        </div>
                    </div>

                    <!-- Ruangan & Lab -->
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-base">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Ruangan / Tempat</span>
                            <div class="font-bold text-slate-900 text-sm mt-0.5" x-text="activeEvent.ruangan"></div>
                        </div>
                    </div>

                    <!-- Dosen Pengampu & Inisial -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-xs">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Dosen Pengampu</span>
                            </div>
                            <span x-show="activeEvent.dosen_inisial" class="font-mono text-[10px] font-normal px-2 py-0.5 rounded-md bg-amber-50 text-amber-900 border border-amber-200" 
                                  x-text="activeEvent.total_dosen_count > 1 ? (activeEvent.total_dosen_count + ' Dosen Pengampu') : ('Inisial: [' + activeEvent.dosen_inisial + ']')"></span>
                        </div>

                        <!-- Dosen Utama -->
                        <template x-if="activeEvent.dosen_utama">
                            <div class="flex items-center gap-2 p-1.5 rounded-lg bg-white border border-slate-200/60 shadow-2xs">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-200" x-text="'[' + activeEvent.dosen_utama.kode + ']'"></span>
                                <span class="font-bold text-slate-900 text-sm" x-text="activeEvent.dosen_utama.nama"></span>
                            </div>
                        </template>
                        <template x-if="!activeEvent.dosen_utama && activeEvent.dosen">
                            <div class="font-bold text-slate-900 text-sm break-words" x-text="activeEvent.dosen"></div>
                        </template>
                        <template x-if="!activeEvent.dosen_utama && !activeEvent.dosen">
                            <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                        </template>

                        <!-- Dosen Pendamping / Tutor -->
                        <template x-if="activeEvent.pendamping_list && activeEvent.pendamping_list.length > 0">
                            <div class="space-y-1.5">
                                <template x-for="p in activeEvent.pendamping_list" :key="p.kode">
                                    <div class="flex items-center gap-2 p-1.5 rounded-lg bg-white border border-slate-200/60 shadow-2xs">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200" x-text="'[' + p.kode + ']'"></span>
                                        <span class="text-xs font-medium text-slate-800" x-text="p.nama || p.kode"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div x-show="activeEvent.catatan && activeEvent.catatan !== '-'" 
                         class="p-3.5 rounded-xl bg-amber-50/60 border border-amber-200/80 text-amber-900">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-amber-700 block mb-1">Catatan Kuliah / Instruksi:</span>
                        <p class="text-xs italic leading-relaxed" x-text="activeEvent.catatan"></p>
                    </div>
                </div>

                <!-- Tombol Tindakan Cepat (Ubah & Hapus) -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <form :action="'{{ url('/admin/jadwals') }}/' + activeEvent.id" method="POST"
                          data-confirm="Apakah Anda yakin ingin menghapus jadwal kuliah ini dari sistem?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200/60 rounded-xl transition flex items-center gap-1.5">
                            <i class="bi bi-trash"></i>
                            <span>Hapus</span>
                        </button>
                    </form>

                    <a :href="activeEvent.edit_url" 
                       class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i class="bi bi-pencil-square"></i>
                        <span>Ubah Jadwal</span>
                    </a>
                </div>
            </div>

            <!-- Keadaan 2: Belum Ada Jadwal yang Diklik -->
            <div x-show="!hasSelectedEvent" 
                 class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-5">

                <!-- Daftar Cepat Modul Termin -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Modul Termin {{ $activeTermin->nomor_termin ?? 4 }}:</span>
                        <template x-if="filterModul">
                            <button type="button" 
                                    @click="filterModul = ''; refetchCalendar()" 
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200/60 transition flex items-center gap-0.5"
                                    title="Tampilkan semua modul">
                                <i class="bi bi-x"></i>
                                <span>Semua</span>
                            </button>
                        </template>
                    </div>
                    @foreach($moduls as $m)
                        <div class="p-2.5 rounded-xl border flex items-center justify-between text-xs cursor-pointer transition select-none group"
                             :class="String(filterModul) === '{{ $m->id }}' 
                                ? 'ring-2 ring-brand-500 bg-brand-50/70 border-brand-300 shadow-xs' 
                                : 'border-slate-100 bg-slate-50/60 hover:bg-slate-100/80 hover:border-slate-300'"
                             @click="filterModul = (String(filterModul) === '{{ $m->id }}' ? '' : '{{ $m->id }}'); refetchCalendar()"
                             title="Klik untuk filter kalender modul {{ $m->nama }}">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs group-hover:scale-110 transition-transform" style="background-color: {{ $m->warna }};"></span>
                                <span class="truncate" :class="String(filterModul) === '{{ $m->id }}' ? 'font-bold text-brand-900' : 'font-semibold text-slate-800'">{{ $m->nama }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <template x-if="String(filterModul) === '{{ $m->id }}'">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-brand-600 text-white flex items-center gap-0.5 shadow-2xs">
                                        <i class="bi bi-check2"></i>
                                        <span>Aktif</span>
                                    </span>
                                </template>
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded border"
                                      :class="String(filterModul) === '{{ $m->id }}' ? 'bg-white border-brand-200 text-brand-700 font-bold' : 'bg-slate-200/60 border-transparent text-slate-600'">
                                    {{ $m->kode }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Referensi 9 Sesi Jam Kuliah Tetap S1 Kedokteran -->
                <div class="pt-4 border-t border-slate-100 text-left space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Jam Sesi Kuliah Tetap:</span>
                        <span class="text-[10px] font-bold text-brand-600 bg-brand-50 border border-brand-200 px-1.5 py-0.5 rounded">9 Sesi</span>
                    </div>
                    <div class="space-y-1 text-xs font-mono">
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 1</span>
                            <span class="font-bold text-slate-800">07.30 - 08.20</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 2</span>
                            <span class="font-bold text-slate-800">08.20 - 09.10</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 3</span>
                            <span class="font-bold text-slate-800">09.10 - 10.00</span>
                        </div>
                        <div class="text-[10px] text-center text-slate-400 font-sans italic py-0.5">&bull; Istirahat 10.00 - 10.10 &bull;</div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 4</span>
                            <span class="font-bold text-slate-800">10.10 - 11.00</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 5</span>
                            <span class="font-bold text-slate-800">11.00 - 11.50</span>
                        </div>
                        <div class="text-[10px] text-center text-amber-700 bg-amber-50 border border-amber-200/70 rounded font-sans font-semibold py-0.5">&bull; ISHOMA (11.50 - 13.00) &bull;</div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 6</span>
                            <span class="font-bold text-slate-800">13.00 - 13.50</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 7</span>
                            <span class="font-bold text-slate-800">13.50 - 14.40</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 8</span>
                            <span class="font-bold text-slate-800">14.40 - 15.30</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-600 font-sans font-medium text-[11px]">Sesi 9</span>
                            <span class="font-bold text-slate-800">15.30 - 16.20</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buat Jadwal Baru -->
                <div class="pt-2">
                    <a href="{{ route('admin.jadwals.create') }}" 
                       class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200/80 transition">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Jadwal Kuliah Baru</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function calendarApp() {
        return {
            filterModul: '',
            filterDosen: '',
            filterKelompok: '',

            dosenList: {!! json_encode($dosens->map(function($d) {
                return [
                    'id' => (string)$d->id,
                    'kode' => $d->kode,
                    'nama' => $d->nama,
                    'label' => '[' . $d->kode . '] ' . $d->nama,
                ];
            })->values()) !!},
            dosenSearchQuery: '',
            isDosenDropdownOpen: false,

            get filteredDosenList() {
                if (!this.dosenSearchQuery || !this.dosenSearchQuery.trim()) {
                    return this.dosenList;
                }
                const q = this.dosenSearchQuery.toLowerCase().trim();
                if (this.filterDosen) {
                    const cur = this.dosenList.find(d => String(d.id) === String(this.filterDosen));
                    if (cur && cur.label.toLowerCase() === q) {
                        return this.dosenList;
                    }
                }
                return this.dosenList.filter(d => 
                    (d.kode && d.kode.toLowerCase().includes(q)) ||
                    (d.nama && d.nama.toLowerCase().includes(q))
                );
            },

            selectDosenFilter(id, label) {
                this.filterDosen = id ? String(id) : '';
                this.dosenSearchQuery = label || '';
                this.isDosenDropdownOpen = false;
                this.refetchCalendar();
            },

            selectFirstFilteredDosen() {
                if (this.filteredDosenList.length > 0) {
                    const first = this.filteredDosenList[0];
                    this.selectDosenFilter(first.id, first.label);
                }
            },

            clearDosenFilter() {
                this.filterDosen = '';
                this.dosenSearchQuery = '';
                this.isDosenDropdownOpen = false;
                this.refetchCalendar();
            },

            closeDosenDropdown() {
                this.isDosenDropdownOpen = false;
                if (this.filterDosen) {
                    const found = this.dosenList.find(d => String(d.id) === String(this.filterDosen));
                    if (found) {
                        this.dosenSearchQuery = found.label;
                    }
                } else {
                    this.dosenSearchQuery = '';
                }
            },

            resetAllFilters() {
                this.filterModul = '';
                this.filterDosen = '';
                this.filterKelompok = '';
                this.dosenSearchQuery = '';
                this.isDosenDropdownOpen = false;
                this.refetchCalendar();
            },

            calendar: null,
            hasSelectedEvent: false,
            activeEvent: {},
            activeWeekTab: 'month',

            goToMonthView() {
                this.activeWeekTab = 'month';
                if (this.calendar) {
                    this.calendar.changeView('dayGridMonth');
                    this.calendar.gotoDate('{{ $activeTermin ? $activeTermin->tanggal_mulai->format("Y-m-d") : "2026-11-02" }}');
                }
            },

            goToWeek(startDate, weekNumber) {
                this.activeWeekTab = weekNumber;
                if (this.calendar) {
                    this.calendar.changeView('timeGridWeek');
                    this.calendar.gotoDate(startDate);
                }
            },

            init() {
                const calendarEl = document.getElementById('calendar');
                const self = this;

                this.calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'id',
                    initialView: 'dayGridMonth',
                    initialDate: '{{ $activeTermin ? $activeTermin->tanggal_mulai->format("Y-m-d") : "2026-11-02" }}',
                    height: 'auto',
                    expandRows: false,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    buttonText: {
                        today: 'Hari Ini',
                        month: 'Bulan',
                        week: 'Minggu',
                        day: 'Hari',
                        list: 'Daftar'
                    },
                    dayMaxEvents: 5,
                    eventDisplay: 'block',
                    slotMinTime: '07:00:00',
                    slotMaxTime: '17:30:00',
                    allDaySlot: false,
                    slotDuration: '00:20:00',
                    slotLabelInterval: '01:00:00',
                    nowIndicator: true,
                    dateClick: function(info) {
                        const clickedDate = info.dateStr;
                        window.location.href = '{{ route("admin.jadwals.matrix") }}?tanggal=' + clickedDate + '&semester={{ $activeSemester }}';
                    },
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    },
                    eventContent: function(arg) {
                        const timeText = arg.timeText;
                        const props = arg.event.extendedProps || {};
                        const inisial = props.dosen_inisial || props.dosen_kode || '';
                        const kelompok = props.kelompok && props.kelompok !== '-' && props.kelompok !== 'Semua Kelompok' ? props.kelompok : (props.kelompok === 'Semua Kelompok' ? 'Semua Kelompok' : '');

                        if (arg.view.type === 'dayGridMonth') {
                            const el = document.createElement('div');
                            el.className = 'fc-event-main-custom flex items-center gap-1.5 text-[11px] leading-tight px-1.5 py-0.5 overflow-hidden text-white';
                            el.innerHTML = `
                                <span class="font-bold opacity-90 text-[10px] whitespace-nowrap shrink-0">${timeText}</span>
                                <span class="truncate font-semibold">${props.nama_kuliah}</span>
                            `;
                            return { domNodes: [el] };
                        }

                        if (arg.view.type === 'timeGridWeek' || arg.view.type === 'timeGridDay') {
                            const el = document.createElement('div');
                            el.className = 'fc-event-main-custom h-full flex flex-col justify-between p-1 text-white leading-tight overflow-hidden';
                            el.innerHTML = `
                                <div>
                                    <div class="text-[10px] font-bold opacity-90 flex items-center justify-between gap-1 mb-1">
                                        <span>${timeText}</span>
                                        ${props.ruangan && props.ruangan !== 'Belum ditentukan' ? `<span class="opacity-80 text-[9px] truncate max-w-[85px] font-normal"><i class="bi bi-geo-alt"></i> ${props.ruangan}</span>` : ''}
                                    </div>
                                    <div class="flex items-center gap-1 flex-wrap mb-1">
                                        ${kelompok ? `<span class="px-1.5 py-0.5 rounded bg-black/30 text-[9px] font-normal tracking-tight shrink-0">[${kelompok}]</span>` : ''}
                                        ${inisial ? `<span class="px-1.5 py-0.5 rounded bg-white/20 text-[10px] font-mono font-normal tracking-wide border border-white/30 shrink-0 inline-flex items-center gap-0.5" title="${props.dosen_lengkap_text || ''}"><i class="bi bi-person text-[9px]"></i>[${inisial}]</span>` : ''}
                                    </div>
                                    <div class="text-xs font-bold leading-snug line-clamp-2">
                                        ${props.nama_kuliah}
                                    </div>
                                </div>
                                ${inisial ? `
                                    <div class="pt-1 mt-1 border-t border-white/20 text-[9px] truncate opacity-90 flex items-center gap-1 font-normal" title="${props.dosen_lengkap_text || ''}">
                                        <span class="font-mono font-normal">[${props.dosen_kode || inisial}]</span>
                                        <span class="truncate font-normal">${props.dosen_nama || ''}</span>
                                        ${props.pendamping_list && props.pendamping_list.length > 0 ? `<span class="opacity-85 text-[8px] font-normal shrink-0">(+${props.pendamping_list.length} tutor)</span>` : ''}
                                    </div>
                                ` : ''}
                            `;
                            return { domNodes: [el] };
                        }

                        return null;
                    },
                    eventDidMount: function(info) {
                        const color = info.event.backgroundColor || (info.event.extendedProps && info.event.extendedProps.modul_warna);
                        if (color) {
                            info.el.style.setProperty('background-color', color, 'important');
                            info.el.style.setProperty('border-color', color, 'important');
                            info.el.style.setProperty('color', '#ffffff', 'important');
                            info.el.style.setProperty('--fc-event-bg-color', color);
                            info.el.style.setProperty('--fc-event-border-color', color);
                        }
                        const dot = info.el.querySelector('.fc-list-event-dot');
                        if (dot && color) {
                            dot.style.setProperty('border-color', color, 'important');
                        }
                    },
                    events: function(fetchInfo, successCallback, failureCallback) {
                        const url = new URL('{{ route("admin.jadwals.events") }}', window.location.origin);
                        if (self.filterModul) url.searchParams.append('modul_id', self.filterModul);
                        if (self.filterDosen) url.searchParams.append('dosen_id', self.filterDosen);
                        if (self.filterKelompok) url.searchParams.append('kelompok', self.filterKelompok);

                        fetch(url)
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => {
                                console.error('Error fetching calendar events:', error);
                                failureCallback(error);
                            });
                    },
                    eventClick: function(info) {
                        self.activeEvent = {
                            id: info.event.extendedProps.id,
                            nama_kuliah: info.event.extendedProps.nama_kuliah,
                            modul_nama: info.event.extendedProps.modul_nama,
                            modul_kode: info.event.extendedProps.modul_kode,
                            tipe_kegiatan: info.event.extendedProps.tipe_kegiatan,
                            kelompok: info.event.extendedProps.kelompok,
                            ruangan: info.event.extendedProps.ruangan,
                            dosen: info.event.extendedProps.dosen,
                            dosen_inisial: info.event.extendedProps.dosen_inisial,
                            dosen_utama: info.event.extendedProps.dosen_utama,
                            pendamping_list: info.event.extendedProps.pendamping_list || [],
                            dosen_lengkap_text: info.event.extendedProps.dosen_lengkap_text,
                            total_dosen_count: info.event.extendedProps.total_dosen_count || 1,
                            jam: info.event.extendedProps.jam,
                            tanggal_format: info.event.extendedProps.tanggal_format,
                            catatan: info.event.extendedProps.catatan,
                            edit_url: info.event.extendedProps.edit_url,
                            backgroundColor: info.event.backgroundColor
                        };
                        self.hasSelectedEvent = true;

                        // Scroll otomatis ke panel detail pada layar smartphone/tablet
                        if (window.innerWidth < 1024) {
                            document.getElementById('panel-detail-kolom')?.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });

                this.calendar.render();

                // Sinkronisasi ukuran kalender agar presisi dan kolom hari Minggu (Min) tidak terpotong
                setTimeout(() => {
                    if (this.calendar) {
                        this.calendar.updateSize();
                    }
                }, 50);

                if (window.ResizeObserver && !this.calendarResizeObserver) {
                    this.calendarResizeObserver = new ResizeObserver(() => {
                        if (this.calendar) {
                            this.calendar.updateSize();
                        }
                    });
                    this.calendarResizeObserver.observe(calendarEl);
                }
            },

            refetchCalendar() {
                if (this.calendar) {
                    this.calendar.refetchEvents();
                }
            }
        }
    }
</script>
@endsection
