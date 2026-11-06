@extends('layouts.app', ['title' => 'Data Pengguna'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Data Pengguna</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Data Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola akun pengguna, data login, serta penetapan peran (role) masing-masing</p>
        </div>
        <a href="{{ route('admin.users.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="bi bi-person-plus-fill"></i>
            <span>Tambah Pengguna Baru</span>
        </a>
    </div>

    <!-- Toolbar Pencarian & Filter -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Kolom Pencarian -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari berdasarkan nama lengkap atau alamat email..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
            </div>

            <!-- Filter Peran -->
            <div class="w-full sm:w-60">
                <select name="role" onchange="this.form.submit()" 
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
                    <option value="">Semua Peran (Roles)</option>
                    @foreach($roles as $roleName)
                        <option value="{{ $roleName }}" {{ $roleFilter === $roleName ? 'selected' : '' }}>
                            Peran: {{ $roleName }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($search || $roleFilter)
                <a href="{{ route('admin.users.index') }}" 
                   class="px-3.5 py-2 text-sm text-slate-500 hover:text-slate-800 border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Filter</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/75 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Informasi Pengguna</th>
                        <th class="px-6 py-3.5">Peran yang Dimiliki</th>
                        <th class="px-6 py-3.5">Tanggal Pendaftaran</th>
                        <th class="px-6 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-semibold flex items-center justify-center text-xs border border-slate-200/60">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 flex items-center gap-2">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === Auth::id())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">Akun Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->name === 'Super Admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($role->name === 'Administrator' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                            <i class="bi bi-shield-check text-[10px] mr-1"></i>
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Belum ditentukan peran</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $user->created_at->translatedFormat('d F Y, H:i') }} WIB
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       title="Ubah Data Pengguna"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" 
                                              data-confirm="Apakah Anda yakin ingin menghapus akun pengguna '{{ addslashes($user->name) }}'? Tindakan ini tidak dapat dibatalkan." class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Pengguna" 
                                                    class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                                <i class="bi bi-trash text-base"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="bi bi-people"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Data pengguna tidak ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian atau filter yang lain.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
