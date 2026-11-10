<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMONSIP') — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased">
@php
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
    $unreadNotifications = $authUser
        ? \App\Models\NotificationLog::where('recipient_user_id', $authUser->id)->where('status', 'sent')->latest('sent_at')->limit(6)->get()
        : collect();
    $unreadCount = $authUser
        ? \App\Models\NotificationLog::where('recipient_user_id', $authUser->id)->where('status', 'sent')->count()
        : 0;
    $pendingApprovalCount = ($authUser && $authUser->isAdminOrSuperAdmin())
        ? \App\Models\SipRecord::where('status', 'menunggu_verifikasi')->count()
        : 0;
@endphp

<div class="flex min-h-screen bg-slate-50">
    <!-- SIDEBAR UTAMA ADMIN SDM -->
    <aside class="w-72 bg-slate-900 text-slate-300 flex flex-col justify-between sticky top-0 h-screen shadow-lg z-30 flex-shrink-0">
        <!-- BAGIAN ATAS: Logo, Profil Admin, & Menu Navigasi Terkelompok -->
        <div class="overflow-y-auto flex-grow">
            <!-- Logo SIMONSIP -->
            <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
                <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                    RS
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-white leading-none">SIMONSIP <span class="text-[10px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-normal">STARKES</span></h1>
                    <span class="text-[10px] text-slate-400">RSUD Ansari Saleh</span>
                </div>
            </div>

            <!-- Kartu Profil Admin (Diperbaiki agar tidak terpotong) -->
            <div class="p-4 mx-3 mt-4 bg-slate-800/60 rounded-xl border border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                        AD
                    </div>
                    <div class="overflow-hidden">
                        <!-- Menggunakan text-wrap/normal agar nama panjang dan gelar terbaca utuh -->
                        <p class="text-xs font-bold text-white whitespace-normal leading-snug">
                            {{ $authUser->name ?? 'Hj. Nurul Hidayah, S.Kep., M.M.' }}
                        </p>
                        <span class="text-[10px] text-emerald-400 font-medium block mt-0.5">{{ $authUser->role === 'super_admin' ? 'Super Admin (IT / Ka. Subbag)' : 'Admin SDM (Kepegawaian)' }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigasi Menu Berdasarkan Kategori (Menu Grouping) -->
            <nav class="p-4 space-y-5 text-xs font-medium">
            @if($authUser->isAdminOrSuperAdmin())
                <!-- Kategori Utama -->
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Menu Utama</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>📊 Dashboard Monitoring</span>
                        </a>
                    </div>
                </div>

                <!-- Kategori Master Data -->
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Master Data</p>
                    <div class="space-y-1">
                        <a href="{{ route('dokter.index') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('dokter.index', 'dokter.create', 'dokter.show', 'dokter.edit') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>👨‍⚕️ Master Data Dokter</span>
                        </a>
                        <a href="{{ route('ksm.index') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('ksm.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>🏛️ Master Data KSM</span>
                        </a>
                        <a href="{{ route('spesialisasi.index') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('spesialisasi.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>🩺 Master Spesialisasi</span>
                        </a>
                    </div>
                </div>

                <!-- Kategori Verifikasi & Sistem -->
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Layanan & Laporan</p>
                    <div class="space-y-1">
                        <a href="{{ route('verifikasi.index') }}"
                           class="flex justify-between items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('verifikasi.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span class="flex items-center space-x-3"><span>📄 Verifikasi SIP Baru</span></span>
                            @if($pendingApprovalCount > 0)
                                <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full">{{ $pendingApprovalCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('notifications.index') }}"
                           class="flex justify-between items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('notifications.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span class="flex items-center space-x-3"><span>🔔 Pusat Notifikasi Web</span></span>
                            @if($unreadCount > 0)
                                <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full" id="sidebar-unread-badge">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('laporan.index') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('laporan.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>📈 Laporan & Rekapitulasi</span>
                        </a>
                    </div>
                </div>

                <!-- Kategori Otoritas Super Admin -->
                @if($authUser->isSuperAdmin())
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Otoritas Super Admin</p>
                        <div class="space-y-1">
                            <a href="{{ route('users.index') }}"
                               class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                                <span>⚙️ Manajemen Pengguna</span>
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <!-- SIDEBAR PORTAL DOKTER -->
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-3">Portal Mandiri Dokter</p>
                    <div class="space-y-1">
                        <a href="{{ route('portal.dashboard') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('portal.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>🏠 Dashboard Saya</span>
                        </a>
                        <a href="{{ route('portal.renewal.create') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('portal.renewal.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>📤 Unggah Perpanjangan SIP</span>
                        </a>
                        <a href="{{ route('portal.riwayat') }}"
                           class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('portal.riwayat') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span>📜 Riwayat SIP & Verifikasi</span>
                        </a>
                        <a href="{{ route('notifications.index') }}"
                           class="flex justify-between items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('notifications.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <span class="flex items-center space-x-3"><span>🔔 Kotak Masuk Notifikasi</span></span>
                            @if($unreadCount > 0)
                                <span class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            @endif
            </nav>
        </div>

        <!-- BAGIAN BAWAH: Scheduler, Ubah Kata Sandi, & Tombol Keluar (Sticky Footer) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex-shrink-0 space-y-2.5">
            <div class="flex justify-between items-center text-[11px] text-slate-400 px-1">
                <span>Scheduler Harian:</span>
                <span class="text-emerald-400 font-bold">07:00 WITA</span>
            </div>

            <div>
                <button type="button" onclick="document.getElementById('admin-password-panel').classList.toggle('hidden')"
                        class="w-full py-2 px-3 bg-slate-800/80 hover:bg-slate-800 text-slate-300 rounded-xl font-semibold text-xs transition flex items-center justify-between">
                    <span>🔐 Ubah Kata Sandi</span>
                    <span class="text-[10px] text-slate-400">▼</span>
                </button>
                <div id="admin-password-panel" class="hidden mt-2 p-3 rounded-xl bg-slate-900 border border-slate-700">
                    <form action="{{ route('profile.password') }}" method="POST" class="space-y-2 text-xs">
                        @csrf
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-0.5">Password Saat Ini</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-0.5">Password Baru</label>
                            <input type="password" name="new_password" required
                                   class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-0.5">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" required
                                   class="w-full px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-white outline-none">
                        </div>
                        <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">
                            Simpan Password
                        </button>
                    </form>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 px-3 bg-slate-800 hover:bg-rose-600 hover:text-white text-slate-200 rounded-xl font-semibold text-xs transition flex items-center justify-center space-x-2">
                    <span>🚪 Keluar Aplikasi</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- TOPBAR -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="px-6 py-3.5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">@yield('page_title', 'Sistem Monitoring SIP Dokter (SIMONSIP)')</h1>
                    <p class="text-xs text-slate-500">RSUD Dr. H. Moch. Ansari Saleh Banjarmasin • Provinsi Kalimantan Selatan</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Browser Web Notification API Permission Button -->
                    <button type="button" id="btn-enable-browser-notif" onclick="requestBrowserNotificationPermission()"
                            class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 transition">
                        <span>🔔</span>
                        <span id="browser-notif-label">Aktifkan Web Alert Browser</span>
                    </button>

                    <!-- Topbar Real-Time Notification Bell Dropdown -->
                    <div class="relative">
                        <button type="button" onclick="toggleNotifDropdown()"
                                class="relative p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition focus:outline-none">
                            <span class="text-lg">🔔</span>
                            <span id="topbar-bell-badge"
                                  class="{{ $unreadCount > 0 ? 'flex' : 'hidden' }} absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-600 text-white text-[11px] font-extrabold items-center justify-center shadow">
                                {{ $unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown Panel -->
                        <div id="notif-dropdown-panel"
                             class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                            <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">In-App Web Notification</p>
                                    <p class="text-sm font-bold">Lonceng Peringatan Real-Time</p>
                                </div>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs bg-slate-800 hover:bg-slate-700 px-2.5 py-1 rounded-lg text-emerald-300 font-semibold">
                                            Tandai Semua Dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div id="notif-dropdown-list" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                @forelse($unreadNotifications as $notif)
                                    <a href="{{ route('notifications.read', ['notificationLog' => $notif->id, 'redirect' => 1]) }}"
                                       class="block px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $notif->type_badge_class }}">
                                                {{ $notif->message_type_label }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">{{ $notif->sent_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-900 line-clamp-1">{{ $notif->title }}</p>
                                        <p class="text-xs text-slate-600 line-clamp-2 mt-0.5">{{ $notif->payload }}</p>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center text-slate-400 text-xs">
                                        Tidak ada notifikasi baru yang belum dibaca.
                                    </div>
                                @endforelse
                            </div>

                            <div class="p-2.5 bg-slate-50 border-t border-slate-200 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                                    Buka Pusat Notifikasi Web Lengkap →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Date / Time WITA Badge -->
                    <div class="hidden md:flex flex-col items-end text-right border-l border-slate-200 pl-3">
                        <span class="text-xs font-bold text-slate-800">{{ now()->translatedFormat('d F Y') }}</span>
                        <span class="text-[11px] text-emerald-700 font-semibold">{{ now()->format('H:i') }} WITA</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH ALERTS -->
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-2.5 text-sm font-semibold">
                        <span class="text-lg">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-2.5 text-sm font-semibold">
                        <span class="text-lg">⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm">
                    <p class="text-sm font-bold mb-1">Mohon periksa kembali isian formulir:</p>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- MAIN VIEW CONTENT -->
        <main class="flex-1 px-6 pb-10">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-2">
            <div>
                <strong>SIMONSIP</strong> — Sistem Monitoring Surat Izin Praktik (SIP) Dokter Berbasis Web Notification & 5 Modul Laporan Eksekutif
            </div>
            <div>
                RSUD Dr. H. Moch. Ansari Saleh Banjarmasin © {{ date('Y') }}
            </div>
        </footer>
    </div>
</div>

<script>
    function toggleNotifDropdown() {
        const panel = document.getElementById('notif-dropdown-panel');
        panel.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        const panel = document.getElementById('notif-dropdown-panel');
        const btn = e.target.closest('[onclick="toggleNotifDropdown()"]');
        if (panel && !panel.contains(e.target) && !btn) {
            panel.classList.add('hidden');
        }
    });

    function updateBrowserNotifButtonState() {
        const label = document.getElementById('browser-notif-label');
        if (!label || !('Notification' in window)) return;
        if (Notification.permission === 'granted') {
            label.textContent = 'Web Alert Browser Aktif';
        } else if (Notification.permission === 'denied') {
            label.textContent = 'Web Alert Diblokir Browser';
        }
    }

    function requestBrowserNotificationPermission() {
        if (!('Notification' in window)) {
            alert('Browser Anda tidak mendukung Web Notification API.');
            return;
        }
        Notification.requestPermission().then(function (permission) {
            updateBrowserNotifButtonState();
            if (permission === 'granted') {
                new Notification('SIMONSIP RSUD Ansari Saleh', {
                    body: 'Notifikasi Web Real-Time telah aktif di perangkat Anda!',
                });
            }
        });
    }

    updateBrowserNotifButtonState();

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // Real-time polling for Topbar Bell Badge & Dropdown every 15 seconds
    let lastSeenTopNotifId = null;
    setInterval(function () {
        fetch('{{ route('notifications.poll') }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (!data) return;
            const badge = document.getElementById('topbar-bell-badge');
            if (badge) {
                badge.textContent = data.unread_count;
                if (data.unread_count > 0) {
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            }

            const sidebarBadge = document.getElementById('sidebar-unread-badge');
            if (sidebarBadge) {
                sidebarBadge.textContent = data.unread_count;
                if (data.unread_count > 0) {
                    sidebarBadge.classList.remove('hidden');
                } else {
                    sidebarBadge.classList.add('hidden');
                }
            }

            const listEl = document.getElementById('notif-dropdown-list');
            if (listEl && Array.isArray(data.latest)) {
                if (data.latest.length === 0) {
                    listEl.innerHTML = '<div class="px-4 py-8 text-center text-slate-400 text-xs">Tidak ada notifikasi baru yang belum dibaca.</div>';
                } else {
                    listEl.innerHTML = data.latest.map(item => `
                        <a href="${escapeHtml(item.read_url)}" class="block px-4 py-3 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border ${escapeHtml(item.type_badge_class || 'bg-slate-100 text-slate-800 border-slate-200')}">
                                    ${escapeHtml(item.type_label)}
                                </span>
                                <span class="text-[11px] text-slate-400">${escapeHtml(item.sent_at_human)}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-900 line-clamp-1">${escapeHtml(item.title)}</p>
                            <p class="text-xs text-slate-600 line-clamp-2 mt-0.5">${escapeHtml(item.payload)}</p>
                        </a>
                    `).join('');
                }
            }

            if (data.latest && data.latest.length > 0) {
                const newest = data.latest[0];
                if (lastSeenTopNotifId !== null && newest.id !== lastSeenTopNotifId) {
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification(newest.title, { body: newest.payload });
                    }
                }
                lastSeenTopNotifId = newest.id;
            }
        })
        .catch(() => {});
    }, 15000);
</script>
@stack('scripts')
</body>
</html>

