<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use App\Models\AlternatifLokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoJsonController extends Controller
{
    public function kelurahan(): JsonResponse
    {
        $kelurahans = Kelurahan::all();
        
        $features = $kelurahans->map(function ($kelurahan) {
            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $kelurahan->id,
                    'nama' => $kelurahan->nama,
                    'kepadatan_penduduk' => $kelurahan->kepadatan_penduduk
                ],
                // Pastikan geojson_boundary adalah valid JSON string di DB
                'geometry' => json_decode($kelurahan->geojson_boundary, true)
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

    public function alternatif(Request $request): JsonResponse
    {
        $query = AlternatifLokasi::with(['jenisUsaha', 'kelurahan']);
        
        if ($request->has('jenis_usaha_id')) {
            $query->where('jenis_usaha_id', $request->jenis_usaha_id);
        }

        if ($request->has('is_kompetitor')) {
            $query->where('adalah_kompetitor', filter_var($request->is_kompetitor, FILTER_VALIDATE_BOOLEAN));
        }

        $alternatifs = $query->get();

        $features = $alternatifs->map(function ($alt) {
            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $alt->id,
                    'nama_lokasi' => $alt->nama_lokasi,
                    'jenis_usaha' => $alt->jenisUsaha->nama ?? null,
                    'kelurahan' => $alt->kelurahan->nama ?? null,
                    'harga_sewa' => $alt->harga_sewa_per_tahun,
                    'skor_keamanan' => $alt->skor_keamanan,
                    'adalah_kompetitor' => $alt->adalah_kompetitor
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $alt->longitude,
                        (float) $alt->latitude
                    ]
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
