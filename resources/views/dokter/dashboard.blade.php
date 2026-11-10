@extends('layouts.dokter')

@section('content')

@if(! $dokter)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-900 shadow-sm">
        <h2 class="text-base font-extrabold">⚠️ Profil Biodata Dokter Belum Terhubung</h2>
        <p class="text-xs mt-1">
            Akun Anda (<strong>{{ auth()->user()->name }}</strong>) belum memiliki data profil dokter dan SIP yang terhubung. Silakan hubungi Subbag Kepegawaian (Admin SDM) untuk melengkapi pendataan awal dokter melalui menu Master Data Dokter.
        </p>
    </div>
@else
@php
    $utamaStatusBadge = match($sipUtama?->status) {
        'aktif' => ['class' => 'bg-emerald-100 text-emerald-800', 'label' => '🟢 Aman (>90 Hari)', 'box' => 'bg-emerald-600'],
        'warning' => ['class' => 'bg-amber-100 text-amber-800', 'label' => '🟡 Peringatan (61-90 Hari)', 'box' => 'bg-amber-500'],
        'mendesak' => ['class' => 'bg-orange-100 text-orange-800', 'label' => '🟠 Mendesak (31-60 Hari)', 'box' => 'bg-orange-600'],
        'expired' => ['class' => 'bg-rose-100 text-rose-800', 'label' => '🔴 Kadaluarsa (<=30 Hari)', 'box' => 'bg-rose-600'],
        default => ['class' => 'bg-slate-100 text-slate-600', 'label' => '⚪ Belum Ada SIP Aktif Terverifikasi', 'box' => 'bg-slate-500'],
    };
@endphp

<!-- Header Banner Identitas Dokter -->
<div class="bg-emerald-900 rounded-2xl p-6 text-white mb-8 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <div class="flex items-center space-x-2 mb-2">
            <span class="px-2.5 py-0.5 bg-emerald-800 text-emerald-200 text-xs font-semibold rounded-full">
                {{ $dokter->ksm?->nama_ksm ?? '-' }} • {{ $dokter->spesialisasi?->nama_spesialisasi ?? '-' }}
            </span>
        </div>
        <h2 class="text-2xl font-extrabold">{{ $dokter->nama_lengkap }}</h2>
        <p class="text-xs text-emerald-200 mt-1">
            NIP/NIK: {{ $dokter->nip_nik }} • No. STR: {{ $dokter->no_str }} 
            @if($dokter->is_str_seumur_hidup)
                <span class="inline-block ml-2 px-2 py-0.5 bg-emerald-700 text-white rounded text-[10px]">STR Seumur Hidup</span>
            @endif
        </p>
    </div>
    <a href="{{ route('dokter.renewal') }}" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs rounded-xl shadow-sm transition shrink-0">
        + Unggah Perpanjangan SIP (1-Step Renewal)
    </a>
</div>

<!-- Pending Verification Alert if any -->
@if(isset($pendingSips) && $pendingSips->count() > 0)
<div class="mb-6 p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 flex flex-wrap items-center justify-between gap-3 shadow-sm">
    <div class="flex items-center gap-3">
        <span class="text-xl">🔵</span>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-sky-700">Status Sementara: Menunggu Verifikasi SDM</p>
            <p class="text-xs sm:text-sm font-semibold">
                Anda memiliki {{ $pendingSips->count() }} pengajuan berkas SIP baru yang sedang diperiksa oleh Admin SDM.
            </p>
        </div>
    </div>
    <a href="{{ route('dokter.riwayat') }}" class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition">
        Pantau Status Verifikasi →
    </a>
</div>
@endif

