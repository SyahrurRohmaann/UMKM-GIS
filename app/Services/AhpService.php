<?php

namespace App\Services;

use InvalidArgumentException;

class AhpService
{
    /**
     * Random Index (RI) table for AHP.
     * Index represents 'n' (number of criteria).
     * Based on Saaty's standard RI table.
     */
    private const RI_TABLE = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90, // Crucial for 4 criteria
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];

    /**
     * Calculate AHP weights (eigenvector) and consistency.
     *
     * @param array $matrix 2D array of pairwise comparisons (n x n)
     * @return array [
     *     'weights' => array of floats (eigenvector),
     *     'lambda_max' => float,
     *     'ci' => float (Consistency Index),
     *     'cr' => float (Consistency Ratio),
     *     'is_consistent' => bool (CR < 0.1)
     * ]
     * @throws InvalidArgumentException if matrix is invalid
     */
    public function calculateWeightsAndConsistency(array $matrix): array
    {
        $n = count($matrix);
        if ($n < 3) {
            throw new InvalidArgumentException("Matrix size must be at least 3x3 for consistency calculation.");
        }

        // 1. Calculate column sums
        $colSums = array_fill(0, $n, 0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $colSums[$j] += $matrix[$i][$j];
            }
        }

        // 2. Normalize matrix & calculate weights (eigenvector)
        $normalizedMatrix = [];
        $weights = array_fill(0, $n, 0);

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                // Handle division by zero just in case
                $normalizedValue = $colSums[$j] > 0 ? $matrix[$i][$j] / $colSums[$j] : 0;
                $normalizedMatrix[$i][$j] = $normalizedValue;
                $weights[$i] += $normalizedValue;
            }
            $weights[$i] /= $n; // Average of the row
        }

        // 3. Calculate lambda max
        // Multiply original matrix by weights
        $weightedSums = array_fill(0, $n, 0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $weightedSums[$i] += $matrix[$i][$j] * $weights[$j];
            }
        }

        // Divide weighted sums by corresponding weights to get lambda estimates per criteria
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($weights[$i] > 0) {
                $lambdaMax += $weightedSums[$i] / $weights[$i];
            }
        }
        $lambdaMax /= $n; // Average to get final lambda max

        // 4. Calculate Consistency Index (CI)
        $ci = ($lambdaMax - $n) / ($n - 1);

        // 5. Calculate Consistency Ratio (CR)
        $ri = self::RI_TABLE[$n] ?? 1.49; // Default to highest if out of bounds
        $cr = $ri > 0 ? $ci / $ri : 0;

        return [
            'weights' => $weights,
            'lambda_max' => round($lambdaMax, 4),
            'ci' => round($ci, 4),
            'cr' => round($cr, 4),
            'is_consistent' => $cr < 0.1
        ];
    }

    /**
     * Hitung skor akhir alternatif
     * @param array $dataKriteria array associative, key = id kriteria, value = ['bobot' => float, 'is_benefit' => bool]
     * @param array $dataAlternatif array 2D, baris = alternatif, kolom = id kriteria (berisi nilai asli)
     * @return array [ id_alternatif => skor_akhir ]
     */
    public function hitungSkorAkhir(array $dataKriteria, array $dataAlternatif): array
    {
        $minMaxKriteria = [];
        foreach ($dataKriteria as $idKriteria => $info) {
            $nilaiSemua = array_column($dataAlternatif, $idKriteria);
            $minMaxKriteria[$idKriteria] = [
                'min' => min($nilaiSemua),
                'max' => max($nilaiSemua),
            ];
        }

        $skorAkhir = [];
        foreach ($dataAlternatif as $idAlternatif => $nilaiKriteria) {
            $totalSkor = 0;
            foreach ($dataKriteria as $idKriteria => $info) {
                $nilaiAsli = $nilaiKriteria[$idKriteria];
                $min = $minMaxKriteria[$idKriteria]['min'];
                $max = $minMaxKriteria[$idKriteria]['max'];
                
                $nilaiTernormalisasi = 1;
                if ($max - $min > 0) {
                    if ($info['is_benefit']) {
                        $nilaiTernormalisasi = ($nilaiAsli - $min) / ($max - $min);
                    } else { // cost
                        $nilaiTernormalisasi = ($max - $nilaiAsli) / ($max - $min);
                    }
                }

                $totalSkor += $info['bobot'] * $nilaiTernormalisasi;
            }
            $skorAkhir[$idAlternatif] = $totalSkor;
        }

        arsort($skorAkhir); // Urutkan descending
        return $skorAkhir;
    }

    /**
     * Hitung jarak Haversine antara dua koordinat (dalam kilometer)
     */
    public function hitungJarakHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Hitung jumlah kompetitor dalam radius tertentu
     */
    public function hitungJumlahKompetitor(float $lat, float $lon, \Illuminate\Support\Collection $dataKompetitor, float $radiusKm = 1.0): int
    {
        $count = 0;
        foreach ($dataKompetitor as $komp) {
            $jarak = $this->hitungJarakHaversine($lat, $lon, $komp->latitude, $komp->longitude);
            if ($jarak <= $radiusKm) {
                $count++;
            }
        }
        // Minimal nilai 1 untuk perhitungan (hindari bagi nol di normalisasi min-max jika semua nol)
        return max(1, $count);
    }
}
