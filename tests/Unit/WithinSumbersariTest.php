<?php

namespace Tests\Unit;

use App\Rules\WithinSumbersari;
use Tests\TestCase;

class WithinSumbersariTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan pakai GeoJSON asli di public path.
        WithinSumbersari::setGeojsonPath(
            dirname(__DIR__, 2) . '/public/assets/geojson/sumbersari_admin.geojson'
        );
    }

    protected function tearDown(): void
    {
        WithinSumbersari::setGeojsonPath(null);
        parent::tearDown();
    }

    public function test_point_inside_sumbersari_is_accepted(): void
    {
        // Koordinat valid dari AlternatifLokasiSeeder (Kandidat Lndry 1 - Sumbersari).
        $this->assertTrue(
            WithinSumbersari::contains(-8.1645, 113.7161),
            'Titik di dalam Sumbersari harus lolos.'
        );
    }

    public function test_point_outside_is_rejected(): void
    {
        // Jakarta - jelas di luar Kecamatan Sumbersari.
        $this->assertFalse(
            WithinSumbersari::contains(-6.2, 106.8),
            'Titik di luar (Jakarta) harus ditolak.'
        );
    }
}
