<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    protected ScoringService $scoringService;

    public function __construct(ScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Endpoint utama untuk Generate Rekomendasi (Menggabungkan AHP & Data Spasial).
     * Request body butuh 'weights' (hasil M4) dan 'jenis_usaha_id'.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id',
            'weights' => 'required|array|size:4',
            'weights.*' => 'required|numeric',
            'custom_locations' => 'nullable|array' // array titik tambahan
        ]);

        $jenisUsahaId = $request->jenis_usaha_id;
        $weights = $request->weights;
        $customLocs = $request->custom_locations ?? [];

        // Ambil semua kandidat lokasi dari DB
        $kandidat = DB::table('alternatif_lokasi')
            ->join('kelurahan', 'alternatif_lokasi.kelurahan_id', '=', 'kelurahan.id')
            ->where('jenis_usaha_id', $jenisUsahaId)
            ->where('adalah_kompetitor', false)
            ->select(
                'alternatif_lokasi.id',
                'alternatif_lokasi.nama_lokasi as nama',
                'alternatif_lokasi.latitude',
                'alternatif_lokasi.longitude',
                'alternatif_lokasi.harga_sewa_per_tahun as nilai_sewa',
                'alternatif_lokasi.skor_keamanan as nilai_keamanan',
                'kelurahan.kepadatan_penduduk as nilai_penduduk'
            )
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        // Hitung jarak kompetitor untuk kandidat DB
        foreach ($kandidat as &$lokasi) {
            $lokasi['nilai_kompetitor'] = $this->countCompetitors($jenisUsahaId, $lokasi['latitude'], $lokasi['longitude']);
        }

        // Gabungkan dengan titik custom (karena skor butuh nilai absolut + titik custom)
        foreach ($customLocs as $cl) {
            $kandidat[] = [
                'id' => $cl['id'],
                'nama' => $cl['nama'],
                'latitude' => $cl['latitude'],
                'longitude' => $cl['longitude'],
                'nilai_sewa' => $cl['nilai_sewa'],
                'nilai_keamanan' => $cl['nilai_keamanan'],
                'nilai_kompetitor' => $cl['nilai_kompetitor'],
                'nilai_penduduk' => $cl['nilai_penduduk'],
                'is_custom' => true
            ];
        }

        // Kalkulasi skor akhir AHP & Ranking
        $hasil = $this->scoringService->calculateFinalScores($kandidat, $weights);

        return response()->json([
            'success' => true,
            'data' => $hasil
        ]);
    }

    private function countCompetitors($jenisUsahaId, $lat, $lng, $radiusKm = 0.5) {
        $sqlKomp = "
            SELECT COUNT(*) as jml
            FROM alternatif_lokasi
            WHERE jenis_usaha_id = ? AND adalah_kompetitor = 1
            AND ( 6371 * acos( cos( radians(?) ) *
              cos( radians( latitude ) ) *
              cos( radians( longitude ) - radians(?) ) +
              sin( radians(?) ) *
              sin( radians( latitude ) ) )
            ) < ?
        ";
        return DB::selectOne($sqlKomp, [$jenisUsahaId, $lat, $lng, $lat, $radiusKm])->jml;
    }

    /**
     * Endpoint untuk titik kustom (Shift + Klik).
     * Hitung nilai penduduk & kompetitor spatial, lalu kembalikan data atribut (bukan skor akhir).
     */
    public function simulateCustomLocationScore(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'jenis_usaha_id' => 'required|integer|exists:jenis_usaha,id',
            'harga_sewa' => 'required|numeric',
            'skor_keamanan' => 'required|numeric|min:1|max:4'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $jenisUsahaId = $request->jenis_usaha_id;

        $kompetitor = $this->countCompetitors($jenisUsahaId, $lat, $lng);

        $sqlPenduduk = "
            SELECT kepadatan_penduduk
            FROM alternatif_lokasi
            JOIN kelurahan ON alternatif_lokasi.kelurahan_id = kelurahan.id
            ORDER BY ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) ASC
            LIMIT 1
        ";
        $penduduk = DB::selectOne($sqlPenduduk, [$lat, $lng, $lat])->kepadatan_penduduk ?? 5000;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => 'custom_' . time(),
                'nama' => $request->nama_lokasi ?? 'Lokasi Kustom',
                'latitude' => $lat,
                'longitude' => $lng,
                'nilai_sewa' => $request->harga_sewa,
                'nilai_keamanan' => $request->skor_keamanan,
                'nilai_kompetitor' => $kompetitor,
                'nilai_penduduk' => $penduduk
            ]
        ]);
    }
}
