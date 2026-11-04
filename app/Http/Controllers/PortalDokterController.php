<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\SipRecord;
use App\Models\User;
use App\Services\SipPdfGeneratorService;
use App\Services\WebNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalDokterController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $dokter = Dokter::with([
            'ksm',
            'spesialisasi',
            'sipRecords' => fn ($q) => $q->with('verifier')->orderBy('tempat_praktik_ke')->orderByDesc('created_at'),
        ])->where('user_id', $user->id)->first();

        if (! $dokter && $user->isAdminOrSuperAdmin()) {
            return redirect()->route('dashboard');
        }

        // Refresh statuses for verified records
        if ($dokter) {
            foreach ($dokter->sipRecords as $sip) {
                if ($sip->is_verified && in_array($sip->status, ['aktif', 'warning', 'mendesak', 'expired'], true)) {
                    $newStatus = SipRecord::determineTrafficLightStatus($sip->tgl_kadaluarsa);
                    if ($sip->status !== $newStatus) {
                        $sip->update(['status' => $newStatus]);
                        $sip->status = $newStatus;
                    }
                }
            }
        }

        $activeSipsByPlace = collect([1, 2, 3])->mapWithKeys(function (int $place) use ($dokter) {
            $sip = $dokter?->sipRecords
                ->where('tempat_praktik_ke', $place)
                ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
                ->first();

            return [$place => $sip];
        });

        $pendingSips = $dokter
            ? $dokter->sipRecords->where('status', 'menunggu_verifikasi')
            : collect();

        $notifications = $user->notificationLogs()
            ->latest('sent_at')
            ->limit(8)
            ->get();

        return view('portal.dashboard', compact(
            'dokter',
            'activeSipsByPlace',
            'pendingSips',
            'notifications'
        ));
    }

    public function createRenewal(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $dokter = Dokter::with(['ksm', 'spesialisasi', 'sipRecords'])
            ->where('user_id', $user->id)
            ->first();

        if (! $dokter) {
            return redirect()->route('dashboard')->with('error', 'Profil dokter tidak ditemukan pada akun ini.');
        }

        $selectedSip = null;
        if ($request->filled('sip_id')) {
            $selectedSip = $dokter->sipRecords->firstWhere('id', (int) $request->query('sip_id'));
        }

        return view('portal.renewal', compact('dokter', 'selectedSip'));
    }

    /**
     * Handle 1-Step Renewal submission by Doctor -> Status: menunggu_verifikasi + Web Notification to SDM.
     */
    public function storeRenewal(
        Request $request,
        WebNotificationService $notificationService,
        SipPdfGeneratorService $pdfGenerator
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $dokter = Dokter::with(['ksm', 'spesialisasi'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'tempat_praktik_ke' => ['required', 'integer', 'in:1,2,3'],
            'no_sip' => ['required', 'string', 'max:100'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after:tgl_terbit'],
            'file_sip' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'catatan_dokter' => ['nullable', 'string', 'max:500'],
        ], [
            'tgl_kadaluarsa.after' => 'Tanggal kadaluarsa SIP Baru harus setelah tanggal terbit.',
        ]);

        $sipRecord = DB::transaction(function () use ($request, $user, $dokter, $validated, $notificationService, $pdfGenerator) {
            if ($request->hasFile('file_sip')) {
                $filePath = $request->file('file_sip')->store('sip_files', 'public');
            } else {
                $safeName = Str::slug($dokter->nama_lengkap) . '-renewal-sip-' . $validated['tempat_praktik_ke'] . '-' . time() . '.pdf';
                $filePath = $pdfGenerator->generateSampleSipPdf(
                    filename: $safeName,
                    namaDokter: $dokter->nama_lengkap,
                    nipNik: $dokter->nip_nik,
                    noStr: $dokter->no_str,
                    noSip: $validated['no_sip'],
                    tempatPraktikKe: (int) $validated['tempat_praktik_ke'],
                    tglTerbit: Carbon::parse($validated['tgl_terbit'])->format('d/m/Y'),
                    tglKadaluarsa: Carbon::parse($validated['tgl_kadaluarsa'])->format('d/m/Y'),
                    spesialisasi: $dokter->spesialisasi?->nama_spesialisasi ?? 'Dokter Spesialis'
                );
            }

            $sipRecord = SipRecord::create([
                'dokter_id' => $dokter->id,
                'no_sip' => $validated['no_sip'],
                'tempat_praktik_ke' => (int) $validated['tempat_praktik_ke'],
                'tgl_terbit' => $validated['tgl_terbit'],
                'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
                'file_sip_path' => $filePath,
                'status' => 'menunggu_verifikasi',
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
                'catatan_verifikasi' => $validated['catatan_dokter']
                    ? 'Catatan Pengajuan Dokter: ' . $validated['catatan_dokter']
                    : 'Pengajuan 1-Step Renewal dari Portal Mandiri Dokter (Menunggu Verifikasi SDM).',
            ]);

            $notificationService->notifyAdminsOfNewSubmission($sipRecord, $user);

            return $sipRecord;
        });

        return redirect()
            ->route('portal.riwayat')
            ->with(
                'success',
                sprintf(
                    'Berkas Perpanjangan SIP Praktik Ke-%d (No: %s) berhasil diunggah! Status saat ini: Menunggu Verifikasi SDM dan notifikasi telah dikirim ke Topbar Admin SDM.',
                    $sipRecord->tempat_praktik_ke,
                    $sipRecord->no_sip
                )
            );
    }

    public function riwayat(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $dokter = Dokter::with([
            'ksm',
            'spesialisasi',
            'sipRecords' => fn ($q) => $q->with('verifier')->orderByDesc('created_at'),
        ])->where('user_id', $user->id)->first();

        if (! $dokter) {
            return redirect()->route('dashboard');
        }

        return view('portal.riwayat', compact('dokter'));
    }
}

