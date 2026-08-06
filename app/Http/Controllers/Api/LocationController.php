<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Ambil alternatif lokasi untuk ditampilkan di peta (berdasarkan jenis usaha).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id'
        ]);

        $lokasi = DB::table('alternatif_lokasi')
            ->join('kelurahan', 'alternatif_lokasi.kelurahan_id', '=', 'kelurahan.id')
            ->where('jenis_usaha_id', $request->jenis_usaha_id)
            ->where('adalah_kompetitor', false)
            ->select(
                'alternatif_lokasi.*',
                'kelurahan.nama as nama_kelurahan',
                'kelurahan.kepadatan_penduduk'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lokasi
        ]);
    }

    /**
     * Analisis Spasial: Buffer Zone (Radius) - M6.
     * Menghitung jumlah kompetitor sejenis dalam radius (meter) dari titik tengah (lat/lng).
     * Menggunakan rumus Haversine.
     */
    public function competitorsWithinRadius(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius_meter' => 'required|integer|min:50',
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        // Konversi meter ke kilometer karena radius bumi dalam km (6371)
        $radiusKm = $request->radius_meter / 1000;

        // Haversine Formula untuk query spatial murni di database
        $sql = "
            SELECT id, nama_lokasi, latitude, longitude,
            ( 6371 * acos( cos( radians(?) ) *
              cos( radians( latitude ) ) *
              cos( radians( longitude ) - radians(?) ) +
              sin( radians(?) ) *
              sin( radians( latitude ) ) )
            ) AS distance
            FROM alternatif_lokasi
            WHERE jenis_usaha_id = ? AND adalah_kompetitor = 1
            HAVING distance < ?
            ORDER BY distance;
        ";

        $competitors = DB::select($sql, [$lat, $lng, $lat, $request->jenis_usaha_id, $radiusKm]);

        return response()->json([
            'success' => true,
            'count' => count($competitors),
            'radius_meter' => $request->radius_meter,
            'data' => $competitors
        ]);
    }
}
