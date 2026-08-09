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
            'custom_locations' => 'nullable|array', // array titik tambahan
            'selected_ids' => 'nullable|array' // ID lokasi DB yang dipilih manual
        ]);

        $jenisUsahaId = $request->jenis_usaha_id;
        $customLocs = $request->custom_locations ?? [];
        $selectedIds = $request->selected_ids;

        // Ambil bobot kriteria dari DB berdasarkan jenis usaha
        // Asumsi urutan kriteria (berdasarkan ScoringService::CRITERIA_TYPES):
        // 0 => Sewa, 1 => Penduduk, 2 => Kompetitor, 3 => Keamanan
        $bobotDb = DB::table('bobot_kriteria')
            ->where('jenis_usaha_id', $jenisUsahaId)
            ->orderBy('kriteria_id')
            ->pluck('bobot')
            ->toArray();

        // Fallback jika belum ada di DB
        if (count($bobotDb) !== 4) {
            // Hardcode MVP fallback weights
            $weights = [0.1834, 0.4905, 0.0898, 0.2363];
        } else {
            $weights = array_map('floatval', $bobotDb);
        }

        // Ambil kandidat lokasi dari DB
        $query = DB::table('alternatif_lokasi')
            ->join('kelurahan', 'alternatif_lokasi.kelurahan_id', '=', 'kelurahan.id')
            ->where('jenis_usaha_id', $jenisUsahaId)
            ->where('adalah_kompetitor', false);
            
        if ($selectedIds && count($selectedIds) > 0) {
            $query->whereIn('alternatif_lokasi.id', $selectedIds);
        }

        $kandidat = $query->select(
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

        // Hitung jarak kompetitor untuk kandidat DB (Bulk PHP Calculation to save DB queries)
        $competitorCounts = $this->getBulkCompetitorCounts($jenisUsahaId, $kandidat);
        foreach ($kandidat as &$lokasi) {
            $lokasi['nilai_kompetitor'] = $competitorCounts[$lokasi['id']] ?? 0;
        }

        // Gabungkan dengan titik custom
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
            'weights_used' => $weights,
            'data' => $hasil
        ]);
    }

    private function countCompetitors($jenisUsahaId, $lat, $lng, $radiusKm = 0.5) {
        if (DB::getDriverName() === 'sqlite') {
            // Fallback for SQLite in tests since it lacks math functions (acos, cos, sin, radians)
            return DB::table('alternatif_lokasi')
                ->where('jenis_usaha_id', $jenisUsahaId)
                ->where('adalah_kompetitor', 1)
                ->count();
        }

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

    private function getBulkCompetitorCounts($jenisUsahaId, $kandidat, $radiusKm = 0.5) {
        if (empty($kandidat)) return [];
        
        if (DB::getDriverName() === 'sqlite') {
            // Fallback for tests
            $kompetitorCount = DB::table('alternatif_lokasi')
                ->where('jenis_usaha_id', $jenisUsahaId)
                ->where('adalah_kompetitor', 1)
                ->count();
            return array_fill_keys(array_column($kandidat, 'id'), $kompetitorCount);
        }

        // Fetch all competitors for this business type once
        $competitors = DB::table('alternatif_lokasi')
            ->where('jenis_usaha_id', $jenisUsahaId)
            ->where('adalah_kompetitor', 1)
            ->select('id', 'latitude', 'longitude')
            ->get();

        $counts = [];
        foreach ($kandidat as $k) {
            $count = 0;
            $kLat = $k['latitude'];
            $kLng = $k['longitude'];
            
            // PHP Haversine calculation avoids O(N*M) database queries
            foreach ($competitors as $c) {
                $cLat = $c->latitude;
                $cLng = $c->longitude;
                
                $latDelta = deg2rad($cLat - $kLat);
                $lonDelta = deg2rad($cLng - $kLng);
                
                $a = sin($latDelta / 2) * sin($latDelta / 2) +
                     cos(deg2rad($kLat)) * cos(deg2rad($cLat)) *
                     sin($lonDelta / 2) * sin($lonDelta / 2);
                $cVal = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = 6371 * $cVal;
                
                if ($distance < $radiusKm) {
                    $count++;
                }
            }
            if (isset($k['id'])) {
                $counts[$k['id']] = $count;
            }
        }
        return $counts;
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
