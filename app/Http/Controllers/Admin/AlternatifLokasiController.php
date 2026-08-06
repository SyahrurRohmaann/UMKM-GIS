<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlternatifLokasiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('alternatif_lokasi')
            ->join('jenis_usaha', 'alternatif_lokasi.jenis_usaha_id', '=', 'jenis_usaha.id')
            ->join('kelurahan', 'alternatif_lokasi.kelurahan_id', '=', 'kelurahan.id')
            ->select('alternatif_lokasi.*', 'jenis_usaha.nama as jenis_usaha', 'kelurahan.nama as kelurahan');
            
        if ($request->filled('kelurahan')) {
            $query->where('alternatif_lokasi.kelurahan_id', $request->kelurahan);
        }
        
        if ($request->filled('jenis_usaha')) {
            $query->where('alternatif_lokasi.jenis_usaha_id', $request->jenis_usaha);
        }
        
        if ($request->filled('tipe')) {
            $query->where('alternatif_lokasi.adalah_kompetitor', $request->tipe === 'kompetitor');
        }

        $lokasi = $query->orderBy('id', 'desc')->paginate(10);
        
        $kelurahanList = DB::table('kelurahan')->get();
        $jenisUsahaList = DB::table('jenis_usaha')->get();
            
        return view('admin.alternatif.index', compact('lokasi', 'kelurahanList', 'jenisUsahaList'));
    }

    public function create()
    {
        $jenisUsaha = DB::table('jenis_usaha')->get();
        $kelurahan = DB::table('kelurahan')->get();
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

        DB::table('alternatif_lokasi')->insert($data);
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $lokasi = DB::table('alternatif_lokasi')->find($id);
        if (!$lokasi) abort(404);

        $jenisUsaha = DB::table('jenis_usaha')->get();
        $kelurahan = DB::table('kelurahan')->get();
        
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

        DB::table('alternatif_lokasi')->where('id', $id)->update($data);
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil diupdate');
    }

    public function destroy($id)
    {
        DB::table('alternatif_lokasi')->where('id', $id)->delete();
        return redirect()->route('admin.alternatif.index')->with('success', 'Lokasi berhasil dihapus');
    }
}
