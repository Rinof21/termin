@extends('layouts.app', ['title' => 'Ubah Izin Akses (Permission)'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.permissions.index') }}" class="text-slate-400 hover:text-slate-600">Izin Akses</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Ubah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Izin Akses</h1>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui nama permission: <span class="font-mono font-semibold text-slate-800">{{ $permission->name }}</span></p>
        </div>
        <a href="{{ route('admin.permissions.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Permission -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Izin Akses (Permission) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="{{ old('name', $permission->name) }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('name') border-rose-300 @enderror">
                <p class="text-[11px] text-slate-400 mt-1.5">Gunakan format huruf kecil dengan tanda strip (misal: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700 font-mono">lihat-laporan</code>).</p>
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.permissions.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Perbarui Izin Akses
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
