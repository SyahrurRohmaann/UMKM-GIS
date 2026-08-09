<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendation_generate_endpoint_returns_ranked_locations(): void
    {
        // ...
        // Jenis usaha Laundry = 1
        $this->artisan('db:seed');

        $response = $this->postJson('/api/recommendations/generate', [
            'jenis_usaha_id' => 1
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        // Cek struktur data balikan
        $data = $response->json('data');
        if (!empty($data)) {
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
}
