<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

/**
 * Validasi spasial: memastikan sebuah titik (latitude, longitude) berada
 * di dalam batas Kecamatan Sumbersari (polygon GeoJSON).
 *
 * Rule ini divalidasi terhadap latitude, dan nilai longitude diambil dari
 * request agar point-in-polygon punya kedua koordinat sekaligus.
 */
class WithinSumbersari implements ValidationRule
{
    protected float $longitude;

    protected static ?string $geojsonPathOverride = null;

    public function __construct(float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lat = is_numeric($value) ? (float) $value : null;
        $lng = $this->longitude;

        if ($lat === null) {
            $fail('Koordinat tidak valid.');
            return;
        }

        if (! self::contains($lat, $lng)) {
            $fail('Koordinat di luar batas Kecamatan Sumbersari');
        }
    }

    /**
     * Cek apakah titik berada di dalam polygon batas Sumbersari.
     */
    public static function contains(float $lat, float $lng): bool
    {
        $polygons = self::loadPolygons();

        // GeoJSON koordinat: [longitude, latitude]
        foreach ($polygons as $rings) {
            if (self::pointInPolygon($lng, $lat, $rings)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Muat daftar polygon (setiap polygon = array of rings, ring[0] = outer)
     * dari GeoJSON. Hasilnya di-cache.
     *
     * @return array<int, array<int, array<int, array{0: float, 1: float}>>>
     */
    protected static function loadPolygons(): array
    {
        $path = self::$geojsonPathOverride ?? public_path('assets/geojson/sumbersari_admin.geojson');

        return Cache::rememberForever('sumbersari_polygons:' . md5($path), function () use ($path) {
            if (! is_file($path)) {
                return [];
            }

            $geojson = json_decode(file_get_contents($path), true);
            if (! is_array($geojson)) {
                return [];
            }

            $geometries = [];

            if (($geojson['type'] ?? null) === 'FeatureCollection') {
                foreach ($geojson['features'] ?? [] as $feature) {
                    if (isset($feature['geometry'])) {
                        $geometries[] = $feature['geometry'];
                    }
                }
            } elseif (($geojson['type'] ?? null) === 'Feature') {
                if (isset($geojson['geometry'])) {
                    $geometries[] = $geojson['geometry'];
                }
            } else {
                $geometries[] = $geojson;
            }

            $polygons = [];

            foreach ($geometries as $geometry) {
                $type = $geometry['type'] ?? null;
                $coords = $geometry['coordinates'] ?? [];

                if ($type === 'Polygon') {
                    $polygons[] = $coords;
                } elseif ($type === 'MultiPolygon') {
                    foreach ($coords as $polygon) {
                        $polygons[] = $polygon;
                    }
                }
            }

            return $polygons;
        });
    }

    /**
     * Ray casting point-in-polygon dengan dukungan holes.
     *
     * @param array<int, array<int, array{0: float, 1: float}>> $rings
     */
    protected static function pointInPolygon(float $x, float $y, array $rings): bool
    {
        if (empty($rings)) {
            return false;
        }

        // Outer ring harus mengandung titik.
        if (! self::inRing($x, $y, $rings[0])) {
            return false;
        }

        // Jika titik jatuh di salah satu hole (inner ring), maka di luar.
        $count = count($rings);
        for ($i = 1; $i < $count; $i++) {
            if (self::inRing($x, $y, $rings[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array{0: float, 1: float}> $ring
     */
    protected static function inRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = (float) $ring[$i][0];
            $yi = (float) $ring[$i][1];
            $xj = (float) $ring[$j][0];
            $yj = (float) $ring[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-15) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Untuk keperluan test: override path GeoJSON.
     */
    public static function setGeojsonPath(?string $path): void
    {
        self::$geojsonPathOverride = $path;
        Cache::forget('sumbersari_polygons:' . md5($path ?? public_path('assets/geojson/sumbersari_admin.geojson')));
    }
}
