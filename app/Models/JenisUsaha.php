<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisUsaha extends Model
{
    protected $table = 'jenis_usaha';
    protected $guarded = ['id'];

    public function alternatifLokasi(): HasMany
    {
        return $this->hasMany(AlternatifLokasi::class, 'jenis_usaha_id');
    }
}
