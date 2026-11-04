<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SipPdfGeneratorService
{
    /**
     * Generate a valid single-page PDF file for a SIP certificate and store it on public disk.
     */
    public function generateSampleSipPdf(
        string $filename,
        string $namaDokter,
        string $nipNik,
        string $noStr,
        string $noSip,
        int $tempatPraktikKe,
        string $tglTerbit,
        string $tglKadaluarsa,
        string $spesialisasi = 'Dokter Spesialis'
    ): string {
        $lines = [
            'PEMERINTAH KOTA BANJARMASIN / PROVINSI KALIMANTAN SELATAN',
            'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',
            'SURAT IZIN PRAKTIK (SIP) DOKTER / DOKTER SPESIALIS',
            '------------------------------------------------------------------',
            'Nomor SIP           : ' . $noSip,
            'Nama Lengkap        : ' . $namaDokter,
            'NIP / NIK           : ' . $nipNik,
            'Kompetensi          : ' . $spesialisasi,
            'Nomor STR           : ' . $noStr . ' (Seumur Hidup)',
            'Tempat Praktik Ke-  : ' . $tempatPraktikKe . ' (RSUD Dr. H. Moch. Ansari Saleh Banjarmasin)',
            'Alamat Praktik      : Jl. Brigjend H. Hasan Basry No.1, Banjarmasin',
            'Tanggal Terbit      : ' . $tglTerbit,
            'Berlaku Sampai      : ' . $tglKadaluarsa,
            '------------------------------------------------------------------',
            'Dokumen Digital Terverifikasi oleh SIMONSIP RSUD Dr. H. Moch. Ansari Saleh',
        ];

        $contentStream = "BT\n/F1 13 Tf\n50 760 Td\n18 TL\n";
        foreach ($lines as $index => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            if ($index === 0) {
                $contentStream .= "({$escaped}) Tj\n";
            } else {
                $contentStream .= "T* ({$escaped}) Tj\n";
            }
        }
        $contentStream .= "ET\n";

        $pdf = $this->buildMinimalPdf($contentStream);
        $relativePath = 'sip_files/' . ltrim($filename, '/');
        Storage::disk('public')->put($relativePath, $pdf);

        return $relativePath;
    }

    private function buildMinimalPdf(string $stream): string
    {
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[5] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";

        $out = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($out);
            $out .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($out);
        $count = count($objects) + 1;
        $out .= "xref\n0 {$count}\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $out .= sprintf("%010d 00000 n \n", $offset);
        }

        $out .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $out;
    }
}

