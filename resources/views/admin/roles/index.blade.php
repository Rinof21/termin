@extends('layouts.app', ['title' => 'Kelola Peran (Roles)'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Kelola Peran (Roles)</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Peran (Roles)</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola kelompok peran otorisasi Spatie serta daftar hak akses (permissions) yang dimiliki</p>
        </div>
        @can('tambah-peran')
        <a href="{{ route('admin.roles.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="bi bi-shield-plus"></i>
            <span>Tambah Peran Baru</span>
        </a>
        @endcan
    </div>

    <!-- Kotak Penjelasan Spatie dalam Bahasa Indonesia -->
    <div class="p-4 bg-sky-50 border border-sky-200 rounded-2xl flex items-start gap-3.5 text-sky-900">
        <i class="bi bi-lightbulb-fill text-sky-600 text-xl shrink-0 mt-0.5"></i>
        <div class="text-xs leading-relaxed">
            <span class="font-bold">Penjelasan Singkat:</span>
            <strong>Peran (Role)</strong> digunakan untuk mengelompokkan pengguna berdasarkan tanggung jawabnya (misalnya <em>Super Admin</em>, <em>Administrator</em>, atau <em>Staf</em>). Setiap peran memiliki sekumpulan <strong>Izin Akses (Permissions)</strong> yang menentukan apa saja yang boleh dan tidak boleh dilakukan di aplikasi.
        </div>
    </div>

    <!-- Grid Kartu Peran -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles as $role)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div class="p-6">
                    <!-- Header Kartu Peran -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="inline-flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl {{ $role->name === 'Super Admin' ? 'bg-purple-100 text-purple-700' : 'bg-brand-100 text-brand-700' }} flex items-center justify-center font-bold text-base">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $role->name }}</h3>
                                <p class="text-[11px] text-slate-400 font-mono">Guard: {{ $role->guard_name }}</p>
                            </div>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold border border-slate-200/60">
                            {{ $role->users_count }} Pengguna
                        </span>
                    </div>

                    <!-- Daftar Izin Akses Terpasang -->
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            <span>Izin Akses Aktif ({{ $role->permissions->count() }})</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto pr-1">
                            @forelse($role->permissions as $permission)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/60">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Belum ada izin akses yang dipilih.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Footer Aksi -->
                <div class="px-6 py-3.5 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-end gap-2">
                    @can('ubah-peran')
                    <a href="{{ route('admin.roles.edit', $role->id) }}" 
                       class="px-3 py-1.5 text-xs font-semibold text-brand-700 bg-white border border-slate-200 hover:bg-brand-50 rounded-lg transition inline-flex items-center gap-1.5">
                        <i class="bi bi-pencil-square"></i>
                        <span>Ubah Peran</span>
                    </a>
                    @endcan

                    @if($role->name !== 'Super Admin')
                        @can('hapus-peran')
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                              data-confirm="Apakah Anda yakin ingin menghapus peran '{{ addslashes($role->name) }}'? Pastikan peran ini tidak lagi dipakai pengguna." class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                    title="Hapus Peran">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
                <i class="bi bi-shield-x text-3xl text-slate-400 mb-2"></i>
                <p class="text-sm font-semibold text-slate-700">Belum ada peran yang dibuat</p>
            </div>
        @endforelse
    </div>

    @if($roles->hasPages())
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80">
            {{ $roles->links() }}
        </div>
    @endif
</div>
@endsection
