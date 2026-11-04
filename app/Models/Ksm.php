<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_ksm'])]
class Ksm extends Model
{
    protected $table = 'ksm';

    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class, 'ksm_id');
    }
}

