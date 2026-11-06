@extends('layouts.app', ['title' => 'Beranda Dashboard'])

@section('breadcrumbs')
    <span class="text-slate-400">Panel Admin /</span> <span class="text-slate-800 font-semibold">Beranda Dashboard</span>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Banner Sambutan -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sistem Penjadwalan S1 Kedokteran</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($activeSemester ?? 'Ganjil') === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    Semester {{ $activeSemester ?? 'Ganjil' }} (Aktif)
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Selamat datang, <strong>{{ Auth::user()->name }}</strong> &bull; Peran: 
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ Auth::user()->roles->pluck('name')->first() ?? 'Belum ada peran' }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.jadwals.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <i class="bi bi-calendar3"></i>
                <span>Buka Kalender FullCalendar</span>
            </a>
            <a href="{{ route('admin.jadwals.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-plus-circle"></i>
                <span>Tambah Jadwal</span>
            </a>
        </div>
    </div>

    <!-- Kartu Metrik Ringkasan Akademik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Kartu 1: Total Sesi Jadwal Kuliah -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Jadwal Kuliah (Sem. {{ $activeSemester }})</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1.5">{{ $totalJadwals }}</p>
                    <a href="{{ route('admin.jadwals.index') }}" class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium mt-2">
                        <span>Lihat kalender termin</span>
                        <i class="bi bi-arrow-right text-[11px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Total Modul Kedokteran -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Modul (Sem. {{ $activeSemester }})</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1.5">{{ $totalModuls }} <span class="text-xs font-normal text-slate-400">/ {{ $totalModulsAll }}</span></p>
                    <a href="{{ route('admin.moduls.index') }}" class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium mt-2">
                        <span>Daftar modul</span>
                        <i class="bi bi-arrow-right text-[11px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl">
                    <i class="bi bi-journals"></i>
                </div>
            </div>
        </div>

        <!-- Kartu 3: Total Dosen Pengajar -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Dosen Pengampu</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1.5">{{ $totalDosens }}</p>
                    <a href="{{ route('admin.dosens.index') }}" class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium mt-2">
                        <span>Data dokter & dosen</span>
                        <i class="bi bi-arrow-right text-[11px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
            </div>
        </div>

        <!-- Kartu 4: Total Pengguna Sistem -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Pengguna</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1.5">{{ $totalUsers }}</p>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 font-medium mt-2">
                        <span>Kelola akun</span>
                        <i class="bi bi-arrow-right text-[11px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Konten 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Jadwal Kuliah Termin Mendatang (2 Kolom) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Tabel Jadwal Kuliah Terdekat -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Jadwal Kuliah & Praktikum Mendatang</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Sesi perkuliahan terdekat pada Termin 4 Semester Ganjil 26/27</p>
                    </div>
                    <a href="{{ route('admin.jadwals.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                        <span>Buka Kalender</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Hari & Jam</th>
                                <th class="px-6 py-3">Modul</th>
                                <th class="px-6 py-3">Topik Kuliah / Kegiatan</th>
                                <th class="px-6 py-3">Dosen & Ruang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($upcomingJadwals as $j)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $j->tanggal->translatedFormat('D, d M') }}
                                        </div>
                                        <div class="text-[11px] font-mono text-brand-600 font-semibold mt-0.5">
                                            {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-semibold"
                                              style="background-color: {{ $j->modul->warna }}18; color: {{ $j->modul->warna }}; border: 1px solid {{ $j->modul->warna }}40;">
                                            {{ $j->modul->kode }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-slate-900 text-xs">
                                            {{ $j->nama_kuliah }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">
                                            {{ $j->tipe_kegiatan }} @if($j->kelompok) &bull; Kelompok {{ $j->kelompok }} @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="text-xs font-semibold text-slate-700">
                                            @if($j->dosen)
                                                [{{ $j->dosen->kode }}] {{ $j->dosen->nama }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $j->ruangan ?? 'Ruang belum diset' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">
                                        Belum ada jadwal kuliah yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pengguna Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Pengguna Terdaftar Terbaru</h2>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                        Kelola Akun
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($recentUsers as $user)
                        <div class="px-6 py-3 flex items-center justify-between hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-semibold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 text-xs">{{ $user->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $user->roles->pluck('name')->first() ?? 'Member' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Ringkasan Modul & Panduan Termin (1 Kolom) -->
        <div class="space-y-6">
            <!-- Ringkasan Modul Termin 4 -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">Modul Termin 4</h2>
                    <a href="{{ route('admin.moduls.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Kelola</a>
                </div>

                <div class="space-y-3">
                    @php
                        $allModuls = \App\Models\Modul::withCount('jadwals')->get();
                    @endphp
                    @foreach($allModuls as $m)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $m->warna }};"></span>
                                <div>
                                    <span class="font-semibold text-slate-800 text-xs block">{{ $m->nama }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">Kode: {{ $m->kode }}</span>
                                </div>
                            </div>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold">
                                {{ $m->jadwals_count }} sesi
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Banner Fitur Kalender -->
            <div class="bg-gradient-to-br from-indigo-600 via-brand-600 to-purple-700 rounded-2xl p-6 text-white shadow-md">
                <div class="flex items-center gap-2 text-brand-200 text-xs font-semibold uppercase tracking-wider mb-2">
                    <i class="bi bi-calendar-check"></i>
                    <span>FullCalendar v6</span>
                </div>
                <h3 class="text-base font-bold">Kalender Termin Interaktif</h3>
                <p class="text-xs text-brand-100 mt-2 leading-relaxed">
                    Kalender telah disinkronkan dengan slot waktu harian (07:30 - 17:30 WIB) dan filter modul, dosen, serta kelompok mahasiswa.
                </p>
                <div class="mt-4 pt-4 border-t border-brand-500/40">
                    <a href="{{ route('admin.jadwals.index') }}" 
                       class="inline-flex items-center justify-center gap-2 w-full py-2 bg-white text-brand-700 font-bold text-xs rounded-xl hover:bg-brand-50 transition">
                        <span>Lihat Kalender Lengkap</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
