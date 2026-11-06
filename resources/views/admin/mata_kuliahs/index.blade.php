@extends('layouts.app', ['title' => 'Mata Kuliah Modul ' . $modul->nama])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.moduls.index') }}" class="text-slate-400 hover:text-slate-600">Data Modul</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">{{ $modul->kode }} - {{ $modul->nama }}</span>
@endsection

@section('content')
<div class="space-y-6" x-data="mataKuliahTableApp([{{ $mataKuliahs->pluck('id')->implode(',') }}])">
    <!-- Header Modul Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 overflow-hidden relative">
        <div class="absolute top-0 left-0 right-0 h-2" style="background-color: {{ $modul->warna }};"></div>
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg shadow-xs"
                          style="background-color: {{ $modul->warna }}18; color: {{ $modul->warna }}; border: 1px solid {{ $modul->warna }}40;">
                        {{ $modul->kode }}
                    </span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $modul->tipe_semester === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        Semester {{ $modul->tipe_semester }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                        {{ $modul->termin }}
                    </span>
                    @if($modul->tipe_semester === $activeSemester)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Semester Aktif
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $modul->nama }}</h1>
                <p class="text-xs text-slate-500 max-w-2xl">{{ $modul->keterangan ?? 'Kelola seluruh daftar mata kuliah, praktikum, dan materi pembelajaran di bawah modul ini.' }}</p>
                
                <div class="flex flex-wrap items-center gap-4 pt-1 text-xs text-slate-600">
                    <div>
                        <span class="text-slate-400">Koordinator Modul:</span>
                        <strong class="text-slate-800 ml-1">
                            {{ $modul->koordinator ? '[' . $modul->koordinator->kode . '] ' . $modul->koordinator->nama : 'Belum Ditentukan' }}
                        </strong>
                    </div>
                    <span class="text-slate-300">&bull;</span>
                    <div>
                        <span class="text-slate-400">Total Mata Kuliah:</span>
                        <strong class="text-slate-800 ml-1">{{ $mataKuliahs->total() }} Materi</strong>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Header -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.moduls.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                <!-- Tombol Mode Pilih / Select -->
                <button type="button" @click="toggleSelectMode()" 
                        :class="isSelectMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border rounded-xl text-xs font-semibold shadow-xs transition">
                    <i :class="isSelectMode ? 'bi bi-check-all text-base' : 'bi bi-check2-square text-brand-600 text-sm'"></i>
                    <span x-text="isSelectMode ? 'Selesai Memilih' : 'Pilih / Select'"></span>
                </button>

                <a href="{{ route('admin.moduls.mata-kuliahs.create', $modul->id) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    <i class="bi bi-plus-circle-fill text-sm"></i>
                    <span>Tambah Mata Kuliah</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Toolbar Pencarian & Filter Tipe Kegiatan -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Pencarian Kata Kunci -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari berdasarkan nama mata kuliah, kode, atau ruangan..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Filter Tipe Kegiatan -->
            <div class="flex items-center gap-2">
                <select name="tipe" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Tipe Kegiatan</option>
                    @foreach($tipeKegiatan as $t)
                        <option value="{{ $t }}" {{ $tipe == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>

                @if($search || $tipe)
                    <a href="{{ route('admin.moduls.mata-kuliahs.index', $modul->id) }}" 
                       class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition"
                       title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Form Aksi Hapus Massal (Bulk Delete Form) -->
    <form id="bulkDeleteForm" action="{{ route('admin.moduls.mata-kuliahs.bulk-delete', $modul->id) }}" method="POST" @submit="confirmBulkDelete($event)">
        @csrf
        <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    <!-- Toolbar Melayang Aksi Massal -->
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
            <span>Mata kuliah terpilih untuk dihapus</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="selectedIds = []" 
                    class="px-3.5 py-2 text-xs text-slate-600 hover:text-slate-800 font-semibold rounded-xl hover:bg-white bg-slate-100/70 border border-slate-200 transition">
                Batalkan Pilihan
            </button>
            <button type="submit" form="bulkDeleteForm"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="bi bi-trash-fill text-sm"></i>
                <span>Hapus (<span x-text="selectedIds.length"></span>) Mata Kuliah Terpilih</span>
            </button>
        </div>
    </div>

    <!-- Tabel Daftar Mata Kuliah -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <!-- Checklist Select All -->
                        <th x-show="isSelectMode" x-cloak class="px-4 py-3.5 w-12 text-center transition">
                            <input type="checkbox" @click="toggleAll()" :checked="allSelected" 
                                   title="Pilih Semua di Halaman Ini"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                        </th>
                        <!-- Nomor Urut i++ -->
                        <th class="px-3 py-3.5 w-14 text-center">No.</th>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Nama Mata Kuliah & Deskripsi</th>
                        <th class="px-5 py-3.5">Tipe Kegiatan</th>
                        <th class="px-5 py-3.5">Dosen Pengampu</th>
                        <th class="px-5 py-3.5">Ruang / Beban Jam</th>
                        <th class="px-5 py-3.5">Jadwal Terplot</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($mataKuliahs as $mk)
                        <tr class="hover:bg-slate-50/60 transition" :class="selectedIds.includes({{ $mk->id }}) ? 'bg-brand-50/30' : ''">
                            <!-- Checkbox Item -->
                            <td x-show="isSelectMode" x-cloak class="px-4 py-4 text-center transition">
                                <input type="checkbox" :value="{{ $mk->id }}" x-model="selectedIds"
                                       class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                            </td>

                            <!-- Nomor Urut i++ -->
                            <td class="px-3 py-4 text-center">
                                <span class="font-mono text-xs font-bold text-slate-500">
                                    {{ $mataKuliahs->firstItem() + $loop->index }}
                                </span>
                            </td>

                            <!-- Kode -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $mk->kode ?? '-' }}
                                </span>
                            </td>

                            <!-- Nama & Deskripsi -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 text-xs">
                                    {{ $mk->nama }}
                                </div>
                                @if($mk->deskripsi)
                                    <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1" title="{{ $mk->deskripsi }}">
                                        {{ $mk->deskripsi }}
                                    </p>
                                @endif
                            </td>

                            <!-- Tipe Kegiatan -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      style="background-color: {{ $modul->warna }}18; color: {{ $modul->warna }}; border: 1px solid {{ $modul->warna }}40;">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $modul->warna }};"></span>
                                    {{ $mk->tipe_kegiatan }}
                                </span>
                            </td>

                            <!-- Dosen Pengampu & Pendamping -->
                            <td class="px-5 py-4">
                                @if($mk->dosen)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.2 bg-slate-100 border border-slate-200 rounded text-[10px] font-mono font-bold text-slate-700">
                                            {{ $mk->dosen->kode }}
                                        </span>
                                        <span class="text-xs text-slate-800 font-medium truncate max-w-[160px]" title="{{ $mk->dosen->nama }}">
                                            {{ $mk->dosen->nama }}
                                        </span>
                                    </div>
                                    @if($mk->dosen_pendamping)
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                            Pendamping: {{ $mk->dosen_pendamping }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </td>

                            <!-- Ruangan & Beban Jam -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-700">
                                    {{ $mk->ruangan ?? '-' }}
                                </div>
                                @if($mk->beban_jam)
                                    <span class="text-[10px] font-mono text-brand-600 block mt-0.5">
                                        {{ $mk->beban_jam }}
                                    </span>
                                @endif
                            </td>

                            <!-- Jadwal Terplot -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.jadwals.table', ['modul_id' => $modul->id, 'search' => $mk->nama]) }}" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 transition">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>{{ $mk->jadwals_count }} Sesi</span>
                                </a>
                            </td>

                            <!-- Tindakan -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.moduls.mata-kuliahs.edit', [$modul->id, $mk->id]) }}" 
                                       title="Ubah Mata Kuliah"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    <form action="{{ route('admin.moduls.mata-kuliahs.destroy', [$modul->id, $mk->id]) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $mk->nama }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Mata Kuliah"
                                                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td :colspan="isSelectMode ? 9 : 8" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="bi bi-journal-x"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Belum ada mata kuliah dalam modul {{ $modul->nama }}</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Mata Kuliah" di atas untuk menambahkan materi perkuliahan baru.</p>
                                <a href="{{ route('admin.moduls.mata-kuliahs.create', $modul->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Tambah Mata Kuliah Pertama</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mataKuliahs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $mataKuliahs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function mataKuliahTableApp(pageIds) {
        return {
            isSelectMode: false,
            allPageIds: pageIds,
            selectedIds: [],
            toggleSelectMode() {
                this.isSelectMode = !this.isSelectMode;
                if (!this.isSelectMode) {
                    this.selectedIds = [];
                }
            },
            get allSelected() {
                return this.allPageIds.length > 0 && this.allPageIds.every(id => this.selectedIds.includes(id));
            },
            toggleAll() {
                if (this.allSelected) {
                    this.selectedIds = [];
                } else {
                    this.selectedIds = [...this.allPageIds];
                }
            },
            confirmBulkDelete(event) {
                if (this.selectedIds.length === 0) {
                    alert('Silakan pilih setidaknya satu mata kuliah untuk dihapus.');
                    event.preventDefault();
                    return false;
                }
                if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selectedIds.length} mata kuliah terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        }
    }
</script>
@endsection

