<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_spesialisasi'])]
class Spesialisasi extends Model
{
    protected $table = 'spesialisasi';

    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class, 'spesialisasi_id');
    }
}

