<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('master_kelurahan');
        });

        static::deleted(function () {
            Cache::forget('master_kelurahan');
        });
    }

    public static function getCachedAll()
    {
        return Cache::remember('master_kelurahan', 86400, function () {
            return self::select('id', 'nama', 'kepadatan_penduduk')->get();
        });
    }
}
