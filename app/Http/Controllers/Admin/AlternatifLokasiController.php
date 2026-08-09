<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlternatifLokasiController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\AlternatifLokasi::with(['jenisUsaha:id,nama', 'kelurahan:id,nama']);
            
        if ($request->filled('kelurahan')) {
            $query->where('kelurahan_id', $request->kelurahan);
        }
        
        if ($request->filled('jenis_usaha')) {
            $query->where('jenis_usaha_id', $request->jenis_usaha);
        }
        
        if ($request->filled('tipe')) {
            $query->where('adalah_kompetitor', $request->tipe === 'kompetitor');
        }

        $lokasi = $query->orderBy('id', 'desc')->paginate(10);
        
        $kelurahanList = \App\Models\Kelurahan::getCachedAll();
        $jenisUsahaList = \App\Models\JenisUsaha::getCachedAll();
            
        return view('admin.alternatif.index', compact('lokasi', 'kelurahanList', 'jenisUsahaList'));
    }

    public function create()
    {
        $jenisUsaha = \App\Models\JenisUsaha::getCachedAll();
        $kelurahan = \App\Models\Kelurahan::getCachedAll();
        return view('admin.alternatif.create', compact('jenisUsaha', 'kelurahan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id',
            'kelurahan_id' => 'required|integer|exists:kelurahan,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'harga_sewa_per_tahun' => 'required|numeric|min:0',
            'skor_keamanan' => 'required|integer|min:0|max:4',
            'adalah_kompetitor' => 'required|boolean',
        ]);

        \App\Models\AlternatifLokasi::create($data);
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $lokasi = \App\Models\AlternatifLokasi::findOrFail($id);
        if (!$lokasi) abort(404);

        $jenisUsaha = \App\Models\JenisUsaha::getCachedAll();
        $kelurahan = \App\Models\Kelurahan::getCachedAll();
        
        return view('admin.alternatif.edit', compact('lokasi', 'jenisUsaha', 'kelurahan'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id',
            'kelurahan_id' => 'required|integer|exists:kelurahan,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'harga_sewa_per_tahun' => 'required|numeric|min:0',
            'skor_keamanan' => 'required|integer|min:0|max:4',
            'adalah_kompetitor' => 'required|boolean',
        ]);

        \App\Models\AlternatifLokasi::where('id', $id)->update($data);
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil diupdate');
    }

    public function destroy($id)
    {
        \App\Models\AlternatifLokasi::where('id', $id)->delete();
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil dihapus');
    }
}
