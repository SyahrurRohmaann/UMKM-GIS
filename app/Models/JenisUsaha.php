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

    public static function getCachedAll()
    {
        return self::select('id', 'nama', 'ikon_marker')->get();
    }

    public function alternatifLokasi(): HasMany
    {
        return $this->hasMany(AlternatifLokasi::class, 'jenis_usaha_id');
    }
}
