@extends('layouts.app', ['title' => 'Data Tahun Semester Akademik'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tahun Semester</span>
@endsection

@section('content')
<div class="space-y-6" x-data="semesterTableApp([{{ $semesters->pluck('id')->implode(',') }}])">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Tahun Semester Akademik</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola master tahun semester (contoh: 2026.1, 2026.2, 2027.1, 2027.2) dan aktifkan periode berjalan</p>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Tombol Toggle Mode Pilih / Select -->
            <button type="button" @click="toggleSelectMode()" 
                    :class="isSelectMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border rounded-xl text-xs font-semibold shadow-xs transition">
                <i :class="isSelectMode ? 'bi bi-check-all text-base' : 'bi bi-check2-square text-brand-600 text-sm'"></i>
                <span x-text="isSelectMode ? 'Selesai Memilih' : 'Pilih / Select'"></span>
            </button>

            <a href="{{ route('admin.semesters.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tambah Semester Baru</span>
            </a>
        </div>
    </div>

    <!-- Banner Status Semester Aktif Saat Ini -->
    <div class="p-4 sm:p-5 bg-gradient-to-r from-brand-500/10 via-indigo-500/5 to-transparent border border-brand-200/80 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-brand-500/20 shrink-0">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs uppercase font-extrabold text-brand-700 tracking-wider">Tahun Semester Aktif Saat Ini</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        Sedang Berjalan
                    </span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 flex items-center gap-2.5">
                    <span class="font-mono text-base font-extrabold text-brand-700 bg-white px-2.5 py-0.5 rounded-lg border border-brand-200 shadow-2xs">
                        {{ $activeTahunSemester ?? '2026.1' }}
                    </span>
                    <span>Semester {{ $activeSemester ?? 'Ganjil' }}</span>
                </div>
                <p class="text-xs text-slate-600 mt-0.5">
                    Seluruh kalender kuliah, plotting modul, dan jadwal termin di sistem saat ini disaring sesuai periode <strong>{{ $activeTahunSemester ?? '2026.1' }}</strong>.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.termins.index') }}" 
               class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 shadow-xs transition flex items-center gap-1.5">
                <i class="bi bi-calendar-range text-brand-600"></i>
                <span>Kelola Jadwal Termin</span>
            </a>
        </div>
    </div>

    <!-- Toolbar Pencarian -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.semesters.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kode (2026.1, 2026.2), nama semester, atau tahun ajaran..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            @if($search)
                <a href="{{ route('admin.semesters.index') }}" 
                   class="px-3.5 py-2 text-sm text-slate-500 hover:text-slate-800 border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Form Aksi Hapus Massal (Bulk Delete Form) -->
    <form id="bulkDeleteForm" action="{{ route('admin.semesters.bulk-delete') }}" method="POST" @submit="confirmBulkDelete($event)">
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
            <span>Semester akademik terpilih untuk tindakan massal</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="selectedIds = []" 
                    class="px-3.5 py-2 text-xs text-slate-600 hover:text-slate-800 font-semibold rounded-xl hover:bg-white bg-slate-100/70 border border-slate-200 transition">
                Batalkan Pilihan
            </button>
            <button type="submit" form="bulkDeleteForm"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="bi bi-trash-fill text-sm"></i>
                <span>Hapus (<span x-text="selectedIds.length"></span>) Semester Terpilih</span>
            </button>
        </div>
    </div>

    <!-- Tabel Data Semester -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th x-show="isSelectMode" x-cloak class="w-12 px-4 py-3.5 text-center">
                            <input type="checkbox" 
                                   @click="toggleSelectAll()" 
                                   :checked="isAllSelected()"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        </th>
                        <th class="w-14 px-4 py-3.5 text-center">No.</th>
                        <th class="px-5 py-3.5">Kode Semester</th>
                        <th class="px-5 py-3.5">Nama Semester</th>
                        <th class="px-4 py-3.5">Tipe & Tahun Ajaran</th>
                        <th class="px-4 py-3.5">Rentang Tanggal</th>
                        <th class="px-4 py-3.5 text-center">Termin</th>
                        <th class="px-4 py-3.5 text-center">Modul</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($semesters as $semester)
                        @php
                            $isCurrentActive = $semester->kode === ($activeTahunSemester ?? '2026.1');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition group {{ $isCurrentActive ? 'bg-emerald-50/25' : '' }}">
                            <!-- Checkbox Row -->
                            <td x-show="isSelectMode" x-cloak class="px-4 py-3.5 text-center">
                                @if(!$isCurrentActive)
                                    <input type="checkbox" 
                                           value="{{ $semester->id }}" 
                                           x-model="selectedIds"
                                           class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                @else
                                    <span title="Semester aktif tidak dapat dipilih untuk dihapus">
                                        <i class="bi bi-lock-fill text-slate-300 text-xs"></i>
                                    </span>
                                @endif
                            </td>

                            <!-- Nomor Urut (i++) -->
                            <td class="px-4 py-3.5 text-center font-semibold text-slate-400">
                                {{ $semesters->firstItem() + $loop->index }}
                            </td>

                            <!-- Kode Semester (2026.1, 2026.2, dll) -->
                            <td class="px-5 py-3.5 font-mono">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $semester->tipe === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                    {{ $semester->kode }}
                                </span>
                            </td>

                            <!-- Nama Semester -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 text-sm block">{{ $semester->nama }}</span>
                                @if($semester->keterangan)
                                    <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $semester->keterangan }}</span>
                                @endif
                            </td>

                            <!-- Tipe & Tahun Ajaran -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-800">
                                        Semester {{ $semester->tipe }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">
                                        Tahun {{ $semester->tahun_ajaran }}
                                    </span>
                                </div>
                            </td>

                            <!-- Rentang Tanggal -->
                            <td class="px-4 py-3.5">
                                <span class="text-slate-600 font-medium">
                                    {{ $semester->rentang_tanggal }}
                                </span>
                            </td>

                            <!-- Termin Terdaftar -->
                            <td class="px-4 py-3.5 text-center">
                                @if($semester->termins_count > 0)
                                    <a href="{{ route('admin.termins.index', ['tipe' => $semester->tipe]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/80 transition"
                                       title="Lihat termin di semester ini">
                                        <i class="bi bi-calendar-range"></i>
                                        <span>{{ $semester->termins_count }} Termin</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">0</span>
                                @endif
                            </td>

                            <!-- Modul Terdaftar -->
                            <td class="px-4 py-3.5 text-center">
                                @if($semester->moduls_count > 0)
                                    <a href="{{ route('admin.moduls.index', ['tipe' => $semester->tipe]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 transition"
                                       title="Lihat modul di semester ini">
                                        <i class="bi bi-journals"></i>
                                        <span>{{ $semester->moduls_count }} Modul</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">0</span>
                                @endif
                            </td>

                            <!-- Status & Tombol Aktifkan -->
                            <td class="px-4 py-3.5 text-center">
                                @if($isCurrentActive)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <form action="{{ route('admin.semesters.activate', $semester) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Aktifkan {{ $semester->kode }} ({{ $semester->nama }}) sebagai semester akademik saat ini?')"
                                                class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 border border-slate-200 hover:border-brand-200 transition">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <!-- Tindakan Edit / Hapus -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.semesters.edit', $semester) }}" 
                                       class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" 
                                       title="Edit Semester">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if(!$isCurrentActive)
                                        <form action="{{ route('admin.semesters.destroy', $semester) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data Semester {{ $semester->kode }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Semester">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-mortarboard text-4xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">Tidak ada data semester ditemukan.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Silakan tambahkan tahun semester baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($semesters->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $semesters->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function semesterTableApp(allIds) {
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
                if (this.selectedIds.length === 0) {
                    event.preventDefault();
                    alert('Pilih setidaknya satu semester yang akan dihapus.');
                    return false;
                }
                const count = this.selectedIds.length;
                if (!confirm(`Apakah Anda yakin ingin menghapus ${count} semester yang dipilih? (Semester yang sedang aktif tidak akan terhapus).`)) {
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        };
    }
</script>
@endsection

