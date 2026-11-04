<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { font-family: 'Times New Roman', Times, serif; }
        .sans-table { font-family: Arial, Helvetica, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 text-slate-900">
    <!-- Floating Print Toolbar -->
    <div class="no-print max-w-6xl mx-auto mb-4 p-4 rounded-xl bg-slate-900 text-white flex items-center justify-between shadow-lg sans-table">
        <div>
            <p class="text-xs font-bold text-emerald-400 uppercase">Pratinjau Cetak / Export PDF Resmi Kop RSUD</p>
            <p class="text-sm font-bold">{{ $reportTitle }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs">
                🖨️ Cetak / Simpan sebagai PDF Sekarang
            </button>
            <a href="{{ route('laporan.index', ['tab' => $tab]) }}" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 text-xs font-bold">
                Kembali
            </a>
        </div>
    </div>

    <!-- Official Paper Sheet -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-10 shadow-xl border border-slate-300">
        <!-- KOP SURAT RESMI RSUD DR. H. MOCH. ANSARI SALEH -->
        <div class="border-b-4 border-double border-black pb-4 mb-6 flex items-center justify-between gap-4">
            <div class="w-16 h-16 rounded-full border-2 border-slate-800 flex items-center justify-center font-bold text-xs text-center shrink-0 sans-table">
                PEMPROV<br>KALSEL
            </div>
            <div class="text-center flex-1">
                <p class="text-sm font-bold uppercase tracking-widest">PEMERINTAH PROVINSI KALIMANTAN SELATAN</p>
                <h1 class="text-xl font-extrabold uppercase tracking-wide mt-0.5">
                    RUMAH SAKIT UMUM DAERAH DR. H. MOCH. ANSARI SALEH
                </h1>
                <p class="text-xs mt-1">
                    Jl. Brigjend H. Hasan Basry No. 1, Banjarmasin 70123 • Telp. (0511) 3301560 / Fax. (0511) 3302246
                </p>
                <p class="text-xs italic">
                    Website: rsudansarisaleh.kalselprov.go.id • Email: rsud.ansarisaleh@kalselprov.go.id
                </p>
            </div>
            <div class="w-16 h-16 rounded-full border-2 border-emerald-800 flex items-center justify-center font-bold text-xs text-center text-emerald-900 shrink-0 sans-table">
                RSUD<br>MAS
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-6">
            <h2 class="text-base font-bold uppercase underline">{{ $reportTitle }}</h2>
            <p class="text-xs text-slate-700 mt-1">{{ $reportSubtitle }}</p>
            <p class="text-xs text-slate-600 mt-1 sans-table">
                Unit KSM: <strong>{{ $selectedKsm?->nama_ksm ?? 'Seluruh Kelompok Staf Medis (KSM)' }}</strong> |
                Dicetak Tanggal: <strong>{{ $printedAt }}</strong>
            </p>
        </div>

        <!-- Ringkasan Eksekutif (KPI) -->
        <div class="mb-5 p-3 border border-slate-400 bg-slate-50 text-xs sans-table flex flex-wrap items-center justify-around gap-4">
            @foreach($metrics as $k => $v)
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-600 block">{{ str_replace('_', ' ', $k) }}</span>
                    <strong class="text-sm font-extrabold text-slate-900">{{ $v }}{{ str_contains($k, 'persentase') ? '%' : '' }}</strong>
                </div>
            @endforeach
        </div>

        <!-- Data Tabel -->
        <div class="sans-table">
            @include('laporan.partials.table')
        </div>

        <!-- Blok Tanda Tangan Pengesahan Resmi -->
        <div class="mt-10 grid grid-cols-2 gap-8 text-xs sans-table">
            <div class="text-center">
                <p>Mengetahui,</p>
                <p class="font-bold">Ketua Komite Medik / Wakil Direktur Pelayanan</p>
                <p>RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</p>
                <div class="h-20"></div>
                <p class="font-bold underline">dr. H. Gusti Muhammad Rasyid, Sp.PD, FINASIM</p>
                <p>NIP. 19800315 200604 1 008</p>
            </div>

            <div class="text-center">
                <p>Banjarmasin, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold">Kepala Bagian Sumber Daya Manusia (SDM)</p>
                <p>RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</p>
                <div class="h-20"></div>
                <p class="font-bold underline">Hj. Nurul Hidayah, S.Kep., M.M.</p>
                <p>NIP. 19810912 200701 2 011</p>
            </div>
        </div>
    </div>
</body>
</html>

