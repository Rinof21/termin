<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Perkuliahan S1 Kedokteran - Portal Publik TERMIN</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- FullCalendar Stylesheet & Script -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>

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
            color: #ffffff !important;
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
        .fc-daygrid-block-event {
            color: #ffffff !important;
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
        .fc-timegrid-slot {
            height: 2.25rem !important;
        }
        .fc-timegrid-event {
            border-radius: 0.5rem !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .fc-timegrid-event .fc-event-main {
            padding: 0 !important;
            height: 100%;
        }
        .fc-timegrid-now-indicator-line {
            border-color: #ef4444;
        }
        .fc-event-main-custom {
            font-size: 0.725rem;
            line-height: 1.25;
            color: #ffffff;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        @media print {
            header, nav, .no-print, #panel-informasi-detail {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
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
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col" x-data="publicPortalApp()">

    <!-- Top Public Navbar (Container Fluid) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs no-print">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3">
                
                <!-- Brand / Logo Portal -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-indigo-500 text-white flex items-center justify-center font-black text-lg shadow-sm shadow-brand-500/20">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 tracking-tight text-base sm:text-lg">TERMIN</span>
                                <span class="text-[10px] bg-brand-100 text-brand-800 font-extrabold px-2 py-0.5 rounded-full border border-brand-200">
                                    S1 KEDOKTERAN
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium hidden sm:block">Portal Jadwal Kuliah & Blok Pembelajaran</p>
                        </div>
                    </a>
                </div>

                <!-- Center: Informasi Tahun Ajaran, Termin & Rentang Tanggal -->
                <div class="hidden md:flex items-center gap-2.5 px-3.5 py-1.5 bg-slate-100/90 border border-slate-200/90 rounded-xl shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ ($currentTermin && $currentTermin->is_aktif) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span class="font-bold text-xs text-slate-900 tracking-tight">
                            {{ $currentTermin ? $currentTermin->nama : 'Termin Perkuliahan' }}
                        </span>
                        @if($currentTermin && $currentTermin->is_aktif)
                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                                AKTIF
                            </span>
                        @endif
                    </div>

                    <span class="text-slate-300">|</span>

                    <span class="text-xs text-slate-600 font-semibold">
                        T.A. {{ $currentTermin->tahun_ajaran ?? '2026/2027' }}
                    </span>

                    @if($currentTermin && $currentTermin->rentang_tanggal && $currentTermin->rentang_tanggal !== '-')
                        <span class="text-slate-300 hidden lg:inline">|</span>
                        <span class="hidden lg:inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                            <i class="bi bi-calendar-range text-slate-400 text-xs"></i>
                            <span>{{ $currentTermin->rentang_tanggal }}</span>
                        </span>
                    @endif
                </div>

                <!-- Right Action Buttons: Filter Semester, Cetak & Login -->
                <div class="flex items-center gap-2">
                    <!-- Dropdown Semester Filter -->
                    <form method="GET" action="{{ route('home') }}" class="hidden md:inline-flex">
                        <input type="hidden" name="view" :value="activeView">
                        <input type="hidden" name="minggu" value="{{ $mingguNomor }}">
                        <input type="hidden" name="hari" value="{{ $selectedDay['slug'] }}">
                        <select name="semester" onchange="this.form.submit()" 
                                class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-brand-500 shadow-2xs cursor-pointer">
                            <option value="Ganjil" {{ $semesterFilter === 'Ganjil' ? 'selected' : '' }}>Sem. Ganjil {{ $activeSemester === 'Ganjil' ? '(Aktif)' : '' }}</option>
                            <option value="Genap" {{ $semesterFilter === 'Genap' ? 'selected' : '' }}>Sem. Genap {{ $activeSemester === 'Genap' ? '(Aktif)' : '' }}</option>
                        </select>
                    </form>

                    <!-- Tombol Cetak / PDF -->
                    <button type="button" onclick="window.print()" 
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition"
                            title="Cetak Jadwal Perkuliahan">
                        <i class="bi bi-printer text-sm"></i>
                        <span class="hidden md:inline">Cetak</span>
                    </button>

                    <!-- Status Login / Tombol Masuk Admin -->
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition">
                            <i class="bi bi-speedometer2 text-sm"></i>
                            <span>Panel Admin</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                            <i class="bi bi-shield-lock text-sm"></i>
                            <span>Masuk Admin</span>
                        </a>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Area (Container Fluid) -->
    <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-5 space-y-5">

        <!-- ============================================================== -->
        <!-- LAYOUT 2 KOLOM: KOLOM KIRI (JADWAL) + KOLOM KANAN (INFO/DETAIL) -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- KOLOM KIRI (JADWAL: MATRIKS ATAU KALENDER) -->
            <div class="lg:col-span-8 xl:col-span-8 2xl:col-span-9 space-y-5 min-w-0">
                
                <!-- SUB-VIEW 1: MATRIKS SPREADSHEET -->
                <div x-show="activeView === 'matrix'" x-cloak class="space-y-5">
                    
                    <!-- Navigasi 6 Minggu Termin Berjalan -->
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3.5 no-print">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-100 shrink-0">
                                    <i class="bi bi-grid-3x3"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">PILIH MINGGU:</span>
                                    <span class="text-xs font-bold text-slate-900">{{ $selectedMinggu['label'] ?? 'Minggu ' . $mingguNomor }}</span>
                                    <span class="text-[11px] text-slate-500 font-normal">({{ $selectedMinggu['rentang'] ?? '' }})</span>
                                </div>
                            </div>

                            <!-- Tab Minggu (Minggu 1 s/d Minggu 6) -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
                                @foreach($daftarMinggu as $m)
                                    <a href="{{ route('home', array_merge(request()->except('tanggal'), ['view' => 'matrix', 'minggu' => $m['nomor']])) }}" 
                                       class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition flex flex-col items-center leading-tight {{ $mingguNomor == $m['nomor'] ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        <span>{{ $m['label'] }}</span>
                                        <span class="text-[9px] font-normal {{ $mingguNomor == $m['nomor'] ? 'text-emerald-100' : 'text-slate-400' }}">
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
                                    <a href="{{ route('home', array_merge(request()->except('tanggal'), ['view' => 'matrix', 'hari' => $d['slug']])) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition relative {{ $selectedDay['slug'] === $d['slug'] ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                        <span>{{ $d['nama'] }}</span>
                                        <span class="text-[10px] opacity-75 font-normal">({{ $d['short_date'] }})</span>
                                        @if(!empty($d['is_today']))
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Hari Ini"></span>
                                        @endif
                                    </a>
                                @endforeach

                                <!-- Tombol Pintas Lompat ke Hari Ini -->
                                <a href="{{ route('home', ['view' => 'matrix', 'semester' => $semesterFilter]) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold transition shrink-0 shadow-2xs ml-auto"
                                   title="Lompat ke Jadwal Hari Ini">
                                    <i class="bi bi-geo-alt-fill text-emerald-600"></i>
                                    <span>Hari Ini</span>
                                </a>

                                <!-- Tombol Kembali ke Kalender Termin -->
                                <a href="{{ route('home', ['view' => 'calendar', 'semester' => $semesterFilter]) }}" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 rounded-lg text-xs font-bold transition shrink-0 shadow-2xs"
                                   title="Kembali ke Kalender Termin">
                                    <i class="bi bi-calendar3 text-brand-600"></i>
                                    <span>Kalender</span>
                                </a>
                            </div>

                            <!-- Opsi Toggle Pisah Kelompok A.1/A.2 -->
                            <div class="flex items-center gap-3 shrink-0">
                                <form method="GET" action="{{ route('home') }}" class="flex items-center gap-2">
                                    <input type="hidden" name="view" value="matrix">
                                    <input type="hidden" name="semester" value="{{ $semesterFilter }}">
                                    <input type="hidden" name="minggu" value="{{ $mingguNomor }}">
                                    <input type="hidden" name="hari" value="{{ $selectedDay['slug'] }}">
                                    <input type="hidden" name="split_kelompok" value="0">
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
                                    <a href="{{ route('home', array_merge(request()->except('tanggal'), ['view' => 'matrix', 'hari' => $days[$prevIndex]['slug']])) }}" 
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
                                    <a href="{{ route('home', array_merge(request()->except('tanggal'), ['view' => 'matrix', 'hari' => $days[$nextIndex]['slug']])) }}" 
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
                                            <!-- Label Jam Sesi -->
                                            <td class="w-28 min-w-[115px] px-2 py-2.5 text-center bg-slate-50/90 text-slate-700 align-middle border-r font-medium">
                                                <span class="block text-[11px] font-bold text-slate-900 tracking-tight">
                                                    {{ $slot['label'] }}
                                                </span>
                                                <span class="inline-block mt-0.5 text-[10px] font-semibold text-slate-500 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs">
                                                    Sesi {{ $slot['sesi'] }}
                                                </span>
                                            </td>

                                            <!-- Sel Kegiatan per Kolom Modul -->
                                            @for($c = 0; $c < count($columns); $c++)
                                                @php
                                                    $cell = $grid[$s][$c] ?? null;
                                                @endphp

                                                @if($cell && $cell['type'] === 'spanned')
                                                    {{-- Tercover rowspan/colspan --}}
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

                                                            <!-- Inisial Dosen Pengampu Sesuai Format Spreadsheet -->
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
                                                    </td>
                                                @else
                                                    @auth
                                                        @php
                                                            $m = $cell['modul'] ?? null;
                                                            $grp = $cell['kelompok'] ?? '';
                                                            $createSlotUrl = route('admin.jadwals.create', [
                                                                'modul_id' => $m ? $m->id : '',
                                                                'tanggal' => $selectedDate,
                                                                'jam_mulai' => $slot['mulai'],
                                                                'jam_selesai' => $slot['selesai'],
                                                                'kelompok' => $grp,
                                                                'sesi' => $slot['sesi'],
                                                            ]);
                                                        @endphp
                                                        <td class="p-2 align-middle text-center slot-cell group relative hover:bg-brand-50/40 transition cursor-pointer"
                                                            onclick="window.location.href='{{ $createSlotUrl }}'">
                                                            <a href="{{ $createSlotUrl }}" 
                                                               class="opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-brand-600 text-slate-600 hover:text-white rounded-lg text-[10px] font-bold border border-slate-200 shadow-xs no-print"
                                                               title="Plot Jadwal di Sesi Ini (Modul {{ $m ? $m->kode : '' }} • Sesi {{ $slot['sesi'] }})">
                                                                <i class="bi bi-plus-lg text-[9px]"></i>
                                                                <span>Plot</span>
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td class="p-2 align-middle text-center text-slate-300 text-xs">
                                                            -
                                                        </td>
                                                    @endauth
                                                @endif
                                            @endfor
                                        </tr>

                                        <!-- Baris ISHOMA Penuh -->
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
                                    <a href="{{ route('home', array_merge(request()->query(), ['view' => 'matrix', 'minggu' => $m['nomor']])) }}" 
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
                                <span>Total {{ count($columns) }} Kolom &bull; 9 Sesi Standar</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SUB-VIEW 2: KALENDER INTERAKTIF -->
                <div x-show="activeView === 'calendar'" x-cloak>
                    <!-- Kalender Card -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
                        <div id="publicCalendar"></div>
                    </div>
                </div>

            </div>

            <!-- ============================================================== -->
            <!-- KOLOM KANAN: PANEL INFORMASI & RINCIAN DETAIL KEGIATAN          -->
            <!-- ============================================================== -->
            <div id="panel-informasi-detail" class="lg:col-span-4 xl:col-span-4 2xl:col-span-3 lg:sticky lg:top-20 space-y-5 no-print">
                
                <!-- Filter Bar Kalender (Dipindahkan ke Panel Kanan) -->
                <div x-show="activeView === 'calendar'" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-funnel text-brand-600"></i>
                            <span>Filter Jadwal</span>
                        </span>
                        <button type="button" 
                                @click="resetCalendarFilters()" 
                                class="text-[11px] text-brand-600 hover:text-brand-700 font-semibold transition">
                            Reset
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Modul</label>
                            <select x-model="calendarFilterModul" @change="refetchCalendarEvents()"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                                <option value="">Semua Modul</option>
                                @foreach($moduls as $m)
                                    <option value="{{ $m->id }}">[{{ $m->kode }}] {{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Dosen</label>
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
                                            x-show="calendarFilterDosen || dosenSearchQuery"
                                            @click.stop="clearDosenFilter()"
                                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                                            title="Hapus pilihan dosen">
                                        <i class="bi bi-x-circle-fill text-xs"></i>
                                    </button>
                                    <div x-show="!calendarFilterDosen && !dosenSearchQuery" class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
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
                                            :class="!calendarFilterDosen ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-700 font-medium'">
                                        <span>Semua Dosen</span>
                                        <span x-show="!calendarFilterDosen" class="text-brand-600"><i class="bi bi-check2"></i></span>
                                    </button>

                                    <template x-for="d in filteredDosenList" :key="d.id">
                                        <button type="button"
                                                @click="selectDosenFilter(d.id, d.label)"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-brand-50 hover:text-brand-700 flex items-center justify-between gap-2 transition"
                                                :class="calendarFilterDosen == d.id ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-700 font-medium'">
                                            <div class="truncate">
                                                <span class="font-mono text-[11px] text-slate-500 font-normal mr-1" x-text="'[' + d.kode + ']'"></span>
                                                <span x-text="d.nama"></span>
                                            </div>
                                            <span x-show="calendarFilterDosen == d.id" class="text-brand-600 shrink-0"><i class="bi bi-check2"></i></span>
                                        </button>
                                    </template>

                                    <div x-show="filteredDosenList.length === 0" class="px-3 py-3 text-center text-xs text-slate-400">
                                        Dosen tidak ditemukan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pilih Kelompok</label>
                            <select x-model="calendarFilterKelompok" @change="refetchCalendarEvents()"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                                <option value="">Semua Kelompok</option>
                                @foreach($kelompoks as $k)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- KEADAAN 1: SAAT ADA JADWAL YANG DIKLIK -->
                <div x-cloak x-show="hasSelectedEvent" 
                     class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 space-y-4 transition-all">
                    
                    <!-- Header Rincian dengan Aksen Warna Modul -->
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

                        <!-- Tombol Tutup Panel Rincian -->
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
                                     x-text="activeEvent.jam || ((activeEvent.jam_mulai ? activeEvent.jam_mulai.substring(0,5) : '') + ' - ' + (activeEvent.jam_selesai ? activeEvent.jam_selesai.substring(0,5) : '') + ' WIB')"></div>
                                <span class="text-[11px] text-slate-500 block mt-0.5" 
                                      x-text="activeEvent.tanggal_indo || formatDateIndo(activeEvent.tanggal)"></span>
                            </div>
                        </div>

                        <!-- Ruangan / Tempat -->
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

                    <!-- Footer Kartu Detail -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Status: Terverifikasi Akademik</span>
                        <button type="button" @click="hasSelectedEvent = false" 
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                            Tutup Detail
                        </button>
                    </div>

                </div>

                <!-- KEADAAN 2: BELUM ADA JADWAL DIKLIK (INFORMASI AKADEMIK & LEGENDA) -->
                <div x-show="!hasSelectedEvent" class="space-y-5">
                    
                    <!-- Legenda & Daftar Modul Pembelajaran -->
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
                                {{ count($jadwalsOnDate) }} Kegiatan
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
                                Tidak ada kegiatan terplot pada hari ini.
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-5 text-center text-xs text-slate-500 no-print mt-auto">
        <div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-brand-600 text-white flex items-center justify-center font-bold text-xs">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span class="font-bold text-slate-800">TERMIN FK</span> &bull; 
                <span>Fakultas Kedokteran Program Studi S1 Kedokteran</span>
            </div>
            <div>
                &copy; {{ date('Y') }} Sistem Plotting & Jadwal Perkuliahan Termin. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <!-- Script Portal Publik -->
    <script>
        function publicPortalApp() {
            return {
                activeView: '{{ $currentView }}',
                hasSelectedEvent: false,
                activeEvent: {},
                activeEventModul: {},
                activeEventDosen: {},
                activeEventDosenPendamping: [],

                calendarInstance: null,
                calendarFilterModul: '',
                calendarFilterDosen: '',
                calendarFilterKelompok: '',

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
                    if (this.calendarFilterDosen) {
                        const cur = this.dosenList.find(d => String(d.id) === String(this.calendarFilterDosen));
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
                    this.calendarFilterDosen = id ? String(id) : '';
                    this.dosenSearchQuery = label || '';
                    this.isDosenDropdownOpen = false;
                    this.refetchCalendarEvents();
                },

                selectFirstFilteredDosen() {
                    if (this.filteredDosenList.length > 0) {
                        const first = this.filteredDosenList[0];
                        this.selectDosenFilter(first.id, first.label);
                    }
                },

                clearDosenFilter() {
                    this.calendarFilterDosen = '';
                    this.dosenSearchQuery = '';
                    this.isDosenDropdownOpen = false;
                    this.refetchCalendarEvents();
                },

                closeDosenDropdown() {
                    this.isDosenDropdownOpen = false;
                    if (this.calendarFilterDosen) {
                        const found = this.dosenList.find(d => String(d.id) === String(this.calendarFilterDosen));
                        if (found) {
                            this.dosenSearchQuery = found.label;
                        }
                    } else {
                        this.dosenSearchQuery = '';
                    }
                },

                resetCalendarFilters() {
                    this.calendarFilterModul = '';
                    this.calendarFilterDosen = '';
                    this.calendarFilterKelompok = '';
                    this.dosenSearchQuery = '';
                    this.isDosenDropdownOpen = false;
                    this.refetchCalendarEvents();
                },

                init() {
                    if (this.activeView === 'calendar') {
                        this.$nextTick(() => {
                            this.initCalendar();
                        });
                    }
                },

                setView(viewName) {
                    this.activeView = viewName;
                    const url = new URL(window.location);
                    url.searchParams.set('view', viewName);
                    window.history.replaceState({}, '', url);

                    if (viewName === 'calendar') {
                        this.$nextTick(() => {
                            if (!this.calendarInstance) {
                                this.initCalendar();
                            } else {
                                this.calendarInstance.render();
                            }
                        });
                    }
                },

                selectEvent(jadwal, modul, dosen, dosenPendamping) {
                    this.activeEvent = jadwal || {};
                    this.activeEventModul = modul || {};
                    this.activeEventDosen = dosen || {};
                    this.activeEventDosenPendamping = dosenPendamping || [];
                    this.hasSelectedEvent = true;

                    // Pada layar mobile, scroll otomatis ke panel informasi
                    if (window.innerWidth < 1024) {
                        const panel = document.getElementById('panel-informasi-detail');
                        if (panel) {
                            panel.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                },

                initCalendar() {
                    const calendarEl = document.getElementById('publicCalendar');
                    if (!calendarEl || this.calendarInstance) return;

                    this.calendarInstance = new FullCalendar.Calendar(calendarEl, {
                        locale: 'id',
                        initialView: 'dayGridMonth',
                        initialDate: '{{ $selectedDate }}',
                        height: 'auto',
                        expandRows: false,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
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
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            meridiem: false,
                            hour12: false
                        },
                        dateClick: (info) => {
                            // Klik kotak tanggal kalender langsung membuka tampilan matriks sesuai tanggal tersebut
                            const clickedDate = info.dateStr;
                            window.location.href = `/?view=matrix&tanggal=${clickedDate}&semester={{ $semesterFilter }}`;
                        },
                        events: (info, successCallback, failureCallback) => {
                            const params = new URLSearchParams({
                                semester: '{{ $semesterFilter }}',
                                modul_id: this.calendarFilterModul,
                                dosen_id: this.calendarFilterDosen,
                                kelompok: this.calendarFilterKelompok
                            });
                            fetch(`/api/public-events?${params.toString()}`)
                                .then(res => res.json())
                                .then(data => successCallback(data))
                                .catch(err => {
                                    console.error('Gagal mengambil jadwal:', err);
                                    failureCallback(err);
                                });
                        },
                        eventContent: function(arg) {
                            const timeText = arg.timeText || '';
                            const props = arg.event.extendedProps || {};
                            const title = props.nama_kuliah || arg.event.title || '';
                            const inisial = props.dosen_inisial || (props.dosen_utama ? props.dosen_utama.kode : '');
                            const kelompok = props.kelompok && props.kelompok !== '-' && props.kelompok !== 'Semua Kelompok' ? props.kelompok : '';

                            if (arg.view.type === 'dayGridMonth') {
                                const el = document.createElement('div');
                                el.className = 'fc-event-main-custom flex items-center gap-1.5 text-[11px] leading-tight px-1.5 py-0.5 overflow-hidden text-white';
                                el.title = title;
                                el.innerHTML = `
                                    ${timeText ? `<span class="font-bold opacity-90 text-[10px] whitespace-nowrap shrink-0">${timeText}</span>` : ''}
                                    <span class="truncate font-semibold">${title}</span>
                                `;
                                return { domNodes: [el] };
                            }

                            if (arg.view.type === 'timeGridWeek' || arg.view.type === 'timeGridDay') {
                                const el = document.createElement('div');
                                el.className = 'fc-event-main-custom h-full flex flex-col justify-between p-1.5 text-white leading-tight overflow-hidden';
                                el.title = `${title} (${timeText})`;
                                el.innerHTML = `
                                    <div>
                                        <div class="text-[10px] font-bold opacity-90 flex items-center justify-between gap-1 mb-1">
                                            <span>${timeText}</span>
                                            ${props.ruangan && props.ruangan !== 'Belum ditentukan' && props.ruangan !== '-' ? `<span class="opacity-80 text-[9px] truncate max-w-[85px] font-normal"><i class="bi bi-geo-alt"></i> ${props.ruangan}</span>` : ''}
                                        </div>
                                        <div class="flex items-center gap-1 flex-wrap mb-1">
                                            ${kelompok ? `<span class="px-1.5 py-0.5 rounded bg-black/30 text-[9px] font-normal tracking-tight shrink-0">[${kelompok}]</span>` : ''}
                                            ${inisial ? `<span class="px-1.5 py-0.5 rounded bg-white/20 text-[10px] font-mono font-normal tracking-wide border border-white/30 shrink-0 inline-flex items-center gap-0.5" title="${props.dosen || ''}"><i class="bi bi-person text-[9px]"></i>[${inisial}]</span>` : ''}
                                        </div>
                                        <div class="text-xs font-bold leading-snug line-clamp-2">
                                            ${title}
                                        </div>
                                    </div>
                                    ${inisial ? `
                                        <div class="pt-1 mt-1 border-t border-white/20 text-[9px] truncate opacity-90 flex items-center gap-1 font-normal" title="${props.dosen || ''}">
                                            <span class="font-mono font-normal">[${inisial}]</span>
                                            <span class="truncate font-normal">${props.dosen_utama ? props.dosen_utama.nama : (props.dosen || '')}</span>
                                            ${props.dosen_pendamping && props.dosen_pendamping.length > 0 ? `<span class="opacity-85 text-[8px] font-normal shrink-0">(+${props.dosen_pendamping.length} tutor)</span>` : ''}
                                        </div>
                                    ` : ''}
                                `;
                                return { domNodes: [el] };
                            }

                            if (arg.view.type.startsWith('list')) {
                                const el = document.createElement('div');
                                el.className = 'flex items-center gap-2 font-medium text-slate-800';
                                el.innerHTML = `
                                    <span class="font-semibold">${title}</span>
                                    ${kelompok ? `<span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-normal border border-slate-200">[${kelompok}]</span>` : ''}
                                    ${inisial ? `<span class="text-xs text-slate-500 font-normal">(${inisial})</span>` : ''}
                                `;
                                return { domNodes: [el] };
                            }

                            const fallback = document.createElement('div');
                            fallback.className = 'fc-event-main-custom p-1 text-white';
                            fallback.innerHTML = `<span class="font-bold">${timeText ? timeText + ' ' : ''}</span>${title}`;
                            return { domNodes: [fallback] };
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
                        eventClick: (info) => {
                            const e = info.event;
                            const p = e.extendedProps;
                            this.selectEvent({
                                id: e.id,
                                nama_kuliah: p.nama_kuliah || e.title,
                                tipe_kegiatan: p.tipe_kegiatan,
                                jam_mulai: e.startStr.split('T')[1],
                                jam_selesai: e.endStr ? e.endStr.split('T')[1] : '',
                                jam: p.jam,
                                ruangan: p.ruangan,
                                kelompok: p.kelompok,
                                tanggal: e.startStr.split('T')[0],
                                tanggal_indo: p.tanggal_indo,
                                catatan: p.catatan
                            }, {
                                id: p.modul_id,
                                nama: p.modul_nama,
                                kode: p.modul_kode,
                                warna: p.modul_warna
                            }, p.dosen_utama, p.dosen_pendamping || []);
                        }
                    });

                    this.calendarInstance.render();
                },

                refetchCalendarEvents() {
                    if (this.calendarInstance) {
                        this.calendarInstance.refetchEvents();
                    }
                },

                formatDateIndo(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                }
            };
        }
    </script>
</body>
</html>
