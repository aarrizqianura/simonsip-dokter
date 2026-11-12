<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    <link rel="icon" type="image/avif" href="{{ asset('images/logo-rsud.avif') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 landscape; /* Memaksa ukuran kertas A4 Lanskap */
            margin: 10mm;
        }
        @media print {
            .no-print { display: none !important; }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-sheet {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
        body {
            font-family: 'Calibri', 'Helvetica', Arial, sans-serif;
            font-size: 10px; /* Ukuran font diperkecil sedikit agar pas */
            color: #333;
            margin: 0;
            padding: 0;
        }
        .sans-table {
            font-family: 'Calibri', 'Helvetica', Arial, sans-serif;
        }
        table {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: fixed; /* Membatasi lebar kolom agar proporsional */
        }
        th, td {
            padding: 6px 4px !important;
            word-wrap: break-word; /* Mencegah teks panjang merusak tabel */
            overflow-wrap: break-word;
            overflow: hidden;
            font-size: 10px !important;
        }
        @if($tab === 'kepatuhan')
            /* Lebar spesifik 11 kolom Laporan Kepatuhan SIP (Total 100%) */
            th:nth-child(1), td:nth-child(1) { width: 4%; }   /* No */
            th:nth-child(2), td:nth-child(2) { width: 14%; }  /* Nama & Gelar */
            th:nth-child(3), td:nth-child(3) { width: 10%; }  /* NIP/NIK */
            th:nth-child(4), td:nth-child(4) { width: 13%; }  /* KSM & Spesialisasi */
            th:nth-child(5), td:nth-child(5) { width: 12%; }  /* No. STR & Masa Berlaku */
            th:nth-child(6), td:nth-child(6) { width: 13%; }  /* No. SIP */
            th:nth-child(7), td:nth-child(7) { width: 6%; }   /* Praktik Ke- */
            th:nth-child(8), td:nth-child(8) { width: 7%; }   /* Tgl Terbit */
            th:nth-child(9), td:nth-child(9) { width: 7%; }   /* Tgl Kadaluarsa */
            th:nth-child(10), td:nth-child(10) { width: 6%; } /* Sisa Hari */
            th:nth-child(11), td:nth-child(11) { width: 8%; } /* Status Kepatuhan */
        @elseif($tab === 'notifikasi')
            /* Lebar spesifik 8 kolom Laporan Audit Notifikasi (Total 100%) */
            th:nth-child(1), td:nth-child(1) { width: 4%; }
            th:nth-child(2), td:nth-child(2) { width: 11%; }
            th:nth-child(3), td:nth-child(3) { width: 14%; }
            th:nth-child(4), td:nth-child(4) { width: 11%; }
            th:nth-child(5), td:nth-child(5) { width: 12%; }
            th:nth-child(6), td:nth-child(6) { width: 26%; }
            th:nth-child(7), td:nth-child(7) { width: 11%; }
            th:nth-child(8), td:nth-child(8) { width: 11%; }
        @elseif($tab === 'verifikasi')
            /* Lebar spesifik 8 kolom Laporan Verifikasi Berkas (Total 100%) */
            th:nth-child(1), td:nth-child(1) { width: 4%; }
            th:nth-child(2), td:nth-child(2) { width: 10%; }
            th:nth-child(3), td:nth-child(3) { width: 10%; }
            th:nth-child(4), td:nth-child(4) { width: 16%; }
            th:nth-child(5), td:nth-child(5) { width: 16%; }
            th:nth-child(6), td:nth-child(6) { width: 12%; }
            th:nth-child(7), td:nth-child(7) { width: 12%; }
            th:nth-child(8), td:nth-child(8) { width: 20%; }
        @elseif($tab === 'histori')
            /* Lebar spesifik 10 kolom Laporan Histori Pengajuan SIP (Total 100%) */
            th:nth-child(1), td:nth-child(1) { width: 4%; }
            th:nth-child(2), td:nth-child(2) { width: 14%; }
            th:nth-child(3), td:nth-child(3) { width: 12%; }
            th:nth-child(4), td:nth-child(4) { width: 9%; }
            th:nth-child(5), td:nth-child(5) { width: 13%; }
            th:nth-child(6), td:nth-child(6) { width: 6%; }
            th:nth-child(7), td:nth-child(7) { width: 12%; }
            th:nth-child(8), td:nth-child(8) { width: 8%; }
            th:nth-child(9), td:nth-child(9) { width: 9%; }
            th:nth-child(10), td:nth-child(10) { width: 13%; }
        @elseif($tab === 'sla')
            /* Lebar spesifik 12 kolom Laporan SLA SDM (Total 100%) */
            th:nth-child(1), td:nth-child(1) { width: 4%; }
            th:nth-child(2), td:nth-child(2) { width: 9%; }
            th:nth-child(3), td:nth-child(3) { width: 12%; }
            th:nth-child(4), td:nth-child(4) { width: 9%; }
            th:nth-child(5), td:nth-child(5) { width: 11%; }
            th:nth-child(6), td:nth-child(6) { width: 11%; }
            th:nth-child(7), td:nth-child(7) { width: 5%; }
            th:nth-child(8), td:nth-child(8) { width: 7%; }
            th:nth-child(9), td:nth-child(9) { width: 7%; }
            th:nth-child(10), td:nth-child(10) { width: 8%; }
            th:nth-child(11), td:nth-child(11) { width: 9%; }
            th:nth-child(12), td:nth-child(12) { width: 8%; }
        @endif
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
    <div class="print-sheet max-w-6xl mx-auto bg-white p-8 sm:p-10 shadow-xl border border-slate-300">
        <!-- KOP SURAT RESMI RSUD DR. H. MOCH. ANSARI SALEH -->
        <div class="border-b-4 border-double border-black pb-4 mb-6 flex items-center justify-between gap-4">
            <div class="w-16 h-16 flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo-kalsel.png') }}" alt="Logo Provinsi Kalimantan Selatan" class="w-16 h-16 object-contain">
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
            <div class="w-16 h-16 flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo-rsud.avif') }}" alt="Logo RSUD Dr. H. Moch. Ansari Saleh" class="w-16 h-16 object-contain">
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

