@extends('layouts.app', ['title' => 'Manajemen Jadwal Termin Akademik'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Jadwal Termin</span>
@endsection

@section('content')
<div class="space-y-6" x-data="terminTableApp([{{ $termins->pluck('id')->implode(',') }}])">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Jadwal Termin Perkuliahan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Siklus blok perkuliahan S1 Kedokteran (4 termin per semester, masing-masing 6 minggu)</p>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Tombol Toggle Mode Pilih / Select -->
            <button type="button" @click="toggleSelectMode()" 
                    :class="isSelectMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border rounded-xl text-xs font-semibold shadow-xs transition">
                <i :class="isSelectMode ? 'bi bi-check-all text-base' : 'bi bi-check2-square text-brand-600 text-sm'"></i>
                <span x-text="isSelectMode ? 'Selesai Memilih' : 'Pilih / Select'"></span>
            </button>

            <a href="{{ route('admin.termins.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition">
                <i class="bi bi-calendar-plus-fill"></i>
                <span>Tambah Termin Baru</span>
            </a>
        </div>
    </div>

    <!-- Status Banner Termin & Semester Aktif -->
    <div class="p-4 sm:p-5 bg-gradient-to-r from-brand-500/10 via-indigo-500/5 to-transparent border border-brand-200/80 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs uppercase font-extrabold text-brand-700 tracking-wider">Status Termin Berjalan Saat Ini</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 flex items-center gap-2">
                    <span class="font-mono text-sm font-extrabold text-brand-700 bg-white px-2 py-0.5 rounded-lg border border-brand-200 shadow-2xs">
                        {{ $activeTermin ? $activeTermin->kode_tahun_semester : ($activeTahunSemester ?? '2026.1') }}
                    </span>
                    <span>{{ $activeTermin ? $activeTermin->nama : 'Belum Ditentukan' }}</span>
                    <span class="text-xs font-normal text-slate-500">
                        ({{ $activeTermin ? 'Semester ' . $activeTermin->tipe_semester . ' ' . $activeTermin->tahun_ajaran : 'Semester ' . $activeSemester }})
                    </span>
                </div>
                <p class="text-xs text-slate-600 mt-0.5">
                    Rentang Waktu: <strong class="text-slate-800">{{ $activeTermin ? $activeTermin->rentang_tanggal : '-' }}</strong> 
                    ({{ $activeTermin ? $activeTermin->jumlah_minggu : 6 }} Minggu Berjalan). Seluruh modul & jadwal disinkronkan dengan termin ini.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($activeTermin)
                <a href="{{ route('admin.termins.show', $activeTermin) }}" 
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                   title="Lihat Laporan Beban Mengajar Dosen pada Termin Aktif">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Laporan Mengajar Dosen</span>
                </a>
            @endif
            <a href="{{ route('admin.jadwals.index') }}" 
               class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-calendar3 text-brand-600"></i>
                <span>Buka Kalender Termin</span>
            </a>
        </div>
    </div>

    <!-- Filter Tab Semester & Pencarian -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Tab Filter Semester -->
        <div class="inline-flex p-1 bg-slate-100/80 rounded-2xl border border-slate-200/60 overflow-x-auto text-xs font-semibold">
            <a href="{{ route('admin.termins.index', ['tipe' => 'Ganjil', 'search' => $search]) }}" 
               class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $tipeFilter === 'Ganjil' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="bi bi-sun {{ $tipeFilter === 'Ganjil' ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                <span>Semester Ganjil</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Ganjil' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-600' }}">{{ $counts['Ganjil'] }}</span>
                @if($activeSemester === 'Ganjil')
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                @endif
            </a>

            <a href="{{ route('admin.termins.index', ['tipe' => 'Genap', 'search' => $search]) }}" 
               class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $tipeFilter === 'Genap' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="bi bi-moon-stars {{ $tipeFilter === 'Genap' ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                <span>Semester Genap</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Genap' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $counts['Genap'] }}</span>
                @if($activeSemester === 'Genap')
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <a href="{{ route('admin.termins.index', ['tipe' => 'Semua', 'search' => $search]) }}" 
               class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $tipeFilter === 'Semua' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Semua Termin</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tipeFilter === 'Semua' ? 'bg-slate-200 text-slate-900 font-bold' : 'bg-slate-200 text-slate-600' }}">{{ $counts['Semua'] }}</span>
            </a>
        </div>

        <!-- Form Pencarian -->
        <div class="relative min-w-[260px]">
            <form method="GET" action="{{ route('admin.termins.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tipe" value="{{ $tipeFilter }}">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari termin, tahun ajaran..."
                           class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 transition">
                </div>
                @if($search)
                    <a href="{{ route('admin.termins.index', ['tipe' => $tipeFilter]) }}" 
                       class="p-1.5 text-slate-400 hover:text-slate-700 text-xs" title="Reset">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Form Aksi Hapus Massal (Bulk Delete Form) -->
    <form id="bulkDeleteForm" action="{{ route('admin.termins.bulk-delete') }}" method="POST" @submit="confirmBulkDelete($event)">
        @csrf
        <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    <!-- Toolbar Melayang Aksi Massal (Muncul ketika ada item dicentang) -->
    <div x-cloak x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-brand-50 border border-brand-200 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3 text-xs text-brand-900 font-semibold">
            <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <span x-text="selectedIds.length"></span>
            </div>
            <span>Termin perkuliahan terpilih untuk tindakan massal</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="selectedIds = []" 
                    class="px-3.5 py-2 text-xs text-slate-600 hover:text-slate-800 font-semibold rounded-xl hover:bg-white bg-slate-100/70 border border-slate-200 transition">
                Batalkan Pilihan
            </button>
            <button type="submit" form="bulkDeleteForm"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="bi bi-trash-fill text-sm"></i>
                <span>Hapus (<span x-text="selectedIds.length"></span>) Termin Terpilih</span>
            </button>
        </div>
    </div>

    <!-- Tabel Data Termin -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <!-- Kolom Checkbox -->
                        <th x-show="isSelectMode" x-cloak class="w-12 px-4 py-3.5 text-center">
                            <input type="checkbox" 
                                   @click="toggleSelectAll()" 
                                   :checked="isAllSelected()"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        </th>
                        <th class="w-14 px-4 py-3.5 text-center">No.</th>
                        <th class="px-5 py-3.5">Nama Termin</th>
                        <th class="px-4 py-3.5">Semester & Tahun</th>
                        <th class="px-4 py-3.5">Rentang Tanggal & Durasi</th>
                        <th class="px-4 py-3.5">Rincian 6 Minggu</th>
                        <th class="px-4 py-3.5 text-center">Modul</th>
                        <th class="px-4 py-3.5 text-center">Status Termin</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($termins as $termin)
                        <tr class="hover:bg-slate-50/70 transition group {{ $termin->is_aktif ? 'bg-emerald-50/25' : '' }}">
                            <!-- Checkbox Row -->
                            <td x-show="isSelectMode" x-cloak class="px-4 py-3.5 text-center">
                                @if(!$termin->is_aktif)
                                    <input type="checkbox" 
                                           value="{{ $termin->id }}" 
                                           x-model="selectedIds"
                                           class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                @else
                                    <span title="Termin aktif tidak dapat dipilih untuk dihapus">
                                        <i class="bi bi-lock-fill text-slate-300 text-xs"></i>
                                    </span>
                                @endif
                            </td>

                            <!-- Nomor Urut (i++) -->
                            <td class="px-4 py-3.5 text-center font-semibold text-slate-400">
                                {{ $termins->firstItem() + $loop->index }}
                            </td>

                            <!-- Nama Termin -->
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.termins.show', $termin) }}" 
                                   class="flex items-center gap-2.5 group cursor-pointer"
                                   title="Klik untuk membuka Laporan Mengajar Dosen selama periode {{ $termin->nama }}">
                                    <div class="w-8 h-8 rounded-xl font-bold flex items-center justify-center text-xs shrink-0 {{ $termin->is_aktif ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-700' }} transition">
                                        T{{ $termin->nomor }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900 text-sm group-hover:text-brand-600 group-hover:underline transition">{{ $termin->nama }}</span>
                                            <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 opacity-90 group-hover:opacity-100">
                                                <i class="bi bi-file-earmark-spreadsheet"></i> Laporan
                                            </span>
                                        </div>
                                        @if($termin->keterangan)
                                            <span class="text-[11px] text-slate-400 block truncate max-w-xs mt-0.5">{{ $termin->keterangan }}</span>
                                        @endif
                                    </div>
                                </a>
                            </td>

                            <!-- Semester & Tahun -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded border {{ $termin->tipe_semester === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                            {{ $termin->kode_tahun_semester }}
                                        </span>
                                        <span class="font-semibold text-slate-800">
                                            {{ $termin->tipe_semester }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400">
                                        Tahun {{ $termin->tahun_ajaran }}
                                    </span>
                                </div>
                            </td>

                            <!-- Rentang Tanggal & Durasi -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <i class="bi bi-calendar-event text-brand-600"></i>
                                        <span>{{ $termin->rentang_tanggal }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-medium">
                                        {{ $termin->jumlah_minggu }} Minggu Berjalan (42 Hari)
                                    </span>
                                </div>
                            </td>

                            <!-- Rincian 6 Minggu Popover/Drawer -->
                            <td class="px-4 py-3.5" x-data="{ openMinggu: false }">
                                <div class="relative">
                                    <button @click="openMinggu = !openMinggu" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition">
                                        <i class="bi bi-clock-history text-slate-500"></i>
                                        <span>Lihat 6 Minggu</span>
                                        <i class="bi bi-chevron-down text-[10px]"></i>
                                    </button>

                                    <!-- Popover Dropdown Rincian 6 Minggu -->
                                    <div x-cloak x-show="openMinggu" 
                                         @click.away="openMinggu = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute left-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 z-30 space-y-2">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                            <span class="font-bold text-slate-800 text-xs">Jadwal 6 Minggu {{ $termin->nama }}</span>
                                            <button @click="openMinggu = false" class="text-slate-400 hover:text-slate-600 text-xs">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach($termin->daftar_minggu as $m)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-50 text-[11px] border border-slate-100">
                                                    <span class="font-bold text-brand-700">{{ $m['label'] }}</span>
                                                    <span class="text-slate-600">{{ $m['rentang'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Modul Terkait -->
                            <td class="px-4 py-3.5 text-center">
                                @if($termin->moduls_count > 0)
                                    <a href="{{ route('admin.moduls.index', ['tipe' => $termin->tipe_semester]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition"
                                       title="Lihat modul perkuliahan termin ini">
                                        <i class="bi bi-journals"></i>
                                        <span>{{ $termin->moduls_count }} Modul</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Status Termin -->
                            <td class="px-4 py-3.5 text-center">
                                @if($termin->is_aktif)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Termin Aktif
                                    </span>
                                @else
                                    <form action="{{ route('admin.termins.activate', $termin) }}" method="POST" class="inline-block"
                                          data-confirm="Aktifkan {{ $termin->nama }} (Semester {{ $termin->tipe_semester }}) sebagai termin perkuliahan saat ini?"
                                          data-confirm-title="Aktifkan Termin">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 border border-slate-200 hover:border-brand-200 transition">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <!-- Tindakan Edit / Hapus -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.termins.show', $termin) }}" 
                                       class="p-1.5 text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" 
                                       title="Buka Laporan Mengajar Dosen (Spreadsheet)">
                                        <i class="bi bi-file-earmark-spreadsheet text-base"></i>
                                    </a>

                                    <a href="{{ route('admin.termins.edit', $termin) }}" 
                                       class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" 
                                       title="Edit Termin">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if(!$termin->is_aktif)
                                        <form action="{{ route('admin.termins.destroy', $termin) }}" method="POST" class="inline" 
                                              data-confirm="Apakah Anda yakin ingin menghapus data {{ $termin->nama }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Termin">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-calendar-x text-4xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">Tidak ada data termin ditemukan.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Silakan tambahkan data termin atau ubah kriteria pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($termins->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $termins->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function terminTableApp(allIds) {
        return {
            isSelectMode: false,
            selectedIds: [],
            allIds: allIds,

            toggleSelectMode() {
                this.isSelectMode = !this.isSelectMode;
                if (!this.isSelectMode) {
                    this.selectedIds = [];
                }
            },

            toggleSelectAll() {
                if (this.isAllSelected()) {
                    this.selectedIds = [];
                } else {
                    this.selectedIds = [...this.allIds];
                }
            },

            isAllSelected() {
                return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
            },

            confirmBulkDelete(event) {
                event.preventDefault();
                if (this.selectedIds.length === 0) {
                    swalAlert('Pilih Termin', 'Pilih setidaknya satu termin perkuliahan yang akan dihapus.', 'info');
                    return false;
                }
                const count = this.selectedIds.length;
                const form = event.target;
                swalConfirm(
                    'Konfirmasi Hapus Massal',
                    `Apakah Anda yakin ingin menghapus ${count} termin yang dipilih? (Termin yang sedang aktif tidak akan terhapus).`,
                    () => {
                        form.submit();
                    }
                );
                return false;
            }
        };
    }
</script>
@endsection

