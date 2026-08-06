<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Alternative;
use App\Models\Criterion;
use Illuminate\Http\Request;

class AlternativeController extends Controller
{
    public function create(Business $business)
    {
        return view('admin.alternatives.create', compact('business'));
    }

    public function store(Request $request, Business $business)
    {
        $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'scores' => 'array'
        ]);

        $alt = $business->alternatives()->create($request->only('name', 'latitude', 'longitude'));
        
        if ($request->has('scores')) {
            foreach ($request->scores as $critId => $val) {
                $alt->scores()->create([
                    'criterion_id' => $critId,
                    'score' => $val
                ]);
            }
        }

        return redirect()->route('admin.businesses.show', $business)->with('success', 'Lokasi ditambahkan.');
    }

    public function edit(Alternative $alternative)
    {
        $business = $alternative->business;
        $criteria = $business->criteria;
        $alternative->load('scores');
        
        $scoresMap = $alternative->scores->pluck('score', 'criterion_id')->toArray();
        
        return view('admin.alternatives.edit', compact('alternative', 'business', 'criteria', 'scoresMap'));
    }

    public function update(Request $request, Alternative $alternative)
    {
        $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'scores' => 'array'
        ]);

        $alternative->update($request->only('name', 'latitude', 'longitude'));
        
        $alternative->scores()->delete(); // reset
        if ($request->has('scores')) {
            foreach ($request->scores as $critId => $val) {
                $alternative->scores()->create([
                    'criterion_id' => $critId,
                    'score' => $val
                ]);
            }
        }

        return redirect()->route('admin.businesses.show', $alternative->business_id)->with('success', 'Lokasi diperbarui.');
    }

    public function destroy(Alternative $alternative)
    {
        $businessId = $alternative->business_id;
        $alternative->delete();
        return redirect()->route('admin.businesses.show', $businessId)->with('success', 'Lokasi dihapus.');
    }
}
