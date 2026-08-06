<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternatifLokasi extends Model
{
    protected $table = 'alternatif_lokasi';
    protected $guarded = ['id'];

    public function jenisUsaha(): BelongsTo
    {
        return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }
}
