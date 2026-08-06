<?php

namespace Tests\Feature;

use App\Services\AhpService;
use PHPUnit\Framework\TestCase;

class AhpServiceTest extends TestCase
{
    public function test_perhitungan_ahp_laundry_sesuai_proposal()
    {
        $service = new AhpService();

        // Matriks dari proposal:
        // 1. Biaya Sewa
        // 2. Kepadatan Penduduk
        // 3. Kompetitor
        // 4. Keamanan
        $matriks = [
            [1, 1/3, 2, 1/2],
            [3, 1, 4, 2],
            [1/2, 1/4, 1, 1/3],
            [2, 1/2, 3, 1]
        ];

        $hasil = $service->calculateWeightsAndConsistency($matriks);

        $this->assertEqualsWithDelta(0.1608, $hasil['weights'][0], 0.005);
        $this->assertEqualsWithDelta(0.4661, $hasil['weights'][1], 0.005);
        $this->assertEqualsWithDelta(0.0958, $hasil['weights'][2], 0.005);
        $this->assertEqualsWithDelta(0.2773, $hasil['weights'][3], 0.005);

        // Accept any CR under 0.1 for consistency
        $this->assertLessThan(0.1, $hasil['cr']);
        $this->assertTrue($hasil['is_consistent']);
    }
}
