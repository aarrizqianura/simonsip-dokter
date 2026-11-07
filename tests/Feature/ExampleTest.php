<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_is_accessible_with_demo_accounts(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIMONSIP DOKTER');
        $response->assertSee('RSUD Dr. H. Moch. Ansari Saleh Banjarmasin');
    }

    public function test_integrated_doctor_creation_and_end_to_end_renewal_approval_workflow(): void
    {
        $adminSdm = User::where('role', 'admin_sdm')->firstOrFail();
        $ksm = Ksm::firstOrFail();
        $spesialisasi = Spesialisasi::firstOrFail();

        // 1. Admin SDM creates a new doctor + auto login account + initial SIP
        $response = $this->actingAs($adminSdm)->post('/dokter', [
            'username' => '199501012022031001',
            'password' => 'Ansari@2026',
            'nip_nik' => '199501012022031001',
            'nama_lengkap' => 'dr. Muhammad Luthfi, Sp.PD',
            'ksm_id' => $ksm->id,
            'spesialisasi_id' => $spesialisasi->id,
            'no_whatsapp' => '081255667788',
            'no_str' => 'STR-9988776655',
            'is_str_seumur_hidup' => true,
            'no_sip' => '503/0991/SIP-DOK/DPMPTSP/2022',
            'tempat_praktik_ke' => 1,
            'tgl_terbit' => Carbon::today()->subYears(5)->addDays(45)->format('Y-m-d'),
            'tgl_kadaluarsa' => Carbon::today()->addDays(45)->format('Y-m-d'),
            'send_welcome_notification' => true,
        ]);

        $response->assertRedirect();

        $dokter = Dokter::where('nip_nik', '199501012022031001')->firstOrFail();
        $dokterUser = $dokter->user;
        $initialSip = $dokter->sipRecords()->firstOrFail();

        // Since tgl_kadaluarsa is +45 days, status must be 'mendesak' (31-60 days)
        $this->assertEquals('mendesak', $initialSip->status);
        $this->assertDatabaseHas('notification_logs', [
            'recipient_user_id' => $dokterUser->id,
            'message_type' => 'account_created',
        ]);

        // 2. Doctor logs in, views Top Navigation Dashboard, Renewal form, and submits 1-Step Renewal
        $this->actingAs($dokterUser)
            ->get(route('dokter.dashboard'))
            ->assertStatus(200)
            ->assertSee('Portal Dokter')
            ->assertSee('Pemberitahuan Sistem')
            ->assertSee('Digital Preview Kartu SIP Saya')
            ->assertSee('503/0991/SIP-DOK/DPMPTSP/2022');

        $this->actingAs($dokterUser)
            ->get(route('dokter.renewal'))
            ->assertStatus(200)
            ->assertSee('FORMULIR 1-STEP RENEWAL');

        $renewalResponse = $this->actingAs($dokterUser)->post('/portal/renewal', [
            'tempat_praktik_ke' => 1,
            'no_sip' => '503/0999/SIP-DOK/DPMPTSP/2026',
            'tgl_terbit' => Carbon::today()->format('Y-m-d'),
            'tgl_kadaluarsa' => Carbon::today()->addYears(5)->format('Y-m-d'),
            'catatan_dokter' => 'Mohon verifikasi SIP perpanjangan 5 tahun.',
        ]);

        $renewalResponse->assertRedirect(route('portal.riwayat'));

        $this->actingAs($dokterUser)
            ->get(route('dokter.riwayat'))
            ->assertStatus(200)
            ->assertSee('503/0999/SIP-DOK/DPMPTSP/2026');

        $newSip = SipRecord::where('no_sip', '503/0999/SIP-DOK/DPMPTSP/2026')->firstOrFail();
        $this->assertEquals('menunggu_verifikasi', $newSip->status);
        $this->assertFalse($newSip->is_verified);

        // 2b. Admin SDM views the redesigned Verifikasi SIP Baru page (Table + Accordion + Quick Preview PDF Modal)
        $this->actingAs($adminSdm)
            ->get(route('verifikasi.index'))
            ->assertStatus(200)
            ->assertSee('Tabel Interaktif')
            ->assertSee('Kartu Ringkas')
            ->assertSee('Detail Lengkap')
            ->assertSee('modal-preview-pdf')
            ->assertSee('modal-quick-verify');

        // 3. Admin SDM approves the new SIP submission
        $approveResponse = $this->actingAs($adminSdm)->post(route('verifikasi.approve', $newSip), [
            'catatan_verifikasi' => 'Dokumen sah dan terverifikasi SDM.',
        ]);

        $approveResponse->assertRedirect();

        $newSip->refresh();
        $initialSip->refresh();

        $this->assertEquals('aktif', $newSip->status);
        $this->assertTrue($newSip->is_verified);
        $this->assertEquals('archived', $initialSip->status);

        // 4. Verify all 5 Executive Reports render 200 OK
        foreach (['kepatuhan', 'notifikasi', 'verifikasi', 'histori', 'sla'] as $tab) {
            $this->actingAs($adminSdm)
                ->get(route('laporan.index', ['tab' => $tab]))
                ->assertStatus(200);

            $this->actingAs($adminSdm)
                ->get(route('laporan.index', ['tab' => $tab, 'export' => 'print']))
                ->assertStatus(200);

            $this->actingAs($adminSdm)
                ->get(route('laporan.index', ['tab' => $tab, 'export' => 'excel']))
                ->assertStatus(200);
        }
    }
}
