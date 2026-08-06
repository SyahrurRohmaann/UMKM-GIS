<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    // Test hit integrasi tanpa RefreshDatabase memori, pakai DB asli seeder spk_lokasi
    public function test_recommendation_generate_endpoint_returns_ranked_locations(): void
    {
        // ...
        // Jenis usaha Laundry = 1
        $weights = [0.1608, 0.4661, 0.0958, 0.2773];

        $response = $this->postJson('/api/recommendations/generate', [
            'jenis_usaha_id' => 1,
            'weights' => $weights
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        // Cek struktur data balikan
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        
        $firstRank = $data[0];
        $this->assertArrayHasKey('id', $firstRank);
        $this->assertArrayHasKey('nama', $firstRank);
        $this->assertArrayHasKey('skor_akhir', $firstRank);
        $this->assertArrayHasKey('ranking', $firstRank);
        $this->assertArrayHasKey('nilai_kompetitor', $firstRank);
        
        // Assert ranking terurut (Ranking 1 di awal)
        $this->assertEquals(1, $firstRank['ranking']);
        
        if (count($data) > 1) {
            $this->assertGreaterThanOrEqual($data[1]['skor_akhir'], $firstRank['skor_akhir']);
        }
    }
}
