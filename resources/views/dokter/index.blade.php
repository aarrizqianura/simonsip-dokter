@extends('layouts.app')

@section('title', 'Master Data Dokter')
@section('page_title', '👨‍⚕️ Master Data Dokter & Kredensial Akun Mandiri')

@section('content')
<div class="space-y-6">
    <!-- Action Header & Bulk Import Section -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Daftar Staf Medis (Dokter & Dokter Spesialis)</h2>
            <p class="text-xs text-slate-500">Setiap pendataan dokter baru terintegrasi langsung dengan pembuatan Akun Login Portal Dokter, STR Seumur Hidup, dan Multi-SIP.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('dokter.template') }}"
               class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-300 transition flex items-center gap-1.5">
                <span>📥</span> Unduh Template CSV/Excel
            </a>

            <button type="button" onclick="document.getElementById('modal-import-dokter').classList.remove('hidden')"
                    class="px-3.5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <span>📊</span> Impor Massal Excel/CSV
            </button>

            <a href="{{ route('dokter.create') }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <span>➕</span> Input Dokter Baru + Auto Akun
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form action="{{ route('dokter.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari Nama Dokter / NIP / STR / No. SIP..."
                   class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">

            <select name="ksm_id" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                <option value="">Semua KSM</option>
                @foreach($ksmList as $ksm)
                    <option value="{{ $ksm->id }}" @selected((string)$ksmId === (string)$ksm->id)>{{ $ksm->nama_ksm }}</option>
                @endforeach
            </select>

            <select name="spesialisasi_id" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                <option value="">Semua Spesialisasi</option>
                @foreach($spesialisasiList as $sp)
                    <option value="{{ $sp->id }}" @selected((string)$spesialisasiId === (string)$sp->id)>{{ $sp->nama_spesialisasi }}</option>
                @endforeach
            </select>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs">
                    Terapkan Filter
                </button>
                @if($search || $ksmId || $spesialisasiId)
                    <a href="{{ route('dokter.index') }}" class="py-2 px-3 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Doctor Cards / Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <th class="py-3.5 px-4">Biodata Dokter & Akun Login</th>
                        <th class="py-3.5 px-4">KSM & Spesialisasi</th>
                        <th class="py-3.5 px-4">Legalitas STR</th>
                        <th class="py-3.5 px-4">Daftar Multi-SIP (Praktik Ke-1 / 2 / 3)</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($dokters as $dokter)
                        <tr class="hover:bg-slate-50/80 transition align-top">
                            <td class="py-4 px-4">
                                <a href="{{ route('dokter.show', $dokter) }}" class="font-extrabold text-sm text-slate-900 hover:text-emerald-700">
                                    {{ $dokter->nama_lengkap }}
                                </a>
                                <div class="mt-1 space-y-0.5 text-[11px] text-slate-500">
                                    <div>NIP/NIK: <span class="font-mono font-semibold text-slate-700">{{ $dokter->nip_nik }}</span></div>
                                    <div>Username Login: <span class="font-mono font-semibold text-emerald-700">{{ $dokter->user?->username }}</span></div>
                                    <div>Kontak WA: <span class="font-semibold text-slate-700">{{ $dokter->no_whatsapp }}</span></div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 font-bold text-xs">
                                    {{ $dokter->ksm?->nama_ksm }}
                                </span>
                                <p class="text-xs text-slate-600 font-medium mt-1">{{ $dokter->spesialisasi?->nama_spesialisasi }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-mono font-bold text-slate-800">{{ $dokter->no_str }}</div>
                                @if($dokter->is_str_seumur_hidup)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200 text-[10px] font-bold mt-1">
                                        ♾️ Berlaku Seumur Hidup
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-500 block mt-1">
                                        Berlaku s/d: {{ $dokter->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="space-y-2">
                                    @forelse($dokter->sipRecords->whereNotIn('status', ['archived']) as $sip)
                                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-white font-bold text-[10px]">
                                                        Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                                    </span>
                                                    <span class="font-mono font-bold text-slate-900">{{ $sip->no_sip }}</span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 mt-0.5">
                                                    Berlaku: {{ $sip->tgl_terbit->format('d/m/Y') }} s/d <strong>{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                                                    ({{ $sip->sisa_hari }} hari)
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                                    {{ $sip->status_label }}
                                                </span>
                                                <a href="{{ route('sip.pdf', $sip) }}" target="_blank" class="p-1 rounded bg-white border border-slate-200 hover:bg-slate-100 text-[11px]" title="Lihat PDF SIP">
                                                    📄
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-slate-400 italic">Belum ada SIP aktif</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('dokter.show', $dokter) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">
                                    Detail & Multi-SIP
                                </a>
                                <a href="{{ route('dokter.edit', $dokter) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">Belum ada data dokter terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dokters->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50">
                {{ $dokters->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL IMPOR MASSAL EXCEL / CSV -->
<div id="modal-import-dokter" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold">📊 Impor Massal Data Dokter & Akun Otomatis</h3>
                <p class="text-xs text-slate-400">Unggah file CSV dari template Excel untuk membuat data dokter, akun login, & SIP awal sekaligus.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-dokter').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">✕</button>
        </div>

        <form action="{{ route('dokter.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-900 space-y-1">
                <p class="font-bold">Petunjuk Impor Massal:</p>
                <p>1. Unduh <a href="{{ route('dokter.template') }}" class="underline font-bold">Template CSV/Excel Resmi SIMONSIP</a>.</p>
                <p>2. Isi kolom NIP/NIK, Nama Dokter, KSM, Spesialisasi, No. STR, dan Nomor SIP Awal.</p>
                <p>3. Sistem otomatis membuat akun login dokter (Username = NIP/NIK) dan menerbitkan Web Notification sambutan.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Pilih File CSV (.csv)</label>
                <input type="file" name="file_import" accept=".csv,.txt" required
                       class="w-full text-xs border border-slate-300 rounded-xl p-2.5 bg-slate-50">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-import-dokter').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                    Proses Impor & Buat Akun Otomatis
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

