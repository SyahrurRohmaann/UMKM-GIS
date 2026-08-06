<?php

namespace Tests\Unit;

use App\Services\ScoringService;
use PHPUnit\Framework\TestCase;

class ScoringServiceTest extends TestCase
{
    public function test_calculate_final_scores_correctly_ranks_alternatives(): void
    {
        $service = new ScoringService();
        
        // Bobot dari test AHP sebelumnya
        $weights = [0.1608, 0.4661, 0.0958, 0.2773];

        // Dummy alternatif
        $alternatives = [
            [
                'id' => 1, 'nama' => 'Lokasi A',
                'nilai_sewa' => 10000000, // min (terbaik utk cost)
                'nilai_penduduk' => 5000,
                'nilai_kompetitor' => 2,
                'nilai_keamanan' => 3,
            ],
            [
                'id' => 2, 'nama' => 'Lokasi B',
                'nilai_sewa' => 20000000, 
                'nilai_penduduk' => 10000, // max (terbaik utk benefit)
                'nilai_kompetitor' => 5, // max (terburuk utk cost)
                'nilai_keamanan' => 4, // max (terbaik utk benefit)
            ],
            [
                'id' => 3, 'nama' => 'Lokasi C',
                'nilai_sewa' => 15000000,
                'nilai_penduduk' => 7500,
                'nilai_kompetitor' => 0, // min (terbaik utk cost), special case 0
                'nilai_keamanan' => 2,
            ]
        ];

        $results = $service->calculateFinalScores($alternatives, $weights);

        // Assert sudah ada skor akhir dan ranking
        $this->assertArrayHasKey('skor_akhir', $results[0]);
        $this->assertArrayHasKey('ranking', $results[0]);
        
        // Assert terurut (skor index 0 > index 1)
        $this->assertGreaterThan($results[1]['skor_akhir'], $results[0]['skor_akhir']);
        
        // Assert ranking berjalan
        $this->assertEquals(1, $results[0]['ranking']);
        $this->assertEquals(2, $results[1]['ranking']);
        $this->assertEquals(3, $results[2]['ranking']);

        // Assert Lokasi B skornya make sense (seharusnya menang di bobot Penduduk 46% & Keamanan 27%)
        // Lok A: Sewa (1) * 0.16 + Pen (0.5)*0.46 + Komp (0)*0.09 + Keam (0.75)*0.27
        // Lok B: Sewa (0.5) * 0.16 + Pen (1)*0.46 + Komp (0)*0.09 + Keam (1)*0.27  -> Dominan
        $this->assertEquals('Lokasi B', $results[0]['nama']);
    }
}
