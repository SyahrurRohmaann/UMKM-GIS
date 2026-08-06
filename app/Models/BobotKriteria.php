<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BobotKriteria extends Model
{
    protected $table = 'bobot_kriteria';
    protected $guarded = ['id'];

    public function sesiPerhitungan(): BelongsTo
    {
        return $this->belongsTo(SesiPerhitungan::class, 'sesi_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }
}
