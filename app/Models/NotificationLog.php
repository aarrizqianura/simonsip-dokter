<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sip_record_id',
    'recipient_user_id',
    'sender_user_id',
    'channel',
    'message_type',
    'title',
    'payload',
    'action_url',
    'status',
    'sent_at',
    'read_at',
])]
class NotificationLog extends Model
{
    protected $table = 'notification_logs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function sipRecord(): BelongsTo
    {
        return $this->belongsTo(SipRecord::class, 'sip_record_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Provide array data compatibility (`$notif->data['title']` & `$notif->data['message']`).
     *
     * @return array{title: string, message: string}
     */
    public function getDataAttribute(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->payload,
        ];
    }

    public function getMessageTypeLabelAttribute(): string
    {
        return match ($this->message_type) {
            'account_created' => 'Akun Baru Dibuat',
            'early_warning' => 'Peringatan Dini (H-90 / H-60)',
            'urgent' => 'Peringatan Darurat / Kadaluarsa',
            'pengajuan_sip_baru' => 'Pengajuan SIP Baru Masuk',
            'verifikasi_approved' => 'SIP Disetujui SDM',
            'verifikasi_rejected' => 'SIP Ditolak / Revisi SDM',
            default => ucfirst(str_replace('_', ' ', $this->message_type)),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->message_type) {
            'account_created' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'early_warning' => 'bg-amber-100 text-amber-800 border-amber-200',
            'urgent' => 'bg-rose-100 text-rose-800 border-rose-200',
            'pengajuan_sip_baru' => 'bg-sky-100 text-sky-800 border-sky-200',
            'verifikasi_approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'verifikasi_rejected' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}

