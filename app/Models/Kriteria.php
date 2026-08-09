<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Kriteria extends Model
{
    protected $table = 'kriteria';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('master_kriteria');
        });

        static::deleted(function () {
            Cache::forget('master_kriteria');
        });
    }

    public static function getCachedAll()
    {
        return Cache::remember('master_kriteria', 86400, function () {
            return self::orderBy('id')->get();
        });
    }
}
