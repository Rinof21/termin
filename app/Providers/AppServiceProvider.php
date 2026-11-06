<?php

namespace App\Providers;

use App\Models\Semester;
use App\Models\Setting;
use App\Models\Termin;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set locale bahasa Indonesia untuk tanggal dan waktu Carbon
        \Carbon\Carbon::setLocale('id');
        app()->setLocale('id');

        // Super Admin secara otomatis memiliki seluruh izin akses Spatie (bypass)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Bagikan variabel semester & termin aktif secara global ke seluruh tampilan Blade
        View::composer('*', function ($view) {
            $activeSemester = 'Genap';
            $activeTahunSemester = '2026.2';
            $activeTermin = null;
            $allTermins = collect();
            $allSemesters = collect();
            $availableTahunSemesters = ['2026.2'];

            try {
                if (Schema::hasTable('settings')) {
                    $activeSemester = Setting::getActiveSemester();
                    $activeTahunSemester = Setting::getActiveTahunSemester();
                }
                if (Schema::hasTable('semesters')) {
                    $allSemesters = Semester::orderBy('kode')->get();
                    if ($allSemesters->isNotEmpty()) {
                        $availableTahunSemesters = $allSemesters->pluck('kode')->toArray();
                    }
                }
                if (Schema::hasTable('termins')) {
                    $activeTermin = Termin::getActiveTermin();
                    $allTermins = Termin::orderBy('nomor')->get();
                }
            } catch (\Throwable $e) {
                // Abaikan jika migrasi belum selesai berjalan
            }

            $view->with([
                'activeSemester' => $activeSemester,
                'activeTahunSemester' => $activeTahunSemester,
                'availableTahunSemesters' => $availableTahunSemesters,
                'allSemesters' => $allSemesters,
                'activeTermin' => $activeTermin,
                'allTermins' => $allTermins,
            ]);
        });
    }
}
