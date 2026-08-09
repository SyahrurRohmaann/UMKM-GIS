<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class JenisUsaha extends Model
{
    use HasFactory;

    protected $table = 'jenis_usaha';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('master_jenis_usaha');
        });

        static::deleted(function () {
            Cache::forget('master_jenis_usaha');
        });
    }

    public static function getCachedAll()
    {
        return Cache::remember('master_jenis_usaha', 86400, function () {
            return self::select('id', 'nama', 'ikon_marker')->get();
        });
    }

    public function alternatifLokasi(): HasMany
    {
        return $this->hasMany(AlternatifLokasi::class, 'jenis_usaha_id');
    }
}
