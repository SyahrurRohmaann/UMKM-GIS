<?php

namespace Database\Factories;

use App\Models\JenisUsaha;
use Illuminate\Database\Eloquent\Factories\Factory;

class JenisUsahaFactory extends Factory
{
    protected $model = JenisUsaha::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->unique()->randomElement(['Laundry', 'Kafe', 'Restoran', 'Minimarket']),
        ];
    }
}