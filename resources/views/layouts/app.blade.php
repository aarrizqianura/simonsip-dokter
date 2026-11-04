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

<div class="min-h-screen flex flex-col lg:flex-row">
    <!-- SIDEBAR NAVIGATION -->
    <aside class="w-full lg:w-72 bg-slate-900 text-slate-100 flex-shrink-0 flex flex-col border-r border-slate-800">
        <!-- Brand Header -->
        <div class="p-5 border-b border-slate-800 flex items-center gap-3.5 bg-slate-950/50">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-extrabold text-lg shadow-lg shadow-emerald-900/40 shrink-0">
                RS
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-base tracking-tight text-white">SIMONSIP</span>
                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">STARKES</span>
                </div>
                <p class="text-xs text-slate-400 truncate">RSUD Dr. H. Moch. Ansari Saleh</p>
            </div>
        </div>

        <!-- User Role Card -->
        <div class="px-4 py-3.5 bg-slate-800/50 border-b border-slate-800/80">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">{{ $authUser->role_label }}</span>
                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Online
                </span>
            </div>
            <p class="text-sm font-bold text-white truncate mt-0.5">{{ $authUser->name }}</p>
            <p class="text-xs text-slate-400 truncate">ID Login: <span class="font-mono text-slate-300">{{ $authUser->username }}</span></p>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @if($authUser->isAdminOrSuperAdmin())
                <div class="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Utama SDM & Eksekutif
                </div>

                <!-- 1. Dashboard Monitoring -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>📊</span>
                        <span>Dashboard Monitoring</span>
                    </span>
                </a>

                <!-- 2. Master Data Dokter -->
                <a href="{{ route('dokter.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dokter.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>👨‍⚕️</span>
                        <span>Master Data Dokter</span>
                    </span>
                </a>

                <!-- 3. Master Data KSM -->
                <a href="{{ route('ksm.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('ksm.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>🏛️</span>
                        <span>Master Data KSM</span>
                    </span>
                </a>

                <!-- 4. Master Spesialisasi -->
                <a href="{{ route('spesialisasi.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('spesialisasi.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>🩺</span>
                        <span>Master Spesialisasi</span>
                    </span>
                </a>

                <!-- 5. Verifikasi SIP (Approval Queue) -->
                <a href="{{ route('verifikasi.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('verifikasi.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>📄</span>
                        <span>Verifikasi SIP Baru</span>
                    </span>
                    @if($pendingApprovalCount > 0)
                        <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-sky-500 text-white">
                            {{ $pendingApprovalCount }}
                        </span>
                    @endif
                </a>

                <!-- 6. Pusat Notifikasi Web -->
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('notifications.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>🔔</span>
                        <span>Pusat Notifikasi Web</span>
                    </span>
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-rose-500 text-white" id="sidebar-unread-badge">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>

                <!-- 7. Laporan & Rekapitulasi (5 Modul) -->
                <a href="{{ route('laporan.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('laporan.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>📈</span>
                        <span>Laporan & Rekapitulasi</span>
                    </span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-800 text-emerald-300 border border-slate-700">5 Modul</span>
                </a>

                <!-- 8. Manajemen Pengguna (Khusus Super Admin) -->
                @if($authUser->isSuperAdmin())
                    <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Otoritas Super Admin
                    </div>
                    <a href="{{ route('users.index') }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <span>⚙️</span>
                            <span>Manajemen Pengguna</span>
                        </span>
                    </a>
                @endif
            @else
                <!-- SIDEBAR PORTAL DOKTER -->
                <div class="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Portal Mandiri Dokter
                </div>

                <!-- 1. Dashboard Saya -->
                <a href="{{ route('portal.dashboard') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('portal.dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>🏠</span>
                        <span>Dashboard Saya</span>
                    </span>
                </a>

                <!-- 2. Unggah Perpanjangan SIP -->
                <a href="{{ route('portal.renewal.create') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('portal.renewal.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>📤</span>
                        <span>Unggah Perpanjangan SIP</span>
                    </span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">1-Step</span>
                </a>

                <!-- 3. Riwayat SIP -->
                <a href="{{ route('portal.riwayat') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('portal.riwayat') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>📜</span>
                        <span>Riwayat SIP & Verifikasi</span>
                    </span>
                </a>

                <!-- 4. Notifikasi Web Saya -->
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('notifications.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <span>🔔</span>
                        <span>Kotak Masuk Notifikasi</span>
                    </span>
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-rose-500 text-white">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
            @endif
        </nav>

        <!-- Sidebar Footer Info -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 text-xs text-slate-400 space-y-2">
            <div class="flex items-center justify-between">
                <span>Scheduler Harian:</span>
                <span class="font-mono text-emerald-400 font-semibold">07:00 WITA</span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-200 hover:text-white font-semibold transition flex items-center justify-center gap-2">
                    <span>🚪</span> Keluar Aplikasi
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

    // Real-time polling for Topbar Bell Badge every 15 seconds
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

