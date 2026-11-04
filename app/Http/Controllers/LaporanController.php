<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Hub 5 Modul Laporan Eksekutif SIMONSIP.
     */
    public function index(Request $request): View|Response
    {
        $tab = $request->query('tab', 'kepatuhan');
        if (! in_array($tab, ['kepatuhan', 'notifikasi', 'verifikasi', 'histori', 'sla'], true)) {
            $tab = 'kepatuhan';
        }

        $ksmId = $request->query('ksm_id');
        $dokterId = $request->query('dokter_id');
        $statusFilter = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $export = $request->query('export');

        $ksmList = Ksm::orderBy('nama_ksm')->get();
        $dokterList = Dokter::orderBy('nama_lengkap')->get();

        $reportData = match ($tab) {
            'kepatuhan' => $this->buildLaporanKepatuhan($ksmId, $statusFilter),
            'notifikasi' => $this->buildLaporanNotifikasi($ksmId, $statusFilter, $startDate, $endDate),
            'verifikasi' => $this->buildLaporanVerifikasi($ksmId, $statusFilter, $startDate, $endDate),
            'histori' => $this->buildLaporanHistori($ksmId, $dokterId, $statusFilter),
            'sla' => $this->buildLaporanSla($ksmId, $statusFilter, $startDate, $endDate),
        };

        $shared = array_merge($reportData, [
            'tab' => $tab,
            'ksmList' => $ksmList,
            'dokterList' => $dokterList,
            'ksmId' => $ksmId,
            'dokterId' => $dokterId,
            'statusFilter' => $statusFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedKsm' => $ksmId ? $ksmList->firstWhere('id', (int) $ksmId) : null,
            'printedAt' => Carbon::now()->format('d/m/Y H:i') . ' WITA',
        ]);

        if ($export === 'print') {
            return view('laporan.print', $shared);
        }

        if ($export === 'excel') {
            return $this->exportExcel($tab, $shared);
        }

        return view('laporan.index', $shared);
    }

    /**
     * 1. Laporan Kepatuhan Legalitas SIP Dokter (STARKES / Akreditasi)
     *
     * @return array<string, mixed>
     */
    private function buildLaporanKepatuhan(?string $ksmId, ?string $statusFilter): array
    {
        $baseQuery = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi'])
            ->where('is_verified', true)
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired'])
            ->when($ksmId, fn ($q) => $q->whereHas('dokter', fn ($dq) => $dq->where('ksm_id', $ksmId)));

        $allActive = (clone $baseQuery)->get();

        foreach ($allActive as $sip) {
            $computed = SipRecord::determineTrafficLightStatus($sip->tgl_kadaluarsa);
            if ($sip->status !== $computed) {
                $sip->update(['status' => $computed]);
                $sip->status = $computed;
            }
        }

        $totalSipAktif = $allActive->count();
        $aman = $allActive->where('status', 'aktif')->count();
        $warning = $allActive->where('status', 'warning')->count();
        $mendesak = $allActive->where('status', 'mendesak')->count();
        $expired = $allActive->where('status', 'expired')->count();
        $persentaseKepatuhan = $totalSipAktif > 0
            ? round((($aman + $warning + $mendesak) / $totalSipAktif) * 100, 1)
            : 100.0;

        $rows = $statusFilter
            ? $allActive->where('status', $statusFilter)->values()
            : $allActive->sortBy('tgl_kadaluarsa')->values();

        return [
            'reportTitle' => 'LAPORAN KEPATUHAN LEGALITAS SIP DOKTER (STARKES / AKREDITASI)',
            'reportSubtitle' => 'Pengukuran Tingkat Kepatuhan Legalitas Seluruh SIP Aktif Staf Medis RSUD Dr. H. Moch. Ansari Saleh',
            'metrics' => [
                'total_sip_aktif' => $totalSipAktif,
                'aman' => $aman,
                'warning' => $warning,
                'mendesak' => $mendesak,
                'expired' => $expired,
                'persentase_kepatuhan' => $persentaseKepatuhan,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 2. Laporan Audit Notifikasi Dini (Web Notification Audit Trail)
     *
     * @return array<string, mixed>
     */
    private function buildLaporanNotifikasi(
        ?string $ksmId,
        ?string $statusFilter,
        ?string $startDate,
        ?string $endDate
    ): array {
        $baseQuery = NotificationLog::with(['recipient.dokter.ksm', 'sender', 'sipRecord.dokter'])
            ->when($ksmId, function ($q) use ($ksmId) {
                $q->whereHas('recipient.dokter', fn ($dq) => $dq->where('ksm_id', $ksmId));
            })
            ->when($startDate, fn ($q) => $q->whereDate('sent_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sent_at', '<=', $endDate));

        $allNotifs = (clone $baseQuery)->orderByDesc('sent_at')->get();
        $totalSent = $allNotifs->count();
        $totalRead = $allNotifs->where('status', 'read')->count();
        $totalUnread = $allNotifs->where('status', 'sent')->count();
        $readRate = $totalSent > 0 ? round(($totalRead / $totalSent) * 100, 1) : 0.0;

        $rows = $statusFilter
            ? $allNotifs->where('status', $statusFilter)->values()
            : $allNotifs;

        return [
            'reportTitle' => 'LAPORAN AUDIT NOTIFIKASI DINI (WEB NOTIFICATION AUDIT TRAIL)',
            'reportSubtitle' => 'Bukti Jejak Audit Pengiriman Peringatan Dini Berbasis Web & Respons Keterbacaan Dokter',
            'metrics' => [
                'total_terkirim' => $totalSent,
                'total_dibaca' => $totalRead,
                'belum_dibaca' => $totalUnread,
                'persentase_baca' => $readRate,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 3. Laporan Audit Verifikasi Berkas (Approval Audit Log)
     *
     * @return array<string, mixed>
     */
    private function buildLaporanVerifikasi(
        ?string $ksmId,
        ?string $statusFilter,
        ?string $startDate,
        ?string $endDate
    ): array {
        $baseQuery = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifier'])
            ->when($ksmId, fn ($q) => $q->whereHas('dokter', fn ($dq) => $dq->where('ksm_id', $ksmId)))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate));

        $allRecords = (clone $baseQuery)->orderByDesc('updated_at')->get();

        $approvedCount = $allRecords->where('is_verified', true)->whereNotIn('status', ['menunggu_verifikasi', 'ditolak'])->count();
        $rejectedCount = $allRecords->where('status', 'ditolak')->count();
        $pendingCount = $allRecords->where('status', 'menunggu_verifikasi')->count();
        $totalVerified = $approvedCount + $rejectedCount;

        $rows = match ($statusFilter) {
            'approved' => $allRecords->where('is_verified', true)->whereNotIn('status', ['menunggu_verifikasi', 'ditolak'])->values(),
            'rejected' => $allRecords->where('status', 'ditolak')->values(),
            'pending' => $allRecords->where('status', 'menunggu_verifikasi')->values(),
            default => $allRecords,
        };

        return [
            'reportTitle' => 'LAPORAN AUDIT VERIFIKASI BERKAS (APPROVAL AUDIT LOG)',
            'reportSubtitle' => 'Jejak Audit Pemeriksaan Berkas SIP Dokter Beserta Keputusan & Catatan Verifikator Admin SDM',
            'metrics' => [
                'total_diverifikasi' => $totalVerified,
                'disetujui' => $approvedCount,
                'ditolak' => $rejectedCount,
                'menunggu' => $pendingCount,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 4. Laporan Histori Pengajuan SIP per Dokter (Individual Track Log)
     *
     * @return array<string, mixed>
     */
    private function buildLaporanHistori(
        ?string $ksmId,
        ?string $dokterId,
        ?string $statusFilter
    ): array {
        $totalDokter = Dokter::when($ksmId, fn ($q) => $q->where('ksm_id', $ksmId))
            ->when($dokterId, fn ($q) => $q->where('id', $dokterId))
            ->count();

        $baseQuery = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifier'])
            ->when($ksmId, fn ($q) => $q->whereHas('dokter', fn ($dq) => $dq->where('ksm_id', $ksmId)))
            ->when($dokterId, fn ($q) => $q->where('dokter_id', $dokterId));

        $allHistory = (clone $baseQuery)
            ->orderBy('dokter_id')
            ->orderBy('tempat_praktik_ke')
            ->orderByDesc('created_at')
            ->get();

        $totalRiwayat = $allHistory->count();
        $avgPerDokter = $totalDokter > 0 ? round($totalRiwayat / $totalDokter, 1) : 0.0;

        $rows = $statusFilter
            ? $allHistory->where('status', $statusFilter)->values()
            : $allHistory;

        return [
            'reportTitle' => 'LAPORAN HISTORI PENGAJUAN SIP PER DOKTER (INDIVIDUAL TRACK LOG)',
            'reportSubtitle' => 'Rekam Jejak Kronologis Berjenjang Seluruh Pengajuan & Masa Berlaku SIP Masing-Masing Dokter',
            'metrics' => [
                'total_dokter' => $totalDokter,
                'total_riwayat_sip' => $totalRiwayat,
                'rata_rata_pengajuan' => $avgPerDokter,
                'total_arsip' => $allHistory->where('status', 'archived')->count(),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 5. Laporan Rekap Tanggal Pengajuan SIP Baru (SLA Verifikasi SDM)
     *
     * @return array<string, mixed>
     */
    private function buildLaporanSla(
        ?string $ksmId,
        ?string $statusFilter,
        ?string $startDate,
        ?string $endDate
    ): array {
        $baseQuery = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifier'])
            ->when($ksmId, fn ($q) => $q->whereHas('dokter', fn ($dq) => $dq->where('ksm_id', $ksmId)))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate));

        $allSubmissions = (clone $baseQuery)->orderByDesc('created_at')->get();

        $totalPeriode = $allSubmissions->count();
        $disetujui = $allSubmissions->where('is_verified', true)->whereNotIn('status', ['menunggu_verifikasi', 'ditolak'])->count();
        $menunggu = $allSubmissions->where('status', 'menunggu_verifikasi')->count();
        $ditolak = $allSubmissions->where('status', 'ditolak')->count();

        // Calculate average SLA verification time in hours for verified records
        $verifiedItems = $allSubmissions->filter(fn (SipRecord $s) => $s->verified_at !== null && $s->created_at !== null);
        $avgMinutes = $verifiedItems->count() > 0
            ? $verifiedItems->avg(fn (SipRecord $s) => max(1, (int) $s->created_at->diffInMinutes($s->verified_at)))
            : 0;

        if ($avgMinutes < 60) {
            $avgSlaLabel = round($avgMinutes) . ' Menit';
        } elseif ($avgMinutes < 1440) {
            $avgSlaLabel = round($avgMinutes / 60, 1) . ' Jam';
        } else {
            $avgSlaLabel = round($avgMinutes / 1440, 1) . ' Hari';
        }

        $rows = $statusFilter
            ? $allSubmissions->where('status', $statusFilter)->values()
            : $allSubmissions;

        return [
            'reportTitle' => 'LAPORAN REKAP TANGGAL PENGAJUAN SIP BARU (SLA VERIFIKASI SDM)',
            'reportSubtitle' => 'Rekapitulasi Aktivitas Pengajuan SIP Baru & Evaluasi Kecepatan Layanan Verifikasi SDM (SLA)',
            'metrics' => [
                'total_pengajuan' => $totalPeriode,
                'disetujui' => $disetujui,
                'menunggu' => $menunggu,
                'ditolak' => $ditolak,
                'rata_rata_sla' => $avgSlaLabel,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Export any of the 5 reports to a clean Excel (.xls) document with official RSUD header.
     *
     * @param  array<string, mixed>  $data
     */
    private function exportExcel(string $tab, array $data): Response
    {
        $filename = 'Laporan_SIMONSIP_' . strtoupper($tab) . '_' . Carbon::now()->format('Ymd_His') . '.xls';
        $html = view('laporan.excel', $data)->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}

