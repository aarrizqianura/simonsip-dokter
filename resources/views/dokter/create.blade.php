@extends('layouts.app')

@section('title', 'Form Input Data Dokter Terpadu')
@section('page_title', '➕ Form Input Data Dokter Terpadu (+ Pembuatan Akun Login Otomatis)')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('dokter.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            ← Kembali ke Master Data Dokter
        </a>
        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
            Single-Step Integrated Form (Biodata + Akun Login + SIP Awal)
        </span>
    </div>

    <form action="{{ route('dokter.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @csrf

        <!-- HEADER BANNER -->
        <div class="px-6 py-4 bg-slate-900 text-white border-b border-slate-800">
            <h2 class="text-base font-extrabold tracking-wide uppercase">FORM INPUT DATA DOKTER TERPADU</h2>
            <p class="text-xs text-slate-400 mt-0.5">RSUD Dr. H. Moch. Ansari Saleh Banjarmasin • Satu langkah untuk mengaktifkan Akun Portal Dokter, Biodata STR, dan Legalitas SIP Awal.</p>
        </div>

        <div class="p-6 space-y-8">
            <!-- SECTION 1: INFORMASI AKUN LOGIN (USER CREDENTIAL) -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">SECTION 1</span>
                        <h3 class="text-sm font-extrabold text-slate-900">INFORMASI AKUN LOGIN (USER CREDENTIAL)</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white text-xs font-bold">Role: Dokter (Default Fixed)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username Login (Auto dari NIP/NIK)</label>
                        <input type="text" name="username" id="input-username" value="{{ old('username') }}"
                               placeholder="Otomatis mengikuti NIP/NIK"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[11px] text-slate-500 mt-1">Digunakan dokter untuk masuk ke Portal Mandiri.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password Default <span class="text-rose-600">*</span></label>
                        <input type="text" name="password" value="{{ old('password', 'Ansari@2026') }}" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[11px] text-slate-500 mt-1">Default: <code class="font-bold">Ansari@2026</code> (Dapat diubah).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran Akses (Role)</label>
                        <input type="text" value="Dokter (Staf Medis Mandiri)" disabled
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-600 text-xs font-semibold cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: BIODATA & SPESIALISASI DOKTER -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-4">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">SECTION 2</span>
                    <h3 class="text-sm font-extrabold text-slate-900">BIODATA & SPESIALISASI DOKTER</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NIP / NIK <span class="text-rose-600">*</span></label>
                        <input type="text" name="nip_nik" id="input-nip-nik" value="{{ old('nip_nik') }}" required
                               placeholder="Contoh: 198504122011011009"
                               oninput="syncUsernameFromNip(this.value)"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Beserta Gelar <span class="text-rose-600">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                               placeholder="Contoh: dr. H. Ahmad Fauzi, Sp.PD, FINASIM"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Staf Medis (KSM) <span class="text-rose-600">*</span></label>
                        <select name="ksm_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Pilih KSM --</option>
                            @foreach($ksmList as $ksm)
                                <option value="{{ $ksm->id }}" @selected(old('ksm_id') == $ksm->id)>{{ $ksm->nama_ksm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Spesialisasi <span class="text-rose-600">*</span></label>
                        <select name="spesialisasi_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Pilih Spesialisasi --</option>
                            @foreach($spesialisasiList as $sp)
                                <option value="{{ $sp->id }}" @selected(old('spesialisasi_id') == $sp->id)>{{ $sp->nama_spesialisasi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp Kepegawaian <span class="text-rose-600">*</span></label>
                        <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp') }}" required
                               placeholder="0812xxxxxxxx"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor STR <span class="text-rose-600">*</span></label>
                        <input type="text" name="no_str" value="{{ old('no_str') }}" required
                               placeholder="Masukkan Nomor STR Konsil Kedokteran"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                        <div class="mt-2 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-teal-800 cursor-pointer">
                                <input type="checkbox" name="is_str_seumur_hidup" id="chk-str-seumur-hidup" value="1" checked
                                       onchange="toggleStrDateInput()"
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>♾️ STR Berlaku Seumur Hidup (UU Kesehatan No. 17/2023)</span>
                            </label>
                        </div>
                        <div id="wrapper-tgl-str" class="hidden mt-2">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Kadaluarsa STR (Jika Tidak Seumur Hidup)</label>
                            <input type="date" name="tgl_kadaluarsa_str" value="{{ old('tgl_kadaluarsa_str') }}"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: DATA LEGALITAS SIP AWAL (RSUD DR. H. MOCH. ANSARI SALEH) -->
            <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-200 pb-3">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">SECTION 3</span>
                        <h3 class="text-sm font-extrabold text-slate-900">DATA LEGALITAS SIP AWAL (RSUD DR. H. MOCH. ANSARI SALEH)</h3>
                    </div>
                    <span class="text-xs font-semibold text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-lg">
                        Masa Berlaku SIP: 5 Tahun (Wajib Monitoring)
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SIP <span class="text-rose-600">*</span></label>
                        <input type="text" name="no_sip" value="{{ old('no_sip') }}" required
                               placeholder="Contoh: 503/0415/SIP-DOK/DPMPTSP/2026"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Praktik Ke- <span class="text-rose-600">*</span></label>
                        <div class="flex items-center gap-4 pt-1.5">
                            @foreach([1 => 'Praktik Ke-1 (Utama)', 2 => 'Praktik Ke-2', 3 => 'Praktik Ke-3'] as $val => $label)
                                <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 cursor-pointer">
                                    <input type="radio" name="tempat_praktik_ke" value="{{ $val }}" @checked(old('tempat_praktik_ke', 1) == $val)
                                           class="text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Terbit SIP <span class="text-rose-600">*</span></label>
                        <input type="date" name="tgl_terbit" id="input-tgl-terbit" value="{{ old('tgl_terbit', date('Y-m-d')) }}" required
                               onchange="autoFillFiveYears()"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Tanggal Kadaluarsa SIP <span class="text-rose-600">*</span></label>
                            <button type="button" onclick="autoFillFiveYears()" class="text-[11px] font-bold text-emerald-700 hover:underline">
                                + Set Otomatis 5 Tahun
                            </button>
                        </div>
                        <input type="date" name="tgl_kadaluarsa" id="input-tgl-kadaluarsa"
                               value="{{ old('tgl_kadaluarsa', now()->addYears(5)->format('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berkas Digital PDF SIP (Drag & Drop File PDF)</label>
                        <input type="file" name="file_sip" accept="application/pdf"
                               class="w-full text-xs border border-dashed border-emerald-400 rounded-xl p-3 bg-white">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Unggah hasil pindai (scan) PDF SIP asli (Maks. 5MB). Jika dikosongkan saat pengujian, sistem otomatis membuatkan dokumen digital PDF SIP standar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- WELCOME NOTIFICATION CHECKBOX & SUBMIT -->
            <div class="p-4 rounded-xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="send_welcome_notification" value="1" checked
                           class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500">
                    <span class="text-xs font-bold text-emerald-300">
                        🔔 Terbitkan Notifikasi Web Sambutan & Petunjuk Login Akun Baru ke Portal Dokter
                    </span>
                </label>

                <button type="submit"
                        class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs uppercase tracking-wide shadow-lg transition">
                    💾 SIMPAN DATA & AKTIFKAN AKUN
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function syncUsernameFromNip(val) {
        const clean = val.replace(/\s+/g, '');
        const userField = document.getElementById('input-username');
        if (userField) {
            userField.value = clean;
        }
    }

    function toggleStrDateInput() {
        const chk = document.getElementById('chk-str-seumur-hidup');
        const wrap = document.getElementById('wrapper-tgl-str');
        if (chk && wrap) {
            wrap.classList.toggle('hidden', chk.checked);
        }
    }

    function autoFillFiveYears() {
        const terbit = document.getElementById('input-tgl-terbit').value;
        if (!terbit) return;
        const d = new Date(terbit);
        d.setFullYear(d.getFullYear() + 5);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('input-tgl-kadaluarsa').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endpush
@endsection

