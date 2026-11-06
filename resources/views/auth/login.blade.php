@extends('layouts.auth', ['title' => 'Masuk'])

@section('content')
<div class="w-full max-w-md">
    <!-- Brand Logo & Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-500/30 mb-3">
            <i class="bi bi-shield-lock-fill text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Termin Portal</h1>
        <p class="text-sm text-slate-500 mt-1">Masuk untuk mengelola sistem dan hak akses</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 backdrop-blur-sm">
        <!-- Flash Alerts -->
        @if (session('success'))
            <div class="mb-5 flex items-center gap-3 p-3.5 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl">
                <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-5 flex items-center gap-3 p-3.5 text-sm text-sky-800 bg-sky-50 border border-sky-200 rounded-xl">
                <i class="bi bi-info-circle-fill text-sky-600 text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3.5 text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-xl">
                <div class="flex items-center gap-2 font-medium">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                    <span>Terdapat kendala:</span>
                </div>
                <ul class="mt-1.5 list-disc list-inside text-xs text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <input type="email" name="email" id="email" required autofocus
                        value="{{ old('email') }}"
                        placeholder="admin@admin.com"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition duration-150 ease-in-out @error('email') border-rose-300 bg-rose-50/30 @enderror">
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-key"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 transition duration-150 ease-in-out">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20">
                    <span class="text-xs text-slate-600">Ingat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-medium text-sm rounded-xl shadow-md shadow-brand-500/20 focus:outline-none focus:ring-4 focus:ring-brand-500/20 transition duration-150 ease-in-out flex items-center justify-center gap-2">
                <span>Masuk ke Dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <!-- Demo Credentials Helper -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-500 mb-2.5 flex items-center gap-1.5">
                <i class="bi bi-person-badge"></i>
                <span>Akun Uji Coba Tersedia:</span>
            </p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" onclick="fillCredentials('admin@admin.com', 'password')"
                    class="text-left p-2.5 rounded-lg border border-slate-200/80 bg-slate-50/70 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                    <div class="font-medium text-slate-800">Super Admin</div>
                    <div class="text-[11px] text-slate-500 truncate">admin@admin.com</div>
                </button>
                <button type="button" onclick="fillCredentials('staff@admin.com', 'password')"
                    class="text-left p-2.5 rounded-lg border border-slate-200/80 bg-slate-50/70 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                    <div class="font-medium text-slate-800">Admin Staff</div>
                    <div class="text-[11px] text-slate-500 truncate">staff@admin.com</div>
                </button>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 text-center">Password: <span class="font-mono font-medium text-slate-600">password</span></p>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="mt-6 text-center space-y-2">
        <a href="{{ route('home') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-800 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Portal Jadwal Publik</span>
        </a>
        <p class="text-xs text-slate-400">
            &copy; {{ date('Y') }} Termin Admin &bull; Sistem Akademik S1 Kedokteran
        </p>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection

