<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisUsaha;
use App\Services\AhpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AhpConfigController extends Controller
{
    public function index(Request $request)
    {
        $jenisUsaha = \App\Models\JenisUsaha::getCachedAll();
        $selectedUsahaId = $request->query('jenis_usaha_id', $jenisUsaha->first()->id ?? null);

        $savedMatrix = [];
        if ($selectedUsahaId) {
            $kriteriaIds = DB::table('kriteria')->orderBy('id')->pluck('id')->toArray();
            $dbMatrix = DB::table('matriks_perbandingan')
                ->where('jenis_usaha_id', $selectedUsahaId)
                ->get();

            // Map kriteria_id back to array index (0-3)
            $idToIndex = array_flip($kriteriaIds);

            foreach ($dbMatrix as $row) {
                if (isset($idToIndex[$row->kriteria_a_id]) && isset($idToIndex[$row->kriteria_b_id])) {
                    $i = $idToIndex[$row->kriteria_a_id];
                    $j = $idToIndex[$row->kriteria_b_id];
                    $savedMatrix[$i][$j] = round($row->nilai_saaty, 3);
                }
            }
        }

        return view('admin.ahp_config.index', compact('jenisUsaha', 'selectedUsahaId', 'savedMatrix'));
    }

    public function save(Request $request, AhpService $ahpService)
    {
        $request->validate([
            'jenis_usaha_id' => 'required|exists:jenis_usaha,id',
            'matrix' => 'required|array'
        ]);

        $matrixInput = $request->matrix;
        
        // Reconstruct full 4x4 matrix
        // 0: Sewa, 1: Penduduk, 2: Kompetitor, 3: Keamanan
        $matrix = [
            [1, 1, 1, 1],
            [1, 1, 1, 1],
            [1, 1, 1, 1],
            [1, 1, 1, 1],
        ];

        // Fill upper and lower triangle
        foreach ($matrixInput as $i => $row) {
            foreach ($row as $j => $val) {
                $val = floatval($val);
                $matrix[$i][$j] = $val;
                $matrix[$j][$i] = 1 / $val;
            }
        }

        try {
            $result = $ahpService->calculateWeightsAndConsistency($matrix);

            if (!$result['is_consistent']) {
                return back()->with('error', 'Matriks tidak konsisten (CR = ' . $result['cr'] . ' >= 0.1). Sesuaikan nilai perbandingan.');
            }

            // Save weights to DB
            DB::transaction(function () use ($request, $result, $matrixInput) {
                $jenisUsahaId = $request->jenis_usaha_id;
                
                // Hapus bobot lama untuk jenis usaha ini
                DB::table('bobot_kriteria')->where('jenis_usaha_id', $jenisUsahaId)->delete();
                DB::table('matriks_perbandingan')->where('jenis_usaha_id', $jenisUsahaId)->delete();

                // Ensure a default session exists for admin configs
                $defaultSession = DB::table('sesi_perhitungan')->first();
                if (!$defaultSession) {
                    $sessionId = DB::table('sesi_perhitungan')->insertGetId([
                        'session_id' => 'admin_config_default',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $sessionId = $defaultSession->id;
                }

                // Kita asumsikan ID kriteria di DB berurutan 1,2,3,4. Ambil dari DB.
                $kriteriaIds = DB::table('kriteria')->orderBy('id')->pluck('id')->toArray();
                
                if (count($kriteriaIds) >= 4) {
                    foreach ($result['weights'] as $i => $weight) {
                        DB::table('bobot_kriteria')->insert([
                            'jenis_usaha_id' => $jenisUsahaId,
                            'kriteria_id' => $kriteriaIds[$i],
                            'bobot' => $weight,
                            'consistency_ratio' => $result['cr'],
                            'sesi_id' => $sessionId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    // Simpan matriks perbandingan
                    foreach ($matrixInput as $i => $row) {
                        foreach ($row as $j => $val) {
                            $val = floatval($val);
                            
                            DB::table('matriks_perbandingan')->insert([
                                'sesi_id' => $sessionId,
                                'jenis_usaha_id' => $jenisUsahaId,
                                'kriteria_a_id' => $kriteriaIds[$i],
                                'kriteria_b_id' => $kriteriaIds[$j],
                                'nilai_saaty' => $val,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            // Insert the reciprocal as well
                            DB::table('matriks_perbandingan')->insert([
                                'sesi_id' => $sessionId,
                                'jenis_usaha_id' => $jenisUsahaId,
                                'kriteria_a_id' => $kriteriaIds[$j],
                                'kriteria_b_id' => $kriteriaIds[$i],
                                'nilai_saaty' => 1 / $val,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            });

            return back()->with('success', 'Bobot AHP berhasil disimpan (CR = ' . $result['cr'] . ').');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
