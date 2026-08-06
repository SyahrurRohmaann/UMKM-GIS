<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisUsahaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_usaha')->insert([
            ['nama' => 'Laundry'],
            ['nama' => 'Kafe'],
        ]);
    }
}
