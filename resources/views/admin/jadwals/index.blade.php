@extends('layouts.app', ['title' => 'Daftar Jadwal Kuliah'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Daftar Jadwal Kuliah</span>
@endsection

@section('content')
<div class="space-y-6" x-data="jadwalTableApp([{{ $jadwals->pluck('id')->implode(',') }}])">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tabel Jadwal Perkuliahan Termin</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($activeSemester ?? 'Ganjil') === 'Ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    Semester {{ $activeSemester ?? 'Ganjil' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Kelola seluruh data jadwal kuliah untuk modul <strong>Semester {{ $activeSemester ?? 'Ganjil' }}</strong></p>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Tombol Toggle Mode Pilih / Select -->
            <button type="button" @click="toggleSelectMode()" 
                    :class="isSelectMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border rounded-xl text-xs font-semibold shadow-xs transition">
                <i :class="isSelectMode ? 'bi bi-check-all text-base' : 'bi bi-check2-square text-brand-600 text-sm'"></i>
                <span x-text="isSelectMode ? 'Selesai Memilih' : 'Pilih / Select'"></span>
            </button>

            <a href="{{ route('admin.jadwals.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-calendar3 text-sm"></i>
                <span>Tampilan Kalender</span>
            </a>
            <a href="{{ route('admin.jadwals.matrix') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="bi bi-grid-3x3 text-sm text-emerald-600"></i>
                <span>Tampilan Matriks</span>
            </a>
            <a href="{{ route('admin.jadwals.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <i class="bi bi-calendar-plus text-sm"></i>
                <span>Tambah Jadwal Kuliah</span>
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.jadwals.table') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Filter Semester -->
            <div>
                <select name="semester" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="Ganjil" {{ ($semesterFilter ?? $activeSemester) === 'Ganjil' ? 'selected' : '' }}>Sem. Ganjil {{ $activeSemester === 'Ganjil' ? '(Aktif)' : '' }}</option>
                    <option value="Genap" {{ ($semesterFilter ?? $activeSemester) === 'Genap' ? 'selected' : '' }}>Sem. Genap {{ $activeSemester === 'Genap' ? '(Aktif)' : '' }}</option>
                    <option value="Semua" {{ ($semesterFilter ?? '') === 'Semua' ? 'selected' : '' }}>Semua Semester</option>
                </select>
            </div>

            <!-- Pencarian Kata Kunci -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama kuliah, ruangan, atau kelompok..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Filter Modul -->
            <div>
                <select name="modul_id" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Modul</option>
                    @foreach($moduls as $m)
                        <option value="{{ $m->id }}" {{ $modulId == $m->id ? 'selected' : '' }}>
                            {{ $m->kode }} - {{ $m->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Dosen -->
            <div>
                <select name="dosen_id" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                    <option value="">Semua Dosen</option>
                    @foreach($dosens as $d)
                        <option value="{{ $d->id }}" {{ $dosenId == $d->id ? 'selected' : '' }}>
                            [{{ $d->kode }}] {{ $d->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal & Reset -->
            <div class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                @if($search || $modulId || $dosenId || $tanggal)
                    <a href="{{ route('admin.jadwals.table') }}" 
                       class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition"
                       title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Form Aksi Hapus Massal (Bulk Delete Form) -->
    <form id="bulkDeleteForm" action="{{ route('admin.jadwals.bulk-delete') }}" method="POST" @submit="confirmBulkDelete($event)">
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
            <span>Jadwal kuliah terpilih untuk tindakan massal</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="selectedIds = []" 
                    class="px-3.5 py-2 text-xs text-slate-600 hover:text-slate-800 font-semibold rounded-xl hover:bg-white bg-slate-100/70 border border-slate-200 transition">
                Batalkan Pilihan
            </button>
            <button type="submit" form="bulkDeleteForm"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="bi bi-trash-fill text-sm"></i>
                <span>Hapus (<span x-text="selectedIds.length"></span>) Jadwal Terpilih</span>
            </button>
        </div>
    </div>

    <!-- Tabel Data Jadwal -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <!-- Kolom Checklist Select All (Hanya Tampil saat Mode Pilih Aktif) -->
                        <th x-show="isSelectMode" x-cloak class="px-4 py-3.5 w-12 text-center transition">
                            <input type="checkbox" @click="toggleAll()" :checked="allSelected" 
                                   title="Pilih Semua di Halaman Ini"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                        </th>
                        <!-- Kolom Nomor Urut i++ -->
                        <th class="px-3 py-3.5 w-14 text-center">No.</th>
                        <th class="px-5 py-3.5">Hari & Waktu</th>
                        <th class="px-5 py-3.5">Modul & Jenis</th>
                        <th class="px-5 py-3.5">Mata Kuliah / Topik</th>
                        <th class="px-5 py-3.5">Dosen Pengampu</th>
                        <th class="px-5 py-3.5">Ruang / Kelompok</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50/60 transition" :class="selectedIds.includes({{ $jadwal->id }}) ? 'bg-brand-50/30' : ''">
                            <!-- Checkbox Item (Hanya Tampil saat Mode Pilih Aktif) -->
                            <td x-show="isSelectMode" x-cloak class="px-4 py-4 text-center transition">
                                <input type="checkbox" :value="{{ $jadwal->id }}" x-model="selectedIds"
                                       class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                            </td>

                            <!-- Nomor Urut i++ (Mempertimbangkan pagination) -->
                            <td class="px-3 py-4 text-center">
                                <span class="font-mono text-xs font-bold text-slate-500">
                                    {{ $jadwals->firstItem() + $loop->index }}
                                </span>
                            </td>

                            <!-- Waktu & Tanggal -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 text-xs">
                                    {{ $jadwal->tanggal->translatedFormat('l, d M Y') }}
                                </div>
                                <div class="text-[11px] font-mono text-brand-600 font-bold mt-0.5">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                </div>
                            </td>

                            <!-- Modul -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      style="background-color: {{ $jadwal->modul->warna }}18; color: {{ $jadwal->modul->warna }}; border: 1px solid {{ $jadwal->modul->warna }}40;">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $jadwal->modul->warna }};"></span>
                                    {{ $jadwal->modul->kode }}
                                </span>
                                <div class="text-[10px] text-slate-400 uppercase font-semibold mt-1">
                                    {{ $jadwal->tipe_kegiatan }}
                                </div>
                            </td>

                            <!-- Nama Kuliah -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800 text-xs max-w-xs">
                                    {{ $jadwal->nama_kuliah }}
                                </div>
                                @if($jadwal->catatan)
                                    <div class="text-[11px] text-slate-400 italic mt-0.5 max-w-xs truncate" title="{{ $jadwal->catatan }}">
                                        {{ $jadwal->catatan }}
                                    </div>
                                @endif
                            </td>

                            <!-- Dosen -->
                            <td class="px-5 py-4">
                                @if($jadwal->dosen)
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 bg-amber-50 border border-amber-200 rounded text-[10px] font-mono font-bold text-amber-800">
                                            [{{ $jadwal->dosen->kode }}]
                                        </span>
                                        <span class="text-xs text-slate-800 font-semibold truncate max-w-[180px]" title="{{ $jadwal->dosen->nama }}">
                                            {{ $jadwal->dosen->nama }}
                                        </span>
                                    </div>
                                    @if($jadwal->dosen_pendamping)
                                        @php
                                            $pendampings = $jadwal->dosen_pendamping_details;
                                        @endphp
                                        <div class="mt-1.5 pt-1.5 border-t border-slate-100 space-y-1">
                                            <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Dosen Pengampu:</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($pendampings as $p)
                                                    <span class="inline-flex items-center gap-1 text-[10px] text-slate-700 bg-slate-50 border border-slate-200/80 rounded px-1.5 py-0.5" title="{{ $p['nama'] }}">
                                                        <span class="font-mono font-bold text-slate-600">[{{ $p['kode'] }}]</span>
                                                        <span class="font-normal">{{ $p['nama'] ?: $p['kode'] }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </td>

                            <!-- Ruangan & Kelompok -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-700">
                                    {{ $jadwal->ruangan ?? '-' }}
                                </div>
                                @if($jadwal->kelompok)
                                    <span class="inline-block px-2 py-0.5 bg-slate-100 rounded text-[10px] text-slate-600 font-medium mt-0.5">
                                        Kelompok: {{ $jadwal->kelompok }}
                                    </span>
                                @endif
                            </td>

                            <!-- Tindakan -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.jadwals.edit', $jadwal->id) }}" 
                                       title="Ubah Jadwal"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    <form action="{{ route('admin.jadwals.destroy', $jadwal->id) }}" method="POST"
                                          data-confirm="Apakah Anda yakin ingin menghapus jadwal '{{ addslashes($jadwal->nama_kuliah) }}'?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Jadwal"
                                                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td :colspan="isSelectMode ? 8 : 7" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Tidak ada jadwal kuliah ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan filter pencarian atau tambahkan jadwal kuliah baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwals->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function jadwalTableApp(pageIds) {
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
                event.preventDefault();
                if (this.selectedIds.length === 0) {
                    swalAlert('Pilih Jadwal', 'Silakan pilih setidaknya satu jadwal kuliah untuk dihapus.', 'info');
                    return false;
                }
                const form = event.target;
                swalConfirm(
                    'Konfirmasi Hapus Massal',
                    `Apakah Anda yakin ingin menghapus ${this.selectedIds.length} jadwal kuliah yang dipilih? Tindakan ini tidak dapat dibatalkan.`,
                    () => {
                        form.submit();
                    }
                );
                return false;
            }
        }
    }
</script>
@endsection
