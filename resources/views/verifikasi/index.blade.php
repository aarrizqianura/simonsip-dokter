@extends('layouts.app')

@section('title', 'Verifikasi SIP Baru (Approval Queue)')
@section('page_title', '📄 Verifikasi SIP Baru (Approval Queue SDM)')

@section('content')
<div class="space-y-6">
    <!-- Pending Queue Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-sky-900 to-slate-900 text-white flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-sky-500/20 border border-sky-400/30 text-sky-300 text-[11px] font-bold">
                        🔵 Status Sementara: Menunggu Verifikasi SDM
                    </span>
                </div>
                <h2 class="text-base font-extrabold mt-1">Antrean Pengajuan 1-Step Renewal SIP Dokter ({{ $pendingRecords->count() }} Berkas)</h2>
                <p class="text-xs text-slate-300">Periksa keabsahan dokumen PDF SIP Baru sebelum menyetujui. Saat disetujui, SIP lama pada tempat praktik yang sama otomatis diarsipkan.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($pendingRecords as $sip)
                <div class="p-6 hover:bg-slate-50/70 transition space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 border border-sky-200 text-xs font-extrabold">
                                    🔵 Menunggu Verifikasi SDM
                                </span>
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white text-xs font-bold">
                                    Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    Diajukan: <strong>{{ $sip->created_at?->format('d/m/Y H:i') }} WITA</strong> ({{ $sip->created_at?->diffForHumans() }})
                                </span>
                            </div>

                            <h3 class="text-lg font-extrabold text-slate-900">{{ $sip->dokter?->nama_lengkap }}</h3>
                            <p class="text-xs text-slate-600">
                                NIP/NIK: <span class="font-mono font-bold">{{ $sip->dokter?->nip_nik }}</span> •
                                KSM: <strong>{{ $sip->dokter?->ksm?->nama_ksm }}</strong> •
                                Spesialisasi: <strong>{{ $sip->dokter?->spesialisasi?->nama_spesialisasi }}</strong>
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">Nomor SIP Baru Diajukan</span>
                                    <strong class="font-mono text-slate-900">{{ $sip->no_sip }}</strong>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">Masa Berlaku SIP Baru</span>
                                    <strong class="text-emerald-700">{{ $sip->tgl_terbit->format('d/m/Y') }} s/d {{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">Nomor STR Dokter</span>
                                    <strong class="font-mono text-slate-800">{{ $sip->dokter?->no_str }}</strong>
                                    @if($sip->dokter?->is_str_seumur_hidup)
                                        <span class="text-[10px] text-teal-700 font-bold">(Seumur Hidup)</span>
                                    @endif
                                </div>
                            </div>

                            @if($sip->catatan_verifikasi)
                                <p class="text-xs text-slate-600 bg-amber-50/70 border border-amber-200 rounded-xl px-3 py-2 mt-2">
                                    💬 {{ $sip->catatan_verifikasi }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0">
                            <a href="{{ route('sip.pdf', $sip) }}" target="_blank"
                               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center flex items-center justify-center gap-2 shadow-sm">
                                <span>📄</span> Buka & Periksa PDF SIP Baru
                            </a>
                        </div>
                    </div>

                    <!-- Action Forms: DISETUJUI vs DITOLAK -->
                    <div class="pt-3 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Form DISETUJUI -->
                        <form action="{{ route('verifikasi.approve', $sip) }}" method="POST"
                              class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 flex flex-col justify-between gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-extrabold text-emerald-900 mb-1">
                                    ✅ AKSI 1: SETUJUI & AKTIFKAN SIP BARU
                                </label>
                                <input type="text" name="catatan_verifikasi"
                                       placeholder="Catatan verifikasi (opsional, misal: Berkas lengkap & QR valid)"
                                       class="w-full px-3 py-2 rounded-lg border border-emerald-300 text-xs bg-white">
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition">
                                ✓ SETUJUI BERKAS & KIRIM NOTIFIKASI APPROVED
                            </button>
                        </form>

                        <!-- Form DITOLAK -->
                        <form action="{{ route('verifikasi.reject', $sip) }}" method="POST"
                              class="p-4 rounded-xl bg-rose-50/70 border border-rose-200 flex flex-col justify-between gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-extrabold text-rose-900 mb-1">
                                    ❌ AKSI 2: TOLAK / MINTA REVISI BERKAS (Wajib Isi Alasan)
                                </label>
                                <input type="text" name="catatan_verifikasi" required
                                       placeholder="Tuliskan alasan penolakan / bagian berkas yang buram..."
                                       class="w-full px-3 py-2 rounded-lg border border-rose-300 text-xs bg-white">
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-sm transition">
                                ✕ TOLAK BERKAS & KIRIM NOTIFIKASI REVISI KE DOKTER
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="text-3xl mb-2">🎉</div>
                    <p class="text-sm font-extrabold text-slate-800">Tidak Ada Antrean Berkas yang Menunggu Verifikasi</p>
                    <p class="text-xs text-slate-500 mt-1">Seluruh pengajuan perpanjangan SIP dokter telah selesai diperiksa oleh Admin SDM.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Verification History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">📜 Riwayat Pemeriksaan & Keputusan Verifikator SDM Terbaru</h3>
                <p class="text-xs text-slate-500">Jejak audit persetujuan dan penolakan berkas SIP dokter</p>
            </div>
            <a href="{{ route('laporan.index', ['tab' => 'verifikasi']) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                Buka Laporan Audit Verifikasi →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-600 border-b border-slate-200">
                        <th class="py-3 px-4">Waktu Keputusan</th>
                        <th class="py-3 px-4">Nama Dokter & KSM</th>
                        <th class="py-3 px-4">No. SIP & Praktik Ke-</th>
                        <th class="py-3 px-4">Keputusan</th>
                        <th class="py-3 px-4">Verifikator SDM</th>
                        <th class="py-3 px-4">Catatan Verifikasi / Alasan</th>
                        <th class="py-3 px-4 text-right">Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($historyRecords as $rec)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                {{ $rec->verified_at?->format('d/m/Y H:i') }} WITA
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $rec->dokter?->nama_lengkap }}</span>
                                <span class="text-[11px] text-slate-500">{{ $rec->dokter?->ksm?->nama_ksm }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-slate-800 block">{{ $rec->no_sip }}</span>
                                <span class="text-[11px] text-slate-500">Praktik Ke-{{ $rec->tempat_praktik_ke }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($rec->status === 'ditolak')
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">
                                        ❌ Ditolak / Revisi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                        ✅ Disetujui ({{ $rec->status_label }})
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-700">
                                {{ $rec->verifier?->name ?? 'Sistem Otomatis' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs">
                                {{ $rec->catatan_verifikasi ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('sip.pdf', $rec) }}" target="_blank" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 font-bold text-[11px]">
                                    📄 PDF
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

