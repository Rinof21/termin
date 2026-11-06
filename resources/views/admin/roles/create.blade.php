@extends('layouts.app', ['title' => 'Tambah Peran (Role) Baru'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.roles.index') }}" class="text-slate-400 hover:text-slate-600">Kelola Peran</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Peran (Role) Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Tentukan nama peran dan pilih izin akses apa saja yang diberikan</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Peran -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Peran (Role) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Manager Operasional, Staf Keuangan, Kasir"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('name') border-rose-300 @enderror">
                <p class="text-[11px] text-slate-400 mt-1">Nama peran harus unik dan merepresentasikan divisi atau fungsi pengguna.</p>
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pengelompokan Izin Akses (Permissions) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Pemberian Hak Akses (Permissions)
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">Centang izin yang diperbolehkan untuk peran ini.</p>
                    </div>
                    <button type="button" onclick="toggleAllPermissions(true)" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                        Pilih / Batal Semua
                    </button>
                </div>

                <div class="space-y-6">
                    @foreach($permissions as $group => $items)
                        <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50">
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200/60">
                                <span class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                    <i class="bi bi-folder2-open text-brand-600"></i>
                                    Modul {{ ucfirst($group) }}
                                </span>
                                <button type="button" onclick="toggleGroupCheckboxes('group-{{ Str::slug($group) }}')" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800">
                                    Pilih Semua Modul Ini
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3">
                                @foreach($items as $permission)
                                    <label class="flex items-start gap-3 p-3 rounded-xl bg-white border border-slate-200 hover:border-brand-300 cursor-pointer transition">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                               {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                               class="mt-0.5 group-{{ Str::slug($group) }} perm-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500/20">
                                        <div>
                                            <span class="text-xs font-semibold text-slate-900 block font-mono">{{ $permission->name }}</span>
                                            <span class="text-[11px] text-slate-500 mt-0.5 block">
                                                @if(str_contains($permission->name, 'lihat'))
                                                    Mengizinkan melihat dan membaca data pada modul {{ $group }}.
                                                @elseif(str_contains($permission->name, 'tambah'))
                                                    Mengizinkan membuat atau menambahkan data baru pada modul {{ $group }}.
                                                @elseif(str_contains($permission->name, 'ubah'))
                                                    Mengizinkan mengubah atau mengedit data yang sudah ada pada modul {{ $group }}.
                                                @elseif(str_contains($permission->name, 'hapus'))
                                                    Mengizinkan menghapus data dari sistem pada modul {{ $group }}.
                                                @else
                                                    Hak akses khusus untuk modul {{ $group }}.
                                                @endif
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Peran Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAllPermissions() {
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }

    function toggleGroupCheckboxes(groupClass) {
        const checkboxes = document.querySelectorAll('.' + groupClass);
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endsection
