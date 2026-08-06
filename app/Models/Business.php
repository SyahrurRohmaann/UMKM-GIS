<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $guarded = [];

    public function criteria()
    {
        return $this->hasMany(Criterion::class);
    }

    public function alternatives()
    {
        return $this->hasMany(Alternative::class);
    }
}
