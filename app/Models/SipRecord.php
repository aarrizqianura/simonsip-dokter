<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'dokter_id',
    'no_sip',
    'tempat_praktik_ke',
    'tgl_terbit',
    'tgl_kadaluarsa',
    'file_sip_path',
    'status',
    'is_verified',
    'verified_by',
    'verified_at',
    'catatan_verifikasi',
])]
class SipRecord extends Model
{
    protected $table = 'sip_records';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tempat_praktik_ke' => 'integer',
            'tgl_terbit' => 'date',
            'tgl_kadaluarsa' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'sip_record_id');
    }

    /**
     * Calculate remaining days until expiration relative to today.
     */
    public function getSisaHariAttribute(): int
    {
        if (! $this->tgl_kadaluarsa) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays($this->tgl_kadaluarsa->startOfDay(), false);
    }

    /**
     * Compute traffic light status from remaining days for verified records.
     */
    public static function determineTrafficLightStatus(Carbon|string $tglKadaluarsa): string
    {
        $expDate = $tglKadaluarsa instanceof Carbon ? $tglKadaluarsa->copy()->startOfDay() : Carbon::parse($tglKadaluarsa)->startOfDay();
        $days = (int) Carbon::today()->diffInDays($expDate, false);

        if ($days > 90) {
            return 'aktif';
        }

        if ($days >= 61) {
            return 'warning';
        }

        if ($days >= 31) {
            return 'mendesak';
        }

        return 'expired';
    }

    /**
     * Human-readable label for status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'Aman (> 90 Hari)',
            'warning' => 'Peringatan (61-90 Hari)',
            'mendesak' => 'Mendesak (31-60 Hari)',
            'expired' => 'Kadaluarsa (<= 30 Hari)',
            'menunggu_verifikasi' => 'Menunggu Verifikasi SDM',
            'ditolak' => 'Ditolak / Revisi',
            'archived' => 'Diarsipkan (SIP Lama)',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS style class for UI traffic light.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
            'mendesak' => 'bg-orange-100 text-orange-800 border-orange-200',
            'expired' => 'bg-rose-100 text-rose-800 border-rose-200',
            'menunggu_verifikasi' => 'bg-sky-100 text-sky-800 border-sky-200',
            'ditolak' => 'bg-red-100 text-red-800 border-red-200',
            'archived' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * SLA processing duration in hours/days if verified.
     */
    public function getDurasiProsesLabelAttribute(): string
    {
        if (! $this->verified_at || ! $this->created_at) {
            return 'Belum Diproses';
        }

        $minutes = (int) $this->created_at->diffInMinutes($this->verified_at);
        if ($minutes < 60) {
            return max(1, $minutes) . ' Menit';
        }

        $hours = round($minutes / 60, 1);
        if ($hours < 24) {
            return $hours . ' Jam';
        }

        $days = round($hours / 24, 1);

        return $days . ' Hari';
    }
}

