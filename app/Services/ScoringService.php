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
     * Hitung skor akhir menggunakan Simple Additive Weighting (SAW) dengan
     * normalisasi Min-Max, konsisten dengan AhpService::hitungSkorAkhir dan
     * spesifikasi PRD-03 §3.4 (rekomendasi normalisasi min-max, arah dibalik
     * untuk kriteria cost).
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

        $fields = [
            0 => 'nilai_sewa',
            1 => 'nilai_penduduk',
            2 => 'nilai_kompetitor',
            3 => 'nilai_keamanan',
        ];

        // 1. Cari Min & Max tiap kriteria untuk normalisasi min-max
        $minMax = [];
        for ($i = 0; $i < 4; $i++) {
            $vals = array_map(static fn ($alt) => (float) $alt[$fields[$i]], $alternatives);
            $minMax[$i] = ['min' => min($vals), 'max' => max($vals)];
        }

        // 2. Normalisasi min-max & penjumlahan skor berbobot
        foreach ($alternatives as &$alt) {
            $skorAkhir = 0;

            for ($i = 0; $i < 4; $i++) {
                $isBenefit = self::CRITERIA_TYPES[$i];
                $nilai = (float) $alt[$fields[$i]];
                $min = $minMax[$i]['min'];
                $max = $minMax[$i]['max'];
                $range = $max - $min;

                // Bila semua alternatif punya nilai identik (range = 0),
                // kriteria ini tidak membedakan apa pun → beri skor netral penuh (1)
                // agar tiap alternatif setara pada kriteria tsb (tidak bias).
                if ($range <= 0) {
                    $normalizedValue = 1.0;
                } elseif ($isBenefit) {
                    // Benefit: makin besar makin baik
                    $normalizedValue = ($nilai - $min) / $range;
                } else {
                    // Cost: makin kecil makin baik (arah dibalik)
                    $normalizedValue = ($max - $nilai) / $range;
                }

                $skorAkhir += $normalizedValue * $weights[$i];
            }

            $alt['skor_akhir'] = round($skorAkhir, 4);
        }
        unset($alt);

        // 3. Sorting berdasar skor (Descending)
        usort($alternatives, function ($a, $b) {
            return $b['skor_akhir'] <=> $a['skor_akhir'];
        });

        // 4. Beri Ranking
        $rank = 1;
        foreach ($alternatives as &$alt) {
            $alt['ranking'] = $rank++;
        }
        unset($alt);

        return $alternatives;
    }
}
