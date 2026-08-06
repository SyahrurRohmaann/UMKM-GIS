<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlternativeScore extends Model
{
    protected $guarded = [];

    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }
}
