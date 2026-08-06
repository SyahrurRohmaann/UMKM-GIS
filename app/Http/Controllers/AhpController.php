<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Services\AhpService;
use Illuminate\Http\Request;

class AhpController extends Controller
{
    protected AhpService $ahpService;

    public function __construct(AhpService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    public function calculate(Request $request)
    {
        $matrix = $request->input('matrix', []);
        $businessId = $request->input('business_id');
        
        if (empty($matrix) || !$businessId) {
            return response()->json(['error' => 'Matrix or business_id is missing'], 400);
        }

        // 1. Hitung bobot kriteria
        $ahpResult = $this->ahpService->calculateWeights($matrix);
        $weights = $ahpResult['weights'];

        // 2. Ambil data kriteria dan skor alternatif untuk business tersebut
        $criteria = Criterion::where('business_id', $businessId)->get()->keyBy('id');
        $criteriaTypes = [];
        foreach ($criteria as $id => $c) {
            $criteriaTypes[$id] = $c->type;
        }

        $alternatives = Alternative::where('business_id', $businessId)->with('scores')->get();
        $alternativeScores = [];
        foreach ($alternatives as $alt) {
            foreach ($alt->scores as $score) {
                // Ensure only criteria that still exist are processed
                if(isset($criteriaTypes[$score->criterion_id])) {
                     $alternativeScores[$alt->id][$score->criterion_id] = (float) $score->score;
                }
            }
        }

        // 3. Hitung ranking (Top 3)
        $rankedIds = $this->ahpService->rankAlternatives($alternativeScores, $weights, $criteriaTypes);
        
        $results = [];
        $limit = 3;
        $count = 0;
        foreach ($rankedIds as $altId => $score) {
            if ($count >= $limit) break;
            $alt = $alternatives->firstWhere('id', $altId);
            $results[] = [
                'id' => $alt->id,
                'name' => $alt->name,
                'latitude' => $alt->latitude,
                'longitude' => $alt->longitude,
                'score' => $score
            ];
            $count++;
        }

        return response()->json([
            'meta' => [
                'is_consistent' => $ahpResult['is_consistent'],
                'cr' => $ahpResult['cr'],
                'weights' => $weights
            ],
            'results' => $results
        ]);
    }
}
