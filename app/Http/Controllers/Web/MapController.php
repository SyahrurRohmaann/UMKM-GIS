<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function index()
    {
        $jenisUsaha = DB::table('jenis_usaha')->get();
        return view('map.index', compact('jenisUsaha'));
    }
}
