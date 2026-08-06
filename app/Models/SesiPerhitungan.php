<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiPerhitungan extends Model
{
    protected $table = 'sesi_perhitungan';
    protected $guarded = ['id'];

    public function matriksPerbandingan(): HasMany
    {
        return $this->hasMany(MatriksPerbandingan::class, 'sesi_id');
    }

    public function bobotKriteria(): HasMany
    {
        return $this->hasMany(BobotKriteria::class, 'sesi_id');
    }

    public function hasilPerankingan(): HasMany
    {
        return $this->hasMany(HasilPerankingan::class, 'sesi_id');
    }

    public function lokasiPilihanUser(): HasMany
    {
        return $this->hasMany(LokasiPilihanUser::class, 'sesi_id');
    }
}
