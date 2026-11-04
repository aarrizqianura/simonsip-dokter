@extends('layouts.app')

@section('title', 'Unggah Perpanjangan SIP (1-Step Renewal)')
@section('page_title', '📤 Unggah Perpanjangan SIP (1-Step Renewal Form)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.dashboard') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
            ← Kembali ke Dashboard Saya
        </a>
        <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold">
            Alur Otomatis: Unggah Berkas → 🔵 Menunggu Verifikasi SDM → 🟢 Aktif
        </span>
    </div>

    <form action="{{ route('portal.renewal.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @csrf

        <div class="px-6 py-4 bg-gradient-to-r from-emerald-800 to-slate-900 text-white">
            <h2 class="text-base font-extrabold uppercase">FORMULIR 1-STEP RENEWAL (PERPANJANGAN SIP MANDIRI)</h2>
            <p class="text-xs text-emerald-200 mt-0.5">
                Dokter Pengaju: <strong>{{ $dokter->nama_lengkap }}</strong> (NIP: {{ $dokter->nip_nik }} • {{ $dokter->ksm?->nama_ksm }})
            </p>
        </div>

        <div class="p-6 space-y-5 text-xs">
            @if($selectedSip)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                    <p class="font-bold">Memperpanjang SIP Praktik Ke-{{ $selectedSip->tempat_praktik_ke }} Sebelumnya:</p>
                    <p class="mt-0.5">Nomor SIP Lama: <span class="font-mono font-bold">{{ $selectedSip->no_sip }}</span> (Berakhir: {{ $selectedSip->tgl_kadaluarsa->format('d/m/Y') }})</p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Tempat Praktik Ke- <span class="text-rose-600">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([1 => 'Praktik Ke-1 (Utama)', 2 => 'Praktik Ke-2', 3 => 'Praktik Ke-3'] as $val => $label)
                            <label class="p-3 rounded-xl border border-slate-300 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 cursor-pointer flex items-center gap-2 font-bold text-slate-800">
                                <input type="radio" name="tempat_praktik_ke" value="{{ $val }}"
                                       @checked(old('tempat_praktik_ke', $selectedSip?->tempat_praktik_ke ?? 1) == $val)
                                       class="text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nomor SIP Baru (Sesuai Dokumen DPMPTSP / MPP Digital) <span class="text-rose-600">*</span></label>
                    <input type="text" name="no_sip" value="{{ old('no_sip') }}" required
                           placeholder="Contoh: 503/0955/SIP-DOK/DPMPTSP/2026"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Terbit SIP Baru <span class="text-rose-600">*</span></label>
                    <input type="date" name="tgl_terbit" id="renewal-terbit" value="{{ old('tgl_terbit', date('Y-m-d')) }}" required
                           onchange="setRenewalFiveYears()"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700">Tanggal Kadaluarsa SIP Baru <span class="text-rose-600">*</span></label>
                        <button type="button" onclick="setRenewalFiveYears()" class="text-[11px] font-bold text-emerald-700 hover:underline">
                            +5 Tahun Otomatis
                        </button>
                    </div>
                    <input type="date" name="tgl_kadaluarsa" id="renewal-kadaluarsa"
                           value="{{ old('tgl_kadaluarsa', now()->addYears(5)->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Unggah Berkas PDF SIP Baru (Maks. 5MB)</label>
                    <input type="file" name="file_sip" accept="application/pdf"
                           class="w-full border border-dashed border-emerald-400 rounded-xl p-3.5 bg-emerald-50/40">
                    <p class="text-[11px] text-slate-500 mt-1">
                        Pastikan nomor SIP, tanda tangan elektronik / QR Code DPMPTSP terlihat jelas.
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan untuk Verifikator SDM (Opsional)</label>
                    <input type="text" name="catatan_dokter" value="{{ old('catatan_dokter') }}"
                           placeholder="Contoh: Perpanjangan SIP Praktik Ke-1 periode 2026-2031 telah terbit."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-4">
                <p class="text-[11px] text-slate-500">
                    Begitu dikirim, Admin SDM akan menerima <strong>Web Notification Real-time</strong> untuk memverifikasi berkas Anda.
                </p>
                <button type="submit"
                        class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shrink-0">
                    📤 KIRIM PENGAJUAN PERPANJANGAN SIP
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function setRenewalFiveYears() {
        const t = document.getElementById('renewal-terbit').value;
        if (!t) return;
        const d = new Date(t);
        d.setFullYear(d.getFullYear() + 5);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('renewal-kadaluarsa').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endpush
@endsection

