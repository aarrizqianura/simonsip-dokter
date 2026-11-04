@extends('layouts.app')

@section('title', 'Dashboard Portal Dokter')
@section('page_title', '🏠 Portal Mandiri Dokter — Countdown Masa Berlaku SIP & STR')

@section('content')
<div class="space-y-6">
    @if($dokter)
        <!-- Top Profile & STR Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-emerald-900 rounded-2xl p-6 text-white shadow-lg flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-2">
                    <span>👨‍⚕️</span> {{ $dokter->ksm?->nama_ksm }} • {{ $dokter->spesialisasi?->nama_spesialisasi }}
                </div>
                <h2 class="text-2xl font-extrabold">{{ $dokter->nama_lengkap }}</h2>
                <p class="text-xs text-slate-300 mt-1">
                    NIP / NIK: <span class="font-mono font-bold text-white">{{ $dokter->nip_nik }}</span> •
                    No. STR: <span class="font-mono font-bold text-emerald-300">{{ $dokter->no_str }}</span>
                    @if($dokter->is_str_seumur_hidup)
                        <span class="ml-1 px-2 py-0.5 rounded bg-teal-500/30 text-teal-200 text-[10px] font-bold">♾️ STR Seumur Hidup</span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('portal.renewal.create') }}"
                   class="px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs shadow-lg transition flex items-center gap-2">
                    <span>📤</span> Unggah Perpanjangan SIP (1-Step Renewal)
                </a>
                <a href="{{ route('portal.riwayat') }}"
                   class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs transition">
                    📜 Riwayat Pengajuan Saya
                </a>
            </div>
        </div>

        <!-- Pending Verification Alert if any -->
        @if($pendingSips->count() > 0)
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🔵</span>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-sky-700">Status Sementara: Menunggu Verifikasi SDM</p>
                        <p class="text-sm font-bold">
                            Anda memiliki {{ $pendingSips->count() }} pengajuan berkas SIP baru yang sedang diperiksa oleh Admin SDM.
                        </p>
                    </div>
                </div>
                <a href="{{ route('portal.riwayat') }}" class="px-3.5 py-2 rounded-xl bg-sky-600 text-white text-xs font-bold">
                    Pantau Status Verifikasi →
                </a>
            </div>
        @endif

        <!-- MULTI-SIP COUNTDOWN CARDS (PRAKTIK KE-1, KE-2, KE-3) -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 mb-3">📋 Status Legalitas Multi-SIP Aktif (RSUD Dr. H. Moch. Ansari Saleh)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach([1, 2, 3] as $place)
                    @php
                        /** @var \App\Models\SipRecord|null $sip */
                        $sip = $activeSipsByPlace[$place] ?? null;
                    @endphp
                    <div class="bg-white rounded-2xl border {{ $sip ? 'border-slate-200' : 'border-dashed border-slate-300 bg-slate-50/60' }} p-5 shadow-sm flex flex-col justify-between">
                        @if($sip)
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-extrabold text-xs">
                                        Praktik Ke-{{ $place }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $sip->status_badge_class }}">
                                        {{ $sip->status_label }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500">Nomor SIP:</p>
                                <p class="font-mono font-extrabold text-sm text-slate-900 break-all">{{ $sip->no_sip }}</p>

                                <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] text-slate-500 block">Countdown Masa Berlaku</span>
                                        <span class="text-xs text-slate-700">Berakhir: <strong>{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xl font-extrabold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : ($sip->sisa_hari <= 60 ? 'text-orange-600' : ($sip->sisa_hari <= 90 ? 'text-amber-600' : 'text-emerald-700')) }}">
                                            {{ $sip->sisa_hari }}
                                        </span>
                                        <span class="block text-[10px] font-bold uppercase text-slate-500">Hari Lagi</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <a href="{{ route('sip.pdf', $sip) }}" target="_blank"
                                   class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs text-center">
                                    📄 Preview Digital SIP
                                </a>
                                <a href="{{ route('portal.renewal.create', ['sip_id' => $sip->id]) }}"
                                   class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center">
                                    📤 Perpanjang SIP
                                </a>
                            </div>
                        @else
                            <div class="py-6 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 font-bold text-xs">
                                    Praktik Ke-{{ $place }}
                                </span>
                                <p class="text-xs text-slate-500 mt-3">Belum ada data SIP Praktik Ke-{{ $place }} yang terdaftar.</p>
                                <a href="{{ route('portal.renewal.create') }}"
                                   class="mt-4 inline-block px-4 py-2 rounded-xl bg-white border border-slate-300 hover:border-emerald-500 text-slate-700 font-bold text-xs">
                                    + Ajukan SIP Praktik Ke-{{ $place }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bottom Grid: Web Notifications Inbox & Password Change -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">🔔 Peringatan Dini & Notifikasi Web Saya</h3>
                        <p class="text-xs text-slate-500">Pesan otomatis dari Early Warning Engine (07:00 WITA) & Verifikator SDM</p>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                        Lihat Semua →
                    </a>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse($notifications as $notif)
                        <div class="p-4 hover:bg-slate-50 transition {{ $notif->status === 'sent' ? 'bg-amber-50/30' : '' }}">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded border text-[10px] font-bold {{ $notif->type_badge_class }}">
                                    {{ $notif->message_type_label }}
                                </span>
                                <span class="text-[11px] text-slate-400">{{ $notif->sent_at?->format('d/m/Y H:i') }} WITA</span>
                            </div>
                            <p class="text-xs font-extrabold text-slate-900">{{ $notif->title }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $notif->payload }}</p>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">Belum ada notifikasi masuk.</div>
                    @endforelse
                </div>
            </div>

            <!-- Ubah Password Mandiri -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                <div class="px-5 py-4 bg-slate-900 text-white">
                    <h3 class="text-sm font-extrabold">🔐 Keamanan Akun Portal Dokter</h3>
                    <p class="text-xs text-slate-400">Ubah password login mandiri Anda</p>
                </div>
                <form action="{{ route('profile.password') }}" method="POST" class="p-5 space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Password Baru</label>
                        <input type="password" name="new_password" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                        Perbarui Password Saya
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

