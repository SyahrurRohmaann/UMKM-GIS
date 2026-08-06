<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\AlternativeScore;
use App\Models\Business;
use App\Models\Criterion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AhpSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Jenis Usaha
        $laundry = Business::create(['name' => 'Laundry', 'slug' => Str::slug('Laundry')]);
        $kafe = Business::create(['name' => 'Kafe', 'slug' => Str::slug('Kafe')]);

        // 1. Kriteria (Laundry)
        $c1 = Criterion::create(['business_id' => $laundry->id, 'code' => 'C1', 'name' => 'Kepadatan Penduduk', 'type' => 'benefit']);
        $c2 = Criterion::create(['business_id' => $laundry->id, 'code' => 'C2', 'name' => 'Harga Sewa', 'type' => 'cost']);
        $c3 = Criterion::create(['business_id' => $laundry->id, 'code' => 'C3', 'name' => 'Jarak dari Pesaing', 'type' => 'benefit']);
        $c4 = Criterion::create(['business_id' => $laundry->id, 'code' => 'C4', 'name' => 'Keamanan Lingkungan', 'type' => 'benefit']);

        // 2. Alternatif (Laundry)
        $a1 = Alternative::create(['business_id' => $laundry->id, 'name' => 'Lokasi Kampus Unej', 'latitude' => -8.164522, 'longitude' => 113.716611]);
        $a2 = Alternative::create(['business_id' => $laundry->id, 'name' => 'Area Mastrip', 'latitude' => -8.157833, 'longitude' => 113.719889]);
        $a3 = Alternative::create(['business_id' => $laundry->id, 'name' => 'Area Tegal Boto', 'latitude' => -8.161111, 'longitude' => 113.714444]);

        // 3. Skor (Laundry)
        $scores = [
            $a1->id => [$c1->id => 5, $c2->id => 4, $c3->id => 2, $c4->id => 4],
            $a2->id => [$c1->id => 4, $c2->id => 5, $c3->id => 3, $c4->id => 3],
            $a3->id => [$c1->id => 3, $c2->id => 3, $c3->id => 5, $c4->id => 5],
        ];

        foreach ($scores as $altId => $critScores) {
            foreach ($critScores as $critId => $score) {
                AlternativeScore::create([
                    'alternative_id' => $altId,
                    'criterion_id' => $critId,
                    'score' => $score
                ]);
            }
        }
    }
}
