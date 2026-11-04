<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Services\WebNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user && $user->role === 'dokter') {
            return redirect()->route('portal.dashboard');
        }

        $ksmId = $request->query('ksm_id');
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        // Refresh statuses dynamically for verified active records
        $verifiedRecords = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'dokter.user'])
            ->where('is_verified', true)
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
            ->get();

        foreach ($verifiedRecords as $rec) {
            $computed = SipRecord::determineTrafficLightStatus($rec->tgl_kadaluarsa);
            if ($rec->status !== $computed) {
                $rec->update(['status' => $computed]);
                $rec->status = $computed;
            }
        }

        $totalSipAktif = $verifiedRecords->count();
        $countAman = $verifiedRecords->where('status', 'aktif')->count();
        $countWarning = $verifiedRecords->where('status', 'warning')->count();
        $countMendesak = $verifiedRecords->where('status', 'mendesak')->count();
        $countExpired = $verifiedRecords->where('status', 'expired')->count();
        $countPendingApproval = SipRecord::where('status', 'menunggu_verifikasi')->count();
        $totalDokter = Dokter::count();

        $persentaseKepatuhan = $totalSipAktif > 0
            ? round((($countAman + $countWarning + $countMendesak) / $totalSipAktif) * 100, 1)
            : 100.0;

        // Filtered monitoring table query
        $monitoringQuery = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'dokter.user'])
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired', 'menunggu_verifikasi'])
            ->when($ksmId, function ($query) use ($ksmId) {
                $query->whereHas('dokter', fn ($q) => $q->where('ksm_id', $ksmId));
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('no_sip', 'like', "%{$search}%")
                        ->orWhereHas('dokter', function ($dq) use ($search) {
                            $dq->where('nama_lengkap', 'like', "%{$search}%")
                                ->orWhere('nip_nik', 'like', "%{$search}%")
                                ->orWhere('no_str', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("FIELD(status, 'expired', 'mendesak', 'warning', 'menunggu_verifikasi', 'aktif')")
            ->orderBy('tgl_kadaluarsa', 'asc');

        $sipRecords = $monitoringQuery->paginate(15)->withQueryString();
        $ksmList = Ksm::withCount('dokters')->orderBy('nama_ksm')->get();

        // KSM Compliance breakdown
        $ksmStats = Ksm::with(['dokters.activeSipRecords'])->get()->map(function (Ksm $ksm) {
            $sips = $ksm->dokters->flatMap->activeSipRecords;
            $total = $sips->count();
            $aman = $sips->where('status', 'aktif')->count();
            $warning = $sips->where('status', 'warning')->count();
            $mendesak = $sips->where('status', 'mendesak')->count();
            $expired = $sips->where('status', 'expired')->count();
            $valid = $aman + $warning + $mendesak;

            return [
                'nama_ksm' => $ksm->nama_ksm,
                'total_dokter' => $ksm->dokters->count(),
                'total_sip' => $total,
                'aman' => $aman,
                'warning' => $warning,
                'mendesak' => $mendesak,
                'expired' => $expired,
                'compliance_rate' => $total > 0 ? round(($valid / $total) * 100, 1) : 100.0,
            ];
        });

        $recentNotifications = NotificationLog::with(['recipient', 'sipRecord.dokter'])
            ->latest('sent_at')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'totalDokter',
            'totalSipAktif',
            'countAman',
            'countWarning',
            'countMendesak',
            'countExpired',
            'countPendingApproval',
            'persentaseKepatuhan',
            'sipRecords',
            'ksmList',
            'ksmStats',
            'recentNotifications',
            'ksmId',
            'statusFilter',
            'search'
        ));
    }

    public function triggerEarlyWarning(Request $request, WebNotificationService $notificationService): RedirectResponse
    {
        $summary = $notificationService->runEarlyWarningCheck(
            triggeredBy: $request->user(),
            forceNotify: $request->boolean('force', true)
        );

        return back()->with(
            'success',
            sprintf(
                'Early Warning Engine berhasil dijalankan! %d SIP diperiksa, %d status diperbarui, dan %d Web Notification dikirim ke Portal Dokter.',
                $summary['checked'],
                $summary['status_updated'],
                $summary['notifications_sent']
            )
        );
    }
}

