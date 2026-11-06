@extends('layouts.app', ['title' => 'Tambah Pengguna Baru'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600">Data Pengguna</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Pengguna Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan akun pengguna baru dan tetapkan peran hak akses yang diberikan</p>
        </div>
        <a href="{{ route('admin.users.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Muhammad Rizki"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('name') border-rose-300 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                       placeholder="nama@perusahaan.com"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('email') border-rose-300 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi (Password) <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           placeholder="Minimal 8 karakter"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('password') border-rose-300 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ulangi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           placeholder="Ketik ulang kata sandi"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition">
                </div>
            </div>

            <!-- Pemilihan Peran (Spatie) -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Pilih Peran (Role) untuk Pengguna Ini
                </label>
                <p class="text-xs text-slate-500 mb-3">Centang satu atau beberapa peran. Pengguna akan mewarisi seluruh izin akses yang terdaftar pada peran terpilih.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-brand-50/40 hover:border-brand-200 cursor-pointer transition">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20">
                            <div>
                                <span class="font-semibold text-sm text-slate-900">{{ $role->name }}</span>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $role->permissions->count() }} izin akses aktif</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
