<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\TerminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (will redirect to login if unauthenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Spatie Permission Management
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users CRUD
        Route::resource('users', UserController::class);

        // Roles CRUD
        Route::resource('roles', RoleController::class);

        // Permissions CRUD
        Route::resource('permissions', PermissionController::class);

        // Jadwal Termin & Kalender
        Route::get('jadwals', [JadwalController::class, 'index'])->name('jadwals.index');
        Route::get('jadwals/table', [JadwalController::class, 'table'])->name('jadwals.table');
        Route::get('jadwals/matrix', [JadwalController::class, 'matrix'])->name('jadwals.matrix');
        Route::get('jadwals/events', [JadwalController::class, 'events'])->name('jadwals.events');
        Route::post('jadwals/bulk-delete', [JadwalController::class, 'bulkDelete'])->name('jadwals.bulk-delete');
        Route::resource('jadwals', JadwalController::class)->except(['index']);

        // Pengaturan Sistem (Semester Aktif)
        Route::post('settings/semester', [ModulController::class, 'switchSemester'])->name('settings.semester');

        // Master Data Tahun Semester (2026.1, 2026.2, 2027.1, dst)
        Route::post('semesters/bulk-delete', [SemesterController::class, 'bulkDelete'])->name('semesters.bulk-delete');
        Route::post('semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');
        Route::resource('semesters', SemesterController::class);

        // Jadwal Termin Akademik (6-8 Termin per Semester)
        Route::post('termins/bulk-delete', [TerminController::class, 'bulkDelete'])->name('termins.bulk-delete');
        Route::post('termins/{termin}/activate', [TerminController::class, 'activate'])->name('termins.activate');
        Route::resource('termins', TerminController::class);

        // Data Modul & Mata Kuliah di dalam Modul
        Route::post('moduls/{modul}/duplicate', [ModulController::class, 'duplicate'])->name('moduls.duplicate');
        Route::post('moduls/{modul}/mata-kuliahs/{mata_kuliah}/duplicate', [MataKuliahController::class, 'duplicate'])->name('moduls.mata-kuliahs.duplicate');
        Route::post('moduls/{modul}/mata-kuliahs/bulk-delete', [MataKuliahController::class, 'bulkDelete'])->name('moduls.mata-kuliahs.bulk-delete');
        Route::resource('moduls.mata-kuliahs', MataKuliahController::class);
        Route::resource('moduls', ModulController::class);

        // Data Dosen
        Route::post('dosens/bulk-delete', [DosenController::class, 'bulkDelete'])->name('dosens.bulk-delete');
        Route::resource('dosens', DosenController::class);
    });
});
