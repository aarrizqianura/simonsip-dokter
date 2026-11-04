@extends('layouts.app')

@section('title', 'Dashboard Monitoring SIP')
@section('page_title', '📊 Dashboard Monitoring & Early Warning SIP Dokter')

@section('content')
<div class="space-y-6">
    <!-- Top Executive Banner + Early Warning Cron Trigger -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-2xl p-6 text-white shadow-lg flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-2">
                <span>⚡</span> Early Warning System Berbasis Web Notification • Standar Akreditasi STARKES
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                Monitoring Legalitas Praktik Dokter RSUD Dr. H. Moch. Ansari Saleh
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Total Dokter Terdaftar: <strong class="text-white">{{ $totalDokter }} Dokter</strong> • Total SIP Aktif Terverifikasi: <strong class="text-white">{{ $totalSipAktif }} SIP</strong> • Tingkat Kepatuhan Legalitas: <strong class="text-emerald-400">{{ $persentaseKepatuhan }}%</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <form action="{{ route('dashboard.early-warning') }}" method="POST">
                @csrf
                <input type="hidden" name="force" value="1">
                <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs shadow-lg transition flex items-center gap-2">
                    <span>🔔</span> Jalankan Early Warning Check Sekarang
                </button>
            </form>

            <a href="{{ route('dokter.create') }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg transition flex items-center gap-2">
                <span>➕</span> Input Dokter Baru + Auto Akun
            </a>

            <a href="{{ route('laporan.index') }}"
               class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs transition flex items-center gap-2">
                <span>📈</span> Cetak 5 Laporan
            </a>
        </div>
    </div>

    <!-- TRAFFIC LIGHT SUMMARY WIDGETS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total SIP Aktif -->
        <a href="{{ route('dashboard') }}" class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:border-slate-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-500">Total SIP Aktif</span>
                <span class="text-lg">📋</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $totalSipAktif }}</p>
            <p class="text-[11px] font-semibold text-emerald-700 mt-1">Kepatuhan: {{ $persentaseKepatuhan }}%</p>
        </a>

        <!-- 🟢 Aman (> 90 Hari) -->
        <a href="{{ route('dashboard', ['status' => 'aktif']) }}" class="bg-white rounded-2xl p-4 border-l-4 border-l-emerald-500 border border-slate-200 shadow-sm hover:bg-emerald-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-emerald-700">🟢 Status Aman</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">&gt; 90 Hari</span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-700 mt-2">{{ $countAman }}</p>
            <p class="text-[11px] text-slate-500 mt-1">SIP berlaku normal</p>
        </a>

        <!-- 🟡 Peringatan (61-90 Hari) -->
        <a href="{{ route('dashboard', ['status' => 'warning']) }}" class="bg-white rounded-2xl p-4 border-l-4 border-l-amber-400 border border-slate-200 shadow-sm hover:bg-amber-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-amber-700">🟡 Peringatan</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">61–90 Hari</span>
            </div>
            <p class="text-2xl font-extrabold text-amber-600 mt-2">{{ $countWarning }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Notifikasi Tahap 1</p>
        </a>

        <!-- 🟠 Mendesak (31-60 Hari) -->
        <a href="{{ route('dashboard', ['status' => 'mendesak']) }}" class="bg-white rounded-2xl p-4 border-l-4 border-l-orange-500 border border-slate-200 shadow-sm hover:bg-orange-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-orange-700">🟠 Mendesak</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-800">31–60 Hari</span>
            </div>
            <p class="text-2xl font-extrabold text-orange-600 mt-2">{{ $countMendesak }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Notifikasi Tahap 2</p>
        </a>

        <!-- 🔴 Kadaluarsa (<= 30 Hari / Expired) -->
        <a href="{{ route('dashboard', ['status' => 'expired']) }}" class="bg-white rounded-2xl p-4 border-l-4 border-l-rose-600 border border-slate-200 shadow-sm hover:bg-rose-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-rose-700">🔴 Kadaluarsa</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">≤ 30 Hr / Exp</span>
            </div>
            <p class="text-2xl font-extrabold text-rose-600 mt-2">{{ $countExpired }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Web Warning Urgent</p>
        </a>

        <!-- 🔵 Menunggu Verifikasi SDM -->
        <a href="{{ route('verifikasi.index') }}" class="bg-white rounded-2xl p-4 border-l-4 border-l-sky-500 border border-slate-200 shadow-sm hover:bg-sky-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-sky-700">🔵 Antrean SDM</span>
                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-sky-100 text-sky-800">Approval</span>
            </div>
            <p class="text-2xl font-extrabold text-sky-600 mt-2">{{ $countPendingApproval }}</p>
            <p class="text-[11px] text-sky-700 font-semibold mt-1">Perlu Verifikasi SDM →</p>
        </a>
    </div>

    <!-- MAIN MONITORING TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-slate-50/60">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Tabel Monitoring Masa Berlaku Multi-SIP & STR Dokter</h3>
                <p class="text-xs text-slate-500">Menampilkan indikator Traffic Light real-time, pembedaan STR Seumur Hidup vs SIP 5 Tahun, dan aksi peringatan dini.</p>
            </div>

            <!-- Filter Bar -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari Nama Dokter / NIP / No. SIP..."
                       class="px-3 py-2 rounded-xl border border-slate-300 text-xs w-56 focus:ring-2 focus:ring-emerald-500 outline-none">

                <select name="ksm_id" class="px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    <option value="">Semua KSM</option>
                    @foreach($ksmList as $ksm)
                        <option value="{{ $ksm->id }}" @selected((string)$ksmId === (string)$ksm->id)>{{ $ksm->nama_ksm }}</option>
                    @endforeach
                </select>

                <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                    <option value="">Semua Status Traffic Light</option>
                    <option value="aktif" @selected($statusFilter === 'aktif')>🟢 Aman (&gt; 90 Hari)</option>
                    <option value="warning" @selected($statusFilter === 'warning')>🟡 Peringatan (61–90 Hari)</option>
                    <option value="mendesak" @selected($statusFilter === 'mendesak')>🟠 Mendesak (31–60 Hari)</option>
                    <option value="expired" @selected($statusFilter === 'expired')>🔴 Kadaluarsa (≤ 30 Hari)</option>
                    <option value="menunggu_verifikasi" @selected($statusFilter === 'menunggu_verifikasi')>🔵 Menunggu Verifikasi SDM</option>
                </select>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                    Filter
                </button>
                @if($search || $ksmId || $statusFilter)
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <th class="py-3.5 px-4">Dokter & NIP/NIK</th>
                        <th class="py-3.5 px-4">KSM & Spesialisasi</th>
                        <th class="py-3.5 px-4">No. STR (Masa Berlaku)</th>
                        <th class="py-3.5 px-4">No. SIP & Praktik Ke-</th>
                        <th class="py-3.5 px-4">Masa Berlaku SIP</th>
                        <th class="py-3.5 px-4 text-center">Sisa Hari</th>
                        <th class="py-3.5 px-4">Status Traffic Light</th>
                        <th class="py-3.5 px-4 text-right">Aksi & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 text-xs">
                    @forelse($sipRecords as $sip)
                        @php
                            $dokter = $sip->dokter;
                            $sisa = $sip->sisa_hari;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('dokter.show', $dokter) }}" class="font-bold text-slate-900 hover:text-emerald-700 block">
                                    {{ $dokter->nama_lengkap }}
                                </a>
                                <span class="font-mono text-[11px] text-slate-500">NIP/NIK: {{ $dokter->nip_nik }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $dokter->ksm?->nama_ksm }}</span>
                                <span class="text-[11px] text-slate-500">{{ $dokter->spesialisasi?->nama_spesialisasi }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-semibold text-slate-800 block">{{ $dokter->no_str }}</span>
                                @if($dokter->is_str_seumur_hidup)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200 mt-0.5">
                                        ♾️ STR Seumur Hidup
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-500">s/d {{ $dokter->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-900 block">{{ $sip->no_sip }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 mt-0.5">
                                    Praktik Ke-{{ $sip->tempat_praktik_ke }} (RSUD Ansari Saleh)
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-600">Terbit: <strong class="text-slate-800">{{ $sip->tgl_terbit->format('d/m/Y') }}</strong></div>
                                <div class="text-slate-600">Berakhir: <strong class="text-slate-900">{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong></div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($sisa < 0)
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-600 text-white font-extrabold text-xs">
                                        Lewat {{ abs($sisa) }} Hr
                                    </span>
                                @else
                                    <span class="font-extrabold text-sm {{ $sisa <= 30 ? 'text-rose-600' : ($sisa <= 60 ? 'text-orange-600' : ($sisa <= 90 ? 'text-amber-600' : 'text-emerald-700')) }}">
                                        {{ $sisa }} Hari
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $sip->status_badge_class }}">
                                    @if($sip->status === 'aktif') 🟢
                                    @elseif($sip->status === 'warning') 🟡
                                    @elseif($sip->status === 'mendesak') 🟠
                                    @elseif($sip->status === 'expired') 🔴
                                    @elseif($sip->status === 'menunggu_verifikasi') 🔵
                                    @endif
                                    {{ $sip->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('sip.pdf', $sip) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px]">
                                    📄 PDF
                                </a>
                                <a href="{{ route('dokter.show', $dokter) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px]">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 text-sm">
                                Tidak ada data SIP yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sipRecords->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50">
                {{ $sipRecords->links() }}
            </div>
        @endif
    </div>

    <!-- BOTTOM GRID: KSM COMPLIANCE MATRIX & RECENT WEB NOTIFICATIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- KSM Compliance Matrix -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">🏛️ Rekapitulasi Kepatuhan SIP per KSM</h3>
                    <p class="text-xs text-slate-500">Distribusi status masa berlaku SIP pada tiap Kelompok Staf Medis</p>
                </div>
                <a href="{{ route('laporan.index', ['tab' => 'kepatuhan']) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    Laporan STARKES →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold uppercase text-slate-500 border-b border-slate-200">
                            <th class="py-2.5 px-4">Kelompok Staf Medis (KSM)</th>
                            <th class="py-2.5 px-3 text-center">Dokter</th>
                            <th class="py-2.5 px-3 text-center">🟢 Aman</th>
                            <th class="py-2.5 px-3 text-center">🟡 Warn</th>
                            <th class="py-2.5 px-3 text-center">🟠 Mndsk</th>
                            <th class="py-2.5 px-3 text-center">🔴 Exp</th>
                            <th class="py-2.5 px-4 text-right">% Patuh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($ksmStats as $stat)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-4 font-bold text-slate-800">{{ $stat['nama_ksm'] }}</td>
                                <td class="py-2.5 px-3 text-center font-semibold">{{ $stat['total_dokter'] }}</td>
                                <td class="py-2.5 px-3 text-center text-emerald-700 font-bold">{{ $stat['aman'] }}</td>
                                <td class="py-2.5 px-3 text-center text-amber-600 font-bold">{{ $stat['warning'] }}</td>
                                <td class="py-2.5 px-3 text-center text-orange-600 font-bold">{{ $stat['mendesak'] }}</td>
                                <td class="py-2.5 px-3 text-center text-rose-600 font-bold">{{ $stat['expired'] }}</td>
                                <td class="py-2.5 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded font-bold {{ $stat['compliance_rate'] >= 90 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $stat['compliance_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Web Notification Audit Trail -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">🔔 Log Notifikasi Web Terbaru</h3>
                    <p class="text-xs text-slate-500">Jejak pengiriman In-App Alert & Status Baca</p>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    Pusat Notifikasi →
                </a>
            </div>
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($recentNotifications as $n)
                    <div class="p-3.5 hover:bg-slate-50 transition">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $n->type_badge_class }}">
                                {{ $n->message_type_label }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                @if($n->status === 'read')
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">✓ Dibaca</span>
                                @else
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">● Terkirim</span>
                                @endif
                                <span class="text-[11px] text-slate-400">{{ $n->sent_at?->format('d/m H:i') }}</span>
                            </div>
                        </div>
                        <p class="text-xs font-bold text-slate-900 mt-1">{{ $n->title }}</p>
                        <p class="text-[11px] text-slate-500">Penerima: <strong class="text-slate-700">{{ $n->recipient?->name }}</strong></p>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">Belum ada log notifikasi web.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

