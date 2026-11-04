@extends('layouts.app')

@section('title', 'Edit Biodata & Akun Dokter')
@section('page_title', '✏️ Edit Biodata, STR & Kredensial Login Dokter')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('dokter.show', $dokter) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
            ← Kembali ke Detail Dokter
        </a>
    </div>

    <form action="{{ route('dokter.update', $dokter) }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 bg-slate-900 text-white">
            <h2 class="text-base font-extrabold">Perbarui Biodata & Akun Login: {{ $dokter->nama_lengkap }}</h2>
        </div>

        <div class="p-6 space-y-5 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">NIP / NIK</label>
                    <input type="text" name="nip_nik" value="{{ old('nip_nik', $dokter->nip_nik) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $dokter->nama_lengkap) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kelompok Staf Medis (KSM)</label>
                    <select name="ksm_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 bg-white">
                        @foreach($ksmList as $ksm)
                            <option value="{{ $ksm->id }}" @selected(old('ksm_id', $dokter->ksm_id) == $ksm->id)>{{ $ksm->nama_ksm }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Spesialisasi</label>
                    <select name="spesialisasi_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 bg-white">
                        @foreach($spesialisasiList as $sp)
                            <option value="{{ $sp->id }}" @selected(old('spesialisasi_id', $dokter->spesialisasi_id) == $sp->id)>{{ $sp->nama_spesialisasi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                    <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp', $dokter->no_whatsapp) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor STR</label>
                    <input type="text" name="no_str" value="{{ old('no_str', $dokter->no_str) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
                    <label class="inline-flex items-center gap-2 mt-2 font-semibold text-teal-800">
                        <input type="checkbox" name="is_str_seumur_hidup" value="1" @checked(old('is_str_seumur_hidup', $dokter->is_str_seumur_hidup))>
                        <span>♾️ STR Seumur Hidup</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login Portal Dokter</label>
                    <input type="text" name="username" value="{{ old('username', $dokter->user?->username) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Reset Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="text" name="new_password" placeholder="Masukkan password baru jika ingin mereset"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <a href="{{ route('dokter.show', $dokter) }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

