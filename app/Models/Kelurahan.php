<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $guarded = ['id'];

    public static function getCachedAll()
    {
        return self::select('id', 'nama', 'kepadatan_penduduk')->get();
    }
}
