<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $guarded = ['id'];

    public const CACHE_KEY = 'kelurahan_cached_all';

    public static function getCachedAll()
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::select('id', 'nama', 'kepadatan_penduduk')->get();
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
