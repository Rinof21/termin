@extends('layouts.app', ['title' => 'Tambah Dosen Baru'])

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Beranda</a>
    <span class="text-slate-400">/</span>
    <a href="{{ route('admin.dosens.index') }}" class="text-slate-400 hover:text-slate-600">Data Dosen</a>
    <span class="text-slate-400">/</span>
    <span class="text-slate-800 font-semibold">Tambah</span>
@endsection

@section('content')
<div class="w-full space-y-6">
    <!-- Header Formulir -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Dosen Pengajar Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan dokter atau dosen pengampu beserta kode inisial jadwalnya</p>
        </div>
        <a href="{{ route('admin.dosens.index') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 rounded-xl hover:bg-white bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Kartu Formulir -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.dosens.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Kode / Inisial Dosen -->
            <div>
                <label for="kode" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kode / Inisial Dosen <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode" id="kode" required value="{{ old('kode') }}"
                       placeholder="Contoh: YAN, SHL, IF, DN (2-4 Huruf Kapital)"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('kode') border-rose-300 @enderror">
                <p class="text-[11px] text-slate-400 mt-1">Inisial ini akan muncul di visualisasi kalender dan jadwal termin perkuliahan.</p>
                @error('kode')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Lengkap Beserta Gelar -->
            <div>
                <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Lengkap & Gelar Akademik <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                       placeholder="Contoh: dr. Yanti, Sp.A, M.Kes"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition @error('nama') border-rose-300 @enderror">
                @error('nama')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIP & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nip" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        NIP / NIDN (Opsional)
                    </label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                           placeholder="Contoh: 197805122005012001"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email (Opsional)
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           placeholder="dosen@fk.ac.id"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Nomor HP / WhatsApp -->
            <div>
                <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    No. Handphone / WhatsApp (Opsional)
                </label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                       placeholder="081234567890"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.dosens.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Data Dosen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

