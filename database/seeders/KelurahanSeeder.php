<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelurahanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Sumbersari', 'kepadatan_penduduk' => 8500],
            ['nama' => 'Karangrejo', 'kepadatan_penduduk' => 7200],
            ['nama' => 'Wirolegi', 'kepadatan_penduduk' => 6100],
            ['nama' => 'Kranjingan', 'kepadatan_penduduk' => 5400],
            ['nama' => 'Tegalgede', 'kepadatan_penduduk' => 6800],
            ['nama' => 'Antirogo', 'kepadatan_penduduk' => 4900],
            ['nama' => 'Kebonsari', 'kepadatan_penduduk' => 9200],
        ];

        DB::table('kelurahan')->insert($data);
    }
}
