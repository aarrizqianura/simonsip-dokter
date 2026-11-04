<?php

namespace App\Console\Commands;

use App\Services\WebNotificationService;
use Illuminate\Console\Command;

class CheckSipExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sip:check-expiry {--force : Paksa kirim notifikasi hari ini meskipun sudah terkirim}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Early Warning Engine: Evaluasi masa berlaku SIP dokter dan kirim Web Notification otomatis (Harian 07:00 WITA)';

    /**
     * Execute the console command.
     */
    public function handle(WebNotificationService $notificationService): int
    {
        $this->info('Memulai pemeriksaan masa berlaku SIP Dokter RSUD Dr. H. Moch. Ansari Saleh...');

        $summary = $notificationService->runEarlyWarningCheck(
            triggeredBy: null,
            forceNotify: (bool) $this->option('force')
        );

        $this->info(sprintf(
            'Selesai! Total SIP diperiksa: %d | Status diperbarui: %d | Notifikasi Web dikirim: %d',
            $summary['checked'],
            $summary['status_updated'],
            $summary['notifications_sent']
        ));

        return self::SUCCESS;
    }
}

