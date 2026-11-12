<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: 'Calibri', Arial, sans-serif; font-size: 11px; }
        th, td { border: 1px solid #334155; padding: 6px; vertical-align: top; }
        th { background-color: #e2e8f0; font-weight: bold; }
    </style>
</head>
<body>
    <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
    <h3>RSUD DR. H. MOCH. ANSARI SALEH BANJARMASIN</h3>
    <h4>{{ $reportTitle }}</h4>
    <p>{{ $reportSubtitle }} | Waktu Ekspor: {{ $printedAt }}</p>
    <br>
    @include('laporan.partials.table')
</body>
</html>

