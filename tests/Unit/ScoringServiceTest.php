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

    /**
     * Regresi BUG-1: bila SEMUA alternatif punya nilai identik pada satu kriteria
     * (range = 0), kriteria itu tidak boleh membuat satu lokasi diuntungkan.
     * Sebelum fix, logika rasio memberi 1.0 ke satu lokasi & <1 ke lainnya.
     */
    public function test_identical_criterion_values_do_not_bias_ranking(): void
    {
        $service = new ScoringService();
        $weights = [0.25, 0.25, 0.25, 0.25];

        // Sewa & keamanan IDENTIK di semua lokasi; pembeda hanya penduduk.
        $alternatives = [
            ['id' => 1, 'nama' => 'A', 'nilai_sewa' => 15000000, 'nilai_penduduk' => 5000,  'nilai_kompetitor' => 2, 'nilai_keamanan' => 3],
            ['id' => 2, 'nama' => 'B', 'nilai_sewa' => 15000000, 'nilai_penduduk' => 9000,  'nilai_kompetitor' => 2, 'nilai_keamanan' => 3],
        ];

        $results = $service->calculateFinalScores($alternatives, $weights);
        $byName = collect($results)->keyBy('nama');

        // Kriteria identik (sewa, kompetitor, keamanan) memberi kontribusi setara (1.0 * bobot).
        // Selisih skor HARUS murni dari penduduk: B(1.0) vs A(0.0) pada bobot 0.25.
        $this->assertEqualsWithDelta(0.25, $byName['B']['skor_akhir'] - $byName['A']['skor_akhir'], 0.0001);
        $this->assertEquals('B', $results[0]['nama']);
    }

    /**
     * Regresi BUG-2: kompetitor = 0 di SEMUA lokasi (belum ada saingan).
     * Kriteria kompetitor (cost) harus netral penuh, bukan 0, sehingga
     * ranking ditentukan kriteria lain — bukan malah menghilang.
     */
    public function test_zero_competitor_everywhere_is_neutral_not_zero(): void
    {
        $service = new ScoringService();
        $weights = [0.1608, 0.4661, 0.0958, 0.2773];

        $alternatives = [
            ['id' => 1, 'nama' => 'A', 'nilai_sewa' => 10000000, 'nilai_penduduk' => 8500, 'nilai_kompetitor' => 0, 'nilai_keamanan' => 3],
            ['id' => 2, 'nama' => 'B', 'nilai_sewa' => 12000000, 'nilai_penduduk' => 9200, 'nilai_kompetitor' => 0, 'nilai_keamanan' => 4],
        ];

        $results = $service->calculateFinalScores($alternatives, $weights);
        $byName = collect($results)->keyBy('nama');

        // Kompetitor identik(0) → kontribusinya setara di kedua lokasi (1.0 * 0.0958).
        // Selisih skor = kontribusi sewa + penduduk + keamanan saja.
        // A menang sewa (lebih murah), B menang penduduk & keamanan → B unggul.
        $expectedDiff = (0.4661 * 1.0) + (0.2773 * 1.0) - (0.1608 * 1.0); // B - A
        $this->assertEqualsWithDelta($expectedDiff, $byName['B']['skor_akhir'] - $byName['A']['skor_akhir'], 0.0001);
        $this->assertEquals('B', $results[0]['nama']);
    }
}
