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
    /* Styling tabel matriks spreadsheet */
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
        aside, header, nav, .no-print {
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
            margin: 1cm;
        }
    }
</style>

<div class="space-y-6" x-data="matrixApp()">
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
                Tampilan jadwal kuliah bergaya spreadsheet matrik per modul, kelompok, dan sesi waktu perkuliahan.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Filter Semester Switcher -->
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

    <!-- Navigasi 6 Minggu Termin Berjalan (Google Sheets Style) -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-4 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-lg border border-brand-100 shrink-0">
                    <i class="bi bi-grid-3x3"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-900">{{ $currentTermin ? $currentTermin->nama : 'Termin Perkuliahan' }}</span>
                        @if($currentTermin && $currentTermin->is_aktif)
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-extrabold px-2 py-0.5 rounded-full border border-emerald-200">AKTIF</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $currentTermin ? $currentTermin->rentang_tanggal : 'Pilih minggu dan hari perkuliahan di bawah' }}
                    </p>
                </div>
            </div>

            <!-- Tab Minggu (Minggu 1 s/d Minggu 6) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
                @foreach($daftarMinggu as $m)
                    <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['minggu' => $m['nomor']])) }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex flex-col items-center leading-tight {{ $mingguNomor == $m['nomor'] ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200' }}">
                        <span>{{ $m['label'] }}</span>
                        <span class="text-[10px] font-normal {{ $mingguNomor == $m['nomor'] ? 'text-brand-100' : 'text-slate-400' }}">
                            {{ \Carbon\Carbon::parse($m['tanggal_mulai'])->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($m['tanggal_selesai'])->translatedFormat('d M') }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Toolbar Pemilihan Hari (Senin - Sabtu) & Opsi Tampilan -->
        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 shrink-0">PILIH HARI:</span>
                @foreach($days as $d)
                    <a href="{{ route('admin.jadwals.matrix', array_merge(request()->query(), ['hari' => $d['slug']])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $selectedDay['slug'] === $d['slug'] ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        <span>{{ $d['nama'] }}</span>
                        <span class="text-[10px] opacity-75 font-normal">({{ $d['short_date'] }})</span>
                    </a>
                @endforeach
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
                        <span>Pisah Kolom Kelompok (A.1 / A.2)</span>
                    </label>
                </form>
            </div>
        </div>
    </div>

    <!-- Kontainer Matriks Spreadsheet -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden matrix-table-container">
        <!-- Banner Hari & Tanggal (Sesuai Referensi Spreadsheet) -->
        <div class="bg-gradient-to-r from-slate-100 via-sky-50 to-slate-100 py-3 px-4 border-b border-slate-200 text-center relative flex items-center justify-between">
            <!-- Tombol Hari Sebelumnya -->
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

            <!-- Banner Utama Hari, Tanggal, dan Tahun -->
            <div class="flex items-center gap-2 justify-center">
                <i class="bi bi-calendar-event text-brand-600 text-base"></i>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    {{ $selectedDay['formatted'] }}
                </h2>
                <span class="text-xs bg-brand-50 text-brand-700 font-semibold px-2 py-0.5 rounded border border-brand-200">
                    {{ $selectedMinggu['label'] ?? 'Minggu ' . $mingguNomor }}
                </span>
            </div>

            <!-- Tombol Hari Berikutnya -->
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
                <!-- Baris Header Atas: Nama Modul & Kelompok -->
                <thead>
                    <tr class="bg-slate-50 text-slate-800 text-xs">
                        <!-- Kolom Jam / Sesi (Kiri) -->
                        <th class="w-32 min-w-[125px] px-3 py-3 text-center font-bold text-slate-500 uppercase tracking-wider bg-slate-100/80">
                            Waktu / Sesi
                        </th>

                        <!-- Kolom-kolom Modul Pembelajaran (Baris atas nama modul nya) -->
                        @foreach($columns as $col)
                            @php
                                $colBg = $col['warna'] ? $col['warna'] . '15' : '#f8fafc';
                                $borderTopColor = $col['warna'] ?: '#4f46e5';
                            @endphp
                            <th class="min-w-[185px] max-w-[220px] px-3 py-3 text-center font-bold align-middle transition"
                                style="background-color: {{ $colBg }}; border-top: 3.5px solid {{ $borderTopColor }};">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $col['warna'] }}"></span>
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

                <!-- Baris-Baris Sesi Waktu Perkuliahan -->
                <tbody class="divide-y divide-slate-200 text-xs">
                    @for($s = 0; $s < count($timeSlots); $s++)
                        @php
                            $slot = $timeSlots[$s];
                            $isIshomaNext = ($s === 5); // Tepat setelah Sesi 5 (11.00 - 11.50)
                        @endphp

                        <!-- Baris Sesi Perkuliahan Biasa -->
                        <tr class="hover:bg-slate-50/40 transition">
                            <!-- Kolom Label Jam & Sesi -->
                            <td class="w-32 min-w-[125px] px-2.5 py-3 text-center bg-slate-50/90 text-slate-700 align-middle border-r font-medium">
                                <span class="block text-[11px] font-bold text-slate-900 tracking-tight">
                                    {{ $slot['label'] }}
                                </span>
                                <span class="inline-block mt-0.5 text-[10px] font-semibold text-slate-500 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs">
                                    Sesi {{ $slot['sesi'] }}
                                </span>
                            </td>

                            <!-- Sel-Sel Matriks per Kolom Modul -->
                            @for($c = 0; $c < count($columns); $c++)
                                @php
                                    $cell = $grid[$s][$c] ?? null;
                                @endphp

                                @if($cell && $cell['type'] === 'spanned')
                                    {{-- Sel sudah di-cover oleh rowspan baris sebelumnya atau colspan sebelah --}}
                                @elseif($cell && $cell['type'] === 'event')
                                    @php
                                        $j = $cell['jadwal'];
                                        $m = $cell['modul'];
                                        $rowspan = $cell['rowspan'] ?? 1;
                                        $colspan = $cell['colspan'] ?? 1;
                                        $mColor = $m->warna ?: '#4f46e5';
                                        
                                        // Ambil inisial dosen pendamping
                                        $dosenPendampingList = $j->dosen_pendamping_details;
                                    @endphp
                                    <td rowspan="{{ $rowspan }}" colspan="{{ $colspan }}"
                                        class="p-2 align-top transition relative group cursor-pointer"
                                        style="background-color: {{ $mColor }}18; border-left: 3px solid {{ $mColor }};"
                                        @click="openDetailModal({{ json_encode($j) }}, {{ json_encode($m) }}, {{ json_encode($j->dosen) }}, {{ json_encode($dosenPendampingList) }})">
                                        
                                        <!-- Kotak Kegiatan Jadwal (Sesuai Referensi Spreadsheet) -->
                                        <div class="h-full flex flex-col justify-between space-y-2">
                                            <!-- Bagian Atas: Judul Mata Kuliah & Tipe Kegiatan -->
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

                                            <!-- Bagian Bawah: Inisial Dosen Pengampu (Kotak Badge Inisial) -->
                                            <div class="pt-2 border-t border-slate-300/60 mt-auto">
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <!-- Inisial Dosen Utama -->
                                                    @if($j->dosen && $j->dosen->kode)
                                                        <span class="inline-flex items-center justify-center font-extrabold text-[10px] sm:text-[11px] bg-white border border-slate-300 text-slate-900 px-2 py-0.5 rounded shadow-2xs hover:bg-slate-50 transition"
                                                              title="{{ $j->dosen->nama }} (Dosen Utama)">
                                                            {{ $j->dosen->kode }}
                                                        </span>
                                                    @endif

                                                    <!-- Inisial Dosen-Dosen Pendamping (Grid / Deretan) -->
                                                    @foreach($dosenPendampingList as $dp)
                                                        <span class="inline-flex items-center justify-center font-bold text-[10px] sm:text-[11px] bg-white/90 border border-slate-300 text-slate-800 px-1.5 py-0.5 rounded shadow-2xs hover:bg-slate-50 transition"
                                                              title="{{ $dp['nama'] ?: $dp['kode'] }} (Dosen Pendamping)">
                                                            {{ $dp['kode'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tombol Quick Edit (Muncul Saat Hover) -->
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
                                    {{-- Sel Kosong: Bisa Diklik untuk Tambah Jadwal Cepat --}}
                                    @php
                                        $m = $cell['modul'] ?? null;
                                        $grp = $cell['kelompok'] ?? '';
                                    @endphp
                                    <td class="p-2 align-middle text-center slot-cell group relative hover:bg-brand-50/30 transition">
                                        <!-- Tombol Tambah Jadwal di Slot Ini -->
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

                        <!-- Baris ISHOMA Banner Penuh (Setelah Sesi 5: 11.50 - 13.00) -->
                        @if($s === 4)
                            <tr class="bg-slate-200/80 font-bold text-slate-700">
                                <td class="px-2.5 py-2.5 text-center text-[11px] font-extrabold text-slate-700 bg-slate-300/70 border-r border-slate-300">
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

        <!-- Footer Baris Tab Minggu ala Google Sheets (Bagian Bawah) -->
        <div class="bg-slate-100 border-t border-slate-200 px-4 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
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

    <!-- Modal Detail Jadwal (Interaktif Saat Sel Diklik) -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="showModal = false">
        
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden" 
             @click.away="showModal = false">
            
            <!-- Modal Header dengan Warna Modul -->
            <div class="p-5 text-white relative" :style="{ backgroundColor: activeEventModul.warna || '#4f46e5' }">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-extrabold tracking-wider bg-black/20 px-2.5 py-0.5 rounded-full text-white/90"
                          x-text="activeEvent.tipe_kegiatan || 'Jadwal Kuliah'"></span>
                    <button type="button" @click="showModal = false" class="text-white/80 hover:text-white transition">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
                <h3 class="text-lg font-bold mt-2 text-white leading-tight" x-text="activeEvent.nama_kuliah"></h3>
                <p class="text-xs text-white/80 mt-1" x-text="activeEventModul.nama"></p>
            </div>

            <!-- Modal Content Detail -->
            <div class="p-5 space-y-4 text-xs">
                <!-- Waktu & Tempat -->
                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block">Waktu Sesi:</span>
                        <span class="font-bold text-slate-800 text-xs" 
                              x-text="(activeEvent.jam_mulai ? activeEvent.jam_mulai.substring(0,5) : '') + ' - ' + (activeEvent.jam_selesai ? activeEvent.jam_selesai.substring(0,5) : '') + ' WIB'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block">Ruangan / Tempat:</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="activeEvent.ruangan || '-'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block">Kelompok Mahasiswa:</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="activeEvent.kelompok || 'Semua Kelompok'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block">Tanggal:</span>
                        <span class="font-bold text-slate-800 text-xs" x-text="formatDateIndo(activeEvent.tanggal)"></span>
                    </div>
                </div>

                <!-- Dosen Utama -->
                <div>
                    <span class="text-slate-400 uppercase font-bold text-[10px] block mb-1">Dosen Pengampu Utama:</span>
                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-brand-100 text-brand-800 border border-brand-200"
                              x-text="activeEventDosen ? activeEventDosen.kode : '-'"></span>
                        <span class="font-bold text-slate-800 text-xs" x-text="activeEventDosen ? activeEventDosen.nama : 'Belum ditentukan'"></span>
                    </div>
                </div>

                <!-- Dosen Pendamping -->
                <div x-show="activeEventDosenPendamping.length > 0">
                    <span class="text-slate-400 uppercase font-bold text-[10px] block mb-1">Dosen Pengampu Pendamping:</span>
                    <div class="flex flex-wrap gap-1.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <template x-for="dp in activeEventDosenPendamping" :key="dp.kode">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-800 shadow-2xs">
                                <span class="font-bold text-brand-700" x-text="'[' + dp.kode + ']'"></span>
                                <span x-text="dp.nama || dp.kode"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Catatan Kuliah -->
                <div x-show="activeEvent.catatan">
                    <span class="text-slate-400 uppercase font-bold text-[10px] block mb-1">Catatan / Keterangan:</span>
                    <p class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs whitespace-pre-line"
                       x-text="activeEvent.catatan"></p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <!-- Tombol Hapus -->
                <form :action="'/admin/jadwals/' + activeEvent.id" method="POST" @submit="confirmDeleteEvent($event)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-semibold transition">
                        <i class="bi bi-trash"></i>
                        <span>Hapus</span>
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showModal = false" 
                            class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold transition">
                        Tutup
                    </button>
                    <a :href="'/admin/jadwals/' + activeEvent.id + '/edit'" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold transition shadow-xs">
                        <i class="bi bi-pencil-square"></i>
                        <span>Ubah Jadwal</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function matrixApp() {
        return {
            showModal: false,
            activeEvent: {},
            activeEventModul: {},
            activeEventDosen: {},
            activeEventDosenPendamping: [],

            openDetailModal(jadwal, modul, dosen, dosenPendamping) {
                this.activeEvent = jadwal || {};
                this.activeEventModul = modul || {};
                this.activeEventDosen = dosen || {};
                this.activeEventDosenPendamping = dosenPendamping || [];
                this.showModal = true;
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

