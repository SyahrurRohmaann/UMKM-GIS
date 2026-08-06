<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlternatifLokasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // LAUNDRY - KANDIDAT LOKASI (10)
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Lndry 1 (Sumbersari)', 'latitude' => -8.1645, 'longitude' => 113.7161, 'harga_sewa_per_tahun' => 15000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Lndry 2 (Sumbersari)', 'latitude' => -8.1632, 'longitude' => 113.7145, 'harga_sewa_per_tahun' => 18000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 2, 'nama_lokasi' => 'Kandidat Lndry 3 (Karangrejo)', 'latitude' => -8.1710, 'longitude' => 113.7180, 'harga_sewa_per_tahun' => 12000000, 'skor_keamanan' => 2, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 3, 'nama_lokasi' => 'Kandidat Lndry 4 (Wirolegi)', 'latitude' => -8.1805, 'longitude' => 113.7300, 'harga_sewa_per_tahun' => 10000000, 'skor_keamanan' => 2, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 5, 'nama_lokasi' => 'Kandidat Lndry 5 (Tegalgede)', 'latitude' => -8.1520, 'longitude' => 113.7250, 'harga_sewa_per_tahun' => 20000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 7, 'nama_lokasi' => 'Kandidat Lndry 6 (Kebonsari)', 'latitude' => -8.1750, 'longitude' => 113.7050, 'harga_sewa_per_tahun' => 14000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 7, 'nama_lokasi' => 'Kandidat Lndry 7 (Kebonsari)', 'latitude' => -8.1765, 'longitude' => 113.7085, 'harga_sewa_per_tahun' => 16000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 4, 'nama_lokasi' => 'Kandidat Lndry 8 (Kranjingan)', 'latitude' => -8.1850, 'longitude' => 113.7220, 'harga_sewa_per_tahun' => 11000000, 'skor_keamanan' => 2, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 6, 'nama_lokasi' => 'Kandidat Lndry 9 (Antirogo)', 'latitude' => -8.1400, 'longitude' => 113.7280, 'harga_sewa_per_tahun' => 9000000, 'skor_keamanan' => 1, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Lndry 10 (Sumbersari)', 'latitude' => -8.1590, 'longitude' => 113.7190, 'harga_sewa_per_tahun' => 22000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],

            // KAFE - KANDIDAT LOKASI (10)
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Kafe 1 (Sumbersari)', 'latitude' => -8.1610, 'longitude' => 113.7150, 'harga_sewa_per_tahun' => 30000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Kafe 2 (Sumbersari)', 'latitude' => -8.1630, 'longitude' => 113.7120, 'harga_sewa_per_tahun' => 35000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 5, 'nama_lokasi' => 'Kandidat Kafe 3 (Tegalgede)', 'latitude' => -8.1510, 'longitude' => 113.7210, 'harga_sewa_per_tahun' => 25000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 5, 'nama_lokasi' => 'Kandidat Kafe 4 (Tegalgede)', 'latitude' => -8.1530, 'longitude' => 113.7260, 'harga_sewa_per_tahun' => 28000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 7, 'nama_lokasi' => 'Kandidat Kafe 5 (Kebonsari)', 'latitude' => -8.1730, 'longitude' => 113.7020, 'harga_sewa_per_tahun' => 20000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 7, 'nama_lokasi' => 'Kandidat Kafe 6 (Kebonsari)', 'latitude' => -8.1710, 'longitude' => 113.7080, 'harga_sewa_per_tahun' => 22000000, 'skor_keamanan' => 3, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 2, 'nama_lokasi' => 'Kandidat Kafe 7 (Karangrejo)', 'latitude' => -8.1700, 'longitude' => 113.7150, 'harga_sewa_per_tahun' => 18000000, 'skor_keamanan' => 2, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 3, 'nama_lokasi' => 'Kandidat Kafe 8 (Wirolegi)', 'latitude' => -8.1810, 'longitude' => 113.7310, 'harga_sewa_per_tahun' => 15000000, 'skor_keamanan' => 2, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 6, 'nama_lokasi' => 'Kandidat Kafe 9 (Antirogo)', 'latitude' => -8.1420, 'longitude' => 113.7290, 'harga_sewa_per_tahun' => 12000000, 'skor_keamanan' => 1, 'adalah_kompetitor' => 0],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Kandidat Kafe 10 (Sumbersari)', 'latitude' => -8.1580, 'longitude' => 113.7170, 'harga_sewa_per_tahun' => 40000000, 'skor_keamanan' => 4, 'adalah_kompetitor' => 0],

            // LAUNDRY - KOMPETITOR (5) - Untuk testing radius hitung kompetitor
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 1, 'nama_lokasi' => 'Komp. Laundry A', 'latitude' => -8.1640, 'longitude' => 113.7165, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 1, 'nama_lokasi' => 'Komp. Laundry B', 'latitude' => -8.1630, 'longitude' => 113.7150, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 5, 'nama_lokasi' => 'Komp. Laundry C', 'latitude' => -8.1525, 'longitude' => 113.7245, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 7, 'nama_lokasi' => 'Komp. Laundry D', 'latitude' => -8.1755, 'longitude' => 113.7060, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 1, 'kelurahan_id' => 2, 'nama_lokasi' => 'Komp. Laundry E', 'latitude' => -8.1715, 'longitude' => 113.7175, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],

            // KAFE - KOMPETITOR (5)
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Komp. Kafe X', 'latitude' => -8.1615, 'longitude' => 113.7145, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Komp. Kafe Y', 'latitude' => -8.1585, 'longitude' => 113.7165, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 5, 'nama_lokasi' => 'Komp. Kafe Z', 'latitude' => -8.1515, 'longitude' => 113.7225, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 7, 'nama_lokasi' => 'Komp. Kafe W', 'latitude' => -8.1720, 'longitude' => 113.7050, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
            ['jenis_usaha_id' => 2, 'kelurahan_id' => 1, 'nama_lokasi' => 'Komp. Kafe V', 'latitude' => -8.1635, 'longitude' => 113.7125, 'harga_sewa_per_tahun' => 0, 'skor_keamanan' => 0, 'adalah_kompetitor' => 1],
        ];

        DB::table('alternatif_lokasi')->insert($data);
    }
}
