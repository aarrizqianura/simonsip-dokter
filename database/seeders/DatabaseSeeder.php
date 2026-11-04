<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use App\Services\SipPdfGeneratorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with RSUD Dr. H. Moch. Ansari Saleh Banjarmasin dataset.
     */
    public function run(): void
    {
        $pdfGenerator = app(SipPdfGeneratorService::class);
        $defaultPassword = Hash::make('Ansari@2026');
        $today = Carbon::today();

        // 1. Create Management & SDM Users
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Ir. H. Muhammad Rizky, M.Kom (Super Admin IT)',
                'email' => 'superadmin@ansarisaleh.kalselprov.go.id',
                'password' => $defaultPassword,
                'role' => 'super_admin',
            ]
        );

        $adminSdm = User::updateOrCreate(
            ['username' => 'adminsdm'],
            [
                'name' => 'Hj. Nurul Hidayah, S.Kep., M.M. (Admin SDM)',
                'email' => 'sdm@ansarisaleh.kalselprov.go.id',
                'password' => $defaultPassword,
                'role' => 'admin_sdm',
            ]
        );

        $adminSdm2 = User::updateOrCreate(
            ['username' => 'verifikatorsdm'],
            [
                'name' => 'M. Hendra Saputra, S.AP (Verifikator SDM)',
                'email' => 'verifikator.sdm@ansarisaleh.kalselprov.go.id',
                'password' => $defaultPassword,
                'role' => 'admin_sdm',
            ]
        );

        // 2. Seed Master KSM
        $ksmNames = [
            'KSM Penyakit Dalam',
            'KSM Bedah',
            'KSM Kesehatan Anak',
            'KSM Obstetri & Ginekologi',
            'KSM Anestesiologi & Terapi Intensif',
            'KSM Jantung & Pembuluh Darah',
            'KSM Saraf (Neurologi)',
            'KSM Umum & IGD',
        ];

        $ksmMap = [];
        foreach ($ksmNames as $name) {
            $ksmMap[$name] = Ksm::firstOrCreate(['nama_ksm' => $name]);
        }

        // 3. Seed Master Spesialisasi
        $spesialisasiNames = [
            'Spesialis Penyakit Dalam (Sp.PD)',
            'Spesialis Bedah Umum (Sp.B)',
            'Spesialis Anak (Sp.A)',
            'Spesialis Obstetri & Ginekologi (Sp.OG)',
            'Spesialis Anestesiologi (Sp.An)',
            'Spesialis Jantung & Pembuluh Darah (Sp.JP)',
            'Spesialis Saraf / Neurologi (Sp.N)',
            'Dokter Umum (dr.)',
        ];

        $spesialisasiMap = [];
        foreach ($spesialisasiNames as $name) {
            $spesialisasiMap[$name] = Spesialisasi::firstOrCreate(['nama_spesialisasi' => $name]);
        }

        // 4. Seed Doctors + Multi-SIPs + Notifications
        $doctorsData = [
            [
                'nip_nik' => '198003152006041008',
                'nama_lengkap' => 'dr. H. Gusti Muhammad Rasyid, Sp.PD, FINASIM',
                'no_whatsapp' => '08115100201',
                'ksm' => 'KSM Penyakit Dalam',
                'spesialisasi' => 'Spesialis Penyakit Dalam (Sp.PD)',
                'no_str' => '6311401230891001',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    // Praktik Ke-1: AMAN (> 90 hari)
                    [
                        'no_sip' => '503/0112/SIP-DOK/DPMPTSP/2024',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(2)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(3)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(2)->addHours(4),
                        'created_at' => $today->copy()->subYears(2),
                        'catatan_verifikasi' => 'Berkas SIP Praktik Ke-1 di RSUD Dr. H. Moch. Ansari Saleh lengkap & sah.',
                    ],
                    // Praktik Ke-2: WARNING (75 hari lagi)
                    [
                        'no_sip' => '503/0188/SIP-DOK-II/DPMPTSP/2022',
                        'tempat_praktik_ke' => 2,
                        'tgl_terbit' => $today->copy()->subYears(5)->addDays(75)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addDays(75)->format('Y-m-d'),
                        'status' => 'warning',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(4)->addHours(3),
                        'created_at' => $today->copy()->subYears(4),
                        'catatan_verifikasi' => 'SIP Praktik Ke-2 terverifikasi.',
                    ],
                ],
            ],
            [
                'nip_nik' => '198307222009032005',
                'nama_lengkap' => 'dr. Hj. Siti Noor Laila, Sp.A, M.Kes',
                'no_whatsapp' => '081251234502',
                'ksm' => 'KSM Kesehatan Anak',
                'spesialisasi' => 'Spesialis Anak (Sp.A)',
                'no_str' => '6321201220772002',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    // Praktik Ke-1: WARNING (68 hari lagi)
                    [
                        'no_sip' => '503/0204/SIP-DOK/DPMPTSP/2022',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(5)->addDays(68)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addDays(68)->format('Y-m-d'),
                        'status' => 'warning',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subMonths(18)->addHours(2),
                        'created_at' => $today->copy()->subMonths(18),
                        'catatan_verifikasi' => 'Dokumen SIP asli telah divalidasi oleh bagian Kepegawaian.',
                    ],
                ],
            ],
            [
                'nip_nik' => '197911042005011011',
                'nama_lengkap' => 'dr. Akhmad Zaini Dahlan, Sp.B, FICS',
                'no_whatsapp' => '081349008811',
                'ksm' => 'KSM Bedah',
                'spesialisasi' => 'Spesialis Bedah Umum (Sp.B)',
                'no_str' => '6311101210443003',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    // Archived old SIP
                    [
                        'no_sip' => '503/0045/SIP-DOK/DPMPTSP/2017',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(10)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->subYears(5)->format('Y-m-d'),
                        'status' => 'archived',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(10)->addHours(5),
                        'created_at' => $today->copy()->subYears(10),
                        'catatan_verifikasi' => 'Riwayat SIP periode sebelumnya (Diarsipkan otomatis).',
                    ],
                    // Current Praktik Ke-1: MENDESAK (42 hari lagi)
                    [
                        'no_sip' => '503/0319/SIP-DOK/DPMPTSP/2022',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(5)->addDays(42)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addDays(42)->format('Y-m-d'),
                        'status' => 'mendesak',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(5)->addDays(42)->addHours(2),
                        'created_at' => $today->copy()->subYears(5)->addDays(42),
                        'catatan_verifikasi' => 'SIP aktif namun memasuki masa peringatan mendesak (H-42).',
                    ],
                    // Newly uploaded 1-Step Renewal waiting for SDM Approval!
                    [
                        'no_sip' => '503/0891/SIP-DOK/DPMPTSP/2026',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subDays(2)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(5)->subDays(2)->format('Y-m-d'),
                        'status' => 'menunggu_verifikasi',
                        'is_verified' => false,
                        'verified_by' => null,
                        'verified_at' => null,
                        'created_at' => $today->copy()->subDays(1)->setHour(9)->setMinute(30),
                        'catatan_verifikasi' => 'Catatan Pengajuan Dokter: Mohon verifikasi perpanjangan SIP Praktik Ke-1 periode 2026-2031.',
                    ],
                ],
            ],
            [
                'nip_nik' => '198601192011012007',
                'nama_lengkap' => 'dr. Dessy Permatasari, Sp.OG (K)',
                'no_whatsapp' => '08115099334',
                'ksm' => 'KSM Obstetri & Ginekologi',
                'spesialisasi' => 'Spesialis Obstetri & Ginekologi (Sp.OG)',
                'no_str' => '6321301240114004',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    // Praktik Ke-1: EXPIRED / <= 30 hari (14 hari lagi)
                    [
                        'no_sip' => '503/0412/SIP-DOK/DPMPTSP/2021',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(5)->addDays(14)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addDays(14)->format('Y-m-d'),
                        'status' => 'expired',
                        'is_verified' => true,
                        'verified_by' => $adminSdm2->id,
                        'verified_at' => $today->copy()->subYears(5)->addDays(14)->addHours(4),
                        'created_at' => $today->copy()->subYears(5)->addDays(14),
                        'catatan_verifikasi' => 'Perhatian: Masa berlaku SIP <= 30 hari (Darurat).',
                    ],
                    // Rejected renewal history so Laporan 3 & 4 show rejected example
                    [
                        'no_sip' => '503/0850/SIP-DOK/DPMPTSP/2026',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subDays(5)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(5)->subDays(5)->format('Y-m-d'),
                        'status' => 'ditolak',
                        'is_verified' => false,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subDays(4)->setHour(14)->setMinute(15),
                        'created_at' => $today->copy()->subDays(5)->setHour(10)->setMinute(0),
                        'catatan_verifikasi' => 'Halaman ke-2 lampiran QR Code DPMPTSP Kota Banjarmasin terpotong/buram. Mohon pindai ulang dengan resolusi jelas.',
                    ],
                ],
            ],
            [
                'nip_nik' => '198405102010011014',
                'nama_lengkap' => 'dr. Muhammad Ridwan Anshari, Sp.An, KIC',
                'no_whatsapp' => '081258007766',
                'ksm' => 'KSM Anestesiologi & Terapi Intensif',
                'spesialisasi' => 'Spesialis Anestesiologi (Sp.An)',
                'no_str' => '6311501230555005',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    // Praktik Ke-1: EXPIRED (-5 hari / sudah lewat 5 hari)
                    [
                        'no_sip' => '503/0098/SIP-DOK/DPMPTSP/2021',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(5)->subDays(5)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->subDays(5)->format('Y-m-d'),
                        'status' => 'expired',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(5)->subDays(5)->addHours(3),
                        'created_at' => $today->copy()->subYears(5)->subDays(5),
                        'catatan_verifikasi' => 'Masa berlaku SIP telah berakhir 5 hari lalu, menunggu pengajuan baru aktif.',
                    ],
                    // Pending renewal in queue
                    [
                        'no_sip' => '503/0902/SIP-DOK/DPMPTSP/2026',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subDays(1)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(5)->subDays(1)->format('Y-m-d'),
                        'status' => 'menunggu_verifikasi',
                        'is_verified' => false,
                        'verified_by' => null,
                        'verified_at' => null,
                        'created_at' => $today->copy()->setHour(8)->setMinute(20),
                        'catatan_verifikasi' => 'Catatan Pengajuan Dokter: Berkas SIP baru telah terbit dari DPMPTSP, mohon verifikasi segera.',
                    ],
                ],
            ],
            [
                'nip_nik' => '198712082014021003',
                'nama_lengkap' => 'dr. H. Farhan Syahbana, Sp.JP, FIHA',
                'no_whatsapp' => '081347112233',
                'ksm' => 'KSM Jantung & Pembuluh Darah',
                'spesialisasi' => 'Spesialis Jantung & Pembuluh Darah (Sp.JP)',
                'no_str' => '6311601240666006',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    [
                        'no_sip' => '503/0520/SIP-DOK/DPMPTSP/2025',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYear()->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(4)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                        'verified_by' => $adminSdm2->id,
                        'verified_at' => $today->copy()->subYear()->addHours(1),
                        'created_at' => $today->copy()->subYear(),
                        'catatan_verifikasi' => 'Verifikasi berkas SIP Praktik Ke-1 sesuai standar STARKES.',
                    ],
                    [
                        'no_sip' => '503/0521/SIP-DOK-II/DPMPTSP/2025',
                        'tempat_praktik_ke' => 2,
                        'tgl_terbit' => $today->copy()->subMonths(8)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(4)->addMonths(4)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subMonths(8)->addHours(2),
                        'created_at' => $today->copy()->subMonths(8),
                        'catatan_verifikasi' => 'SIP Praktik Ke-2 aktif dan terverifikasi.',
                    ],
                ],
            ],
            [
                'nip_nik' => '198906252015032009',
                'nama_lengkap' => 'dr. Nabila Khairunnisa, Sp.N',
                'no_whatsapp' => '081254446677',
                'ksm' => 'KSM Saraf (Neurologi)',
                'spesialisasi' => 'Spesialis Saraf / Neurologi (Sp.N)',
                'no_str' => '6321701230777007',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    [
                        'no_sip' => '503/0611/SIP-DOK/DPMPTSP/2022',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subYears(5)->addDays(48)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addDays(48)->format('Y-m-d'),
                        'status' => 'mendesak',
                        'is_verified' => true,
                        'verified_by' => $adminSdm->id,
                        'verified_at' => $today->copy()->subYears(5)->addDays(48)->addHours(2),
                        'created_at' => $today->copy()->subYears(5)->addDays(48),
                        'catatan_verifikasi' => 'Status Mendesak (H-48), dokter telah dikirim peringatan dini Tahap 2.',
                    ],
                ],
            ],
            [
                'nip_nik' => '199208142019021006',
                'nama_lengkap' => 'dr. রেজা Pratama Putra',
                'no_whatsapp' => '081351990088',
                'ksm' => 'KSM Umum & IGD',
                'spesialisasi' => 'Dokter Umum (dr.)',
                'no_str' => '6311100240888008',
                'is_str_seumur_hidup' => true,
                'sips' => [
                    [
                        'no_sip' => '503/0705/SIP-DOK/DPMPTSP/2025',
                        'tempat_praktik_ke' => 1,
                        'tgl_terbit' => $today->copy()->subMonths(6)->format('Y-m-d'),
                        'tgl_kadaluarsa' => $today->copy()->addYears(4)->addMonths(6)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                        'verified_by' => $adminSdm2->id,
                        'verified_at' => $today->copy()->subMonths(6)->addHours(3),
                        'created_at' => $today->copy()->subMonths(6),
                        'catatan_verifikasi' => 'SIP Dokter IGD RSUD Dr. H. Moch. Ansari Saleh lengkap & sah.',
                    ],
                ],
            ],
        ];

        // Fix typo in doctor 8 name so it is clean Indonesian
        $doctorsData[7]['nama_lengkap'] = 'dr. Reza Pratama Putra';

        foreach ($doctorsData as $docData) {
            $user = User::updateOrCreate(
                ['username' => $docData['nip_nik']],
                [
                    'name' => $docData['nama_lengkap'],
                    'email' => Str::slug($docData['nip_nik']) . '@ansarisaleh.kalselprov.go.id',
                    'password' => $defaultPassword,
                    'role' => 'dokter',
                ]
            );

            $dokter = Dokter::updateOrCreate(
                ['nip_nik' => $docData['nip_nik']],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $docData['nama_lengkap'],
                    'no_whatsapp' => $docData['no_whatsapp'],
                    'ksm_id' => $ksmMap[$docData['ksm']]->id,
                    'spesialisasi_id' => $spesialisasiMap[$docData['spesialisasi']]->id,
                    'no_str' => $docData['no_str'],
                    'is_str_seumur_hidup' => $docData['is_str_seumur_hidup'],
                    'tgl_kadaluarsa_str' => null,
                ]
            );

            foreach ($docData['sips'] as $idx => $sipData) {
                $safeFile = Str::slug($docData['nama_lengkap']) . '-sip-' . $sipData['tempat_praktik_ke'] . '-' . ($idx + 1) . '.pdf';
                $filePath = $pdfGenerator->generateSampleSipPdf(
                    filename: $safeFile,
                    namaDokter: $docData['nama_lengkap'],
                    nipNik: $docData['nip_nik'],
                    noStr: $docData['no_str'],
                    noSip: $sipData['no_sip'],
                    tempatPraktikKe: $sipData['tempat_praktik_ke'],
                    tglTerbit: Carbon::parse($sipData['tgl_terbit'])->format('d/m/Y'),
                    tglKadaluarsa: Carbon::parse($sipData['tgl_kadaluarsa'])->format('d/m/Y'),
                    spesialisasi: $docData['spesialisasi']
                );

                $sip = SipRecord::updateOrCreate(
                    [
                        'dokter_id' => $dokter->id,
                        'no_sip' => $sipData['no_sip'],
                    ],
                    [
                        'tempat_praktik_ke' => $sipData['tempat_praktik_ke'],
                        'tgl_terbit' => $sipData['tgl_terbit'],
                        'tgl_kadaluarsa' => $sipData['tgl_kadaluarsa'],
                        'file_sip_path' => $filePath,
                        'status' => $sipData['status'],
                        'is_verified' => $sipData['is_verified'],
                        'verified_by' => $sipData['verified_by'],
                        'verified_at' => $sipData['verified_at'],
                        'catatan_verifikasi' => $sipData['catatan_verifikasi'],
                        'created_at' => $sipData['created_at'],
                        'updated_at' => $sipData['verified_at'] ?? $sipData['created_at'],
                    ]
                );

                // Seed Welcome Notification for primary active SIP
                if ($idx === 0) {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $user->id,
                            'message_type' => 'account_created',
                        ],
                        [
                            'sender_user_id' => $adminSdm->id,
                            'channel' => 'web',
                            'title' => 'Selamat Datang di Portal SIMONSIP RSUD Dr. H. Moch. Ansari Saleh',
                            'payload' => "Akun Portal Dokter Anda ({$user->username}) telah diaktifkan oleh Admin SDM beserta pencatatan SIP Praktik Ke-{$sip->tempat_praktik_ke} ({$sip->no_sip}).",
                            'action_url' => '/portal',
                            'status' => 'read',
                            'sent_at' => $today->copy()->subDays(30)->setHour(8)->setMinute(0),
                            'read_at' => $today->copy()->subDays(29)->setHour(10)->setMinute(15),
                        ]
                    );
                }

                // Seed Early Warning / Urgent Notifications
                if ($sip->status === 'warning') {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $user->id,
                            'message_type' => 'early_warning',
                        ],
                        [
                            'sender_user_id' => null,
                            'channel' => 'web',
                            'title' => "[PERINGATAN TAHAP 1 (H-90)] SIP Praktik Ke-{$sip->tempat_praktik_ke} ({$sip->no_sip})",
                            'payload' => "Yth. {$dokter->nama_lengkap}, masa berlaku SIP Anda akan berakhir pada " . $sip->tgl_kadaluarsa->format('d/m/Y') . " (tersisa {$sip->sisa_hari} hari). Mohon mulai mempersiapkan berkas perpanjangan SIP.",
                            'action_url' => '/portal/renewal?sip_id=' . $sip->id,
                            'status' => 'read',
                            'sent_at' => $today->copy()->subDays(3)->setHour(7)->setMinute(0),
                            'read_at' => $today->copy()->subDays(2)->setHour(11)->setMinute(20),
                        ]
                    );
                }

                if ($sip->status === 'mendesak') {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $user->id,
                            'message_type' => 'early_warning',
                        ],
                        [
                            'sender_user_id' => null,
                            'channel' => 'web',
                            'title' => "[PERINGATAN MENDESAK TAHAP 2 (H-60)] SIP Praktik Ke-{$sip->tempat_praktik_ke} ({$sip->no_sip})",
                            'payload' => "Yth. {$dokter->nama_lengkap}, masa berlaku SIP Praktik Ke-{$sip->tempat_praktik_ke} Anda tersisa {$sip->sisa_hari} hari (Berakhir: " . $sip->tgl_kadaluarsa->format('d/m/Y') . "). Segera unggah SIP baru melalui menu 1-Step Renewal.",
                            'action_url' => '/portal/renewal?sip_id=' . $sip->id,
                            'status' => 'sent',
                            'sent_at' => $today->copy()->setHour(7)->setMinute(0),
                            'read_at' => null,
                        ]
                    );
                }

                if ($sip->status === 'expired') {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $user->id,
                            'message_type' => 'urgent',
                        ],
                        [
                            'sender_user_id' => $adminSdm->id,
                            'channel' => 'web',
                            'title' => "[DARURAT: MASA BERLAKU SIP KRITIS / EXPIRED] ({$sip->no_sip})",
                            'payload' => "PERHATIAN DARURAT: Yth. {$dokter->nama_lengkap}, masa berlaku SIP Praktik Ke-{$sip->tempat_praktik_ke} Anda berada pada zona merah (Tgl Kadaluarsa: " . $sip->tgl_kadaluarsa->format('d/m/Y') . "). Demi kepatuhan legalitas STARKES RSUD Ansari Saleh, mohon segera unggah SIP perpanjangan.",
                            'action_url' => '/portal/renewal?sip_id=' . $sip->id,
                            'status' => 'sent',
                            'sent_at' => $today->copy()->setHour(7)->setMinute(5),
                            'read_at' => null,
                        ]
                    );
                }

                if ($sip->status === 'menunggu_verifikasi') {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $adminSdm->id,
                            'message_type' => 'pengajuan_sip_baru',
                        ],
                        [
                            'sender_user_id' => $user->id,
                            'channel' => 'web',
                            'title' => 'Pengajuan Verifikasi SIP Baru: ' . $dokter->nama_lengkap,
                            'payload' => "{$dokter->nama_lengkap} ({$docData['ksm']}) telah mengunggah berkas perpanjangan SIP Praktik Ke-{$sip->tempat_praktik_ke} dengan Nomor: {$sip->no_sip}. Menunggu pemeriksaan SDM.",
                            'action_url' => '/verifikasi-sip',
                            'status' => 'sent',
                            'sent_at' => $sip->created_at,
                            'read_at' => null,
                        ]
                    );
                }

                if ($sip->status === 'ditolak') {
                    NotificationLog::firstOrCreate(
                        [
                            'sip_record_id' => $sip->id,
                            'recipient_user_id' => $user->id,
                            'message_type' => 'verifikasi_rejected',
                        ],
                        [
                            'sender_user_id' => $adminSdm->id,
                            'channel' => 'web',
                            'title' => 'Pengajuan SIP Perlu Revisi / Ditolak (' . $sip->no_sip . ')',
                            'payload' => "Berkas pengajuan SIP Praktik Ke-{$sip->tempat_praktik_ke} Anda ditolak oleh {$adminSdm->name}. Alasan: {$sip->catatan_verifikasi}",
                            'action_url' => '/portal/renewal',
                            'status' => 'read',
                            'sent_at' => $sip->verified_at,
                            'read_at' => $sip->verified_at?->copy()->addHours(2),
                        ]
                    );
                }
            }
        }
    }
}
