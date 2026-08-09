<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLokasi;
use App\Models\BobotKriteria;
use App\Models\HasilPerankingan;
use App\Models\JenisUsaha;
use App\Models\Kriteria;
use App\Models\MatriksPerbandingan;
use App\Models\SesiPerhitungan;
use App\Services\AhpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CalculationController extends Controller
{
    protected AhpService $ahpService;

    public function __construct(AhpService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'jenis_usaha_id' => 'required|exists:jenis_usaha,id',
            'matrix' => 'required|array|min:3',
            'matrix.*' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $result = $this->ahpService->calculateWeightsAndConsistency($request->matrix);
            
            if (!$result['is_consistent']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Input perbandingan tidak konsisten (CR >= 0.1). Silakan sesuaikan ulang.',
                    'data' => $result
                ], 422);
            }

            // 1. Buat Sesi Perhitungan
            $sesi = SesiPerhitungan::create([
                'session_id' => Str::uuid(),
            ]);

            // 2. Simpan Bobot Kriteria
            // Asumsi matriks urutannya sama dengan ID Kriteria
            $kriterias = Kriteria::getCachedAll();
            $dataKriteria = [];
            foreach ($kriterias as $index => $kriteria) {
                BobotKriteria::create([
                    'sesi_id' => $sesi->id,
                    'kriteria_id' => $kriteria->id,
                    'bobot' => $result['weights'][$index],
                    'consistency_ratio' => $result['cr'],
                ]);
                
                // Siapkan data untuk hitung skor akhir
                // Harga Sewa & Kompetitor = Cost (false), Kepadatan & Keamanan = Benefit (true)
                $isBenefit = !in_array($kriteria->nama, ['Harga Sewa Lahan/Bangunan', 'Kedekatan dengan Kompetitor Sejenis']);
                $dataKriteria[$kriteria->id] = [
                    'bobot' => $result['weights'][$index],
                    'is_benefit' => $isBenefit
                ];
            }

            // 3. Ambil data alternatif dan kompetitor sesuai jenis usaha
            $alternatifs = AlternatifLokasi::where('jenis_usaha_id', $request->jenis_usaha_id)
                                          ->where('adalah_kompetitor', false)
                                          ->with('kelurahan:id,nama,kepadatan_penduduk')
                                          ->select('id', 'kelurahan_id', 'latitude', 'longitude', 'harga_sewa_per_tahun', 'skor_keamanan')
                                          ->get();
            
            $kompetitors = AlternatifLokasi::where('jenis_usaha_id', $request->jenis_usaha_id)
                                          ->where('adalah_kompetitor', true)
                                          ->select('latitude', 'longitude')
                                          ->get();
            
            if ($alternatifs->count() > 0) {
                // Siapkan data alternatif untuk AhpService
                $dataAlternatif = [];
                
                $idHarga = $kriterias->where('nama', 'Harga Sewa Lahan/Bangunan')->first()->id;
                $idKepadatan = $kriterias->where('nama', 'Kepadatan Penduduk')->first()->id;
                $idKompetitor = $kriterias->where('nama', 'Kedekatan dengan Kompetitor Sejenis')->first()->id;
                $idKeamanan = $kriterias->where('nama', 'Tingkat Keamanan Lingkungan')->first()->id;

                foreach ($alternatifs as $alt) {
                    $jumlahKompetitor = $this->ahpService->hitungJumlahKompetitor($alt->latitude, $alt->longitude, $kompetitors, 1.0); // radius 1km
                    
                    $dataAlternatif[$alt->id] = [
                        $idHarga => (float) $alt->harga_sewa_per_tahun,
                        $idKepadatan => (float) ($alt->kelurahan->kepadatan_penduduk ?? 1),
                        $idKompetitor => (float) $jumlahKompetitor,
                        $idKeamanan => (float) $alt->skor_keamanan,
                    ];
                }

                // Hitung skor akhir
                $skorAkhir = $this->ahpService->hitungSkorAkhir($dataKriteria, $dataAlternatif);

                // 4. Simpan Hasil Perankingan
                $ranking = 1;
                foreach ($skorAkhir as $idAlternatif => $skor) {
                    HasilPerankingan::create([
                        'sesi_id' => $sesi->id,
                        'alternatif_lokasi_id' => $idAlternatif,
                        'skor_akhir' => $skor,
                        'ranking' => $ranking++
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perhitungan AHP selesai.',
                'data' => [
                    'sesi_id' => $sesi->id,
                    'session_id' => $sesi->session_id,
                    'ahp_result' => $result
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
