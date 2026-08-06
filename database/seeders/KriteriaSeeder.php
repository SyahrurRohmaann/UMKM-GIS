<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Harga Sewa Lahan/Bangunan', 'deskripsi' => 'Biaya sewa yang berdampak langsung pada modal awal & operasional jangka panjang'],
            ['nama' => 'Kepadatan Penduduk', 'deskripsi' => 'Indikator ukuran pasar & potensi permintaan; wilayah padat = eksposur pelanggan lebih besar'],
            ['nama' => 'Kedekatan dengan Kompetitor Sejenis', 'deskripsi' => 'Mengukur tingkat kejenuhan pasar dalam radius tertentu'],
            ['nama' => 'Tingkat Keamanan Lingkungan', 'deskripsi' => 'Kriteria kualitatif tidak terstruktur — memengaruhi risiko kehilangan aset & kenyamanan konsumen'],
        ];

        DB::table('kriteria')->insert($data);
    }
}
