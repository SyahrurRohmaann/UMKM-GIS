<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecommendationPersistTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_mode_persists_lokasi_pilihan_user_and_sesi(): void
    {
        $this->artisan('db:seed');

        // Ambil 2 kandidat DB (bukan kompetitor) untuk jenis usaha 1 (Laundry)
        $ids = DB::table('alternatif_lokasi')
            ->where('jenis_usaha_id', 1)
            ->where('adalah_kompetitor', 0)
            ->limit(2)
            ->pluck('id')
            ->toArray();

        $this->assertCount(2, $ids, 'Butuh minimal 2 kandidat untuk komparasi manual');

        $sesiBefore = DB::table('sesi_perhitungan')->count();
        $lokasiBefore = DB::table('lokasi_pilihan_user')->count();

        $response = $this->postJson('/api/recommendations/generate', [
            'jenis_usaha_id' => 1,
            'selected_ids' => $ids,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
        $this->assertNotNull($response->json('sesi_id'));

        $this->assertEquals($sesiBefore + 1, DB::table('sesi_perhitungan')->count());
        $this->assertEquals($lokasiBefore + count($ids), DB::table('lokasi_pilihan_user')->count());

        // Baris tersimpan menunjuk ke sesi yang baru dibuat
        $sesiId = $response->json('sesi_id');
        $this->assertEquals(
            count($ids),
            DB::table('lokasi_pilihan_user')->where('sesi_id', $sesiId)->count()
        );
    }

    public function test_system_mode_without_selected_ids_does_not_persist(): void
    {
        $this->artisan('db:seed');

        $sesiBefore = DB::table('sesi_perhitungan')->count();
        $lokasiBefore = DB::table('lokasi_pilihan_user')->count();

        $response = $this->postJson('/api/recommendations/generate', [
            'jenis_usaha_id' => 1,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
        $this->assertNull($response->json('sesi_id'));

        $this->assertEquals($sesiBefore, DB::table('sesi_perhitungan')->count());
        $this->assertEquals($lokasiBefore, DB::table('lokasi_pilihan_user')->count());
    }
}
