@extends('layouts.app', ['title' => 'Izin Akses (Permissions)'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Izin Akses (Permissions)</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Izin Akses (Permissions)</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftar hak akses granular Spatie yang dapat dipasangkan ke dalam peran (roles)</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="bi bi-key-fill"></i>
            <span>Tambah Izin Baru</span>
        </a>
    </div>

    <!-- Kotak Penjelasan Edukatif Bahasa Indonesia -->
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3.5 text-amber-900">
        <i class="bi bi-info-circle-fill text-amber-600 text-xl shrink-0 mt-0.5"></i>
        <div class="text-xs leading-relaxed">
            <span class="font-bold">Panduan Standar Penamaan Izin Akses:</span>
            Gunakan format <code class="bg-amber-100/80 px-1 py-0.5 rounded font-mono font-semibold text-amber-900">aksi-modul</code>, contohnya:
            <span class="font-mono">lihat-pengguna</span>, <span class="font-mono">tambah-pengguna</span>, <span class="font-mono">ubah-pengguna</span>, <span class="font-mono">hapus-pengguna</span>.
            Format ini memudahkan pengelompokan otomatis pada halaman pembuatan peran.
        </div>
    </div>

    <!-- Toolbar Pencarian -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.permissions.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama izin akses (contoh: lihat-pengguna, tambah-peran)..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
            </div>

            @if($search)
                <a href="{{ route('admin.permissions.index') }}" 
                   class="px-3.5 py-2 text-sm text-slate-500 hover:text-slate-800 border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Tabel Izin Akses -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Izin Akses (Permission)</th>
                        <th class="px-6 py-3.5">Guard Otentikasi</th>
                        <th class="px-6 py-3.5">Dipakai oleh Peran (Roles)</th>
                        <th class="px-6 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                                        <i class="bi bi-key"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 font-mono text-xs block">{{ $permission->name }}</span>
                                        <span class="text-[11px] text-slate-400">
                                            @if(str_contains($permission->name, 'lihat'))
                                                Izin untuk melihat/membaca data
                                            @elseif(str_contains($permission->name, 'tambah'))
                                                Izin untuk menambah data baru
                                            @elseif(str_contains($permission->name, 'ubah'))
                                                Izin untuk mengubah/mengedit data
                                            @elseif(str_contains($permission->name, 'hapus'))
                                                Izin untuk menghapus data
                                            @else
                                                Izin akses khusus
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-mono bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $permission->guard_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($permission->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 border border-brand-200">
                                            <i class="bi bi-shield text-[10px] mr-1"></i>
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Belum dipasangkan ke peran manapun</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                                       title="Ubah Nama Izin"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin akses {{ $permission->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Izin"
                                                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data izin akses ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permissions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