<!-- Grid Countdown & Status Utama -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Card Countdown SIP Utama (Praktik Ke-1) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm md:col-span-2">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-4 pb-2 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">
                SIP Praktik Utama (RSUD DR. H. MOCH. ANSARI SALEH)
            </h3>
            <span class="px-3 py-1 {{ $utamaStatusBadge['class'] }} text-xs font-bold rounded-full">{{ $utamaStatusBadge['label'] }}</span>
        </div>

        @if($sipUtama)
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="w-32 h-32 {{ $utamaStatusBadge['box'] }} rounded-2xl flex flex-col justify-center items-center text-white shadow-inner shrink-0">
                    @if($sipUtama->sisa_hari < 0)
                        <span class="text-2xl font-black">LEWAT {{ abs($sipUtama->sisa_hari) }}</span>
                        <span class="text-[10px] tracking-widest font-semibold uppercase">HARI</span>
                    @else
                        <span class="text-3xl font-black">{{ $sipUtama->sisa_hari }}</span>
                        <span class="text-[10px] tracking-widest font-semibold uppercase">HARI LAGI</span>
                    @endif
                </div>
                
                <div class="space-y-1 text-xs text-slate-600">
                    <p class="text-slate-400 font-medium">Nomor SIP Resmi:</p>
                    <p class="text-sm font-bold text-slate-900">{{ $sipUtama->no_sip }}</p>
                    <p class="mt-2">
                        <span class="text-slate-400">Masa Berlaku:</span>
                        {{ $sipUtama->tgl_terbit?->format('Y-m-d') ?? '-' }} — <strong>{{ $sipUtama->tgl_kadaluarsa?->format('Y-m-d') ?? '-' }}</strong>
                    </p>
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <p class="text-emerald-600 font-semibold flex items-center">
                            ✓ Terdaftar Resmi di Subbag Kepegawaian
                        </p>
                        <a href="{{ route('sip.pdf', $sipUtama) }}" target="_blank" class="text-slate-500 hover:text-emerald-700 font-semibold underline">
                            📄 Lihat PDF SIP Utama
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="py-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <p class="text-xs font-bold text-slate-700">Belum ada SIP aktif terverifikasi yang tercatat.</p>
                <p class="text-[11px] text-slate-500 mt-1 mb-3">Silakan unggah berkas SIP Anda atau pantau status verifikasi pengajuan Anda.</p>
                <a href="{{ route('dokter.renewal') }}" class="inline-block px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl">
                    + Unggah Berkas SIP Sekarang
                </a>
            </div>
        @endif
    </div>

    <!-- Widget Informasi Web Alert Status -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase">EARLY WARNING SYSTEM</h3>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Web Alert Active</span>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Peringatan dini otomatis dikirimkan melalui notifikasi web pada:
            </p>
            <div class="text-[11px] space-y-1.5 text-slate-600">
                <p>• H-90 hari (🟡 Peringatan Dini Tahap 1)</p>
                <p>• H-60 hari (🟠 Status Mendesak Tahap 2)</p>
                <p>• H-30 hari (🔴 Status Kritis / Kadaluarsa)</p>
            </div>
        </div>
    </div>
</div>

