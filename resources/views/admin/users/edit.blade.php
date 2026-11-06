@extends('layouts.app', ['title' => 'Ubah Data Pengguna'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600">Data Pengguna</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Ubah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Data Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui profil pengguna dan sesuaikan peran hak akses untuk {{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('name') border-rose-300 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition @error('email') border-rose-300 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ganti Password Opsional -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Ubah Kata Sandi (Opsional)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Biarkan kosong jika tidak ingin mengganti kata sandi lama.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kata Sandi Baru
                        </label>
                        <input type="password" name="password" id="password"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition @error('password') border-rose-300 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Ulangi Kata Sandi Baru
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               placeholder="Ketik ulang kata sandi baru"
                               class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition">
                    </div>
                </div>
            </div>

            <!-- Pemilihan Peran -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Pengaturan Peran (Roles)
                </label>
                <p class="text-xs text-slate-500 mb-3">Tentukan peran yang aktif untuk akun pengguna ini.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-brand-50/40 hover:border-brand-200 cursor-pointer transition">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}
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
                    Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
