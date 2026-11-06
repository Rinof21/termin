@extends('layouts.app', ['title' => 'Data Dosen Pengajar'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Data Dosen</span>
@endsection

@section('content')
<div class="space-y-6" x-data="dosenTableApp([{{ $dosens->pluck('id')->implode(',') }}])">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Dosen Prodi S1 Kedokteran</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftar dokter pendidik klinis & dosen pengampu perkuliahan termin</p>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Tombol Toggle Mode Pilih / Select -->
            <button type="button" @click="toggleSelectMode()" 
                    :class="isSelectMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 border rounded-xl text-xs font-semibold shadow-xs transition">
                <i :class="isSelectMode ? 'bi bi-check-all text-base' : 'bi bi-check2-square text-brand-600 text-sm'"></i>
                <span x-text="isSelectMode ? 'Selesai Memilih' : 'Pilih / Select'"></span>
            </button>

            <a href="{{ route('admin.dosens.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah Dosen Baru</span>
            </a>
        </div>
    </div>

    <!-- Toolbar Pencarian -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.dosens.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari berdasarkan nama dosen, inisial/kode (misal: YAN, SHL), atau NIP..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            @if($search)
                <a href="{{ route('admin.dosens.index') }}" 
                   class="px-3.5 py-2 text-sm text-slate-500 hover:text-slate-800 border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Form Aksi Hapus Massal (Bulk Delete Form) -->
    <form id="bulkDeleteForm" action="{{ route('admin.dosens.bulk-delete') }}" method="POST" @submit="confirmBulkDelete($event)">
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
            <span>Data dosen terpilih untuk tindakan massal</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="selectedIds = []" 
                    class="px-3.5 py-2 text-xs text-slate-600 hover:text-slate-800 font-semibold rounded-xl hover:bg-white bg-slate-100/70 border border-slate-200 transition">
                Batalkan Pilihan
            </button>
            <button type="submit" form="bulkDeleteForm"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="bi bi-trash-fill text-sm"></i>
                <span>Hapus (<span x-text="selectedIds.length"></span>) Dosen Terpilih</span>
            </button>
        </div>
    </div>

    <!-- Tabel Data Dosen -->
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
                        <th class="px-6 py-3.5">Kode / Inisial</th>
                        <th class="px-6 py-3.5">Nama Dosen & Gelar</th>
                        <th class="px-6 py-3.5">NIP / Kontak</th>
                        <th class="px-6 py-3.5">Beban Jadwal</th>
                        <th class="px-6 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($dosens as $dosen)
                        <tr class="hover:bg-slate-50/60 transition" :class="selectedIds.includes({{ $dosen->id }}) ? 'bg-brand-50/30' : ''">
                            <!-- Checkbox Item (Hanya Tampil saat Mode Pilih Aktif) -->
                            <td x-show="isSelectMode" x-cloak class="px-4 py-4 text-center transition">
                                <input type="checkbox" :value="{{ $dosen->id }}" x-model="selectedIds"
                                       class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                            </td>

                            <!-- Nomor Urut i++ (Mempertimbangkan pagination) -->
                            <td class="px-3 py-4 text-center">
                                <span class="font-mono text-xs font-bold text-slate-500">
                                    {{ $dosens->firstItem() + $loop->index }}
                                </span>
                            </td>

                            <!-- Kode Inisial -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-brand-50 text-brand-700 border border-brand-200 shadow-xs">
                                    {{ $dosen->kode }}
                                </span>
                            </td>

                            <!-- Nama Dosen -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-sm">
                                    {{ $dosen->nama }}
                                </div>
                                @if($dosen->modul_koordinator_count > 0)
                                    <span class="inline-block mt-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.2 rounded-full">
                                        Koordinator {{ $dosen->modul_koordinator_count }} Modul
                                    </span>
                                @endif
                            </td>

                            <!-- NIP & Email -->
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-600 font-mono">
                                    NIP: {{ $dosen->nip ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $dosen->email ?? '-' }}
                                </div>
                            </td>

                            <!-- Beban Jadwal -->
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.jadwals.table', ['dosen_id' => $dosen->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 transition">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>{{ $dosen->jadwals_count }} Sesi Kuliah</span>
                                </a>
                            </td>

                            <!-- Tindakan -->
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.dosens.edit', $dosen->id) }}" 
                                       title="Ubah Data Dosen"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    <form action="{{ route('admin.dosens.destroy', $dosen->id) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dosen {{ $dosen->nama }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Dosen"
                                                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td :colspan="isSelectMode ? 7 : 6" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="bi bi-person-x"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Data dosen tidak ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dosens->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $dosens->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function dosenTableApp(pageIds) {
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
                    alert('Silakan pilih setidaknya satu dosen untuk dihapus.');
                    event.preventDefault();
                    return false;
                }
                if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selectedIds.length} data dosen yang dipilih? Tindakan ini tidak dapat dibatalkan.`)) {
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        }
    }
</script>
@endsection
