<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Modul;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Tampilkan beranda dashboard admin kedokteran.
     */
    public function index()
    {
        $activeSemester = Setting::getActiveSemester();

        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();

        $totalDosens = Dosen::count();
        $totalModuls = Modul::where('tipe_semester', $activeSemester)->count();
        $totalModulsAll = Modul::count();
        $totalJadwals = Jadwal::whereHas('modul', function ($q) use ($activeSemester) {
            $q->where('tipe_semester', $activeSemester);
        })->count();
        $totalJadwalsAll = Jadwal::count();

        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        $upcomingJadwals = Jadwal::with('modul', 'dosen')
            ->whereHas('modul', function ($q) use ($activeSemester) {
                $q->where('tipe_semester', $activeSemester);
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->take(6)
            ->get();

        $rolesSummary = Role::withCount('users', 'permissions')
            ->get();

        return view('dashboard', compact(
            'activeSemester',
            'totalUsers',
            'totalRoles',
            'totalPermissions',
            'totalDosens',
            'totalModuls',
            'totalModulsAll',
            'totalJadwals',
            'totalJadwalsAll',
            'recentUsers',
            'upcomingJadwals',
            'rolesSummary'
        ));
    }
}
