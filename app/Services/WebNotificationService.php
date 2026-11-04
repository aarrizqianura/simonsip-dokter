<?php

namespace App\Services;

use App\Models\Dokter;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\User;
use Carbon\Carbon;

class WebNotificationService
{
    /**
     * Create a new in-app web notification log.
     */
    public function send(
        User $recipient,
        string $messageType,
        string $title,
        string $payload,
        ?SipRecord $sipRecord = null,
        ?User $sender = null,
        ?string $actionUrl = null
    ): NotificationLog {
        return NotificationLog::create([
            'sip_record_id' => $sipRecord?->id,
            'recipient_user_id' => $recipient->id,
            'sender_user_id' => $sender?->id,
            'channel' => 'web',
            'message_type' => $messageType,
            'title' => $title,
            'payload' => $payload,
            'action_url' => $actionUrl,
            'status' => 'sent',
            'sent_at' => Carbon::now(),
        ]);
    }

    /**
     * Send welcome notification when a new doctor & user account are created.
     */
    public function notifyAccountCreated(
        User $recipient,
        Dokter $dokter,
        SipRecord $sipRecord,
        string $plainPassword,
        ?User $sender = null
    ): NotificationLog {
        $title = 'Selamat Datang di Portal SIMONSIP RSUD Dr. H. Moch. Ansari Saleh';
        $payload = sprintf(
            'Halo %s (%s), akun Portal Mandiri SIMONSIP Anda telah aktif dengan Username: %s (Password awal: %s). Data SIP Praktik Ke-%d (%s) berlaku s/d %s telah tercatat di sistem.',
            $dokter->nama_lengkap,
            $dokter->nip_nik,
            $recipient->username,
            $plainPassword,
            $sipRecord->tempat_praktik_ke,
            $sipRecord->no_sip,
            $sipRecord->tgl_kadaluarsa->format('d/m/Y')
        );

        return $this->send(
            recipient: $recipient,
            messageType: 'account_created',
            title: $title,
            payload: $payload,
            sipRecord: $sipRecord,
            sender: $sender,
            actionUrl: route('portal.dashboard', absolute: false)
        );
    }

    /**
     * Notify all Admin SDM & Super Admin users when a doctor uploads a 1-Step Renewal SIP.
     */
    public function notifyAdminsOfNewSubmission(SipRecord $sipRecord, User $dokterUser): int
    {
        $dokter = $sipRecord->dokter;
        $admins = User::whereIn('role', ['super_admin', 'admin_sdm'])->get();
        $count = 0;

        foreach ($admins as $admin) {
            $this->send(
                recipient: $admin,
                messageType: 'pengajuan_sip_baru',
                title: 'Pengajuan Verifikasi SIP Baru: ' . $dokter->nama_lengkap,
                payload: sprintf(
                    '%s (KSM %s) telah mengunggah berkas perpanjangan SIP Praktik Ke-%d dengan Nomor SIP: %s (Berlaku: %s s/d %s). Mohon segera lakukan pemeriksaan & verifikasi berkas.',
                    $dokter->nama_lengkap,
                    $dokter->ksm?->nama_ksm ?? '-',
                    $sipRecord->tempat_praktik_ke,
                    $sipRecord->no_sip,
                    $sipRecord->tgl_terbit->format('d/m/Y'),
                    $sipRecord->tgl_kadaluarsa->format('d/m/Y')
                ),
                sipRecord: $sipRecord,
                sender: $dokterUser,
                actionUrl: route('verifikasi.index', absolute: false)
            );
            $count++;
        }

        return $count;
    }

