@extends('layouts.app')

@section('title', 'Profil & Multi-SIP Dokter')
@section('page_title', '👨‍⚕️ Detail Profil, Multi-SIP & Log Notifikasi Dokter')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('dokter.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
            ← Kembali ke Daftar Master Dokter
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('dokter.edit', $dokter) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                ✏️ Edit Biodata & Akun Login
            </a>
            <form action="{{ route('dokter.destroy', $dokter) }}" method="POST" onsubmit="return confirm('Hapus data dokter beserta akun loginnya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold">
                    🗑️ Hapus Dokter
                </button>
            </form>
        </div>
    </div>

    <!-- Doctor Profile Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-3">
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                <span>🏥</span> {{ $dokter->ksm?->nama_ksm }} • {{ $dokter->spesialisasi?->nama_spesialisasi }}
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900">{{ $dokter->nama_lengkap }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 block">NIP / NIK</span>
                    <strong class="font-mono text-slate-900 text-sm">{{ $dokter->nip_nik }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 block">Username Portal Dokter</span>
                    <strong class="font-mono text-emerald-700 text-sm">{{ $dokter->user?->username }}</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 block">No. Telepon / WhatsApp</span>
                    <strong class="text-slate-900 text-sm">{{ $dokter->no_whatsapp }}</strong>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 p-4 rounded-2xl bg-gradient-to-br from-teal-900 to-slate-900 text-white flex flex-col justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-300">Data Surat Tanda Registrasi (STR)</span>
                <p class="font-mono text-lg font-extrabold mt-1">{{ $dokter->no_str }}</p>
                @if($dokter->is_str_seumur_hidup)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold mt-2">
                        ♾️ Berlaku Seumur Hidup
                    </span>
                @else
                    <p class="text-xs text-teal-200 mt-2">Berlaku s/d: {{ $dokter->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-' }}</p>
                @endif
            </div>
            <p class="text-[11px] text-slate-300 mt-4 pt-3 border-t border-white/10">
                Catatan Regulasi: STR berlaku seumur hidup, sedangkan <strong>SIP wajib diperbarui setiap 5 tahun</strong>.
            </p>
        </div>
    </div>

    <!-- Grid: Multi-SIP Records & Add New SIP Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Multi-SIP List -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">📄 Daftar Multi-SIP & Riwayat Masa Berlaku</h3>
                    <p class="text-xs text-slate-500">Mencakup SIP Praktik Ke-1, Praktik Ke-2, Praktik Ke-3, serta arsip SIP lama</p>
                </div>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($dokter->sipRecords as $sip)
                    <div class="p-4 hover:bg-slate-50 transition flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-bold text-xs">
                                    Praktik Ke-{{ $sip->tempat_praktik_ke }}
                                </span>
                                <span class="font-mono font-extrabold text-sm text-slate-900">{{ $sip->no_sip }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $sip->status_badge_class }}">
                                    {{ $sip->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600">
                                Tanggal Terbit: <strong>{{ $sip->tgl_terbit->format('d/m/Y') }}</strong> •
                                Tanggal Kadaluarsa: <strong>{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</strong> •
                                Sisa Waktu: <strong class="{{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $sip->sisa_hari }} Hari</strong>
                            </div>
                            @if($sip->catatan_verifikasi)
                                <p class="text-[11px] text-slate-500 italic">Catatan: "{{ $sip->catatan_verifikasi }}"</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('sip.pdf', $sip) }}" target="_blank"
                               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-300">
                                📄 Lihat PDF SIP
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">Belum ada record SIP untuk dokter ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Right: Form Tambah Multi-SIP (Praktik Ke-1 / 2 / 3) -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-fit">
            <div class="px-5 py-4 bg-slate-900 text-white">
                <h3 class="text-sm font-extrabold">➕ Tambah / Perbarui Multi-SIP</h3>
                <p class="text-xs text-slate-400">Input SIP Praktik Ke-1, Ke-2, atau Ke-3 oleh Admin SDM</p>
            </div>

            <form action="{{ route('dokter.sip.store', $dokter) }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tempat Praktik Ke-</label>
                    <select name="tempat_praktik_ke" required class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                        <option value="1">Praktik Ke-1 (Utama RSUD Ansari Saleh)</option>
                        <option value="2">Praktik Ke-2</option>
                        <option value="3">Praktik Ke-3</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor SIP Baru</label>
                    <input type="text" name="no_sip" required placeholder="503/xxxx/SIP-DOK/DPMPTSP/2026"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tgl Terbit</label>
                        <input type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required
                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tgl Kadaluarsa</label>
                        <input type="date" name="tgl_kadaluarsa" value="{{ now()->addYears(5)->format('Y-m-d') }}" required
                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unggah PDF SIP (Opsional)</label>
                    <input type="file" name="file_sip" accept="application/pdf"
                           class="w-full border border-slate-300 rounded-xl p-2 bg-slate-50">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Verifikasi SDM</label>
                    <input type="text" name="catatan_verifikasi" placeholder="Berkas SIP telah diverifikasi SDM"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    Simpan & Aktifkan SIP
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

