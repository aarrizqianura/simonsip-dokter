@extends('layouts.app')

@section('title', 'Riwayat SIP & Verifikasi')
@section('page_title', '📜 Riwayat Pengajuan SIP & Catatan Verifikator SDM')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Track Log Pengajuan Berkas SIP: {{ $dokter->nama_lengkap }}</h2>
            <p class="text-xs text-slate-500">Rekam jejak kronologis seluruh SIP aktif, menunggu verifikasi, ditolak/revisi, maupun diarsipkan.</p>
        </div>
        <a href="{{ route('portal.renewal.create') }}"
           class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">
            + Unggah Perpanjangan SIP Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-600 border-b border-slate-200">
                        <th class="py-3.5 px-4">Waktu Pengajuan</th>
                        <th class="py-3.5 px-4">Praktik Ke-</th>
                        <th class="py-3.5 px-4">Nomor SIP</th>
                        <th class="py-3.5 px-4">Masa Berlaku</th>
                        <th class="py-3.5 px-4">Status Verifikasi</th>
                        <th class="py-3.5 px-4">Verifikator & Catatan SDM</th>
                        <th class="py-3.5 px-4 text-right">Berkas PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($dokter->sipRecords as $sip)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                {{ $sip->created_at?->format('d/m/Y H:i') }} WITA
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-bold text-[10px]">
                                    Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $sip->no_sip }}
                            </td>
                            <td class="py-3.5 px-4">
                                {{ $sip->tgl_terbit->format('d/m/Y') }} s/d <strong>{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $sip->status_badge_class }}">
                                    {{ $sip->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $sip->verifier?->name ?? ($sip->status === 'menunggu_verifikasi' ? 'Sedang Diperiksa SDM' : '-') }}
                                </div>
                                @if($sip->catatan_verifikasi)
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $sip->catatan_verifikasi }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('sip.pdf', $sip) }}" target="_blank"
                                   class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-[11px]">
                                    📄 Buka PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Belum ada riwayat SIP.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