    /**
     * Notify doctor when Admin SDM approves or rejects a SIP submission.
     */
    public function notifyVerificationDecision(
        SipRecord $sipRecord,
        bool $approved,
        User $verifier,
        ?string $catatan = null
    ): NotificationLog {
        $dokter = $sipRecord->dokter;
        $recipient = $dokter->user;

        if ($approved) {
            $title = 'Pengajuan SIP Disetujui SDM (' . $sipRecord->no_sip . ')';
            $payload = sprintf(
                'Berkas perpanjangan SIP Praktik Ke-%d Anda (No: %s, berlaku s/d %s) telah DISETUJUI oleh %s. Status kepatuhan SIP Anda kini aktif dan aman. Catatan SDM: %s',
                $sipRecord->tempat_praktik_ke,
                $sipRecord->no_sip,
                $sipRecord->tgl_kadaluarsa->format('d/m/Y'),
                $verifier->name,
                $catatan ?: 'Berkas lengkap dan sesuai.'
            );

            return $this->send(
                recipient: $recipient,
                messageType: 'verifikasi_approved',
                title: $title,
                payload: $payload,
                sipRecord: $sipRecord,
                sender: $verifier,
                actionUrl: route('portal.riwayat', absolute: false)
            );
        }

        $title = 'Pengajuan SIP Perlu Revisi / Ditolak (' . $sipRecord->no_sip . ')';
        $payload = sprintf(
            'Berkas pengajuan SIP Praktik Ke-%d Anda (No: %s) DITOLAK / PERLU REVISI oleh %s. Alasan / Catatan Verifikator: "%s". Silakan unggah ulang berkas perbaikan melalui menu Unggah Perpanjangan SIP.',
            $sipRecord->tempat_praktik_ke,
            $sipRecord->no_sip,
            $verifier->name,
            $catatan ?: 'Mohon periksa kejelasan scan dokumen PDF SIP.'
        );

        return $this->send(
            recipient: $recipient,
            messageType: 'verifikasi_rejected',
            title: $title,
            payload: $payload,
            sipRecord: $sipRecord,
            sender: $verifier,
            actionUrl: route('portal.renewal.create', absolute: false)
        );
    }

    /**
     * Execute Daily Early Warning System check across all active/verified SIP records.
     *
     * @return array{checked: int, status_updated: int, notifications_sent: int}
     */
    public function runEarlyWarningCheck(?User $triggeredBy = null, bool $forceNotify = false): array
    {
        $records = SipRecord::with(['dokter.user', 'dokter.ksm'])
            ->where('is_verified', true)
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
            ->get();

        $checked = 0;
        $statusUpdated = 0;
        $notificationsSent = 0;

        foreach ($records as $sip) {
            $checked++;
            $newStatus = SipRecord::determineTrafficLightStatus($sip->tgl_kadaluarsa);

            if ($sip->status !== $newStatus) {
                $sip->update(['status' => $newStatus]);
                $statusUpdated++;
            }

            if (! in_array($newStatus, ['warning', 'mendesak', 'expired'], true)) {
                continue;
            }

            $dokter = $sip->dokter;
            if (! $dokter || ! $dokter->user) {
                continue;
            }

            $messageType = $newStatus === 'expired' ? 'urgent' : 'early_warning';
            $sisaHari = $sip->sisa_hari;

            $alreadySentToday = NotificationLog::where('sip_record_id', $sip->id)
                ->where('recipient_user_id', $dokter->user->id)
                ->where('message_type', $messageType)
                ->whereDate('sent_at', Carbon::today())
                ->exists();

            if (! $alreadySentToday || $forceNotify) {
                $stageText = match ($newStatus) {
                    'warning' => 'PERINGATAN TAHAP 1 (H-90 s/d H-61)',
                    'mendesak' => 'PERINGATAN MENDESAK TAHAP 2 (H-60 s/d H-31)',
                    'expired' => $sisaHari < 0 ? 'DARURAT: SIP TELAH KADALUARSA' : 'DARURAT: MASA BERLAKU SIP <= 30 HARI',
                    default => 'PERINGATAN MASA BERLAKU SIP',
                };

                $sisaText = $sisaHari < 0
                    ? 'telah melewati masa berlaku ' . abs($sisaHari) . ' hari yang lalu'
                    : 'tersisa ' . $sisaHari . ' hari lagi';

                $this->send(
                    recipient: $dokter->user,
                    messageType: $messageType,
                    title: sprintf('[%s] SIP Praktik Ke-%d (%s)', $stageText, $sip->tempat_praktik_ke, $sip->no_sip),
                    payload: sprintf(
                        'Yth. %s, masa berlaku SIP Praktik Ke-%d Anda (Nomor: %s) akan berakhir pada %s (%s). Mohon segera memproses dan mengunggah berkas perpanjangan SIP melalui fitur 1-Step Renewal.',
                        $dokter->nama_lengkap,
                        $sip->tempat_praktik_ke,
                        $sip->no_sip,
                        $sip->tgl_kadaluarsa->format('d/m/Y'),
                        $sisaText
                    ),
                    sipRecord: $sip,
                    sender: $triggeredBy,
                    actionUrl: route('portal.renewal.create', ['sip_id' => $sip->id], false)
                );
                $notificationsSent++;
            }
        }

        return [
            'checked' => $checked,
            'status_updated' => $statusUpdated,
            'notifications_sent' => $notificationsSent,
        ];
    }
}

