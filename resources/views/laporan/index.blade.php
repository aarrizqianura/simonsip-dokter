@extends('layouts.app')

@section('title', '5 Modul Laporan Eksekutif SIMONSIP')
@section('page_title', '📈 5 Modul Laporan Eksekutif & Analytics Compliance SIP (STARKES)')

@section('content')
<div class="space-y-6">
    <!-- 5 REPORT MODULE TABS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-2 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
        @php
            $tabs = [
                'kepatuhan' => ['num' => '1', 'label' => 'Kepatuhan Legalitas SIP (STARKES)', 'icon' => '🏥'],
                'notifikasi' => ['num' => '2', 'label' => 'Audit Notifikasi Dini (Web Trail)', 'icon' => '🔔'],
                'verifikasi' => ['num' => '3', 'label' => 'Audit Verifikasi Berkas (Approval)', 'icon' => '✅'],
                'histori' => ['num' => '4', 'label' => 'Histori Pengajuan SIP per Dokter', 'icon' => '📜'],
                'sla' => ['num' => '5', 'label' => 'Rekap Tgl Pengajuan & SLA SDM', 'icon' => '⏱️'],
            ];
        @endphp

        @foreach($tabs as $key => $info)
            <a href="{{ route('laporan.index', ['tab' => $key]) }}"
               class="p-3 rounded-xl border transition flex items-start gap-2.5 {{ $tab === $key ? 'bg-slate-900 text-white border-slate-900 shadow' : 'bg-slate-50/70 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <span class="text-base">{{ $info['icon'] }}</span>
                <div class="min-w-0">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $tab === $key ? 'text-emerald-400' : 'text-slate-400' }}">
                        Laporan {{ $info['num'] }}
                    </span>
                    <p class="text-xs font-extrabold leading-snug mt-0.5">{{ $info['label'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <!-- FILTER & EXPORT TOOLBAR -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
        <form action="{{ route('laporan.index') }}" method="GET" class="flex flex-wrap items-end gap-3 text-xs">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label class="block font-bold text-slate-600 mb-1">Filter KSM</label>
                <select name="ksm_id" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">Semua KSM RSUD Ansari Saleh</option>
                    @foreach($ksmList as $ksm)
                        <option value="{{ $ksm->id }}" @selected((string)$ksmId === (string)$ksm->id)>{{ $ksm->nama_ksm }}</option>
                    @endforeach
                </select>
            </div>

            @if($tab === 'histori')
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Filter Dokter</label>
                    <select name="dokter_id" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                        <option value="">Semua Dokter</option>
                        @foreach($dokterList as $d)
                            <option value="{{ $d->id }}" @selected((string)$dokterId === (string)$d->id)>{{ $d->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(in_array($tab, ['notifikasi', 'verifikasi', 'sla'], true))
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                </div>
            @endif

            <div>
                <label class="block font-bold text-slate-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">Semua Status</option>
                    @if($tab === 'kepatuhan')
                        <option value="aktif" @selected($statusFilter === 'aktif')>🟢 Aman (&gt;90 Hari)</option>
                        <option value="warning" @selected($statusFilter === 'warning')>🟡 Peringatan (61-90 Hari)</option>
                        <option value="mendesak" @selected($statusFilter === 'mendesak')>🟠 Mendesak (31-60 Hari)</option>
                        <option value="expired" @selected($statusFilter === 'expired')>🔴 Kadaluarsa (≤30 Hari)</option>
                    @elseif($tab === 'notifikasi')
                        <option value="read" @selected($statusFilter === 'read')>Dibaca (Read)</option>
                        <option value="sent" @selected($statusFilter === 'sent')>Terkirim / Belum Dibaca</option>
                    @elseif($tab === 'verifikasi')
                        <option value="approved" @selected($statusFilter === 'approved')>Disetujui (Approved)</option>
                        <option value="rejected" @selected($statusFilter === 'rejected')>Ditolak / Revisi (Rejected)</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Menunggu Verifikasi</option>
                    @else
                        <option value="aktif" @selected($statusFilter === 'aktif')>Aman / Aktif</option>
                        <option value="menunggu_verifikasi" @selected($statusFilter === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                        <option value="ditolak" @selected($statusFilter === 'ditolak')>Ditolak / Revisi</option>
                        <option value="archived" @selected($statusFilter === 'archived')>Diarsipkan</option>
                    @endif
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                Terapkan Filter
            </button>
            <a href="{{ route('laporan.index', ['tab' => $tab]) }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">
                Reset
            </a>
        </form>

        <!-- Export Buttons (PDF Kop RSUD & Excel) -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-sm flex items-center gap-2">
                <span>🖨️</span> Cetak / Export PDF Resmi (Kop RSUD)
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm flex items-center gap-2">
                <span>📊</span> Export Excel (.XLS)
            </a>
        </div>
    </div>

    <!-- REPORT DOCUMENT CONTAINER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Kop Resmi RSUD Preview Header -->
        <div class="p-6 border-b-4 border-double border-slate-800 bg-slate-50/50 flex items-center justify-between gap-4">
            <img src="{{ asset('images/logo-kalsel.png') }}" alt="Logo Provinsi Kalimantan Selatan" class="w-14 h-14 object-contain shrink-0 hidden sm:block">
            <div class="text-center flex-1">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-600">PEMERINTAH PROVINSI KALIMANTAN SELATAN</p>
                <h2 class="text-lg sm:text-xl font-extrabold uppercase text-slate-900 tracking-wide">
                    RUMAH SAKIT UMUM DAERAH DR. H. MOCH. ANSARI SALEH BANJARMASIN
                </h2>
                <p class="text-xs text-slate-500">
                    Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin 70123 • Telp. (0511) 3301560 • Email: rsud.ansarisaleh@kalselprov.go.id
                </p>
            </div>
            <img src="{{ asset('images/logo-rsud.avif') }}" alt="Logo RSUD Dr. H. Moch. Ansari Saleh" class="w-14 h-14 object-contain shrink-0 hidden sm:block">
        </div>

        <!-- Report Title & KPI Summary Cards -->
        <div class="p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 uppercase">{{ $reportTitle }}</h3>
                    <p class="text-xs text-slate-500">{{ $reportSubtitle }}</p>
                </div>
                <div class="text-xs text-slate-500 sm:text-right">
                    <div>KSM: <strong class="text-slate-800">{{ $selectedKsm?->nama_ksm ?? 'Seluruh KSM' }}</strong></div>
                    <div>Waktu Cetak: <strong class="text-slate-800">{{ $printedAt }}</strong></div>
                </div>
            </div>

            <!-- Dynamic KPI Metric Summary per Report Tab -->
            @if($tab === 'kepatuhan')
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total SIP Aktif</span>
                        <strong class="text-xl font-extrabold text-slate-900">{{ $metrics['total_sip_aktif'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-emerald-800 font-bold block">🟢 Aman (&gt;90 Hr)</span>
                        <strong class="text-xl font-extrabold text-emerald-700">{{ $metrics['aman'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                        <span class="text-amber-800 font-bold block">🟡 Peringatan</span>
                        <strong class="text-xl font-extrabold text-amber-700">{{ $metrics['warning'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-orange-50 border border-orange-200">
                        <span class="text-orange-800 font-bold block">🟠 Mendesak</span>
                        <strong class="text-xl font-extrabold text-orange-700">{{ $metrics['mendesak'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200">
                        <span class="text-rose-800 font-bold block">🔴 Kadaluarsa</span>
                        <strong class="text-xl font-extrabold text-rose-700">{{ $metrics['expired'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 text-white">
                        <span class="text-emerald-300 font-bold block">% Kepatuhan</span>
                        <strong class="text-xl font-extrabold">{{ $metrics['persentase_kepatuhan'] }}%</strong>
                    </div>
                </div>
            @elseif($tab === 'notifikasi')
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total Notifikasi Terkirim</span>
                        <strong class="text-xl font-extrabold text-slate-900">{{ $metrics['total_terkirim'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-emerald-800 font-bold block">Total Dibaca (Read)</span>
                        <strong class="text-xl font-extrabold text-emerald-700">{{ $metrics['total_dibaca'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                        <span class="text-amber-800 font-bold block">Belum Dibaca (Unread)</span>
                        <strong class="text-xl font-extrabold text-amber-700">{{ $metrics['belum_dibaca'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 text-white">
                        <span class="text-emerald-300 font-bold block">Persentase Respons Baca</span>
                        <strong class="text-xl font-extrabold">{{ $metrics['persentase_baca'] }}%</strong>
                    </div>
                </div>
            @elseif($tab === 'verifikasi')
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total Berkas Diverifikasi</span>
                        <strong class="text-xl font-extrabold text-slate-900">{{ $metrics['total_diverifikasi'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-emerald-800 font-bold block">Disetujui (Approved)</span>
                        <strong class="text-xl font-extrabold text-emerald-700">{{ $metrics['disetujui'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200">
                        <span class="text-rose-800 font-bold block">Ditolak / Revisi (Rejected)</span>
                        <strong class="text-xl font-extrabold text-rose-700">{{ $metrics['ditolak'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200">
                        <span class="text-sky-800 font-bold block">Menunggu Verifikasi (Pending)</span>
                        <strong class="text-xl font-extrabold text-sky-700">{{ $metrics['menunggu'] }}</strong>
                    </div>
                </div>
            @elseif($tab === 'histori')
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total Dokter Terdaftar</span>
                        <strong class="text-xl font-extrabold text-slate-900">{{ $metrics['total_dokter'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-emerald-800 font-bold block">Total Riwayat SIP</span>
                        <strong class="text-xl font-extrabold text-emerald-700">{{ $metrics['total_riwayat_sip'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total Arsip SIP Lama</span>
                        <strong class="text-xl font-extrabold text-slate-800">{{ $metrics['total_arsip'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 text-white">
                        <span class="text-emerald-300 font-bold block">Rata-rata SIP per Dokter</span>
                        <strong class="text-xl font-extrabold">{{ $metrics['rata_rata_pengajuan'] }} SIP</strong>
                    </div>
                </div>
            @elseif($tab === 'sla')
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 font-bold block">Total Pengajuan</span>
                        <strong class="text-xl font-extrabold text-slate-900">{{ $metrics['total_pengajuan'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                        <span class="text-emerald-800 font-bold block">Disetujui</span>
                        <strong class="text-xl font-extrabold text-emerald-700">{{ $metrics['disetujui'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200">
                        <span class="text-sky-800 font-bold block">Menunggu Verifikasi</span>
                        <strong class="text-xl font-extrabold text-sky-700">{{ $metrics['menunggu'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200">
                        <span class="text-rose-800 font-bold block">Ditolak</span>
                        <strong class="text-xl font-extrabold text-rose-700">{{ $metrics['ditolak'] }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 text-white">
                        <span class="text-emerald-300 font-bold block">Rata-rata SLA Verifikasi</span>
                        <strong class="text-xl font-extrabold">{{ $metrics['rata_rata_sla'] }}</strong>
                    </div>
                </div>
            @endif

            <!-- Table Data -->
            <div class="overflow-x-auto">
                @include('laporan.partials.table')
            </div>
        </div>
    </div>
</div>
@endsection

