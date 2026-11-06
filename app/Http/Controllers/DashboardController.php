<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan beranda dashboard admin kedokteran (Kalender Termin).
     */
    public function index(Request $request)
    {
        return app(JadwalController::class)->index($request);
    }
}
