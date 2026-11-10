<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use App\Services\SipPdfGeneratorService;
use App\Services\WebNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokterController extends Controller
{
    public function index(Request $request): View
    {
        $ksmId = $request->query('ksm_id');
        $spesialisasiId = $request->query('spesialisasi_id');
        $search = $request->query('q');

        $dokters = Dokter::with(['user', 'ksm', 'spesialisasi', 'sipRecords' => function ($q) {
            $q->orderBy('tempat_praktik_ke')->orderByDesc('tgl_kadaluarsa');
        }])
            ->when($ksmId, fn ($q) => $q->where('ksm_id', $ksmId))
            ->when($spesialisasiId, fn ($q) => $q->where('spesialisasi_id', $spesialisasiId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nip_nik', 'like', "%{$search}%")
                        ->orWhere('no_str', 'like', "%{$search}%")
                        ->orWhereHas('sipRecords', fn ($sq) => $sq->where('no_sip', 'like', "%{$search}%"));
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate(12)
            ->withQueryString();

        $ksmList = Ksm::orderBy('nama_ksm')->get();
        $spesialisasiList = Spesialisasi::orderBy('nama_spesialisasi')->get();

        return view('dokter.index', compact(
            'dokters',
            'ksmList',
            'spesialisasiList',
            'ksmId',
            'spesialisasiId',
            'search'
        ));
    }

    public function create(): View
    {
        $ksmList = Ksm::orderBy('nama_ksm')->get();
        $spesialisasiList = Spesialisasi::orderBy('nama_spesialisasi')->get();

        return view('dokter.create', compact('ksmList', 'spesialisasiList'));
    }

    public function store(
        Request $request,
        WebNotificationService $notificationService,
        SipPdfGeneratorService $pdfGenerator
    ): RedirectResponse {
        $validated = $request->validate([
            // Section 1: User Account
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            // Section 2: Biodata & Spesialisasi
            'nip_nik' => ['required', 'string', 'max:50', 'unique:dokters,nip_nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'ksm_id' => ['required', 'exists:ksm,id'],
            'spesialisasi_id' => ['required', 'exists:spesialisasi,id'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'no_str' => ['required', 'string', 'max:100'],
            'is_str_seumur_hidup' => ['nullable', 'boolean'],
            'tgl_kadaluarsa_str' => ['nullable', 'date'],
            // Section 3: Data Legalitas SIP Awal
            'no_sip' => ['required', 'string', 'max:100'],
            'tempat_praktik_ke' => ['required', 'integer', 'in:1,2,3'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after_or_equal:tgl_terbit'],
            'file_sip' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'send_welcome_notification' => ['nullable', 'boolean'],
        ], [
            'nip_nik.unique' => 'NIP / NIK tersebut sudah terdaftar pada data dokter lain.',
            'username.unique' => 'Username login tersebut sudah digunakan.',
            'tgl_kadaluarsa.after_or_equal' => 'Tanggal kadaluarsa SIP harus setelah atau sama dengan tanggal terbit.',
        ]);

        $username = ! empty($validated['username'])
            ? trim($validated['username'])
            : preg_replace('/\s+/', '', $validated['nip_nik']);

        if (User::where('username', $username)->exists()) {
            $username .= '_' . Str::lower(Str::random(3));
        }

        $plainPassword = $validated['password'];
        $isStrSeumurHidup = $request->boolean('is_str_seumur_hidup', true);
        $sendWelcome = $request->boolean('send_welcome_notification', true);

        $dokter = DB::transaction(function () use (
            $request,
            $validated,
            $username,
            $plainPassword,
            $isStrSeumurHidup,
            $sendWelcome,
            $notificationService,
            $pdfGenerator
        ) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'username' => $username,
                'password' => Hash::make($plainPassword),
                'role' => 'dokter',
            ]);

            $dokter = Dokter::create([
                'user_id' => $user->id,
                'nip_nik' => $validated['nip_nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_whatsapp' => $validated['no_whatsapp'],
                'ksm_id' => $validated['ksm_id'],
                'spesialisasi_id' => $validated['spesialisasi_id'],
                'no_str' => $validated['no_str'],
                'is_str_seumur_hidup' => $isStrSeumurHidup,
                'tgl_kadaluarsa_str' => $isStrSeumurHidup ? null : ($validated['tgl_kadaluarsa_str'] ?? null),
            ]);

            if ($request->hasFile('file_sip')) {
                $filePath = $request->file('file_sip')->store('sip_files', 'public');
            } else {
                $spesialisasi = Spesialisasi::find($validated['spesialisasi_id']);
                $safeName = Str::slug($validated['nama_lengkap']) . '-sip-' . $validated['tempat_praktik_ke'] . '-' . time() . '.pdf';
                $filePath = $pdfGenerator->generateSampleSipPdf(
                    filename: $safeName,
                    namaDokter: $validated['nama_lengkap'],
                    nipNik: $validated['nip_nik'],
                    noStr: $validated['no_str'],
                    noSip: $validated['no_sip'],
                    tempatPraktikKe: (int) $validated['tempat_praktik_ke'],
                    tglTerbit: Carbon::parse($validated['tgl_terbit'])->format('d/m/Y'),
                    tglKadaluarsa: Carbon::parse($validated['tgl_kadaluarsa'])->format('d/m/Y'),
                    spesialisasi: $spesialisasi?->nama_spesialisasi ?? 'Dokter Spesialis'
                );
            }

            $status = SipRecord::determineTrafficLightStatus($validated['tgl_kadaluarsa']);

            $sipRecord = SipRecord::create([
                'dokter_id' => $dokter->id,
                'no_sip' => $validated['no_sip'],
                'tempat_praktik_ke' => (int) $validated['tempat_praktik_ke'],
                'tgl_terbit' => $validated['tgl_terbit'],
                'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
                'file_sip_path' => $filePath,
                'status' => $status,
                'is_verified' => true,
                'verified_by' => $request->user()?->id,
                'verified_at' => Carbon::now(),
                'catatan_verifikasi' => 'Pendataan awal dokter & verifikasi otomatis oleh Admin SDM.',
            ]);

            if ($sendWelcome) {
                $notificationService->notifyAccountCreated(
                    recipient: $user,
                    dokter: $dokter,
                    sipRecord: $sipRecord,
                    plainPassword: $plainPassword,
                    sender: $request->user()
                );
            }

            // If initial SIP is already in warning/mendesak/expired window, also create early warning log
            if (in_array($status, ['warning', 'mendesak', 'expired'], true)) {
                $notificationService->runEarlyWarningCheck($request->user());
            }

            return $dokter;
        });

        return redirect()
            ->route('dokter.show', $dokter)
            ->with(
                'success',
                sprintf(
                    'Data dokter %s beserta Akun Login (Username: %s) dan SIP Awal berhasil disimpan & diaktifkan!',
                    $dokter->nama_lengkap,
                    $username
                )
            );
    }

    public function show(Dokter $dokter): View
    {
        $dokter->load([
            'user.notificationLogs' => fn ($q) => $q->latest('sent_at')->limit(15),
            'ksm',
            'spesialisasi',
            'sipRecords' => fn ($q) => $q->with('verifier')->orderBy('tempat_praktik_ke')->orderByDesc('created_at'),
        ]);

        return view('dokter.show', compact('dokter'));
    }

    public function edit(Dokter $dokter): View
    {
        $dokter->load('user');
        $ksmList = Ksm::orderBy('nama_ksm')->get();
        $spesialisasiList = Spesialisasi::orderBy('nama_spesialisasi')->get();

        return view('dokter.edit', compact('dokter', 'ksmList', 'spesialisasiList'));
    }

    public function update(Request $request, Dokter $dokter): RedirectResponse
    {
        $validated = $request->validate([
            'nip_nik' => ['required', 'string', 'max:50', Rule::unique('dokters', 'nip_nik')->ignore($dokter->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'ksm_id' => ['required', 'exists:ksm,id'],
            'spesialisasi_id' => ['required', 'exists:spesialisasi,id'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'no_str' => ['required', 'string', 'max:100'],
            'is_str_seumur_hidup' => ['nullable', 'boolean'],
            'tgl_kadaluarsa_str' => ['nullable', 'date'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($dokter->user_id)],
            'new_password' => ['nullable', 'string', 'min:6'],
        ]);

        $isStrSeumurHidup = $request->boolean('is_str_seumur_hidup');

        DB::transaction(function () use ($dokter, $validated, $isStrSeumurHidup) {
            $dokter->update([
                'nip_nik' => $validated['nip_nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_whatsapp' => $validated['no_whatsapp'],
                'ksm_id' => $validated['ksm_id'],
                'spesialisasi_id' => $validated['spesialisasi_id'],
                'no_str' => $validated['no_str'],
                'is_str_seumur_hidup' => $isStrSeumurHidup,
                'tgl_kadaluarsa_str' => $isStrSeumurHidup ? null : ($validated['tgl_kadaluarsa_str'] ?? null),
            ]);

            if ($dokter->user) {
                $userUpdates = [
                    'name' => $validated['nama_lengkap'],
                    'username' => $validated['username'],
                ];
                if (! empty($validated['new_password'])) {
                    $userUpdates['password'] = Hash::make($validated['new_password']);
                }
                $dokter->user->update($userUpdates);
            }
        });

        return redirect()
            ->route('dokter.show', $dokter)
            ->with('success', 'Biodata & kredensial dokter berhasil diperbarui.');
    }

    public function destroy(Dokter $dokter): RedirectResponse
    {
        $nama = $dokter->nama_lengkap;
        if ($dokter->user) {
            $dokter->user->delete();
        } else {
            $dokter->delete();
        }

        return redirect()
            ->route('dokter.index')
            ->with('success', "Data dokter {$nama} beserta akun loginnya telah dihapus.");
    }

    /**
     * Add a new Multi-SIP (Praktik Ke-1, 2, or 3) directly by Admin SDM.
     */
    public function storeSip(
        Request $request,
        Dokter $dokter,
        SipPdfGeneratorService $pdfGenerator
    ): RedirectResponse {
        $validated = $request->validate([
            'no_sip' => ['required', 'string', 'max:100'],
            'tempat_praktik_ke' => ['required', 'integer', 'in:1,2,3'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after_or_equal:tgl_terbit'],
            'file_sip' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        // Archive existing active or pending SIP for the same tempat_praktik_ke if any
        $existingIds = SipRecord::where('dokter_id', $dokter->id)
            ->where('tempat_praktik_ke', (int) $validated['tempat_praktik_ke'])
            ->whereIn('status', ['aktif', 'warning', 'mendesak', 'expired', 'menunggu_verifikasi'])
            ->pluck('id');

        if ($existingIds->isNotEmpty()) {
            SipRecord::whereIn('id', $existingIds)->update(['status' => 'archived']);

            NotificationLog::whereIn('sip_record_id', $existingIds)
                ->where('message_type', 'pengajuan_sip_baru')
                ->where('status', 'sent')
                ->update([
                    'status' => 'read',
                    'read_at' => Carbon::now(),
                ]);
        }

        if ($request->hasFile('file_sip')) {
            $filePath = $request->file('file_sip')->store('sip_files', 'public');
        } else {
            $safeName = Str::slug($dokter->nama_lengkap) . '-sip-' . $validated['tempat_praktik_ke'] . '-' . time() . '.pdf';
            $filePath = $pdfGenerator->generateSampleSipPdf(
                filename: $safeName,
                namaDokter: $dokter->nama_lengkap,
                nipNik: $dokter->nip_nik,
                noStr: $dokter->no_str,
                noSip: $validated['no_sip'],
                tempatPraktikKe: (int) $validated['tempat_praktik_ke'],
                tglTerbit: Carbon::parse($validated['tgl_terbit'])->format('d/m/Y'),
                tglKadaluarsa: Carbon::parse($validated['tgl_kadaluarsa'])->format('d/m/Y'),
                spesialisasi: $dokter->spesialisasi?->nama_spesialisasi ?? 'Dokter Spesialis'
            );
        }

        $status = SipRecord::determineTrafficLightStatus($validated['tgl_kadaluarsa']);

        SipRecord::create([
            'dokter_id' => $dokter->id,
            'no_sip' => $validated['no_sip'],
            'tempat_praktik_ke' => (int) $validated['tempat_praktik_ke'],
            'tgl_terbit' => $validated['tgl_terbit'],
            'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
            'file_sip_path' => $filePath,
            'status' => $status,
            'is_verified' => true,
            'verified_by' => $request->user()?->id,
            'verified_at' => Carbon::now(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?: 'Ditambahkan langsung oleh Admin SDM.',
        ]);

        return back()->with('success', 'Data SIP Praktik Ke-' . $validated['tempat_praktik_ke'] . ' berhasil ditambahkan dan diaktifkan.');
    }

    /**
     * Stream or preview the SIP PDF file in the browser.
     */
    public function previewSipPdf(Request $request, SipRecord $sipRecord, SipPdfGeneratorService $pdfGenerator): Response
    {
        $sipRecord->load(['dokter.spesialisasi']);

        /** @var User|null $user */
        $user = $request->user();
        if ($user && $user->isDokter() && $sipRecord->dokter?->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen SIP milik dokter lain.');
        }

        if (! Storage::disk('public')->exists($sipRecord->file_sip_path)) {
            $dokter = $sipRecord->dokter;
            $safeName = basename($sipRecord->file_sip_path ?: ('sip-' . $sipRecord->id . '.pdf'));
            $newPath = $pdfGenerator->generateSampleSipPdf(
                filename: $safeName,
                namaDokter: $dokter?->nama_lengkap ?? 'Dokter RSUD Ansari Saleh',
                nipNik: $dokter?->nip_nik ?? '-',
                noStr: $dokter?->no_str ?? '-',
                noSip: $sipRecord->no_sip,
                tempatPraktikKe: $sipRecord->tempat_praktik_ke,
                tglTerbit: $sipRecord->tgl_terbit->format('d/m/Y'),
                tglKadaluarsa: $sipRecord->tgl_kadaluarsa->format('d/m/Y'),
                spesialisasi: $dokter?->spesialisasi?->nama_spesialisasi ?? 'Dokter Spesialis'
            );
            $sipRecord->update(['file_sip_path' => $newPath]);
        }

        $content = Storage::disk('public')->get($sipRecord->file_sip_path);
        $filename = 'SIP-' . Str::slug($sipRecord->no_sip) . '.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download CSV/Excel Template for Bulk Import of Doctors & Initial SIPs.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Template_Impor_Dokter_SIMONSIP_AnsariSaleh.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'NIP_NIK',
                'NAMA_LENGKAP_DAN_GELAR',
                'NO_WHATSAPP',
                'NAMA_KSM',
                'NAMA_SPESIALISASI',
                'NO_STR',
                'STR_SEUMUR_HIDUP (YA/TIDAK)',
                'NO_SIP_AWAL',
                'PRAKTIK_KE (1/2/3)',
                'TGL_TERBIT_SIP (YYYY-MM-DD)',
                'TGL_KADALUARSA_SIP (YYYY-MM-DD)',
                'PASSWORD_DEFAULT',
            ]);

            fputcsv($out, [
                '198504122011011009',
                'dr. H. Ahmad Fauzi, Sp.PD, FINASIM',
                '081250001122',
                'KSM Penyakit Dalam',
                'Spesialis Penyakit Dalam (Sp.PD)',
                'STR-63110029384',
                'YA',
                '503/0199/SIP-DOK/DPMPTSP/2024',
                '1',
                '2024-05-01',
                '2029-05-01',
                'Ansari@2026',
            ]);

            fputcsv($out, [
                '198809212014022004',
                'dr. Siti Rahmah, Sp.A, M.Kes',
                '081348889900',
                'KSM Kesehatan Anak',
                'Spesialis Anak (Sp.A)',
                'STR-63220018475',
                'YA',
                '503/0245/SIP-DOK/DPMPTSP/2022',
                '1',
                '2022-01-10',
                '2027-01-10',
                'Ansari@2026',
            ]);

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Process Bulk CSV/Excel Import of Doctors + Auto User Creation + Initial SIP + Web Notification.
     */
    public function import(
        Request $request,
        WebNotificationService $notificationService,
        SipPdfGeneratorService $pdfGenerator
    ): RedirectResponse {
        $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'file_import.required' => 'Pilih file CSV / Template Excel terlebih dahulu.',
            'file_import.mimes' => 'Format file harus berupa CSV (.csv) dari template yang disediakan.',
        ]);

        $path = $request->file('file_import')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (count($rows) < 2) {
            return back()->with('error', 'File impor kosong atau tidak memiliki baris data.');
        }

        // Remove header row
        array_shift($rows);

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $row) {
            if (count($row) < 11 || empty(trim($row[0] ?? '')) || empty(trim($row[1] ?? ''))) {
                continue;
            }

            $nipNik = preg_replace('/[^\w\-]/', '', trim($row[0]));
            $namaLengkap = trim($row[1]);
            $noWa = trim($row[2] ?: '08115000000');
            $namaKsm = trim($row[3] ?: 'KSM Umum & IGD');
            $namaSpesialisasi = trim($row[4] ?: 'Dokter Umum');
            $noStr = trim($row[5] ?: 'STR-' . $nipNik);
            $isSeumurHidup = strtoupper(trim($row[6] ?? 'YA')) !== 'TIDAK';
            $noSip = trim($row[7] ?: '503/' . rand(100, 999) . '/SIP-DOK/DPMPTSP/2025');
            $praktikKe = in_array((int) ($row[8] ?? 1), [1, 2, 3], true) ? (int) $row[8] : 1;
            $tglTerbit = ! empty($row[9]) ? Carbon::parse(trim($row[9]))->format('Y-m-d') : Carbon::today()->subYears(2)->format('Y-m-d');
            $tglKadaluarsa = ! empty($row[10]) ? Carbon::parse(trim($row[10]))->format('Y-m-d') : Carbon::today()->addYears(3)->format('Y-m-d');
            $plainPass = ! empty($row[11]) ? trim($row[11]) : 'Ansari@2026';

            if (Dokter::where('nip_nik', $nipNik)->exists() || User::where('username', $nipNik)->exists()) {
                $skippedCount++;
                continue;
            }

            DB::transaction(function () use (
                $request,
                $nipNik,
                $namaLengkap,
                $noWa,
                $namaKsm,
                $namaSpesialisasi,
                $noStr,
                $isSeumurHidup,
                $noSip,
                $praktikKe,
                $tglTerbit,
                $tglKadaluarsa,
                $plainPass,
                $notificationService,
                $pdfGenerator,
                &$importedCount
            ) {
                $ksm = Ksm::firstOrCreate(['nama_ksm' => $namaKsm]);
                $spesialisasi = Spesialisasi::firstOrCreate(['nama_spesialisasi' => $namaSpesialisasi]);

                $user = User::create([
                    'name' => $namaLengkap,
                    'username' => $nipNik,
                    'password' => Hash::make($plainPass),
                    'role' => 'dokter',
                ]);

                $dokter = Dokter::create([
                    'user_id' => $user->id,
                    'nip_nik' => $nipNik,
                    'nama_lengkap' => $namaLengkap,
                    'no_whatsapp' => $noWa,
                    'ksm_id' => $ksm->id,
                    'spesialisasi_id' => $spesialisasi->id,
                    'no_str' => $noStr,
                    'is_str_seumur_hidup' => $isSeumurHidup,
                    'tgl_kadaluarsa_str' => null,
                ]);

                $safeName = Str::slug($namaLengkap) . '-sip-' . $praktikKe . '-' . time() . '.pdf';
                $filePath = $pdfGenerator->generateSampleSipPdf(
                    filename: $safeName,
                    namaDokter: $namaLengkap,
                    nipNik: $nipNik,
                    noStr: $noStr,
                    noSip: $noSip,
                    tempatPraktikKe: $praktikKe,
                    tglTerbit: Carbon::parse($tglTerbit)->format('d/m/Y'),
                    tglKadaluarsa: Carbon::parse($tglKadaluarsa)->format('d/m/Y'),
                    spesialisasi: $spesialisasi->nama_spesialisasi
                );

                $status = SipRecord::determineTrafficLightStatus($tglKadaluarsa);

                $sipRecord = SipRecord::create([
                    'dokter_id' => $dokter->id,
                    'no_sip' => $noSip,
                    'tempat_praktik_ke' => $praktikKe,
                    'tgl_terbit' => $tglTerbit,
                    'tgl_kadaluarsa' => $tglKadaluarsa,
                    'file_sip_path' => $filePath,
                    'status' => $status,
                    'is_verified' => true,
                    'verified_by' => $request->user()?->id,
                    'verified_at' => Carbon::now(),
                    'catatan_verifikasi' => 'Impor massal data dokter & SIP oleh Admin SDM.',
                ]);

                $notificationService->notifyAccountCreated(
                    recipient: $user,
                    dokter: $dokter,
                    sipRecord: $sipRecord,
                    plainPassword: $plainPass,
                    sender: $request->user()
                );

                $importedCount++;
            });
        }

        return redirect()
            ->route('dokter.index')
            ->with(
                'success',
                sprintf(
                    'Impor massal selesai! %d dokter beserta akun login & SIP berhasil ditambahkan (%d dilewati karena NIP sudah terdaftar).',
                    $importedCount,
                    $skippedCount
                )
            );
    }
}

