<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $countLokasi = DB::table('alternatif_lokasi')->where('adalah_kompetitor', false)->count();
        $countKompetitor = DB::table('alternatif_lokasi')->where('adalah_kompetitor', true)->count();
        $countKelurahan = DB::table('kelurahan')->count();

        return view('admin.dashboard', compact('countLokasi', 'countKompetitor', 'countKelurahan'));
    }
}
