<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Business;
use App\Models\Criterion;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $businesses = Business::all();
        return view('home', compact('businesses'));
    }

    public function map($slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $criteria = Criterion::where('business_id', $business->id)->orderBy('id')->get(['id', 'code', 'name', 'type']);
        $alternatives = Alternative::where('business_id', $business->id)->get(['id', 'name', 'latitude', 'longitude']);

        return view('map.index', compact('business', 'criteria', 'alternatives'));
    }
}
