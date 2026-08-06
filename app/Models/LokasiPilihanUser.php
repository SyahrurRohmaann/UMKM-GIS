<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LokasiPilihanUser extends Model
{
    protected $table = 'lokasi_pilihan_user';
    protected $guarded = ['id'];

    public function sesiPerhitungan(): BelongsTo
    {
        return $this->belongsTo(SesiPerhitungan::class, 'sesi_id');
    }

    public function alternatifLokasi(): BelongsTo
    {
        return $this->belongsTo(AlternatifLokasi::class, 'alternatif_lokasi_id');
    }
}
