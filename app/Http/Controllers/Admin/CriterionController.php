<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Criterion;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    public function create(Business $business)
    {
        return view('admin.criteria.create', compact('business'));
    }

    public function store(Request $request, Business $business)
    {
        $request->validate([
            'code' => 'required|string|unique:criteria,code',
            'name' => 'required|string',
            'type' => 'required|in:benefit,cost'
        ]);

        $business->criteria()->create($request->only('code', 'name', 'type'));
        return redirect()->route('admin.businesses.show', $business)->with('success', 'Kriteria ditambahkan.');
    }

    public function edit(Criterion $criterion)
    {
        return view('admin.criteria.edit', compact('criterion'));
    }

    public function update(Request $request, Criterion $criterion)
    {
        $request->validate([
            'code' => 'required|string|unique:criteria,code,' . $criterion->id,
            'name' => 'required|string',
            'type' => 'required|in:benefit,cost'
        ]);

        $criterion->update($request->only('code', 'name', 'type'));
        return redirect()->route('admin.businesses.show', $criterion->business_id)->with('success', 'Kriteria diperbarui.');
    }

    public function destroy(Criterion $criterion)
    {
        $businessId = $criterion->business_id;
        $criterion->delete();
        return redirect()->route('admin.businesses.show', $businessId)->with('success', 'Kriteria dihapus.');
    }
}
