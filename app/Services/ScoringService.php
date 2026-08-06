<?php

namespace App\Services;

class ScoringService
{
    /**
     * Tipe kriteria: 
     * true  = Benefit (semakin besar semakin baik)
     * false = Cost (semakin kecil semakin baik)
     * Indeks array berdasar urutan matrix:
     * 0 => Sewa (Cost)
     * 1 => Penduduk (Benefit)
     * 2 => Kompetitor (Cost)
     * 3 => Keamanan (Benefit)
     */
    private const CRITERIA_TYPES = [
        0 => false, // Harga Sewa Lahan/Bangunan
        1 => true,  // Kepadatan Penduduk
        2 => false, // Kedekatan dengan Kompetitor
        3 => true,  // Tingkat Keamanan Lingkungan
    ];

    /**
     * Hitung skor akhir menggunakan Simple Additive Weighting (SAW) berdasar bobot AHP.
     *
     * @param array $alternatives Array asosiatif lokasi dengan field: id, nilai_sewa, nilai_penduduk, nilai_kompetitor, nilai_keamanan
     * @param array $weights Bobot kriteria (eigenvector dari AHP)
     * @return array Alternatif beserta skor akhir (sudah terurut dari terbesar)
     */
    public function calculateFinalScores(array $alternatives, array $weights): array
    {
        if (empty($alternatives)) {
            return [];
        }

        // 1. Cari Max & Min tiap kriteria untuk normalisasi
        $minMax = [
            0 => ['min' => INF, 'max' => -INF],
            1 => ['min' => INF, 'max' => -INF],
            2 => ['min' => INF, 'max' => -INF],
            3 => ['min' => INF, 'max' => -INF],
        ];

        foreach ($alternatives as $alt) {
            $vals = [
                0 => $alt['nilai_sewa'],
                1 => $alt['nilai_penduduk'],
                2 => $alt['nilai_kompetitor'],
                3 => $alt['nilai_keamanan'],
            ];

            for ($i = 0; $i < 4; $i++) {
                if ($vals[$i] < $minMax[$i]['min']) $minMax[$i]['min'] = $vals[$i];
                if ($vals[$i] > $minMax[$i]['max']) $minMax[$i]['max'] = $vals[$i];
            }
        }

        // 2. Normalisasi & Penjumlahan Skor
        foreach ($alternatives as &$alt) {
            $vals = [
                0 => $alt['nilai_sewa'],
                1 => $alt['nilai_penduduk'],
                2 => $alt['nilai_kompetitor'],
                3 => $alt['nilai_keamanan'],
            ];

            $skorAkhir = 0;

            for ($i = 0; $i < 4; $i++) {
                $isBenefit = self::CRITERIA_TYPES[$i];
                $min = $minMax[$i]['min'];
                $max = $minMax[$i]['max'];
                
                // Hindari division by zero jika semua alternatif punya nilai yang sama untuk suatu kriteria
                $normalizedValue = 0;
                if ($max > 0 || $min > 0) {
                    if ($isBenefit) {
                        // Max-based normalization untuk benefit
                        $normalizedValue = $max > 0 ? $vals[$i] / $max : 0;
                    } else {
                        // Min-based normalization untuk cost
                        $normalizedValue = $vals[$i] > 0 ? $min / $vals[$i] : 0;
                    }
                } else if ($vals[$i] == 0 && !$isBenefit) {
                     // Khusus nilai 0 pada kriteria cost (misal kompetitor = 0), beri skor full
                     $normalizedValue = 1;
                }

                $skorAkhir += $normalizedValue * $weights[$i];
            }

            $alt['skor_akhir'] = round($skorAkhir, 4);
        }

        // 3. Sorting berdasar skor (Descending)
        usort($alternatives, function($a, $b) {
            return $b['skor_akhir'] <=> $a['skor_akhir'];
        });

        // 4. Beri Ranking
        $rank = 1;
        foreach ($alternatives as &$alt) {
            $alt['ranking'] = $rank++;
        }

        return $alternatives;
    }
}
