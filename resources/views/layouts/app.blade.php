<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Panel Admin Termin</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50" 
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: localStorage.getItem('termin_sidebar_collapsed') === 'true',
          toggleSidebar() {
              if (window.innerWidth < 768) {
                  this.sidebarOpen = !this.sidebarOpen;
              } else {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
                  localStorage.setItem('termin_sidebar_collapsed', this.sidebarCollapsed);
              }
          }
      }">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Backdrop Sidebar Mobile -->
        <div x-cloak x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm md:hidden"></div>

        <!-- Sidebar Menu Navigasi (Bisa Disembunyikan / Toggleable) -->
        <aside :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'md:translate-x-0 md:w-64': !sidebarCollapsed,
                   'md:-translate-x-full md:w-0 md:border-r-0 md:overflow-hidden md:p-0': sidebarCollapsed
               }" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-all duration-300 ease-in-out md:sticky md:top-0 md:h-screen md:shrink-0 md:z-40">
            
            <div class="w-64 flex flex-col h-full shrink-0">
                <!-- Logo & Nama Aplikasi -->
                <div class="h-16 px-5 flex items-center justify-between border-b border-slate-100 shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-md shadow-brand-500/20 font-bold text-lg">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="leading-tight">
                            <span class="font-bold text-slate-900 text-base tracking-tight">TERMIN</span>
                            <span class="block text-[10px] uppercase font-semibold text-brand-600 tracking-wider">Panel Admin</span>
                        </div>
                    </a>
                    <!-- Tombol Tutup Mobile & Sembunyikan Desktop -->
                    <button type="button" @click="toggleSidebar()" 
                            class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                            title="Sembunyikan Sidebar">
                        <i class="bi bi-x-lg text-base md:hidden"></i>
                        <i class="bi bi-chevron-left text-sm hidden md:inline"></i>
                    </button>
                </div>

            <!-- Tautan Menu Navigasi -->
            <div class="flex-1 px-4 py-5 space-y-6 overflow-y-auto">
                <!-- Bagian 1: Utama -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') || request()->routeIs('admin.jadwals.index') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-calendar3 text-lg {{ request()->routeIs('dashboard') || request()->routeIs('admin.jadwals.index') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Kalender Termin</span>
                        </a>
                    </nav>
                </div>

                <!-- Bagian 2: Jadwal Termin & Perkuliahan S1 Kedokteran -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Jadwal & Akademik</p>
                    <nav class="space-y-1">
                        <!-- Matriks Jadwal (Tampilan Spreadsheet Modul) -->
                        <a href="{{ route('admin.jadwals.matrix') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.jadwals.matrix') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-grid-3x3 text-lg {{ request()->routeIs('admin.jadwals.matrix') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Matriks Jadwal</span>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded-full">Baru</span>
                        </a>

                        <!-- Tabel Jadwal Kuliah -->
                        <a href="{{ route('admin.jadwals.table') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.jadwals.table') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-calendar-week text-lg {{ request()->routeIs('admin.jadwals.table') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Daftar Jadwal Kuliah</span>
                        </a>

                        <!-- Jadwal Termin Akademik -->
                        <a href="{{ route('admin.termins.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.termins.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-calendar-range text-lg {{ request()->routeIs('admin.termins.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Jadwal Termin</span>
                        </a>

                        <!-- Master Tahun Semester (2026.1, 2026.2, dll) -->
                        <a href="{{ route('admin.semesters.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.semesters.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-mortarboard text-lg {{ request()->routeIs('admin.semesters.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Tahun Semester</span>
                        </a>

                        <!-- Data Modul -->
                        <a href="{{ route('admin.moduls.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.moduls.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-journals text-lg {{ request()->routeIs('admin.moduls.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Data Modul</span>
                        </a>

                        <!-- Data Dosen -->
                        <a href="{{ route('admin.dosens.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dosens.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-person-lines-fill text-lg {{ request()->routeIs('admin.dosens.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Data Dosen</span>
                        </a>
                    </nav>
                </div>

                <!-- Bagian 3: Manajemen Akses Spatie -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Kontrol Hak Akses</p>
                    <nav class="space-y-1">
                        <!-- Pengguna -->
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-people-fill text-lg {{ request()->routeIs('admin.users.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Data Pengguna</span>
                        </a>

                        <!-- Peran (Roles) -->
                        <a href="{{ route('admin.roles.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-person-badge-fill text-lg {{ request()->routeIs('admin.roles.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Kelola Peran (Roles)</span>
                        </a>

                        <!-- Izin Akses (Permissions) -->
                        <a href="{{ route('admin.permissions.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.permissions.*') ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="bi bi-key-fill text-lg {{ request()->routeIs('admin.permissions.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span class="flex-1">Izin Akses (Permissions)</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Info Profil Pengguna di Bawah Sidebar -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-semibold flex items-center justify-center text-sm shadow-xs border border-brand-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ Auth::user()->roles->pluck('name')->first() ?? 'Tanpa Peran' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white transition">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
            </div>
        </aside>

        <!-- Area Konten Utama -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- Header Navigasi Atas -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 md:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <!-- Tombol Toggle Sidebar (Mobile & Desktop) -->
                    <button type="button" @click="toggleSidebar()" 
                            class="p-2 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition flex items-center justify-center shrink-0"
                            :title="sidebarCollapsed ? 'Tampilkan Sidebar' : 'Sembunyikan Sidebar'">
                        <i class="bi text-xl" :class="sidebarCollapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset'"></i>
                    </button>

                    <div class="text-sm font-medium text-slate-500 hidden sm:block">
                        @yield('breadcrumbs')
                    </div>
                </div>

                <!-- Bagian Kanan Header -->
                <div class="flex items-center gap-2.5">
                    <!-- Widget Pengalih Semester & Termin Aktif -->
                    <div class="relative" x-data="{ openSwitcher: false }" @click.away="openSwitcher = false">
                        <button @click="openSwitcher = !openSwitcher" 
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border transition shadow-xs {{ ($activeSemester ?? 'Ganjil') === 'Ganjil' ? 'bg-indigo-50/80 text-indigo-700 border-indigo-200 hover:bg-indigo-100' : 'bg-emerald-50/80 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}"
                                title="Klik untuk mengganti Tahun Semester & Termin aktif">
                            <span class="w-2 h-2 rounded-full {{ ($activeSemester ?? 'Ganjil') === 'Ganjil' ? 'bg-indigo-600' : 'bg-emerald-600' }} animate-pulse"></span>
                            <span class="font-mono font-extrabold text-brand-700 bg-white px-1.5 py-0.5 rounded border border-slate-200/80 shadow-2xs">{{ $activeTahunSemester ?? '2026.1' }}</span>
                            <span class="hidden sm:inline">Semester {{ $activeSemester ?? 'Ganjil' }}</span>
                            <span class="text-slate-300 font-normal">|</span>
                            <span class="font-bold text-slate-800">{{ $activeTermin ? $activeTermin->nama : 'Termin' }}</span>
                            <span class="text-[10px] uppercase font-bold px-1.5 py-0.2 rounded bg-white/90 border border-slate-200/80 text-emerald-700 shadow-xs">Aktif</span>
                            <i class="bi bi-chevron-down text-[10px] ml-0.5"></i>
                        </button>

                        <!-- Dropdown Pilihan Semester & Termin -->
                        <div x-cloak x-show="openSwitcher" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2.5 z-50">
                            <!-- Bagian 1: Pilih Tahun Semester Akademik (2026.1, 2026.2, 2027.1, 2027.2) -->
                            <div class="px-4 py-2 border-b border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tahun Semester</p>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.semesters.create') }}" 
                                           class="text-[10px] font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2 py-0.5 rounded border border-brand-200 transition">
                                            + Tambah
                                        </a>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($availableTahunSemesters as $tsCode)
                                        @php
                                            $isGenap = str_ends_with($tsCode, '.2');
                                            $isCurrent = ($activeTahunSemester ?? '2026.1') === $tsCode;
                                        @endphp
                                        <form action="{{ route('admin.settings.semester') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tahun_semester" value="{{ $tsCode }}">
                                            <button type="submit" 
                                                    class="w-full text-center px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center justify-between transition {{ $isCurrent ? ($isGenap ? 'bg-emerald-600 text-white shadow-xs' : 'bg-indigo-600 text-white shadow-xs') : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="bi {{ $isGenap ? 'bi-moon-stars' : 'bi-sun' }}"></i>
                                                    <span class="font-mono font-bold">{{ $tsCode }}</span>
                                                </div>
                                                <span class="text-[10px] opacity-85 font-medium">{{ $isGenap ? 'Genap' : 'Ganjil' }}</span>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Bagian 2: Pilih Termin Aktif Berjalan -->
                            <div class="px-4 py-2.5">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pilih Termin Aktif ({{ $activeTahunSemester ?? '2026.1' }})</p>
                                    <span class="text-[10px] text-slate-500 font-medium">6 Minggu / Termin</span>
                                </div>
                                <div class="grid grid-cols-4 gap-1.5 max-h-44 overflow-y-auto pr-0.5">
                                    @php
                                        $currentTermins = ($allTermins ?? collect())->filter(function($t) use ($activeTahunSemester, $activeSemester) {
                                            return $t->tahun_semester === $activeTahunSemester || $t->tipe_semester === $activeSemester;
                                        })->sortBy('nomor');
                                    @endphp
                                    @foreach($currentTermins as $terminItem)
                                        <form action="{{ route('admin.termins.activate', $terminItem) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    title="{{ $terminItem->nama }} ({{ $terminItem->rentang_tanggal }})"
                                                    class="w-full py-1.5 rounded-xl text-xs font-bold text-center transition {{ $terminItem->is_aktif ? 'bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-400' : 'bg-slate-50 text-slate-700 hover:bg-brand-50 hover:text-brand-700 border border-slate-200' }}">
                                                T{{ $terminItem->nomor }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                                @if($activeTermin)
                                    <div class="mt-2.5 p-2 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-600">
                                        <span class="font-bold text-slate-800">{{ $activeTermin->nama }}:</span> {{ $activeTermin->rentang_tanggal }}
                                    </div>
                                @endif
                            </div>

                            <!-- Footer Link ke Kelola Semester & Termin -->
                            <div class="pt-2 px-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                <a href="{{ route('admin.semesters.index') }}" class="font-semibold text-slate-600 hover:text-indigo-600 transition flex items-center gap-1">
                                    <i class="bi bi-mortarboard text-indigo-600"></i>
                                    <span>Kelola Semester</span>
                                </a>
                                <a href="{{ route('admin.termins.index') }}" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1">
                                    <span>Kelola Termin</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 hidden sm:inline-flex">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Akses Aktif
                    </span>

                    <!-- Dropdown Profil -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition">
                            <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-medium flex items-center justify-center text-xs shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="text-xs font-medium text-slate-700 hidden md:block">{{ Auth::user()->name }}</span>
                            <i class="bi bi-chevron-down text-slate-400 text-xs hidden md:block"></i>
                        </button>

                        <div x-cloak x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200/80 py-1.5 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 text-xs">
                                <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                                <p class="text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('home') }}" target="_blank" class="w-full text-left px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition border-b border-slate-100">
                                <i class="bi bi-box-arrow-up-right text-brand-600"></i>
                                <span>Lihat Portal Publik</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Isi Halaman (Container Fluid: Lebar Penuh) -->
            <main class="flex-1 p-4 md:p-6 lg:p-8 w-full">
                <!-- Notifikasi Berhasil -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center justify-between p-4 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-xs">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                @endif

                <!-- Notifikasi Peringatan / Gagal -->
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center justify-between p-4 text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-xl shadow-xs">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-slash-circle-fill text-rose-600 text-lg"></i>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                @endif

                <!-- Notifikasi Kesalahan Validasi -->
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2 font-semibold">
                                <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                                <span>Mohon periksa kembali isian formulir:</span>
                            </div>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')

    <!-- SweetAlert2 Notifikasi Global & Konfirmasi Aksi -->
    <script>
        // Global SweetAlert Toast mixin
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Helper fungsi global konfirmasi & alert SweetAlert2
        window.swalConfirm = function(title, text, confirmCallback, options = {}) {
            Swal.fire({
                title: title || 'Konfirmasi',
                text: text || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmColor || '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: options.confirmText || 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl font-sans shadow-xl',
                    confirmButton: 'rounded-xl px-4 py-2.5 text-sm font-semibold',
                    cancelButton: 'rounded-xl px-4 py-2.5 text-sm font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmCallback();
                }
            });
        };

        window.swalAlert = function(title, text, icon = 'info') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-2xl font-sans shadow-xl',
                    confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                }
            });
        };

        // Trigger Notifikasi Flash saat Halaman Dimuat
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    timer: 3000,
                    timerProgressBar: true,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-2xl font-sans shadow-xl',
                        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Peringatan / Gagal',
                    text: @json(session('error')),
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'rounded-2xl font-sans shadow-xl',
                        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                    }
                });
            @endif

            @if ($errors->any())
                let errorHtml = '<div class="text-left mt-2"><p class="text-xs font-semibold text-slate-700 mb-2">Mohon periksa data formulir berikut:</p><ul class="list-disc list-inside text-xs text-rose-600 space-y-1">';
                @foreach ($errors->all() as $error)
                    errorHtml += '<li>' + @json($error) + '</li>';
                @endforeach
                errorHtml += '</ul></div>';

                Swal.fire({
                    icon: 'warning',
                    title: 'Periksa Isian Formulir',
                    html: errorHtml,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Perbaiki',
                    customClass: {
                        popup: 'rounded-2xl font-sans shadow-xl',
                        confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold'
                    }
                });
            @endif
        });

        // Global Form Submission Interceptor untuk konfirmasi hapus / submit
        document.addEventListener('submit', function (e) {
            const form = e.target;
            const isDelete = form.querySelector('input[name="_method"][value="DELETE"]') !== null;
            const hasDataConfirm = form.hasAttribute('data-confirm');

            if ((isDelete || hasDataConfirm) && !form.dataset.swalConfirmed) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const message = form.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
                const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Hapus';

                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl font-sans shadow-xl',
                        confirmButton: 'rounded-xl px-4 py-2.5 text-sm font-semibold',
                        cancelButton: 'rounded-xl px-4 py-2.5 text-sm font-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.swalConfirmed = 'true';
                        form.submit();
                    }
                });
                return false;
            }
        }, true);
    </script>
</body>
</html>
