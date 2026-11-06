@extends('layouts.app', ['title' => 'Data Modul Kedokteran'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Data Modul</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ viewMode: (new URLSearchParams(window.location.search).get('view') || localStorage.getItem('modul_view_mode') || 'grid') }">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Modul Perkuliahan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftar blok/modul kurikulum kedokteran untuk Semester Ganjil dan Genap</p>
        </div>
        <a href="{{ route('admin.moduls.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="bi bi-journal-plus"></i>
            <span>Tambah Modul Baru</span>
        </a>
    </div>

    <!-- Panel Kontrol Aktivasi Semester Aktif -->
    <div class="p-5 rounded-2xl border shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $activeSemester === 'Ganjil' ? 'bg-gradient-to-r from-indigo-50/90 via-white to-indigo-50/50 border-indigo-200/80' : 'bg-gradient-to-r from-emerald-50/90 via-white to-emerald-50/50 border-emerald-200/80' }}">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-xs {{ $activeSemester === 'Ganjil' ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white' }}">
                <i class="{{ $activeSemester === 'Ganjil' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill' }}"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Semester Akademik Aktif:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $activeSemester === 'Ganjil' ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                        Semester {{ $activeSemester }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 mt-0.5">
                    Modul yang aktif tampil di kalender perkuliahan dan plotting jadwal adalah modul <strong>Semester {{ $activeSemester }}</strong>.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.settings.semester') }}" method="POST"
                  data-confirm="Apakah Anda yakin ingin mengalihkan semester aktif ke Semester {{ $activeSemester === 'Ganjil' ? 'Genap' : 'Ganjil' }}? Tampilan jadwal dan modul akan otomatis disesuaikan.">
                @csrf
                <input type="hidden" name="semester" value="{{ $activeSemester === 'Ganjil' ? 'Genap' : 'Ganjil' }}">
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold shadow-xs transition {{ $activeSemester === 'Ganjil' ? 'bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 hover:border-emerald-400' : 'bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-300 hover:border-indigo-400' }}">
                    <i class="bi bi-arrow-repeat text-sm"></i>
                    <span>Aktifkan Semester {{ $activeSemester === 'Ganjil' ? 'Genap' : 'Ganjil' }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Tab Filter Semester & Pencarian -->
    <div class="space-y-3">
        <!-- Tabs Pilihan -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.moduls.index', ['tipe' => $activeSemester, 'search' => $search]) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition border {{ $tipeFilter === $activeSemester ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <i class="bi bi-star-fill text-[11px] text-amber-300"></i>
                <span>Semester Aktif ({{ $activeSemester }})</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === $activeSemester ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts[$activeSemester] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('admin.moduls.index', ['tipe' => 'Ganjil', 'search' => $search]) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ $tipeFilter === 'Ganjil' && $tipeFilter !== $activeSemester ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : ($tipeFilter === 'Ganjil' ? '' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50') }}">
                <i class="bi bi-sun text-[11px]"></i>
                <span>Semester Ganjil</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Ganjil' && $tipeFilter !== $activeSemester ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['Ganjil'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('admin.moduls.index', ['tipe' => 'Genap', 'search' => $search]) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ $tipeFilter === 'Genap' && $tipeFilter !== $activeSemester ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : ($tipeFilter === 'Genap' ? '' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50') }}">
                <i class="bi bi-moon-stars text-[11px]"></i>
                <span>Semester Genap</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Genap' && $tipeFilter !== $activeSemester ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['Genap'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('admin.moduls.index', ['tipe' => 'Semua', 'search' => $search]) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ $tipeFilter === 'Semua' ? 'bg-slate-800 text-white border-slate-800 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <i class="bi bi-collection text-[11px]"></i>
                <span>Semua Modul</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['Semua'] ?? 0 }}
                </span>
            </a>
        </div>

        <!-- Toolbar Pencarian & Pengalih Tampilan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.moduls.index') }}" class="flex items-center gap-3 flex-1">
                <input type="hidden" name="tipe" value="{{ $tipeFilter }}">
                <input type="hidden" name="view" :value="viewMode">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari berdasarkan nama modul, kode modul, atau termin..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                @if($search)
                    <a href="{{ route('admin.moduls.index', ['tipe' => $tipeFilter]) }}" 
                       class="px-3.5 py-2 text-sm text-slate-500 hover:text-slate-800 border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <!-- Pengalih Tampilan (Kartu / Tabel) -->
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl border border-slate-200/80 shrink-0 self-end sm:self-auto">
                <button type="button" 
                        @click="viewMode = 'grid'; localStorage.setItem('modul_view_mode', 'grid')"
                        :class="viewMode === 'grid' ? 'bg-white text-brand-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                        title="Tampilan Kartu (Grid)">
                    <i class="bi bi-grid-fill"></i>
                    <span>Kartu</span>
                </button>
                <button type="button" 
                        @click="viewMode = 'table'; localStorage.setItem('modul_view_mode', 'table')"
                        :class="viewMode === 'table' ? 'bg-white text-brand-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
                        title="Tampilan Tabel">
                    <i class="bi bi-table"></i>
                    <span>Tabel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 1. Tampilan Grid Kartu Modul -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse($moduls as $modul)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition overflow-hidden">
                <div class="h-2 w-full" style="background-color: {{ $modul->warna }};"></div>
                
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-md"
                                  style="background-color: {{ $modul->warna }}18; color: {{ $modul->warna }}; border: 1px solid {{ $modul->warna }}40;">
                                {{ $modul->kode }}
                            </span>
                            
                            <div class="flex items-center gap-1.5">
                                <!-- Badge Tahun Semester -->
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200/80">
                                    {{ $modul->kode_tahun_semester }}
                                </span>

                                <!-- Badge Tipe Semester -->
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $modul->tipe_semester === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $modul->tipe_semester }}
                                </span>
                                
                                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                    {{ $modul->termin }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start justify-between gap-1">
                            <h3 class="text-base font-bold text-slate-900 leading-snug">
                                <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                                   class="hover:text-brand-600 transition inline-flex items-center gap-1.5 group"
                                   title="Klik untuk membuka & kelola mata kuliah modul {{ $modul->nama }}">
                                    <span>{{ $modul->nama }}</span>
                                    <i class="bi bi-arrow-right text-xs text-slate-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition"></i>
                                </a>
                            </h3>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $modul->semester }}</p>

                        <!-- Indikator Status Keaktifan pada Kalender -->
                        @if($modul->tipe_semester === $activeSemester)
                            <div class="mt-2.5 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Tampil di Kalender (Aktif)</span>
                            </div>
                        @else
                            <div class="mt-2.5 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-semibold">
                                <i class="bi bi-eye-slash text-[10px]"></i>
                                <span>Tersembunyi (Semester Tidak Aktif)</span>
                            </div>
                        @endif

                        <!-- Koordinator Modul -->
                        <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                            <span class="text-slate-400 text-[11px] block font-medium">Koordinator:</span>
                            @if($modul->koordinator)
                                <div class="font-semibold text-slate-800 mt-0.5 truncate" title="{{ $modul->koordinator->nama }}">
                                    [{{ $modul->koordinator->kode }}] {{ $modul->koordinator->nama }}
                                </div>
                            @else
                                <span class="text-slate-400 italic">Belum ditentukan</span>
                            @endif
                        </div>

                        <!-- Jumlah Mata Kuliah & Jadwal -->
                        <div class="mt-3 pt-2.5 border-t border-dashed border-slate-100 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 flex items-center gap-1.5">
                                    <i class="bi bi-journal-text text-brand-600"></i>
                                    <span>Mata Kuliah:</span>
                                </span>
                                <strong class="text-slate-800">{{ $modul->mata_kuliahs_count ?? 0 }} Materi</strong>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 flex items-center gap-1.5">
                                    <i class="bi bi-calendar-check text-emerald-600"></i>
                                    <span>Jadwal Terplot:</span>
                                </span>
                                <strong class="text-slate-800">{{ $modul->jadwals_count }} Sesi</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tindakan -->
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-xl text-xs border border-brand-200 transition">
                            <i class="bi bi-card-checklist"></i>
                            <span>Kelola Mata Kuliah &rarr;</span>
                        </a>

                        <div class="flex items-center gap-1">
                            <!-- Tombol Duplikat Modul -->
                            <form action="{{ route('admin.moduls.duplicate', $modul->id) }}" method="POST"
                                  data-confirm="Duplikat modul '{{ addslashes($modul->nama) }}' beserta seluruh materi mata kuliah di dalamnya?"
                                  data-confirm-title="Duplikat Modul"
                                  class="inline">
                                @csrf
                                <button type="submit" 
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition"
                                        title="Duplikat Modul (Salin Modul & Mata Kuliah)">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>

                            <a href="{{ route('admin.moduls.edit', $modul->id) }}" 
                               class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition"
                               title="Ubah Modul">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('admin.moduls.destroy', $modul->id) }}" method="POST"
                                  data-confirm="Apakah Anda yakin ingin menghapus modul '{{ addslashes($modul->nama) }}'?" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                        title="Hapus Modul">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
                <i class="bi bi-journal-x text-3xl text-slate-400 mb-2"></i>
                <p class="text-sm font-semibold text-slate-700">Tidak ada modul yang sesuai dengan filter pilihan.</p>
                @if($tipeFilter !== 'Semua')
                    <a href="{{ route('admin.moduls.index', ['tipe' => 'Semua']) }}" class="inline-block mt-3 text-xs text-brand-600 font-semibold hover:underline">
                        Tampilkan Semua Modul (Ganjil & Genap)
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- 2. Tampilan Tabel Modul -->
    <div x-cloak x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5 text-center">No.</th>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Nama Modul</th>
                        <th class="px-5 py-3.5">Semester & Termin</th>
                        <th class="px-5 py-3.5">Koordinator</th>
                        <th class="px-5 py-3.5 text-center">Mata Kuliah</th>
                        <th class="px-5 py-3.5 text-center">Jadwal Terplot</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($moduls as $modul)
                        @php
                            $isAktif = $modul->tipe_semester === $activeSemester;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition group {{ $isAktif ? 'bg-emerald-50/15' : '' }}">
                            <!-- No -->
                            <td class="px-5 py-4 text-center font-semibold text-slate-400">
                                {{ $moduls->firstItem() + $loop->index }}
                            </td>

                            <!-- Kode -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md inline-flex items-center gap-1.5"
                                      style="background-color: {{ $modul->warna }}18; color: {{ $modul->warna }}; border: 1px solid {{ $modul->warna }}40;">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $modul->warna }};"></span>
                                    <span>{{ $modul->kode }}</span>
                                </span>
                            </td>

                            <!-- Nama Modul -->
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                                   class="font-bold text-slate-900 hover:text-brand-600 transition flex items-center gap-1.5 group/link text-sm">
                                    <span>{{ $modul->nama }}</span>
                                    <i class="bi bi-arrow-right text-[10px] text-slate-300 group-hover/link:text-brand-600 group-hover/link:translate-x-0.5 transition"></i>
                                </a>
                                @if($modul->keterangan)
                                    <p class="text-[11px] text-slate-400 mt-0.5 max-w-xs truncate" title="{{ $modul->keterangan }}">
                                        {{ $modul->keterangan }}
                                    </p>
                                @endif
                            </td>

                            <!-- Semester & Termin -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200/80">
                                        {{ $modul->kode_tahun_semester }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $modul->tipe_semester === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $modul->tipe_semester }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-600">
                                        {{ $modul->termin }}
                                    </span>
                                </div>
                            </td>

                            <!-- Koordinator -->
                            <td class="px-5 py-4">
                                @if($modul->koordinator)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 bg-amber-50 border border-amber-200 rounded text-[10px] font-mono font-bold text-amber-800">
                                            [{{ $modul->koordinator->kode }}]
                                        </span>
                                        <span class="text-xs text-slate-800 font-semibold truncate max-w-[150px]" title="{{ $modul->koordinator->nama }}">
                                            {{ $modul->koordinator->nama }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </td>

                            <!-- Mata Kuliah Count -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition"
                                   title="Lihat seluruh materi di modul ini">
                                    <i class="bi bi-card-checklist"></i>
                                    <span>{{ $modul->mata_kuliahs_count }} Materi</span>
                                </a>
                            </td>

                            <!-- Jadwal Terplot Count -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.jadwals.table', ['modul_id' => $modul->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition"
                                   title="Lihat jadwal modul di kalender/tabel">
                                    <i class="bi bi-calendar-check text-emerald-600"></i>
                                    <span>{{ $modul->jadwals_count }} Sesi</span>
                                </a>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($isAktif)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium text-slate-400 bg-slate-100">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Tindakan -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                                       class="px-2.5 py-1 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-lg transition inline-flex items-center gap-1"
                                       title="Kelola Mata Kuliah">
                                        <i class="bi bi-card-checklist"></i>
                                        <span class="hidden md:inline">Mata Kuliah</span>
                                    </a>

                                    <!-- Tombol Duplikat Modul -->
                                    <form action="{{ route('admin.moduls.duplicate', $modul->id) }}" method="POST"
                                          data-confirm="Duplikat modul '{{ addslashes($modul->nama) }}' beserta seluruh materi mata kuliah di dalamnya?"
                                          data-confirm-title="Duplikat Modul"
                                          class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition"
                                                title="Duplikat Modul">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>

                                    <!-- Tombol Ubah Modul -->
                                    <a href="{{ route('admin.moduls.edit', $modul->id) }}" 
                                       class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition"
                                       title="Ubah Modul">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Tombol Hapus Modul -->
                                    <form action="{{ route('admin.moduls.destroy', $modul->id) }}" method="POST"
                                          data-confirm="Apakah Anda yakin ingin menghapus modul '{{ addslashes($modul->nama) }}'?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                                title="Hapus Modul">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-journal-x text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-semibold text-slate-700">Tidak ada modul yang sesuai dengan filter pilihan.</p>
                                    @if($tipeFilter !== 'Semua')
                                        <a href="{{ route('admin.moduls.index', ['tipe' => 'Semua']) }}" class="inline-block mt-2 text-xs text-brand-600 font-semibold hover:underline">
                                            Tampilkan Semua Modul (Ganjil & Genap)
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($moduls->hasPages())
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80">
            {{ $moduls->links() }}
        </div>
    @endif
</div>
@endsection
