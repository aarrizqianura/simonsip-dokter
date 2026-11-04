<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MasterKsmController;
use App\Http\Controllers\MasterSpesialisasiController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\PortalDokterController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VerifikasiSipController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->role === 'dokter'
        ? redirect()->route('portal.dashboard')
        : redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('login.quick');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Digital SIP PDF Preview / Download
    Route::get('/sip-records/{sipRecord}/pdf', [DokterController::class, 'previewSipPdf'])->name('sip.pdf');

    // Web Notification Center & Bell Polling
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/{notificationLog}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/api/notifications/poll', [NotificationCenterController::class, 'pollUnread'])->name('notifications.poll');

    // Admin SDM & Super Admin Modules
    Route::middleware('role:super_admin,admin_sdm')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/early-warning', [DashboardController::class, 'triggerEarlyWarning'])->name('dashboard.early-warning');

        // Master Data Dokter + Integrated User Account + Bulk Import
        Route::get('/dokter/template', [DokterController::class, 'downloadTemplate'])->name('dokter.template');
        Route::post('/dokter/import', [DokterController::class, 'import'])->name('dokter.import');
        Route::post('/dokter/{dokter}/sip', [DokterController::class, 'storeSip'])->name('dokter.sip.store');
        Route::resource('dokter', DokterController::class);

        // Master Data KSM & Spesialisasi
        Route::resource('master-ksm', MasterKsmController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['master-ksm' => 'ksm'])
            ->names('ksm');

        Route::resource('master-spesialisasi', MasterSpesialisasiController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['master-spesialisasi' => 'spesialisasi'])
            ->names('spesialisasi');

        // Verifikasi SIP (Approval Queue)
        Route::get('/verifikasi-sip', [VerifikasiSipController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi-sip/{sipRecord}/approve', [VerifikasiSipController::class, 'approve'])->name('verifikasi.approve');
        Route::post('/verifikasi-sip/{sipRecord}/reject', [VerifikasiSipController::class, 'reject'])->name('verifikasi.reject');

        // Direct Web Alert from Admin SDM
        Route::post('/notifications/send-direct', [NotificationCenterController::class, 'sendDirect'])->name('notifications.send-direct');

        // 5 Modul Laporan Eksekutif (Web, Print/PDF Kop RSUD, Excel)
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });

    // Khusus Super Admin: Manajemen Pengguna
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    // Portal Mandiri Dokter
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalDokterController::class, 'dashboard'])->name('dashboard');
        Route::get('/renewal', [PortalDokterController::class, 'createRenewal'])->name('renewal.create');
        Route::post('/renewal', [PortalDokterController::class, 'storeRenewal'])->name('renewal.store');
        Route::get('/riwayat', [PortalDokterController::class, 'riwayat'])->name('riwayat');
    });
});
