<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AhpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AhpController extends Controller
{
    protected AhpService $ahpService;

    public function __construct(AhpService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    /**
     * Hitung bobot dan CR dari input matriks AHP pengguna.
     * Menerima array 2D nxn (kriteria 4x4)
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'matrix' => 'required|array|min:3',
            'matrix.*' => 'required|array',
        ]);

        try {
            $result = $this->ahpService->calculateWeightsAndConsistency($request->matrix);
            
            if (!$result['is_consistent']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Input perbandingan tidak konsisten (CR >= 0.1). Silakan sesuaikan ulang nilai perbandingan Anda.',
                    'data' => $result
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Perhitungan AHP konsisten dan berhasil disimpan.',
                'data' => $result
            ]);
            
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
