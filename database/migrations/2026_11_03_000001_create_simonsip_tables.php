<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ksm', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ksm', 150);
            $table->timestamps();
        });

        Schema::create('spesialisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_spesialisasi', 150);
            $table->timestamps();
        });

        Schema::create('dokters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip_nik', 50)->unique();
            $table->string('nama_lengkap', 255);
            $table->string('no_whatsapp', 20);
            $table->foreignId('ksm_id')->constrained('ksm')->restrictOnDelete();
            $table->foreignId('spesialisasi_id')->constrained('spesialisasi')->restrictOnDelete();
            $table->string('no_str', 100);
            $table->boolean('is_str_seumur_hidup')->default(true);
            $table->date('tgl_kadaluarsa_str')->nullable();
            $table->timestamps();
        });

        Schema::create('sip_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokters')->cascadeOnDelete();
            $table->string('no_sip', 100);
            $table->tinyInteger('tempat_praktik_ke')->default(1);
            $table->date('tgl_terbit');
            $table->date('tgl_kadaluarsa');
            $table->string('file_sip_path', 255);
            $table->enum('status', [
                'aktif',
                'warning',
                'mendesak',
                'expired',
                'menunggu_verifikasi',
                'archived',
                'ditolak',
            ])->default('aktif');
            $table->boolean('is_verified')->default(true);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sip_record_id')->nullable()->constrained('sip_records')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 50)->default('web');
            $table->enum('message_type', [
                'account_created',
                'early_warning',
                'urgent',
                'pengajuan_sip_baru',
                'verifikasi_approved',
                'verifikasi_rejected',
            ]);
            $table->string('title', 255);
            $table->text('payload');
            $table->string('action_url', 255)->nullable();
            $table->enum('status', ['sent', 'read'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('sip_records');
        Schema::dropIfExists('dokters');
        Schema::dropIfExists('spesialisasi');
        Schema::dropIfExists('ksm');
    }
};

