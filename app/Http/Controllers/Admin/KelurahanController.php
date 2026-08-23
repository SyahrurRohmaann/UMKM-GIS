<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use Illuminate\Http\Request;

class KelurahanController extends Controller
{
    public function index()
    {
        $kelurahan = Kelurahan::orderBy('nama')->paginate(10);
        return view('admin.kelurahan.index', compact('kelurahan'));
    }

    public function create()
    {
        return view('admin.kelurahan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kepadatan_penduduk' => 'required|numeric|min:0',
        ]);

        Kelurahan::create($data);
        Kelurahan::forgetCache();

        return redirect()->route('admin.kelurahan.index')->with('success', 'Kelurahan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kelurahan = Kelurahan::findOrFail($id);
        return view('admin.kelurahan.edit', compact('kelurahan'));
    }

    public function update(Request $request, $id)
    {
        $kelurahan = Kelurahan::findOrFail($id);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kepadatan_penduduk' => 'required|numeric|min:0',
        ]);

        $kelurahan->update($data);
        Kelurahan::forgetCache();

        return redirect()->route('admin.kelurahan.index')->with('success', 'Kelurahan berhasil diupdate');
    }

    public function destroy($id)
    {
        Kelurahan::where('id', $id)->delete();
        Kelurahan::forgetCache();

        return redirect()->route('admin.kelurahan.index')->with('success', 'Kelurahan berhasil dihapus');
    }
}
