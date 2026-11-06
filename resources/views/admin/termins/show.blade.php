@extends('layouts.app', ['title' => 'Laporan Beban Mengajar Dosen - ' . $termin->nama])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.termins.index') }}" class="text-slate-400 hover:text-slate-600">Jadwal Termin</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Laporan Mengajar Dosen ({{ $termin->nama }})</span>
@endsection

@section('content')
<div x-data="laporanTerminApp()" class="space-y-6">

    <!-- Header & Aksi Halaman -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 no-print">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-bold flex items-center justify-center text-lg shadow-md shadow-emerald-500/20 shrink-0">
                T{{ $termin->nomor }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Beban Mengajar Dosen</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $termin->tipe_semester === 'Ganjil' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $termin->nama }}
                    </span>
                    @if($termin->is_aktif)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Termin Aktif
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span><i class="bi bi-mortarboard text-slate-400"></i> Semester {{ $termin->tipe_semester }} Tahun {{ $termin->tahun_ajaran }} ({{ $termin->kode_tahun_semester }})</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-calendar-range text-slate-400"></i> {{ $termin->rentang_tanggal }}</span>
                </p>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Export CSV Excel -->
            <a :href="'{{ route('admin.termins.export', $termin) }}?target_beban=' + targetBeban" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-xs transition"
               title="Unduh laporan ini dalam format file CSV / Excel">
                <i class="bi bi-file-earmark-spreadsheet-fill text-sm"></i>
                <span>Export Excel</span>
            </a>

            <!-- Cetak / Print -->
            <button type="button" @click="window.print()" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition"
                    title="Cetak laporan">
                <i class="bi bi-printer text-sm"></i>
                <span>Cetak</span>
            </button>

            <!-- Kalender Termin -->
            <a href="{{ route('admin.jadwals.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-brand-700 border border-brand-200 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-calendar3 text-sm"></i>
                <span class="hidden sm:inline">Buka Kalender</span>
            </a>

            <!-- Kembali -->
            <a href="{{ route('admin.termins.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Banner Ringkasan Format Spreadsheet (Persis seperti dokumen rujukan) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 no-print">
        <!-- Box 1: Judul Termin Spreadsheet -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Identitas Sheet</span>
                <h3 class="font-black text-slate-900 text-lg tracking-tight uppercase">TERMIN {{ $termin->nomor }}</h3>
                <span class="text-xs text-slate-500 font-medium">Semester {{ $termin->tipe_semester }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
                <i class="bi bi-file-spreadsheet"></i>
            </div>
        </div>

        <!-- Box 2: Total Minggu (Card Kuning persis rujukan) -->
        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 shadow-xs flex items-center justify-between text-amber-950">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block mb-0.5">Total Minggu</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-amber-900 leading-none">{{ count($daftarMinggu) }}</span>
                    <span class="text-xs font-bold text-amber-800">Minggu Pekan</span>
                </div>
                <span class="text-[11px] text-amber-700 mt-1 block font-medium">Periode siklus blok termin</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-calendar-week"></i>
            </div>
        </div>

        <!-- Box 3: Jumlah Pekan Aktif Semester (Card Hijau persis rujukan) -->
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200 shadow-xs flex items-center justify-between text-emerald-950">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block mb-0.5">Pekan Aktif Semester</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-emerald-900 leading-none">{{ $jumlahPekanAktif }}</span>
                    <span class="text-xs font-bold text-emerald-800">Pekan Berjalan</span>
                </div>
                <span class="text-[11px] text-emerald-700 mt-1 block font-medium">Total semester akademik</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-200/80 text-emerald-800 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>

        <!-- Box 4: Pengaturan Target Beban Interaktif -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="flex-1 mr-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Target Beban Standar</span>
                <div class="flex items-center gap-2 mt-1">
                    <input type="number" x-model.number="targetBeban" min="1" max="100"
                           class="w-20 px-2.5 py-1 text-base font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <span class="text-xs font-semibold text-slate-500">Sesi / Jam</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Standar target per dosen</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">
                <i class="bi bi-sliders"></i>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Tambahan -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 no-print">
        <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Dosen Terdaftar</span>
            <span class="text-lg font-bold text-slate-900">{{ $totalDosenAktif }} Dosen</span>
        </div>
        <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Dosen Mengajar di Termin Ini</span>
            <span class="text-lg font-bold text-brand-700">{{ $dosenMengajarCount }} Dosen</span>
        </div>
        <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Sesi Perkuliahan</span>
            <span class="text-lg font-bold text-emerald-700">{{ $totalSeluruhSesi }} Sesi Jadwal</span>
        </div>
        <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Beban Tertinggi</span>
            <span class="text-xs font-bold text-slate-900 truncate block mt-1" title="{{ $bebanTertinggi['nama'] ?? '-' }}">
                [{{ $bebanTertinggi['kode'] ?? '-' }}] {{ $bebanTertinggi['total'] ?? 0 }} Sesi
            </span>
        </div>
    </div>

    <!-- Tabs Navigasi (Gaya Sheet Tabs seperti Google Spreadsheet pada rujukan) -->
    <div class="border-b border-slate-200 no-print">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-px">
            <!-- Tab TOTAL LENGKAP -->
            <button type="button" @click="activeTab = 'total'" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-xs font-bold transition border-t border-x"
                    :class="activeTab === 'total' ? 'bg-white text-emerald-700 border-slate-200 border-b-2 border-b-white -mb-px shadow-xs' : 'bg-slate-50 text-slate-500 hover:text-slate-800 border-transparent'">
                <i class="bi bi-file-earmark-spreadsheet-fill text-sm text-emerald-600"></i>
                <span class="uppercase tracking-wide">TOTAL LENGKAP final rev</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold" x-text="filteredLaporan.length + ' Dosen'"></span>
            </button>

            <!-- Tabs Minggu 1 s/d 6 -->
            @foreach($daftarMinggu as $m)
                <button type="button" @click="activeTab = 'minggu-{{ $m['nomor'] }}'" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-t-xl text-xs font-semibold transition border-t border-x whitespace-nowrap"
                        :class="activeTab === 'minggu-{{ $m['nomor'] }}' ? 'bg-white text-brand-700 border-slate-200 border-b-2 border-b-white -mb-px shadow-xs font-bold' : 'bg-slate-50 text-slate-500 hover:text-slate-800 border-transparent'">
                    <i class="bi bi-calendar-event text-xs"></i>
                    <span>{{ $m['label'] }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                          :class="activeTab === 'minggu-{{ $m['nomor'] }}' ? 'bg-brand-50 text-brand-700' : 'bg-slate-200/80 text-slate-600'">
                        {{ $weeklyTotals[$m['nomor']] ?? 0 }} sesi
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- KONTEN TAB 1: TOTAL LENGKAP SPREADSHEET (Sesuai Gambar Rujukan) -->
    <div x-show="activeTab === 'total'" class="space-y-4">
        <!-- Toolbar Filter Dosen & Beban -->
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3 no-print">
            <div class="relative flex-1 w-full md:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                <input type="text" x-model="searchQuery" 
                       placeholder="Cari nama dosen atau kode ID (contoh: AN, ERY, Andriani)..."
                       class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end flex-wrap">
                <!-- Toggle Sembunyikan yang 0 -->
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer select-none bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 transition">
                    <input type="checkbox" x-model="sembunyikanYangNol" 
                           class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500 cursor-pointer">
                    <span>Sembunyikan Dosen Beban 0</span>
                </label>

                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-medium text-slate-500">Filter:</span>
                    <select x-model="bebanFilter" 
                            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-brand-500 transition">
                        <option value="teaching">Dosen Mengajar ({{ $dosenMengajarCount }} Dosen)</option>
                        <option value="kurang">Kurang Beban (Minus)</option>
                        <option value="kelebihan">Kelebihan Beban (Plus)</option>
                        <option value="sesuai">Tepat Sesuai Target (0)</option>
                        <option value="all">Semua Dosen (Termasuk yang 0)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel Spreadsheet Utama -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <!-- Header Tabel Sesuai Gambar Rujukan -->
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 border-b border-slate-300 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-3 py-3 text-center border-r border-slate-200 w-12">No.</th>
                            <th class="px-4 py-3 border-r border-slate-200 min-w-[200px]">Nama</th>
                            <th class="px-3 py-3 text-center border-r border-slate-200 w-16">ID</th>
                            <th class="px-3 py-3 text-center border-r border-slate-200 w-20">Status</th>
                            @foreach($daftarMinggu as $m)
                                <th class="px-3 py-3 text-center border-r border-slate-200 min-w-[75px]" title="{{ $m['rentang'] }}">
                                    {{ $m['label'] }}
                                </th>
                            @endforeach
                            <th class="px-3 py-3 text-center border-r border-slate-200 bg-slate-200 text-slate-900 w-20">Total</th>
                            <th class="px-4 py-3 text-center border-r border-slate-200 min-w-[190px]">
                                Kekurangan atau kelebihan Beban Termin ini
                            </th>
                            <th class="px-4 py-3 border-r border-slate-200 min-w-[150px]">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        <template x-for="(d, idx) in filteredLaporan" :key="d.dosen_id">
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- No -->
                                <td class="px-3 py-2 text-center text-slate-400 font-medium border-r border-slate-200" x-text="idx + 1"></td>

                                <!-- Nama Dosen -->
                                <td class="px-4 py-2 font-bold text-slate-900 border-r border-slate-200 truncate max-w-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span x-text="d.nama"></span>
                                    </div>
                                </td>

                                <!-- ID / Kode -->
                                <td class="px-3 py-2 text-center font-mono font-bold text-slate-700 border-r border-slate-200" x-text="d.kode"></td>

                                <!-- Status -->
                                <td class="px-3 py-2 text-center border-r border-slate-200">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                </td>

                                <!-- Minggu 1 s/d 6 -->
                                @foreach($daftarMinggu as $m)
                                    <td class="px-3 py-2 text-center border-r border-slate-200 font-semibold"
                                        :class="d.minggu[{{ $m['nomor'] }}] > 0 ? 'text-slate-900 bg-brand-50/30 font-bold' : 'text-slate-300 font-normal'"
                                        x-text="d.minggu[{{ $m['nomor'] }}] > 0 ? d.minggu[{{ $m['nomor'] }}] : '-'">
                                    </td>
                                @endforeach

                                <!-- Total Sesi -->
                                <td class="px-3 py-2 text-center font-extrabold text-sm border-r border-slate-200 bg-slate-100/60 text-slate-900" 
                                    x-text="d.total">
                                </td>

                                <!-- Kekurangan atau Kelebihan Beban (Warna Persis Dokumen Rujukan) -->
                                <td class="px-4 py-2 text-center font-bold text-xs border-r border-slate-200 transition"
                                    :class="getBebanCellClass(d.total - targetBeban)">
                                    <span x-text="formatSelisih(d.total - targetBeban)"></span>
                                </td>

                                <!-- Catatan -->
                                <td class="px-4 py-2 text-xs border-r border-slate-200 text-slate-500 truncate max-w-xs"
                                    x-text="getCatatan(d.total - targetBeban)">
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredLaporan.length === 0">
                            <td colspan="{{ 7 + count($daftarMinggu) }}" class="px-6 py-10 text-center text-slate-400">
                                <i class="bi bi-search text-3xl mb-2 block text-slate-300"></i>
                                Tidak ada data dosen yang sesuai dengan kata kunci atau filter beban.
                            </td>
                        </tr>
                    </tbody>

                    <!-- Footer Baris Total (Sum per Kolom) -->
                    <tfoot class="bg-slate-100 font-bold text-slate-800 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right uppercase tracking-wider text-xs border-r border-slate-200">
                                Total Keseluruhan Sesi
                            </td>
                            @foreach($daftarMinggu as $m)
                                <td class="px-3 py-3 text-center border-r border-slate-200 font-extrabold text-brand-700">
                                    {{ $weeklyTotals[$m['nomor']] ?? 0 }}
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-center border-r border-slate-200 bg-slate-200 font-black text-sm text-slate-900">
                                {{ $totalSeluruhSesi }}
                            </td>
                            <td colspan="2" class="px-4 py-3 text-xs text-slate-500 font-medium">
                                Seluruh kegiatan perkuliahan aktif periode {{ $termin->nama }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- KONTEN TAB 2 s/d 7: RINCIAN PER MINGGU (Minggu 1 s/d 6) -->
    @foreach($daftarMinggu as $m)
        <div x-show="activeTab === 'minggu-{{ $m['nomor'] }}'" class="space-y-4">
            <!-- Header Rincian Minggu -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 font-bold flex items-center justify-center text-base border border-brand-200">
                        M{{ $m['nomor'] }}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">{{ $m['label'] }} &bull; {{ $termin->nama }}</h3>
                        <p class="text-xs text-slate-500 font-medium">
                            <i class="bi bi-calendar3"></i> Rentang: <span class="font-bold text-slate-700">{{ $m['rentang'] }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                        <i class="bi bi-clock-history"></i> {{ count($jadwalPerMinggu[$m['nomor']] ?? []) }} Sesi Perkuliahan
                    </span>
                </div>
            </div>

            <!-- Tabel Jadwal Perkuliahan di Minggu Terkait -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="px-4 py-3 w-12 text-center">No.</th>
                                <th class="px-4 py-3">Hari & Tanggal</th>
                                <th class="px-4 py-3">Waktu / Sesi</th>
                                <th class="px-4 py-3">Modul & Jenis</th>
                                <th class="px-4 py-3 min-w-[220px]">Mata Kuliah / Topik</th>
                                <th class="px-4 py-3 min-w-[200px]">Dosen Pengampu</th>
                                <th class="px-4 py-3">Ruang / Kelompok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($jadwalPerMinggu[$m['nomor']] ?? [] as $jadwalIdx => $j)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-400">{{ $jadwalIdx + 1 }}</td>
                                    
                                    <!-- Hari & Tanggal -->
                                    <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d M Y') }}
                                    </td>

                                    <!-- Jam -->
                                    <td class="px-4 py-3 font-mono text-slate-700 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 font-bold">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</span>
                                    </td>

                                    <!-- Modul & Jenis -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @if($j->modul)
                                                <span class="px-2 py-0.5 rounded-md font-bold text-[10px] text-white shrink-0" 
                                                      style="background-color: {{ $j->modul->warna ?: '#0284c7' }}">
                                                    {{ $j->modul->kode }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] font-medium text-slate-600">{{ $j->tipe_kegiatan }}</span>
                                        </div>
                                    </td>

                                    <!-- Mata Kuliah -->
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-slate-900 block leading-tight">{{ $j->nama_kuliah }}</span>
                                        @if($j->catatan)
                                            <span class="text-[10px] text-slate-400 italic block mt-0.5">{{ $j->catatan }}</span>
                                        @endif
                                    </td>

                                    <!-- Dosen Pengampu & Pendamping -->
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            @if($j->dosen)
                                                <div class="flex items-center gap-1.5 font-semibold text-slate-900">
                                                    <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-800 border border-amber-200">
                                                        [{{ $j->dosen->kode }}]
                                                    </span>
                                                    <span>{{ $j->dosen->nama }}</span>
                                                </div>
                                            @endif

                                            @if(!empty($j->dosen_pendamping))
                                                <div class="text-[10px] text-slate-500 flex items-center gap-1">
                                                    <span class="font-medium text-slate-400">Tutor:</span>
                                                    <span class="font-mono font-semibold">{{ $j->dosen_pendamping }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Ruang & Kelompok -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-medium text-slate-800">{{ $j->ruangan ?: '-' }}</span>
                                            @if($j->kelompok && $j->kelompok !== '-')
                                                <span class="text-[10px] text-slate-400">Kelompok: {{ $j->kelompok }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        <i class="bi bi-calendar-x text-3xl mb-1.5 block text-slate-300"></i>
                                        Belum ada jadwal perkuliahan yang terjadwal di {{ $m['label'] }} ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

</div>

<!-- Alpine.js Laporan Controller -->
<script>
    function laporanTerminApp() {
        return {
            activeTab: 'total',
            targetBeban: {{ $defaultTargetBeban }},
            searchQuery: '',
            bebanFilter: 'teaching', // Default hanya dosen yang mengajar
            sembunyikanYangNol: true, // Default sembunyikan dosen yang bebannya 0

            rawLaporan: @json($laporanDosen),

            get filteredLaporan() {
                const q = this.searchQuery.toLowerCase().trim();
                const filter = this.bebanFilter;
                const target = this.targetBeban;

                return this.rawLaporan.filter(d => {
                    // Sembunyikan dosen dengan total 0 mengajar jika toggle aktif atau filter teaching
                    if (this.sembunyikanYangNol && d.total <= 0 && filter !== 'all') {
                        return false;
                    }

                    // Filter pencarian teks
                    if (q) {
                        const matchNama = d.nama && d.nama.toLowerCase().includes(q);
                        const matchKode = d.kode && d.kode.toLowerCase().includes(q);
                        if (!matchNama && !matchKode) return false;
                    }

                    // Filter kategori beban
                    const selisih = d.total - target;
                    if (filter === 'teaching' && d.total <= 0) return false;
                    if (filter === 'kurang' && selisih >= 0) return false;
                    if (filter === 'kelebihan' && selisih <= 0) return false;
                    if (filter === 'sesuai' && selisih !== 0) return false;

                    return true;
                });
            },

            formatSelisih(diff) {
                if (diff > 0) return '+' + diff;
                return String(diff);
            },

            getBebanCellClass(diff) {
                if (diff <= -30) {
                    return 'bg-red-600 text-white font-extrabold shadow-2xs';
                }
                if (diff < 0) {
                    return 'bg-rose-100 text-rose-800 font-bold';
                }
                if (diff > 0) {
                    return 'bg-emerald-100 text-emerald-800 font-bold';
                }
                return 'bg-slate-50 text-slate-600 font-semibold';
            },

            getCatatan(diff) {
                if (diff < 0) {
                    return 'Kurang ' + Math.abs(diff) + ' sesi beban';
                }
                if (diff > 0) {
                    return 'Kelebihan +' + diff + ' sesi beban';
                }
                return 'Sesuai target beban';
            }
        };
    }
</script>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 8px !important;
        }
    }
</style>
@endsection

