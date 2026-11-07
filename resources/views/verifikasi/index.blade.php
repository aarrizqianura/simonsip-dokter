@extends('layouts.app')

@section('title', 'Verifikasi SIP Baru (Approval Queue)')
@section('page_title', '📄 Verifikasi SIP Baru (Approval Queue SDM)')

@section('content')
<div class="space-y-6">
    <!-- Pending Queue Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header Bar with View Switcher -->
        <div class="px-6 py-4 bg-gradient-to-r from-sky-900 to-slate-900 text-white flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-sky-500/20 border border-sky-400/30 text-sky-300 text-[11px] font-bold">
                        🔵 Status Sementara: Menunggu Verifikasi SDM
                    </span>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold">
                        {{ $pendingRecords->count() }} Antrean Berkas
                    </span>
                </div>
                <h2 class="text-base font-extrabold mt-1">Antrean Pengajuan 1-Step Renewal SIP Dokter</h2>
                <p class="text-xs text-slate-300">Periksa dokumen melalui Quick Preview PDF sebelum menyetujui. Saat disetujui, SIP lama pada tempat praktik yang sama otomatis diarsipkan.</p>
            </div>

            @if($pendingRecords->isNotEmpty())
                <!-- View Mode Switcher: Tabel Kompak vs Kartu Ringkas -->
                <div class="inline-flex items-center bg-slate-800/90 p-1 rounded-xl border border-slate-700 text-xs">
                    <button type="button" id="btn-view-table" onclick="switchQueueView('table')"
                            class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 bg-sky-600 text-white shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Tabel Interaktif</span>
                    </button>
                    <button type="button" id="btn-view-cards" onclick="switchQueueView('cards')"
                            class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 text-slate-300 hover:text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Kartu Ringkas</span>
                    </button>
                </div>
            @endif
        </div>

        @if($pendingRecords->isEmpty())
            <div class="p-12 text-center">
                <div class="text-3xl mb-2">🎉</div>
                <p class="text-sm font-extrabold text-slate-800">Tidak Ada Antrean Berkas yang Menunggu Verifikasi</p>
                <p class="text-xs text-slate-500 mt-1">Seluruh pengajuan perpanjangan SIP dokter telah selesai diperiksa oleh Admin SDM.</p>
            </div>
        @else
            <!-- MODE 1: DESAIN TABEL INTERAKTIF KOMPAK (Data Table with Quick Action & Accordion) -->
            <div id="queue-table-view" class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-600 border-b border-slate-200">
                            <th class="py-3.5 px-4">Dokter & KSM</th>
                            <th class="py-3.5 px-4">Nomor SIP Baru</th>
                            <th class="py-3.5 px-4 text-center">Detail Pengajuan</th>
                            <th class="py-3.5 px-4 text-center">Preview PDF</th>
                            <th class="py-3.5 px-4 w-[360px]">Verifikasi Cepat (Catatan & Aksi)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($pendingRecords as $sip)
                            <!-- Main Interactive Row (Informasi Inti: Nama Dokter, KSM, Nomor SIP) -->
                            <tr class="hover:bg-slate-50/90 transition align-middle">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-800 font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            P{{ $sip->tempat_praktik_ke }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-sm text-slate-900 leading-snug">
                                                {{ $sip->dokter?->nama_lengkap }}
                                            </div>
                                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">
                                                {{ $sip->dokter?->ksm?->nama_ksm ?? '-' }}
                                                <span class="text-slate-400">•</span>
                                                <span class="text-slate-500 font-normal">{{ $sip->dokter?->spesialisasi?->nama_spesialisasi }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-extrabold text-slate-900 text-xs block">{{ $sip->no_sip }}</span>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[10px] font-bold">
                                            Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">
                                            {{ $sip->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Tombol Accordion untuk Detail Lengkap -->
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            onclick="toggleAccordionDetail('detail-row-{{ $sip->id }}', 'chevron-row-{{ $sip->id }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] border border-slate-200 transition">
                                        <span>Detail Lengkap</span>
                                        <svg id="chevron-row-{{ $sip->id }}" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </td>

                                <!-- Tombol Ikon Mata (Quick Preview PDF Modal) -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick="openPdfPreviewModal(this)"
                                                data-pdf-url="{{ route('sip.pdf', $sip) }}"
                                                data-dokter="{{ $sip->dokter?->nama_lengkap }}"
                                                data-nosip="{{ $sip->no_sip }}"
                                                data-praktik="Praktik Ke-{{ $sip->tempat_praktik_ke }}"
                                                title="Preview Dokumen PDF SIP Baru"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 hover:border-sky-600 font-bold text-xs transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Preview</span>
                                        </button>

                                        <button type="button"
                                                onclick="openVerificationModal(this)"
                                                data-sip-id="{{ $sip->id }}"
                                                data-approve-url="{{ route('verifikasi.approve', $sip) }}"
                                                data-reject-url="{{ route('verifikasi.reject', $sip) }}"
                                                data-pdf-url="{{ route('sip.pdf', $sip) }}"
                                                data-dokter="{{ $sip->dokter?->nama_lengkap }}"
                                                data-nip="{{ $sip->dokter?->nip_nik }}"
                                                data-ksm="{{ $sip->dokter?->ksm?->nama_ksm }}"
                                                data-spesialisasi="{{ $sip->dokter?->spesialisasi?->nama_spesialisasi }}"
                                                data-nosip="{{ $sip->no_sip }}"
                                                data-praktik="Praktik Ke-{{ $sip->tempat_praktik_ke }}"
                                                data-masaberlaku="{{ $sip->tgl_terbit->format('d/m/Y') }} s/d {{ $sip->tgl_kadaluarsa->format('d/m/Y') }}"
                                                data-nostr="{{ $sip->dokter?->no_str }}{{ $sip->dokter?->is_str_seumur_hidup ? ' (Seumur Hidup)' : '' }}"
                                                data-catatan="{{ $sip->catatan_verifikasi }}"
                                                title="Buka Panel Verifikasi & Preview"
                                                class="p-2 rounded-xl bg-slate-100 hover:bg-slate-800 text-slate-700 hover:text-white border border-slate-200 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Kolom Aksi Terpadu: Input Catatan di Atas + 2 Tombol Ringkas Berdampingan di Bawah -->
                                <td class="py-3 px-4">
                                    <form action="{{ route('verifikasi.approve', $sip) }}" method="POST"
                                          onsubmit="return validateInlineVerificationForm(this, event)"
                                          class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                        @csrf
                                        <input type="text" name="catatan_verifikasi"
                                               placeholder="Catatan verifikasi (wajib diisi jika menolak)..."
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-sky-500 outline-none">
                                        <div class="flex items-center gap-2">
                                            <button type="submit"
                                                    formaction="{{ route('verifikasi.approve', $sip) }}"
                                                    data-action-type="approve"
                                                    onclick="this.form.dataset.submitType='approve'"
                                                    class="flex-1 py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Setujui</span>
                                            </button>
                                            <button type="submit"
                                                    formaction="{{ route('verifikasi.reject', $sip) }}"
                                                    data-action-type="reject"
                                                    onclick="this.form.dataset.submitType='reject'"
                                                    class="flex-1 py-1.5 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <!-- Expandable Accordion Row (Detail Lengkap & Catatan Pengajuan) -->
                            <tr id="detail-row-{{ $sip->id }}" class="hidden bg-slate-50/80 border-b border-slate-200">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-slate-500 block text-[11px]">Masa Berlaku SIP Baru</span>
                                            <strong class="text-emerald-700">{{ $sip->tgl_terbit->format('d/m/Y') }} s/d {{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-slate-500 block text-[11px]">Nomor STR Dokter</span>
                                            <strong class="font-mono text-slate-800">{{ $sip->dokter?->no_str }}</strong>
                                            @if($sip->dokter?->is_str_seumur_hidup)
                                                <span class="text-[10px] text-teal-700 font-bold">(Seumur Hidup)</span>
                                            @endif
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-slate-500 block text-[11px]">NIP / NIK Dokter</span>
                                            <strong class="font-mono text-slate-800">{{ $sip->dokter?->nip_nik }}</strong>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-slate-500 block text-[11px]">Waktu Pengajuan</span>
                                            <strong class="text-slate-800">{{ $sip->created_at?->format('d/m/Y H:i') }} WITA</strong>
                                        </div>
                                    </div>

                                    @if($sip->catatan_verifikasi)
                                        <div class="mt-3 text-xs text-slate-700 bg-amber-50/90 border border-amber-200 rounded-xl px-3.5 py-2.5 flex items-start gap-2">
                                            <span>💬</span>
                                            <div>
                                                <span class="font-bold text-amber-900">Catatan Pengajuan Dokter:</span>
                                                <span>{{ $sip->catatan_verifikasi }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- MODE 2: TAMPILAN KARTU RINGKAS (Compact Cards with Accordion & Inline Footer Action) -->
            <div id="queue-cards-view" class="hidden p-5 grid grid-cols-1 lg:grid-cols-2 gap-4 bg-slate-50/50">
                @foreach($pendingRecords as $sip)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between overflow-hidden">
                        <!-- Card Header & Core Info (Nama Dokter, KSM, Nomor SIP + Tombol Mata Preview PDF) -->
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[10px] font-bold">
                                            Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">
                                            {{ $sip->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-extrabold text-slate-900">{{ $sip->dokter?->nama_lengkap }}</h3>
                                    <p class="text-xs text-slate-600 font-semibold">
                                        {{ $sip->dokter?->ksm?->nama_ksm }}
                                        <span class="text-slate-400 font-normal">• {{ $sip->dokter?->spesialisasi?->nama_spesialisasi }}</span>
                                    </p>
                                </div>

                                <!-- Tombol Ikon Mata (Preview PDF Modal) -->
                                <button type="button"
                                        onclick="openPdfPreviewModal(this)"
                                        data-pdf-url="{{ route('sip.pdf', $sip) }}"
                                        data-dokter="{{ $sip->dokter?->nama_lengkap }}"
                                        data-nosip="{{ $sip->no_sip }}"
                                        data-praktik="Praktik Ke-{{ $sip->tempat_praktik_ke }}"
                                        title="Quick Preview PDF SIP Baru"
                                        class="px-3 py-2 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 hover:border-sky-600 font-bold text-xs transition inline-flex items-center gap-1.5 shrink-0 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Preview PDF</span>
                                </button>
                            </div>

                            <!-- Baris Ringkas Nomor SIP & Tombol Accordion Detail -->
                            <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-2 text-xs">
                                <div>
                                    <span class="text-[11px] text-slate-500">No. SIP Baru:</span>
                                    <strong class="font-mono text-slate-900 ml-1">{{ $sip->no_sip }}</strong>
                                </div>
                                <button type="button"
                                        onclick="toggleAccordionDetail('card-detail-{{ $sip->id }}', 'card-chevron-{{ $sip->id }}')"
                                        class="text-[11px] font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1">
                                    <span>Detail Lengkap</span>
                                    <svg id="card-chevron-{{ $sip->id }}" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Accordion Content (Tersembunyi secara default) -->
                            <div id="card-detail-{{ $sip->id }}" class="hidden pt-1 space-y-2 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                        <span class="text-[11px] text-slate-500 block">Masa Berlaku SIP</span>
                                        <strong class="text-emerald-700">{{ $sip->tgl_terbit->format('d/m/Y') }} s/d {{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                        <span class="text-[11px] text-slate-500 block">Nomor STR Dokter</span>
                                        <strong class="font-mono text-slate-800">{{ $sip->dokter?->no_str }}</strong>
                                        @if($sip->dokter?->is_str_seumur_hidup)
                                            <span class="text-[10px] text-teal-700 font-bold">(Seumur Hidup)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-500 px-1">
                                    NIP/NIK: <span class="font-mono font-bold text-slate-700">{{ $sip->dokter?->nip_nik }}</span> •
                                    Diajukan: <strong>{{ $sip->created_at?->format('d/m/Y H:i') }} WITA</strong>
                                </div>
                                @if($sip->catatan_verifikasi)
                                    <p class="text-xs text-slate-700 bg-amber-50/80 border border-amber-200 rounded-xl px-3 py-2">
                                        💬 {{ $sip->catatan_verifikasi }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Unified Footer Card: Input Catatan di Atas + Dua Tombol Ringkas (Setujui & Tolak) di Bawah -->
                        <form action="{{ route('verifikasi.approve', $sip) }}" method="POST"
                              onsubmit="return validateInlineVerificationForm(this, event)"
                              class="px-4 py-3 bg-slate-50 border-t border-slate-200 space-y-2.5">
                            @csrf
                            <div>
                                <input type="text" name="catatan_verifikasi"
                                       placeholder="Catatan verifikasi (opsional saat setuju, wajib diisi jika tolak)..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-sky-500 outline-none">
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        formaction="{{ route('verifikasi.approve', $sip) }}"
                                        onclick="this.form.dataset.submitType='approve'"
                                        class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Setujui Berkas</span>
                                </button>
                                <button type="submit"
                                        formaction="{{ route('verifikasi.reject', $sip) }}"
                                        onclick="this.form.dataset.submitType='reject'"
                                        class="flex-1 py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Tolak / Revisi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
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
                                <button type="button"
                                        onclick="openPdfPreviewModal(this)"
                                        data-pdf-url="{{ route('sip.pdf', $rec) }}"
                                        data-dokter="{{ $rec->dokter?->nama_lengkap }}"
                                        data-nosip="{{ $rec->no_sip }}"
                                        data-praktik="Praktik Ke-{{ $rec->tempat_praktik_ke }}"
                                        title="Quick Preview PDF SIP"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-sky-600 text-slate-700 hover:text-white font-bold text-[11px] transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>PDF</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL 1: EMBEDDED QUICK PREVIEW PDF (Pop-up Tengah Layar) -->
<div id="modal-preview-pdf" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full h-[85vh] border border-slate-200 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-lg bg-sky-500/20 text-sky-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
                <div>
                    <h3 id="pdf-modal-title" class="text-sm font-extrabold">Quick Preview Dokumen PDF SIP</h3>
                    <p id="pdf-modal-subtitle" class="text-[11px] text-slate-300 font-mono"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="pdf-modal-external-link" href="#" target="_blank"
                   class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                    ↗ Buka di Tab Baru
                </a>
                <button type="button" onclick="closePdfPreviewModal()"
                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white font-bold transition flex items-center justify-center">
                    ✕
                </button>
            </div>
        </div>
        <div class="flex-1 bg-slate-100 relative">
            <iframe id="pdf-modal-iframe" src="about:blank" class="w-full h-full border-0" title="Preview PDF SIP"></iframe>
        </div>
    </div>
</div>

<!-- MODAL 2: QUICK ACTION VERIFICATION DRAWER / MODAL (Split Detail + PDF + Compact Action) -->
<div id="modal-quick-verify" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-5xl w-full h-[88vh] border border-slate-200 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between gap-4 shrink-0">
            <div>
                <span id="verify-modal-praktik" class="px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 text-[10px] font-bold uppercase"></span>
                <h3 id="verify-modal-dokter" class="text-base font-extrabold mt-0.5"></h3>
                <p id="verify-modal-ksm" class="text-xs text-slate-300"></p>
            </div>
            <button type="button" onclick="closeVerificationModal()"
                    class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white font-bold transition flex items-center justify-center">
                ✕
            </button>
        </div>

        <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 overflow-hidden">
            <!-- Left Column: Embedded PDF Viewer -->
            <div class="lg:col-span-7 bg-slate-100 border-b lg:border-b-0 lg:border-r border-slate-200 flex flex-col h-full">
                <div class="px-4 py-2 bg-slate-200/70 text-[11px] font-bold text-slate-700 flex items-center justify-between">
                    <span>📄 Pratinjau Dokumen PDF SIP Baru</span>
                    <a id="verify-modal-pdf-link" href="#" target="_blank" class="text-sky-700 hover:underline">Buka Layar Penuh ↗</a>
                </div>
                <iframe id="verify-modal-iframe" src="about:blank" class="w-full flex-1 border-0" title="Dokumen SIP"></iframe>
            </div>

            <!-- Right Column: Full Details & Compact Footer Action -->
            <div class="lg:col-span-5 p-5 flex flex-col justify-between overflow-y-auto space-y-4">
                <div class="space-y-3 text-xs">
                    <h4 class="font-extrabold text-slate-900 uppercase tracking-wider text-[11px]">Rincian Data Pengajuan SIP</h4>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Nomor SIP Baru</span>
                        <strong id="verify-modal-nosip" class="font-mono text-slate-900 text-sm"></strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Masa Berlaku SIP</span>
                        <strong id="verify-modal-masaberlaku" class="text-emerald-700"></strong>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">Nomor STR</span>
                            <strong id="verify-modal-nostr" class="font-mono text-slate-800"></strong>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">NIP / NIK</span>
                            <strong id="verify-modal-nip" class="font-mono text-slate-800"></strong>
                        </div>
                    </div>
                    <div id="verify-modal-catatan-box" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                        <span class="font-bold block text-[11px]">💬 Catatan Pengajuan Dokter:</span>
                        <p id="verify-modal-catatan" class="mt-0.5 text-xs"></p>
                    </div>
                </div>

                <!-- Unified Compact Action Footer inside Modal -->
                <form id="verify-modal-form" method="POST"
                      onsubmit="return validateInlineVerificationForm(this, event)"
                      class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 mb-1">
                            Catatan Verifikasi / Alasan Penolakan
                        </label>
                        <input type="text" name="catatan_verifikasi"
                               placeholder="Opsional untuk Setujui, wajib diisi minimal 5 karakter untuk Tolak..."
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" id="verify-modal-btn-approve"
                                onclick="this.form.dataset.submitType='approve'"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Setujui Berkas</span>
                        </button>
                        <button type="submit" id="verify-modal-btn-reject"
                                onclick="this.form.dataset.submitType='reject'"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-sm transition inline-flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Tolak Berkas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchQueueView(mode) {
        const tableView = document.getElementById('queue-table-view');
        const cardsView = document.getElementById('queue-cards-view');
        const btnTable = document.getElementById('btn-view-table');
        const btnCards = document.getElementById('btn-view-cards');

        if (!tableView || !cardsView) return;

        if (mode === 'cards') {
            tableView.classList.add('hidden');
            cardsView.classList.remove('hidden');
            btnCards.className = 'px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 bg-sky-600 text-white shadow-sm';
            btnTable.className = 'px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 text-slate-300 hover:text-white';
        } else {
            cardsView.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.className = 'px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 bg-sky-600 text-white shadow-sm';
            btnCards.className = 'px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 text-slate-300 hover:text-white';
        }
    }

    function toggleAccordionDetail(rowId, chevronId) {
        const row = document.getElementById(rowId);
        const chevron = document.getElementById(chevronId);
        if (row) {
            row.classList.toggle('hidden');
        }
        if (chevron) {
            chevron.classList.toggle('rotate-180');
        }
    }

    function openPdfPreviewModal(btn) {
        const modal = document.getElementById('modal-preview-pdf');
        const iframe = document.getElementById('pdf-modal-iframe');
        const title = document.getElementById('pdf-modal-title');
        const subtitle = document.getElementById('pdf-modal-subtitle');
        const externalLink = document.getElementById('pdf-modal-external-link');

        const pdfUrl = btn.getAttribute('data-pdf-url');
        const dokter = btn.getAttribute('data-dokter') || 'Dokter';
        const noSip = btn.getAttribute('data-nosip') || '-';
        const praktik = btn.getAttribute('data-praktik') || '';

        title.textContent = dokter + (praktik ? ' (' + praktik + ')' : '');
        subtitle.textContent = 'No. SIP: ' + noSip;
        iframe.src = pdfUrl;
        externalLink.href = pdfUrl;

        modal.classList.remove('hidden');
    }

    function closePdfPreviewModal() {
        const modal = document.getElementById('modal-preview-pdf');
        const iframe = document.getElementById('pdf-modal-iframe');
        if (modal) modal.classList.add('hidden');
        if (iframe) iframe.src = 'about:blank';
    }

    function openVerificationModal(btn) {
        const modal = document.getElementById('modal-quick-verify');
        const iframe = document.getElementById('verify-modal-iframe');
        const pdfLink = document.getElementById('verify-modal-pdf-link');
        const form = document.getElementById('verify-modal-form');
        const btnApprove = document.getElementById('verify-modal-btn-approve');
        const btnReject = document.getElementById('verify-modal-btn-reject');

        const pdfUrl = btn.getAttribute('data-pdf-url');
        const approveUrl = btn.getAttribute('data-approve-url');
        const rejectUrl = btn.getAttribute('data-reject-url');

        document.getElementById('verify-modal-praktik').textContent = btn.getAttribute('data-praktik') || '';
        document.getElementById('verify-modal-dokter').textContent = btn.getAttribute('data-dokter') || '';
        document.getElementById('verify-modal-ksm').textContent = (btn.getAttribute('data-ksm') || '') + ' • ' + (btn.getAttribute('data-spesialisasi') || '');
        document.getElementById('verify-modal-nosip').textContent = btn.getAttribute('data-nosip') || '';
        document.getElementById('verify-modal-masaberlaku').textContent = btn.getAttribute('data-masaberlaku') || '';
        document.getElementById('verify-modal-nostr').textContent = btn.getAttribute('data-nostr') || '';
        document.getElementById('verify-modal-nip').textContent = btn.getAttribute('data-nip') || '';

        const catatan = btn.getAttribute('data-catatan') || '';
        const catatanBox = document.getElementById('verify-modal-catatan-box');
        if (catatan.trim() !== '') {
            document.getElementById('verify-modal-catatan').textContent = catatan;
            catatanBox.classList.remove('hidden');
        } else {
            catatanBox.classList.add('hidden');
        }

        iframe.src = pdfUrl;
        pdfLink.href = pdfUrl;
        form.action = approveUrl;
        btnApprove.setAttribute('formaction', approveUrl);
        btnReject.setAttribute('formaction', rejectUrl);

        modal.classList.remove('hidden');
    }

    function closeVerificationModal() {
        const modal = document.getElementById('modal-quick-verify');
        const iframe = document.getElementById('verify-modal-iframe');
        if (modal) modal.classList.add('hidden');
        if (iframe) iframe.src = 'about:blank';
    }

    function validateInlineVerificationForm(form, event) {
        const submitter = event.submitter;
        const submitType = (submitter && submitter.getAttribute('data-action-type')) || form.dataset.submitType || 'approve';
        const noteInput = form.querySelector('input[name="catatan_verifikasi"]');

        if (submitType === 'reject' && noteInput) {
            const val = noteInput.value.trim();
            if (val.length < 5) {
                event.preventDefault();
                noteInput.focus();
                noteInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-300');
                alert('Mohon isi alasan penolakan / catatan revisi (minimal 5 karakter) sebelum menolak berkas SIP.');
                return false;
            }
        }
        return true;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePdfPreviewModal();
            closeVerificationModal();
        }
    });
</script>
@endpush
@endsection

