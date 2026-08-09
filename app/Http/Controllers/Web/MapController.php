<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JenisUsaha;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $jenisUsaha = JenisUsaha::getCachedAll();
        return view('map.index', compact('jenisUsaha'));
    }
}
