@extends(auth()->user()?->isDokter() ? 'layouts.dokter' : 'layouts.app')

@section('title', 'Pusat Notifikasi Web')
@section('page_title', '🔔 Pusat Notifikasi Web (In-App Notification Center & Audit Log)')

@section('content')
@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
@endphp

<div class="space-y-6">
    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-slate-500">Total Notifikasi Web</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalNotif }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Channel: Internal Web In-App</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border-l-4 border-l-emerald-500 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-emerald-700">Sudah Dibaca (Read)</span>
            <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $totalRead }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Terekam timestamp read_at</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border-l-4 border-l-amber-500 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-amber-700">Belum Dibaca (Sent)</span>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $totalUnread }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Tampil di Lonceng Topbar</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border-l-4 border-l-sky-500 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-sky-700">Persentase Respons Baca</span>
            <p class="text-2xl font-extrabold text-sky-700 mt-1">{{ $readRate }}%</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Akuntabilitas Notifikasi Dini</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Notification Log List -->
        <div class="{{ $authUser->isAdminOrSuperAdmin() ? 'lg:col-span-8' : 'lg:col-span-12' }} bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Log Notifikasi Web & Jejak Keterbacaan</h3>
                    <p class="text-xs text-slate-500">Klik pada notifikasi atau tombol "Tandai Dibaca" untuk memperbarui status audit.</p>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                            ✓ Tandai Semua Notifikasi Saya Dibaca
                        </button>
                    </form>
                </div>
            </div>

            <!-- Filter Bar -->
            <form action="{{ route('notifications.index') }}" method="GET" class="p-4 border-b border-slate-200 bg-white flex flex-wrap items-center gap-2 text-xs">
                @if($authUser->isAdminOrSuperAdmin())
                    <select name="mode" class="px-3 py-2 rounded-xl border border-slate-300 bg-white font-semibold">
                        <option value="all" @selected($viewMode === 'all')>Semua Log Sistem (Monitoring SDM)</option>
                        <option value="personal" @selected($viewMode === 'personal')>Hanya Kotak Masuk Saya</option>
                    </select>
                @endif

                <select name="message_type" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">Semua Jenis Notifikasi</option>
                    <option value="account_created" @selected($typeFilter === 'account_created')>Akun Baru Dibuat</option>
                    <option value="early_warning" @selected($typeFilter === 'early_warning')>Peringatan Dini (H-90 / H-60)</option>
                    <option value="urgent" @selected($typeFilter === 'urgent')>Peringatan Darurat / Kadaluarsa</option>
                    <option value="pengajuan_sip_baru" @selected($typeFilter === 'pengajuan_sip_baru')>Pengajuan SIP Baru Masuk</option>
                    <option value="verifikasi_approved" @selected($typeFilter === 'verifikasi_approved')>SIP Disetujui SDM</option>
                    <option value="verifikasi_rejected" @selected($typeFilter === 'verifikasi_rejected')>SIP Ditolak / Revisi SDM</option>
                </select>

                <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">Semua Status Baca</option>
                    <option value="sent" @selected($statusFilter === 'sent')>Belum Dibaca (Sent)</option>
                    <option value="read" @selected($statusFilter === 'read')>Sudah Dibaca (Read)</option>
                </select>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-bold">Filter</button>
                <a href="{{ route('notifications.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">Reset</a>
            </form>

            <div class="divide-y divide-slate-200">
                @forelse($notifications as $notif)
                    <div class="p-4 hover:bg-slate-50 transition {{ $notif->status === 'sent' ? 'bg-amber-50/20' : '' }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded border text-[10px] font-bold {{ $notif->type_badge_class }}">
                                    {{ $notif->message_type_label }}
                                </span>
                                <span class="text-xs font-bold text-slate-700">
                                    Penerima: {{ $notif->recipient?->name }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 text-[11px]">
                                @if($notif->status === 'read')
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">
                                        ✓ Dibaca ({{ $notif->read_at?->format('d/m/Y H:i') }} WITA)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">
                                        ● Terkirim / Belum Dibaca
                                    </span>
                                @endif
                                <span class="text-slate-400 font-mono">{{ $notif->sent_at?->format('d/m/Y H:i') }} WITA</span>
                            </div>
                        </div>

                        <h4 class="text-sm font-extrabold text-slate-900">{{ $notif->title }}</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notif->payload }}</p>

                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Pengirim: {{ $notif->sender?->name ?? 'Scheduler Otomatis SIMONSIP (07:00 WITA)' }}</span>
                            <div class="flex items-center gap-2">
                                @if($notif->status === 'sent' && ($notif->recipient_user_id === $authUser->id || ($authUser->isAdminOrSuperAdmin() && $notif->message_type === 'pengajuan_sip_baru')))
                                    <form action="{{ route('notifications.read', $notif) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-emerald-700 font-bold hover:underline">
                                            ✓ Tandai Dibaca
                                        </button>
                                    </form>
                                @endif
                                @if($notif->action_url || ($authUser->isAdminOrSuperAdmin() && ($notif->sip_record_id || $notif->recipient?->dokter)))
                                    <a href="{{ route('notifications.read', ['notificationLog' => $notif->id, 'redirect' => 1]) }}"
                                       class="text-sky-700 font-bold hover:underline">
                                        Buka Tautan Terkait →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-400 text-xs">Tidak ada data notifikasi web.</div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

        <!-- Direct In-App Web Alert Sender (For Admin SDM & Super Admin) -->
        @if($authUser->isAdminOrSuperAdmin())
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-slate-900 text-white">
                        <h3 class="text-sm font-extrabold">📨 Kirim Direct In-App Alert ke Dokter</h3>
                        <p class="text-xs text-slate-400">Kirim peringatan langsung ke lonceng notifikasi Portal Dokter</p>
                    </div>

                    <form action="{{ route('notifications.send-direct') }}" method="POST" class="p-5 space-y-3.5 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih Penerima (Dokter)</label>
                            <select name="recipient_user_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                                <option value="">-- Pilih Dokter --</option>
                                @foreach($dokterList as $d)
                                    @if($d->user)
                                        <option value="{{ $d->user->id }}">
                                            {{ $d->nama_lengkap }} ({{ $d->ksm?->nama_ksm }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori Pesan Peringatan</label>
                            <select name="message_type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                                <option value="early_warning">🟡 Peringatan Dini (Early Warning)</option>
                                <option value="urgent">🔴 Peringatan Darurat (Urgent / Kadaluarsa)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Judul Notifikasi Web</label>
                            <input type="text" name="title" required
                                   value="[Peringatan SDM] Segera Perbarui Berkas SIP Anda"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Isi Pesan Peringatan</label>
                            <textarea name="payload" rows="4" required
                                      class="w-full px-3 py-2 rounded-xl border border-slate-300">Yth. Dokter Staf Medis RSUD Dr. H. Moch. Ansari Saleh, mohon segera melakukan unggah berkas perpanjangan SIP melalui fitur 1-Step Renewal pada Portal Mandiri Dokter.</textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-sm">
                            🔔 Kirim In-App Web Notification
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

