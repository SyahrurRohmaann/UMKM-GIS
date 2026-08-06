<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriksPerbandingan extends Model
{
    protected $table = 'matriks_perbandingan';
    protected $guarded = ['id'];

    public function sesiPerhitungan(): BelongsTo
    {
        return $this->belongsTo(SesiPerhitungan::class, 'sesi_id');
    }

    public function jenisUsaha(): BelongsTo
    {
        return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id');
    }

    public function kriteriaA(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_a_id');
    }

    public function kriteriaB(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_b_id');
    }
}
