<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::withCount(['criteria', 'alternatives'])->get();
        return view('admin.businesses.index', compact('businesses'));
    }

    public function create()
    {
        return view('admin.businesses.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Business::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time()
        ]);
        return redirect()->route('admin.businesses.index')->with('success', 'Jenis Usaha ditambahkan.');
    }

    public function show(Business $business)
    {
        $business->load(['criteria', 'alternatives']);
        return view('admin.businesses.show', compact('business'));
    }

    public function edit(Business $business)
    {
        return view('admin.businesses.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $business->update(['name' => $request->name]);
        return redirect()->route('admin.businesses.index')->with('success', 'Jenis Usaha diperbarui.');
    }

    public function destroy(Business $business)
    {
        $business->delete();
        return redirect()->route('admin.businesses.index')->with('success', 'Jenis Usaha dihapus.');
    }
}
