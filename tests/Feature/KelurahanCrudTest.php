<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelurahanCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_store_creates_kelurahan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'nama' => 'Sumbersari',
            'kepadatan_penduduk' => 8500,
        ]);

        $response->assertRedirect(route('admin.kelurahan.index'));
        $this->assertDatabaseHas('kelurahan', [
            'nama' => 'Sumbersari',
            'kepadatan_penduduk' => 8500,
        ]);
    }

    public function test_store_validation_fails_on_invalid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'nama' => '',
            'kepadatan_penduduk' => -5,
        ]);

        $response->assertSessionHasErrors(['nama', 'kepadatan_penduduk']);
        $this->assertDatabaseCount('kelurahan', 0);
    }

    public function test_update_modifies_kelurahan(): void
    {
        $kelurahan = Kelurahan::create([
            'nama' => 'Lama',
            'kepadatan_penduduk' => 1000,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.kelurahan.update', $kelurahan->id), [
            'nama' => 'Baru',
            'kepadatan_penduduk' => 2500,
        ]);

        $response->assertRedirect(route('admin.kelurahan.index'));
        $this->assertDatabaseHas('kelurahan', [
            'id' => $kelurahan->id,
            'nama' => 'Baru',
            'kepadatan_penduduk' => 2500,
        ]);
    }

    public function test_destroy_deletes_kelurahan(): void
    {
        $kelurahan = Kelurahan::create([
            'nama' => 'Hapus',
            'kepadatan_penduduk' => 500,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kelurahan.destroy', $kelurahan->id));

        $response->assertRedirect(route('admin.kelurahan.index'));
        $this->assertDatabaseMissing('kelurahan', ['id' => $kelurahan->id]);
    }

    public function test_mutations_invalidate_cached_all(): void
    {
        // Prime the cache.
        Kelurahan::getCachedAll();

        $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'nama' => 'Kelurahan Baru',
            'kepadatan_penduduk' => 3000,
        ]);

        $cached = Kelurahan::getCachedAll();
        $this->assertTrue(
            $cached->contains('nama', 'Kelurahan Baru'),
            'Cache getCachedAll harus ter-invalidasi setelah store.'
        );
    }
}
