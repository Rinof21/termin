@extends('layouts.app', ['title' => 'Matriks Jadwal Perkuliahan S1 Kedokteran'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.jadwals.index') }}" class="text-slate-400 hover:text-slate-600">Kalender Termin</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Matriks Jadwal</span>
@endsection

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .matrix-table th, .matrix-table td {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
    }
    .matrix-table th:first-child, .matrix-table td:first-child {
        border-left: 1px solid #e2e8f0;
    }
    .matrix-table tr:first-child th {
        border-top: 1px solid #e2e8f0;
    }
    .slot-cell {
        min-height: 85px;
        transition: all 0.15s ease;
    }
    .slot-cell:hover .add-slot-btn {
        opacity: 1;
        transform: scale(1);
    }
    @media print {
        aside, header, nav, .no-print, #panel-informasi-detail {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
        }
        .matrix-table-container {
            overflow: visible !important;
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        @page {
            size: landscape;
            margin: 0.8cm;
        }
    }
</style>

<div class="space-y-5" x-data="matrixApp()">
    <!-- Header Halaman & Aksi Cepat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Matriks Jadwal Termin S1 Kedokteran</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $semesterFilter === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    Semester {{ $semesterFilter }} {{ $semesterFilter === $activeSemester ? '(Aktif)' : '' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Tampilan jadwal perkuliahan bergaya spreadsheet matriks per modul, kelompok, dan sesi waktu kurikulum kedokteran.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <form method="GET" action="{{ route('admin.jadwals.matrix') }}" class="inline-flex">
                <input type="hidden" name="minggu" value="{{ $mingguNomor }}">
                <input type="hidden" name="hari" value="{{ $selectedDay['slug'] }}">
                <select name="semester" onchange="this.form.submit()" 
                        class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-brand-500 shadow-xs cursor-pointer">
                    <option value="Ganjil" {{ $semesterFilter === 'Ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                    <option value="Genap" {{ $semesterFilter === 'Genap' ? 'selected' : '' }}>Semester Genap</option>
                </select>
            </form>

            <a href="{{ route('admin.jadwals.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-calendar3 text-sm"></i>
                <span>Tampilan Kalender</span>
            </a>
            <a href="{{ route('admin.jadwals.table') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-table text-sm"></i>
                <span>Tampilan Tabel</span>
            </a>
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition"
                    title="Cetak Jadwal Matriks">
                <i class="bi bi-printer text-sm"></i>
                <span>Cetak / PDF</span>
            </button>
            <a href="{{ route('admin.jadwals.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <i class="bi bi-calendar-plus text-sm"></i>
                <span>Tambah Jadwal</span>
            </a>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- LAYOUT 2 KOLOM: KOLOM KIRI (MATRIKS) + KOLOM KANAN (INFORMASI & DETAIL) -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- KOLOM KIRI: MATRIKS SPREADSHEET & NAVIGASI -->
        <div class="lg:col-span-8 xl:col-span-8 2xl:col-span-9 space-y-5 min-w-0">
            
            <!-- Navigasi 6 Minggu Termin Berjalan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3.5 no-print">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-base border border-brand-100 shrink-0">
                            <i class="bi bi-grid-3x3"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">{{ $currentTermin ? $currentTermin->nama : 'Termin Perkuliahan' }}</span>
                                @if($currentTermin && $currentTermin->is_aktif)
                                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-extrabold px-2 py-0.2 rounded-full border border-emerald-200">AKTIF</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $currentTermin ? $currentTermin->rentang_tanggal : 'Pilih minggu dan hari perkuliahan di bawah' }}
                            </p>
                        </div>
                    </div>

                    <!-- Tab Minggu (Minggu 1 s/d Minggu 6) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
                        @foreach($daftarMinggu as $m)
                            <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['minggu' => $m['nomor']])) }}" 
                               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition flex flex-col items-center leading-tight {{ $mingguNomor == $m['nomor'] ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200' }}">
                                <span>{{ $m['label'] }}</span>
                                <span class="text-[9px] font-normal {{ $mingguNomor == $m['nomor'] ? 'text-brand-100' : 'text-slate-400' }}">
                                    {{ \Carbon\Carbon::parse($m['tanggal_mulai'])->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($m['tanggal_selesai'])->translatedFormat('d M') }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Toolbar Pemilihan Hari (Senin - Sabtu) & Opsi Tampilan -->
                <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 flex-wrap sm:flex-nowrap">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1 shrink-0">HARI:</span>
                        @foreach($days as $d)
                            <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['hari' => $d['slug']])) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition relative {{ $selectedDay['slug'] === $d['slug'] ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                <span>{{ $d['nama'] }}</span>
                                <span class="text-[10px] opacity-75 font-normal">({{ $d['short_date'] }})</span>
                                @if(!empty($d['is_today']))
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Hari Ini"></span>
                                @endif
                            </a>
                        @endforeach

                        <!-- Tombol Pintas Lompat ke Hari Ini -->
                        <a href="{{ route('admin.jadwals.matrix', ['semester' => $semesterFilter]) }}" 
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold transition shrink-0 shadow-2xs ml-auto"
                           title="Lompat ke Jadwal Hari Ini">
                            <i class="bi bi-geo-alt-fill text-emerald-600"></i>
                            <span>Hari Ini</span>
                        </a>
                    </div>

                    <!-- Opsi Toggle Pisah Kelompok A.1/A.2 -->
                    <div class="flex items-center gap-3 shrink-0">
                        <form method="GET" action="{{ route('admin.jadwals.matrix') }}" class="flex items-center gap-2">
                            <input type="hidden" name="semester" value="{{ $semesterFilter }}">
                            <input type="hidden" name="minggu" value="{{ $mingguNomor }}">
                            <input type="hidden" name="hari" value="{{ $selectedDay['slug'] }}">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600 font-medium cursor-pointer">
                                <input type="checkbox" name="split_kelompok" value="1" {{ $splitKelompok ? 'checked' : '' }} 
                                       onchange="this.form.submit()"
                                       class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                                <span>Pisah Kolom (A.1 / A.2)</span>
                            </label>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kontainer Matriks Spreadsheet -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden matrix-table-container">
                <!-- Banner Hari & Tanggal -->
                <div class="bg-gradient-to-r from-slate-100 via-sky-50 to-slate-100 py-2.5 px-4 border-b border-slate-200 text-center relative flex items-center justify-between">
                    @php
                        $prevIndex = ($selectedDay['index'] > 0) ? $selectedDay['index'] - 1 : null;
                        $nextIndex = ($selectedDay['index'] < count($days) - 1) ? $selectedDay['index'] + 1 : null;
                    @endphp
                    <div>
                        @if($prevIndex !== null)
                            <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['hari' => $days[$prevIndex]['slug']])) }}" 
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs no-print">
                                <i class="bi bi-chevron-left"></i>
                                <span>{{ $days[$prevIndex]['nama'] }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 justify-center flex-wrap">
                        <i class="bi bi-calendar-event text-brand-600 text-sm"></i>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                            {{ $selectedDay['formatted'] }}
                        </h2>
                        @if(!empty($selectedDay['is_today']))
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                Hari Ini
                            </span>
                        @endif
                        <span class="text-[10px] bg-brand-50 text-brand-700 font-semibold px-2 py-0.5 rounded border border-brand-200">
                            {{ $selectedMinggu['label'] ?? 'Minggu ' . $mingguNomor }}
                        </span>
                    </div>

                    <div>
                        @if($nextIndex !== null)
                            <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['hari' => $days[$nextIndex]['slug']])) }}" 
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs no-print">
                                <span>{{ $days[$nextIndex]['nama'] }}</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tabel Grid Matriks -->
                <div class="overflow-x-auto">
                    <table class="matrix-table text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-800 text-xs">
                                <th class="w-28 min-w-[115px] px-2.5 py-2.5 text-center font-bold text-slate-500 uppercase tracking-wider bg-slate-100/80">
                                    Waktu / Sesi
                                </th>

                                @foreach($columns as $col)
                                    @php
                                        $colBg = $col['warna'] ? $col['warna'] . '15' : '#f8fafc';
                                        $borderTopColor = $col['warna'] ?: '#4f46e5';
                                    @endphp
                                    <th class="min-w-[170px] px-3 py-2.5 text-center font-bold align-middle transition"
                                        style="background-color: {{ $colBg }}; border-top: 3.5px solid {{ $borderTopColor }};">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                <span class="w-2 h-2 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $col['warna'] }}"></span>
                                                <span class="text-slate-900 font-bold text-xs leading-snug">
                                                    {{ $col['nama'] }}
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-white/80 border border-slate-200 text-slate-600">
                                                    {{ $col['kode'] }}
                                                </span>
                                                @if($col['kelompok'])
                                                    <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-brand-100 text-brand-800 border border-brand-200">
                                                        Kelompok {{ $col['kelompok'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 text-xs">
                            @for($s = 0; $s < count($timeSlots); $s++)
                                @php
                                    $slot = $timeSlots[$s];
                                @endphp

                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="w-28 min-w-[115px] px-2 py-2.5 text-center bg-slate-50/90 text-slate-700 align-middle border-r font-medium">
                                        <span class="block text-[11px] font-bold text-slate-900 tracking-tight">
                                            {{ $slot['label'] }}
                                        </span>
                                        <span class="inline-block mt-0.5 text-[10px] font-semibold text-slate-500 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs">
                                            Sesi {{ $slot['sesi'] }}
                                        </span>
                                    </td>

                                    @for($c = 0; $c < count($columns); $c++)
                                        @php
                                            $cell = $grid[$s][$c] ?? null;
                                        @endphp

                                        @if($cell && $cell['type'] === 'spanned')
                                            {{-- Tercover --}}
                                        @elseif($cell && $cell['type'] === 'event')
                                            @php
                                                $j = $cell['jadwal'];
                                                $m = $cell['modul'];
                                                $rowspan = $cell['rowspan'] ?? 1;
                                                $colspan = $cell['colspan'] ?? 1;
                                                $mColor = $m->warna ?: '#4f46e5';
                                                $dosenPendampingList = $j->dosen_pendamping_details;
                                            @endphp
                                            <td rowspan="{{ $rowspan }}" colspan="{{ $colspan }}"
                                                class="p-2 align-top transition relative group cursor-pointer hover:brightness-95 hover:shadow-xs"
                                                :class="activeEvent.id === {{ $j->id }} ? 'ring-2 ring-brand-500 shadow-md' : ''"
                                                style="background-color: {{ $mColor }}18; border-left: 3.5px solid {{ $mColor }};"
                                                @click="selectEvent({{ json_encode($j) }}, {{ json_encode($m) }}, {{ json_encode($j->dosen) }}, {{ json_encode($dosenPendampingList) }})">
                                                
                                                <div class="h-full flex flex-col justify-between space-y-2">
                                                    <div>
                                                        <div class="flex items-start justify-between gap-1.5">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block leading-tight">
                                                                {{ $j->tipe_kegiatan }}
                                                            </span>
                                                            @if($j->kelompok && $j->kelompok !== 'Semua Kelompok')
                                                                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-white/90 border border-slate-300 text-slate-800 shrink-0">
                                                                    {{ $j->kelompok }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <h4 class="font-bold text-slate-900 text-xs sm:text-[13px] leading-snug mt-1 hover:text-brand-700 transition">
                                                            {{ $j->nama_kuliah }}
                                                        </h4>

                                                        @if($j->ruangan)
                                                            <div class="flex items-center gap-1 text-[10px] text-slate-600 mt-1">
                                                                <i class="bi bi-geo-alt text-[9px] text-slate-400"></i>
                                                                <span class="truncate">{{ $j->ruangan }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Inisial Dosen Pengampu -->
                                                    <div class="pt-1.5 border-t border-slate-300/60 mt-auto">
                                                        <div class="flex items-center gap-1 flex-wrap">
                                                            @if($j->dosen && $j->dosen->kode)
                                                                <span class="inline-flex items-center justify-center font-extrabold text-[10px] sm:text-[11px] bg-white border border-slate-300 text-slate-900 px-1.5 py-0.5 rounded shadow-2xs"
                                                                      title="{{ $j->dosen->nama }} (Dosen Utama)">
                                                                    {{ $j->dosen->kode }}
                                                                </span>
                                                            @endif

                                                            @foreach($dosenPendampingList as $dp)
                                                                <span class="inline-flex items-center justify-center font-bold text-[10px] sm:text-[11px] bg-white/90 border border-slate-300 text-slate-800 px-1.5 py-0.5 rounded shadow-2xs"
                                                                      title="{{ $dp['nama'] ?: $dp['kode'] }} (Dosen Pendamping)">
                                                                    {{ $dp['kode'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Quick Edit Icon Hover -->
                                                <div class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition no-print">
                                                    <a href="{{ route('admin.jadwals.edit', $j->id) }}" 
                                                       @click.stop=""
                                                       class="w-6 h-6 rounded-lg bg-white/90 hover:bg-white text-slate-700 hover:text-brand-600 shadow-xs border border-slate-200 flex items-center justify-center text-xs"
                                                       title="Ubah Jadwal">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        @else
                                            @php
                                                $m = $cell['modul'] ?? null;
                                                $grp = $cell['kelompok'] ?? '';
                                            @endphp
                                            <td class="p-2 align-middle text-center slot-cell group relative hover:bg-brand-50/30 transition">
                                                <a href="{{ route('admin.jadwals.create', [
                                                        'modul_id' => $m ? $m->id : '',
                                                        'tanggal' => $selectedDate,
                                                        'jam_mulai' => $slot['mulai'],
                                                        'jam_selesai' => $slot['selesai'],
                                                        'kelompok' => $grp,
                                                   ]) }}" 
                                                   class="add-slot-btn opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition inline-flex items-center gap-1 px-2 py-1 bg-white hover:bg-brand-600 text-slate-500 hover:text-white rounded-lg text-[10px] font-bold border border-slate-200 shadow-2xs no-print"
                                                   title="Tambah Jadwal di Sesi Ini">
                                                    <i class="bi bi-plus-lg text-[9px]"></i>
                                                    <span>Plot</span>
                                                </a>
                                            </td>
                                        @endif
                                    @endfor
                                </tr>

                                @if($s === 4)
                                    <tr class="bg-slate-200/80 font-bold text-slate-700">
                                        <td class="px-2 py-2 text-center text-[10px] font-extrabold text-slate-700 bg-slate-300/70 border-r border-slate-300">
                                            11.50 - 13.00
                                        </td>
                                        <td colspan="{{ count($columns) }}" 
                                            class="px-4 py-2 text-center text-xs tracking-wider uppercase font-extrabold text-slate-600 bg-slate-200/90 shadow-inner">
                                            <div class="flex items-center justify-center gap-2">
                                                <i class="bi bi-cup-hot text-amber-700"></i>
                                                <span>ISHOMA &bull; Istirahat, Sholat, Makan</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tab Minggu -->
                <div class="bg-slate-100 border-t border-slate-200 px-4 py-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
                    <div class="flex items-center gap-1 overflow-x-auto">
                        <i class="bi bi-file-earmark-spreadsheet text-emerald-600 text-base mr-2"></i>
                        <span class="text-xs font-bold text-slate-500 mr-2 uppercase">Tab Minggu:</span>
                        @foreach($daftarMinggu as $m)
                            <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['minggu' => $m['nomor']])) }}" 
                               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5 {{ $mingguNomor == $m['nomor'] ? 'bg-white text-emerald-800 border border-emerald-300 shadow-xs ring-1 ring-emerald-400/40' : 'text-slate-600 hover:bg-slate-200/80' }}">
                                <span>{{ $m['label'] }}</span>
                                @if($mingguNomor == $m['nomor'])
                                    <i class="bi bi-check-circle-fill text-[10px] text-emerald-600"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>Total {{ count($columns) }} Kolom Terplot &bull; 9 Sesi Standar</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- KOLOM KANAN: PANEL INFORMASI & DETAIL KEGIATAN                 -->
        <!-- ============================================================== -->
        <div id="panel-informasi-detail" class="lg:col-span-4 xl:col-span-4 2xl:col-span-3 lg:sticky lg:top-20 space-y-5 no-print">
            
            <!-- KEADAAN 1: SAAT ADA JADWAL YANG DIKLIK -->
            <div x-cloak x-show="hasSelectedEvent" 
                 class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 space-y-4 transition-all">
                
                <!-- Header Rincian -->
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="space-y-1.5 min-w-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white shadow-2xs"
                              :style="'background-color: ' + (activeEventModul.warna || '#4f46e5')">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            <span x-text="activeEventModul.nama || 'Modul Perkuliahan'"></span>
                        </span>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug" x-text="activeEvent.nama_kuliah"></h3>
                        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium flex-wrap">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px]" 
                                  x-text="activeEvent.tipe_kegiatan || 'Kuliah'"></span>
                            <span x-show="activeEvent.kelompok && activeEvent.kelompok !== 'Semua Kelompok'" class="text-slate-400">&bull;</span>
                            <span x-show="activeEvent.kelompok && activeEvent.kelompok !== 'Semua Kelompok'" 
                                  class="text-brand-600 font-bold" 
                                  x-text="'Kelompok: ' + activeEvent.kelompok"></span>
                        </div>
                    </div>

                    <button type="button" @click="hasSelectedEvent = false" 
                            title="Tutup Rincian" 
                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition shrink-0">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <!-- Informasi Rincian Jadwal -->
                <div class="space-y-3 text-xs">
                    <!-- Waktu & Tanggal -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 text-base">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Waktu Sesi:</span>
                            <div class="font-bold text-slate-900 text-xs sm:text-sm mt-0.5 font-mono" 
                                 x-text="(activeEvent.jam_mulai ? activeEvent.jam_mulai.substring(0,5) : '') + ' - ' + (activeEvent.jam_selesai ? activeEvent.jam_selesai.substring(0,5) : '') + ' WIB'"></div>
                            <span class="text-[11px] text-slate-500 block mt-0.5" 
                                  x-text="formatDateIndo(activeEvent.tanggal)"></span>
                        </div>
                    </div>

                    <!-- Ruangan -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-base">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ruangan / Tempat:</span>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm mt-0.5 block" x-text="activeEvent.ruangan || '-'"></span>
                        </div>
                    </div>

                    <!-- Dosen Utama -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Dosen Pengampu Utama:</span>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200">
                            <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-brand-100 text-brand-800 border border-brand-200"
                                  x-text="activeEventDosen ? activeEventDosen.kode : '-'"></span>
                            <span class="font-bold text-slate-800 text-xs" x-text="activeEventDosen ? activeEventDosen.nama : 'Belum ditentukan'"></span>
                        </div>
                    </div>

                    <!-- Dosen Pendamping -->
                    <div x-show="activeEventDosenPendamping && activeEventDosenPendamping.length > 0" 
                         class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Dosen Pendamping / Tutor:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="dp in activeEventDosenPendamping" :key="dp.kode">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-800 shadow-2xs">
                                    <span class="font-bold text-brand-700" x-text="'[' + dp.kode + ']'"></span>
                                    <span x-text="dp.nama || dp.kode"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Catatan / Silabus -->
                    <div x-show="activeEvent.catatan" 
                         class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Catatan Kuliah / Silabus:</span>
                        <p class="text-slate-700 text-xs leading-relaxed whitespace-pre-line" x-text="activeEvent.catatan"></p>
                    </div>
                </div>

                <!-- Tombol Aksi Admin -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <form :action="'/admin/jadwals/' + activeEvent.id" method="POST" @submit="confirmDeleteEvent($event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold transition">
                            <i class="bi bi-trash"></i>
                            <span>Hapus</span>
                        </button>
                    </form>

                    <div class="flex items-center gap-2">
                        <a :href="'/admin/jadwals/' + activeEvent.id + '/edit'" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-semibold transition shadow-xs">
                            <i class="bi bi-pencil-square"></i>
                            <span>Ubah</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- KEADAAN 2: BELUM ADA JADWAL DIKLIK -->
            <div x-show="!hasSelectedEvent" class="space-y-5">
                
                <!-- Kartu Petunjuk -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-xl shadow-2xs border border-brand-100">
                        <i class="bi bi-cursor-fill animate-bounce"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Pilih Jadwal di Matriks</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Klik salah satu jadwal pada tabel matriks di sebelah kiri untuk melihat rincian jam, dosen, ruangan, dan tindakan.
                        </p>
                    </div>
                </div>

                <!-- Daftar Modul & Legenda Warna -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Modul Semester {{ $semesterFilter }}:</h4>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ count($moduls) }} Modul</span>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($moduls as $m)
                            <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100 flex items-center justify-between text-xs transition">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-3 h-3 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $m->warna }};"></span>
                                    <span class="font-semibold text-slate-800 truncate">{{ $m->nama }}</span>
                                </div>
                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 shrink-0">
                                    {{ $m->kode }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Jadwal Terplot pada Hari Terpilih -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">
                            Jadwal {{ $selectedDay['nama'] }} ({{ $selectedDay['short_date'] }}):
                        </h4>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                            {{ count($jadwalsOnDate) }} Sesi
                        </span>
                    </div>

                    @if(count($jadwalsOnDate) > 0)
                        <div class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                            @foreach($jadwalsOnDate as $jod)
                                @php
                                    $dosenPendampingList = $jod->dosen_pendamping_details;
                                @endphp
                                <div class="p-2.5 rounded-xl border border-slate-200/70 bg-white hover:border-brand-300 hover:shadow-xs transition cursor-pointer group"
                                     @click="selectEvent({{ json_encode($jod) }}, {{ json_encode($jod->modul) }}, {{ json_encode($jod->dosen) }}, {{ json_encode($dosenPendampingList) }})">
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <span class="text-[10px] font-bold text-slate-500 font-mono">
                                            {{ substr($jod->jam_mulai, 0, 5) }} - {{ substr($jod->jam_selesai, 0, 5) }}
                                        </span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700">
                                            {{ $jod->tipe_kegiatan }}
                                        </span>
                                    </div>
                                    <h5 class="text-xs font-bold text-slate-900 group-hover:text-brand-600 transition leading-snug">
                                        {{ $jod->nama_kuliah }}
                                    </h5>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                        <span class="truncate">{{ $jod->modul->nama ?? '' }}</span>
                                        <span class="font-bold text-slate-700 shrink-0">[{{ $jod->dosen ? $jod->dosen->kode : '-' }}]</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-100">
                            Tidak ada jadwal terplot pada hari ini.
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    function matrixApp() {
        return {
            hasSelectedEvent: false,
            activeEvent: {},
            activeEventModul: {},
            activeEventDosen: {},
            activeEventDosenPendamping: [],

            selectEvent(jadwal, modul, dosen, dosenPendamping) {
                this.activeEvent = jadwal || {};
                this.activeEventModul = modul || {};
                this.activeEventDosen = dosen || {};
                this.activeEventDosenPendamping = dosenPendamping || [];
                this.hasSelectedEvent = true;

                if (window.innerWidth < 1024) {
                    const panel = document.getElementById('panel-informasi-detail');
                    if (panel) {
                        panel.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            },

            formatDateIndo(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            },

            confirmDeleteEvent(e) {
                e.preventDefault();
                const form = e.target;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Jadwal Kuliah?',
                        text: 'Jadwal "' + (this.activeEvent.nama_kuliah || '') + '" akan dihapus permanen dari kalender dan matriks.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus Jadwal',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                        form.submit();
                    }
                }
            }
        };
    }
</script>
@endsection
