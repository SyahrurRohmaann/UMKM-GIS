<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\JenisUsaha;

class AhpApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create required JenisUsaha for testing
        JenisUsaha::factory()->create(['id' => 1, 'nama' => 'Laundry']);
        JenisUsaha::factory()->create(['id' => 2, 'nama' => 'Kafe']);
    }

    public function test_ahp_calculate_endpoint_validates_consistent_matrix(): void
    {
        // Matrix input based on PRD-03 section 3.3
        $matrix = [
            [1,   1/3, 2,   1/2],
            [3,   1,   4,   2  ],
            [1/2, 1/4, 1,   1/3],
            [2,   1/2, 3,   1  ],
        ];

        $response = $this->postJson('/api/ahp/calculate', [
            'jenis_usaha_id' => 1,
            'matrix' => $matrix
        ]);

        $response->assertStatus(200);
        
        $response->assertJsonFragment([
            'success' => true
        ]);

        // Check if lambda_max is there
        $this->assertArrayHasKey('lambda_max', $response->json('data.ahp_result'));
        $this->assertArrayHasKey('cr', $response->json('data.ahp_result'));
        $this->assertTrue($response->json('data.ahp_result.is_consistent'));
    }

    public function test_ahp_calculate_endpoint_rejects_inconsistent_matrix(): void
    {
        $matrix = [
            [1, 9, 1/9, 1],
            [1/9, 1, 9, 1],
            [9, 1/9, 1, 1],
            [1, 1, 1, 1],
        ];

        $response = $this->postJson('/api/ahp/calculate', [
            'jenis_usaha_id' => 1,
            'matrix' => $matrix
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                 ]);
                 
        $this->assertFalse($response->json('data.is_consistent'));
    }
}