<!-- Digital Preview Kartu Multi-SIP -->
<h3 class="text-base font-bold text-slate-800 mb-4">Digital Preview Kartu SIP Saya</h3>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    @for($i = 1; $i <= 3; $i++)
        @php
            $sip = $sips->firstWhere('tempat_praktik_ke', $i);
            $pendingForPlace = $dokter->sipRecords->where('tempat_praktik_ke', $i)->firstWhere('status', 'menunggu_verifikasi');
            $rejectedForPlace = ! $sip && ! $pendingForPlace
                ? $dokter->sipRecords->where('tempat_praktik_ke', $i)->firstWhere('status', 'ditolak')
                : null;
            $cardBadge = match($sip?->status) {
                'warning' => ['class' => 'bg-amber-100 text-amber-800', 'label' => '🟡 Peringatan'],
                'mendesak' => ['class' => 'bg-orange-100 text-orange-800', 'label' => '🟠 Mendesak'],
                'expired' => ['class' => 'bg-rose-100 text-rose-800', 'label' => '🔴 Kadaluarsa'],
                default => ['class' => 'bg-emerald-100 text-emerald-800', 'label' => '🟢 Aman'],
            };
        @endphp
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold text-xs rounded-lg">Praktik Ke-{{ $i }}</span>
                    @if($sip)
                        <span class="px-2 py-0.5 {{ $cardBadge['class'] }} font-bold text-[10px] rounded-full">{{ $cardBadge['label'] }}</span>
                    @elseif($pendingForPlace)
                        <span class="px-2 py-0.5 bg-sky-100 text-sky-800 font-bold text-[10px] rounded-full">🔵 Menunggu Verifikasi</span>
                    @elseif($rejectedForPlace)
                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold text-[10px] rounded-full">❌ Perlu Revisi</span>
                    @else
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-400 font-bold text-[10px] rounded-full">Belum Ada</span>
                    @endif
                </div>

                @if($sip)
                    <p class="text-[11px] text-slate-400 font-medium">Nomor SIP:</p>
                    <p class="text-xs font-bold text-slate-800 mb-2">{{ $sip->no_sip }}</p>
                    <p class="text-[11px] text-slate-500">Masa Berlaku: {{ $sip->tgl_kadaluarsa?->format('Y-m-d') ?? $sip->tgl_kadaluarsa }}</p>
                    @if($pendingForPlace)
                        <p class="mt-2 px-2.5 py-1.5 rounded-lg bg-sky-50 border border-sky-200 text-[11px] text-sky-800 font-semibold">
                            🔵 Pengajuan baru ({{ $pendingForPlace->no_sip }}) sedang diperiksa SDM.
                        </p>
                    @endif
                @elseif($pendingForPlace)
                    <p class="text-[11px] text-sky-700 font-semibold">Nomor SIP Diajukan:</p>
                    <p class="text-xs font-bold text-slate-800 mb-1">{{ $pendingForPlace->no_sip }}</p>
                    <p class="text-[11px] text-slate-500">Sedang menunggu pemeriksaan & persetujuan Admin SDM.</p>
                @elseif($rejectedForPlace)
                    <p class="text-[11px] text-rose-700 font-semibold">Pengajuan Terakhir Ditolak:</p>
                    <p class="text-xs font-bold text-slate-800 mb-1">{{ $rejectedForPlace->no_sip }}</p>
                    <p class="text-[11px] text-rose-600 line-clamp-2">Alasan: {{ $rejectedForPlace->catatan_verifikasi }}</p>
                @else
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada SIP tempat praktik ke-{{ $i }} yang terdaftar.</p>
                @endif
            </div>

            <div class="pt-4 mt-2 border-t border-slate-100 flex justify-between items-center">
                @if($sip)
                    <a href="{{ route('sip.pdf', $sip) }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-emerald-600">📄 Lihat PDF</a>
                    @if($pendingForPlace)
                        <a href="{{ route('dokter.riwayat') }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg transition">Pantau Verifikasi</a>
                    @else
                        <a href="{{ route('dokter.renewal', ['sip_id' => $sip->id]) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition">Perpanjang</a>
                    @endif
                @elseif($pendingForPlace)
                    <a href="{{ route('sip.pdf', $pendingForPlace) }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-sky-600">📄 Lihat PDF</a>
                    <a href="{{ route('dokter.riwayat') }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg transition">Status Verifikasi</a>
                @elseif($rejectedForPlace)
                    <a href="{{ route('dokter.renewal', ['tempat_praktik_ke' => $i]) }}" class="w-full text-center py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg transition">Unggah Ulang Revisi SIP Ke-{{ $i }}</a>
                @else
                    <a href="{{ route('dokter.renewal', ['tempat_praktik_ke' => $i]) }}" class="w-full text-center py-1.5 bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 text-xs font-semibold rounded-lg transition">+ Daftarkan SIP Ke-{{ $i }}</a>
                @endif
            </div>
        </div>
    @endfor
</div>
@endif

@endsection

