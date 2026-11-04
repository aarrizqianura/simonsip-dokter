@extends('layouts.app')

@section('title', 'Master Spesialisasi')
@section('page_title', '🩺 Master Data Spesialisasi Dokter')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Tambah Spesialisasi -->
    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-fit">
        <div class="px-5 py-4 bg-slate-900 text-white">
            <h3 class="text-sm font-extrabold">➕ Tambah Master Spesialisasi</h3>
            <p class="text-xs text-slate-400">Kompetensi Spesialis / Sub-Spesialis Dokter</p>
        </div>
        <form action="{{ route('spesialisasi.store') }}" method="POST" class="p-5 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Spesialisasi</label>
                <input type="text" name="nama_spesialisasi" required placeholder="Contoh: Spesialis Radiologi (Sp.Rad)"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                Simpan Spesialisasi
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Spesialisasi -->
    <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-extrabold text-slate-900">Daftar Spesialisasi Terdaftar ({{ $spesialisasiList->count() }} Kompetensi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-600 border-b border-slate-200">
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Spesialisasi</th>
                        <th class="py-3 px-4 text-center">Jumlah Dokter</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($spesialisasiList as $idx => $sp)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <form action="{{ route('spesialisasi.update', $sp) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama_spesialisasi" value="{{ $sp->nama_spesialisasi }}"
                                           class="px-2.5 py-1.5 rounded-lg border border-slate-200 font-bold text-slate-800 w-full max-w-sm focus:border-emerald-500 outline-none">
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-bold text-[11px] transition">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 font-bold">
                                    {{ $sp->dokters_count }} Dokter
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('spesialisasi.destroy', $sp) }}" method="POST" onsubmit="return confirm('Hapus Spesialisasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px]">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

