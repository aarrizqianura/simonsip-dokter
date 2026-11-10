<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Services\WebNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiSipController extends Controller
{
    public function index(Request $request): View
    {
        $pendingRecords = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'dokter.user'])
            ->where('status', 'menunggu_verifikasi')
            ->orderBy('created_at', 'asc')
            ->get();

        $historyRecords = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifier'])
            ->whereNotNull('verified_at')
            ->orderByDesc('verified_at')
            ->limit(25)
            ->get();

        return view('verifikasi.index', compact('pendingRecords', 'historyRecords'));
    }

    /**
     * Approve a doctor's SIP renewal submission.
     */
    public function approve(
        Request $request,
        SipRecord $sipRecord,
        WebNotificationService $notificationService
    ): RedirectResponse {
        if ($sipRecord->status !== 'menunggu_verifikasi') {
            return back()->with('error', 'Berkas pengajuan SIP ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);

        $sipRecord->load(['dokter.user']);

        DB::transaction(function () use ($request, $sipRecord, $validated, $notificationService) {
            // Archive any previous active/warning/mendesak/expired SIP for same doctor & tempat_praktik_ke
            SipRecord::where('dokter_id', $sipRecord->dokter_id)
                ->where('tempat_praktik_ke', $sipRecord->tempat_praktik_ke)
                ->where('id', '!=', $sipRecord->id)
                ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
                ->update(['status' => 'archived']);

            $newStatus = SipRecord::determineTrafficLightStatus($sipRecord->tgl_kadaluarsa);

            $sipRecord->update([
                'status' => $newStatus,
                'is_verified' => true,
                'verified_by' => $request->user()->id,
                'verified_at' => Carbon::now(),
                'catatan_verifikasi' => $validated['catatan_verifikasi'] ?: 'Berkas SIP Baru telah diperiksa dan dinyatakan sah/lengkap oleh Admin SDM.',
            ]);

            // Mark admin submission notifications for this SIP record as read
            NotificationLog::where('sip_record_id', $sipRecord->id)
                ->where('message_type', 'pengajuan_sip_baru')
                ->where('status', 'sent')
                ->update([
                    'status' => 'read',
                    'read_at' => Carbon::now(),
                ]);

            $notificationService->notifyVerificationDecision(
                sipRecord: $sipRecord,
                approved: true,
                verifier: $request->user(),
                catatan: $sipRecord->catatan_verifikasi
            );
        });

        return back()->with(
            'success',
            sprintf(
                'Pengajuan SIP Baru milik %s (No: %s) berhasil DISETUJUI! Status kepatuhan diperbarui dan Web Notification telah dikirim ke dokter.',
                $sipRecord->dokter->nama_lengkap,
                $sipRecord->no_sip
            )
        );
    }

    /**
     * Reject a doctor's SIP renewal submission and request revision.
     */
    public function reject(
        Request $request,
        SipRecord $sipRecord,
        WebNotificationService $notificationService
    ): RedirectResponse {
        if ($sipRecord->status !== 'menunggu_verifikasi') {
            return back()->with('error', 'Berkas pengajuan SIP ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan_verifikasi.required' => 'Alasan penolakan / catatan revisi wajib diisi agar dokter mengetahui bagian yang perlu diperbaiki.',
        ]);

        $sipRecord->load(['dokter.user']);

        DB::transaction(function () use ($request, $sipRecord, $validated, $notificationService) {
            $sipRecord->update([
                'status' => 'ditolak',
                'is_verified' => false,
                'verified_by' => $request->user()->id,
                'verified_at' => Carbon::now(),
                'catatan_verifikasi' => $validated['catatan_verifikasi'],
            ]);

            // Mark admin submission notifications for this SIP record as read
            NotificationLog::where('sip_record_id', $sipRecord->id)
                ->where('message_type', 'pengajuan_sip_baru')
                ->where('status', 'sent')
                ->update([
                    'status' => 'read',
                    'read_at' => Carbon::now(),
                ]);

            $notificationService->notifyVerificationDecision(
                sipRecord: $sipRecord,
                approved: false,
                verifier: $request->user(),
                catatan: $validated['catatan_verifikasi']
            );
        });

        return back()->with(
            'success',
            sprintf(
                'Pengajuan SIP milik %s telah DITOLAK dengan catatan revisi. Web Notification alasan penolakan telah dikirim ke dokter.',
                $sipRecord->dokter->nama_lengkap
            )
        );
    }
}

