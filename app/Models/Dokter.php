<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'nip_nik',
    'nama_lengkap',
    'no_whatsapp',
    'ksm_id',
    'spesialisasi_id',
    'no_str',
    'is_str_seumur_hidup',
    'tgl_kadaluarsa_str',
])]
class Dokter extends Model
{
    protected $table = 'dokters';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_str_seumur_hidup' => 'boolean',
            'tgl_kadaluarsa_str' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ksm(): BelongsTo
    {
        return $this->belongsTo(Ksm::class, 'ksm_id');
    }

    public function spesialisasi(): BelongsTo
    {
        return $this->belongsTo(Spesialisasi::class, 'spesialisasi_id');
    }

    public function sipRecords(): HasMany
    {
        return $this->hasMany(SipRecord::class, 'dokter_id');
    }

    public function activeSipRecords(): HasMany
    {
        return $this->hasMany(SipRecord::class, 'dokter_id')
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
            ->where('is_verified', true);
    }

    public function latestSipRecord(): HasOne
    {
        return $this->hasOne(SipRecord::class, 'dokter_id')->latestOfMany('tgl_kadaluarsa');
    }
}

