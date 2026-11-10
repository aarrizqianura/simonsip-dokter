<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Dokter - SIMONSIP RSUD Dr. H. Moch. Ansari Saleh')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col justify-between" x-data="{ mobileMenuOpen: false, mobilePasswordOpen: false }">

    <!-- TOP HEADER / NAVBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            
            <!-- Logo & Title -->
            <a href="{{ route('dokter.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    RS
                </div>
                <div>
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase block">Portal Dokter</span>
                    <h1 class="text-base font-extrabold text-slate-900 leading-none">SIMONSIP Ansari Saleh</h1>
                </div>
            </a>

            <!-- Desktop Navigation Links (Hidden on Mobile) -->
            <nav class="hidden md:flex space-x-2">
                <a href="{{ route('dokter.dashboard') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-lg flex items-center space-x-2 transition {{ request()->routeIs('dokter.dashboard', 'portal.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>Dashboard Saya</span>
                </a>

                <a href="{{ route('dokter.renewal') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-lg flex items-center space-x-2 transition {{ request()->routeIs('dokter.renewal*', 'portal.renewal.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>Unggah Perpanjangan SIP</span>
                </a>

                <a href="{{ route('dokter.riwayat') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-lg flex items-center space-x-2 transition {{ request()->routeIs('dokter.riwayat', 'portal.riwayat') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>Riwayat SIP</span>
                </a>
            </nav>

            <!-- Right Actions: Notification Bell & Hamburger Menu Button -->
            <div class="flex items-center space-x-2">
                
                <!-- Web Notification Bell Dropdown -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button type="button" @click="notifOpen = !notifOpen" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full relative focus:outline-none transition" title="Pemberitahuan Sistem">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/></svg>
                        
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span id="dokter-bell-dot" class="absolute top-0 right-0 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                        @else
                            <span id="dokter-bell-dot" class="hidden absolute top-0 right-0 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                        @endif
                    </button>

                    <!-- Dropdown Content Notifikasi -->
                    <div x-show="notifOpen" x-cloak @click.away="notifOpen = false" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-slate-100 p-4 z-50">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-100">
                            <h4 class="text-sm font-bold text-slate-800">Pemberitahuan Sistem</h4>
                            <div class="flex items-center gap-2">
                                <span id="dokter-unread-badge" class="text-xs bg-emerald-100 text-emerald-800 font-medium px-2 py-0.5 rounded-full">
                                    {{ auth()->user()->unreadNotifications->count() }} Baru
                                </span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-emerald-700 hover:underline font-semibold">
                                            Baca Semua
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        
                        <div id="dokter-notif-dropdown-list" class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse(auth()->user()->notifications->take(5) as $notif)
                                <a href="{{ route('notifications.read', ['notificationLog' => $notif->id, 'redirect' => 1]) }}"
                                   class="block p-2.5 rounded-lg text-xs transition hover:bg-emerald-50 {{ $notif->read_at ? 'bg-slate-50 text-slate-600' : 'bg-emerald-50/60 text-slate-900 font-semibold' }}">
                                    <p class="font-bold text-slate-800">{{ $notif->data['title'] ?? 'Pemberitahuan' }}</p>
                                    <p class="mt-0.5 line-clamp-2">{{ $notif->data['message'] ?? '' }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ ($notif->sent_at ?? $notif->created_at)?->diffForHumans() }}</span>
                                </a>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada pemberitahuan.</p>
                            @endforelse
                        </div>

                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <button type="button" onclick="requestBrowserNotificationPermission()" id="btn-enable-browser-notif" class="text-slate-500 hover:text-emerald-700 font-medium">
                                🔔 <span id="browser-notif-label">Aktifkan Web Alert</span>
                            </button>
                            <a href="{{ route('notifications.index') }}" class="font-bold text-emerald-700 hover:text-emerald-800">
                                Lihat Semua →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hamburger Menu Button (Visible on Mobile Only, Top Right) -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg focus:outline-none transition" aria-label="Buka Menu Navigasi">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Desktop Profile Dropdown (Ubah Kata Sandi & Logout, Hidden on Mobile) -->
                <div class="hidden md:block relative pl-3 border-l border-slate-200" x-data="{ profileOpen: false, desktopPasswordOpen: false }">
                    <button type="button" @click="profileOpen = !profileOpen" class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">dr</div>
                        <span class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Desktop Panel -->
                    <div x-show="profileOpen" x-cloak @click.away="profileOpen = false" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 space-y-3">
                        <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shrink-0">dr</div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                <span class="text-[10px] text-emerald-600 font-semibold block">ID Login: {{ auth()->user()->username }}</span>
                            </div>
                        </div>

                        <!-- Tombol Toggle Ubah Kata Sandi (Desktop Dropdown) -->
                        <div>
                            <button type="button" @click="desktopPasswordOpen = !desktopPasswordOpen"
                                    class="w-full py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-xs transition flex items-center justify-between">
                                <span class="flex items-center space-x-2">
                                    <span>🔐</span>
                                    <span>Ubah Kata Sandi</span>
                                </span>
                                <span class="text-[10px] text-slate-500" x-text="desktopPasswordOpen ? '▲ Tutup' : '▼ Buka'">▼ Buka</span>
                            </button>

                            <div x-show="desktopPasswordOpen" x-cloak class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <form action="{{ route('profile.password') }}" method="POST" class="space-y-2.5 text-xs">
                                    @csrf
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Password Saat Ini</label>
                                        <input type="password" name="current_password" required
                                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Password Baru</label>
                                        <input type="password" name="new_password" required
                                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                                        <input type="password" name="new_password_confirmation" required
                                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </div>
                                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                                        Simpan Kata Sandi Baru
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Tombol Keluar Aplikasi (Desktop Dropdown) -->
                        <form action="{{ route('logout') }}" method="POST" class="pt-2 border-t border-slate-100">
                            @csrf
                            <button type="submit" class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl font-semibold text-xs transition flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </header>

    <!-- MOBILE SLIDE-OVER MENU (DRAWER) -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 flex md:hidden" style="display: none;">
        <!-- Backdrop Gelap -->
        <div @click="mobileMenuOpen = false"
             x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <!-- Panel Menu Geser dari Kanan -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="relative ml-auto w-80 max-w-full bg-white h-full shadow-2xl flex flex-col justify-between p-6 overflow-y-auto">
            
            <div>
                <!-- Header Drawer -->
                <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">dr</div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 line-clamp-1">{{ auth()->user()->name }}</p>
                            <span class="text-[10px] text-emerald-600 font-semibold">Staf Medis Aktif</span>
                        </div>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navigasi Menu Mobile -->
                <nav class="space-y-2 text-sm font-medium">
                    <a href="{{ route('dokter.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('dokter.dashboard', 'portal.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>🏠 Dashboard Saya</span>
                    </a>
                    <a href="{{ route('dokter.renewal') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('dokter.renewal*', 'portal.renewal.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>📤 Unggah Perpanjangan SIP</span>
                    </a>
                    <a href="{{ route('dokter.riwayat') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('dokter.riwayat', 'portal.riwayat') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>📜 Riwayat SIP</span>
                    </a>
                </nav>
            </div>

            <!-- Pengaturan Kata Sandi & Tombol Keluar di Bagian Bawah Drawer -->
            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                <!-- Tombol & Form Ubah Kata Sandi (Mobile Drawer) -->
                <div>
                    <button type="button" @click="mobilePasswordOpen = !mobilePasswordOpen"
                            class="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-xs transition flex items-center justify-between">
                        <span class="flex items-center space-x-2">
                            <span>🔐</span>
                            <span>Ubah Kata Sandi</span>
                        </span>
                        <span class="text-[10px] text-slate-500" x-text="mobilePasswordOpen ? '▲ Tutup' : '▼ Buka'">▼ Buka</span>
                    </button>

                    <div x-show="mobilePasswordOpen" x-cloak class="mt-2 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <form action="{{ route('profile.password') }}" method="POST" class="space-y-2.5 text-xs">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Password Saat Ini</label>
                                <input type="password" name="current_password" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Password Baru</label>
                                <input type="password" name="new_password" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="new_password_confirmation" required
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <button type="submit" class="w-full py-2.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                                Simpan Kata Sandi Baru
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tombol Keluar Aplikasi -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl font-semibold text-xs transition flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar Aplikasi</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold text-xs">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm">
                <p class="text-xs font-bold mb-1">Mohon periksa kembali isian formulir:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-4 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div>
                <strong>SIMONSIP Portal Staf Medis</strong> &copy; {{ date('Y') }} RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
            </div>
            <div>
                Pusat Bantuan SDM Kepegawaian: (0511) 3300759 / ext. 104
            </div>
        </div>
    </footer>

    <script>
        function updateBrowserNotifButtonState() {
            const label = document.getElementById('browser-notif-label');
            if (!label || !('Notification' in window)) return;
            if (Notification.permission === 'granted') {
                label.textContent = 'Web Alert Aktif';
            } else if (Notification.permission === 'denied') {
                label.textContent = 'Web Alert Diblokir';
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

        function escapeDokterHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // Real-time polling for unread notification badge & dropdown every 15 seconds
        let lastSeenDokterNotifId = null;
        setInterval(function () {
            fetch('{{ route('notifications.poll') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (!data) return;
                const dot = document.getElementById('dokter-bell-dot');
                const badge = document.getElementById('dokter-unread-badge');
                if (badge) {
                    badge.textContent = data.unread_count + ' Baru';
                }
                if (dot) {
                    if (data.unread_count > 0) {
                        dot.classList.remove('hidden');
                    } else {
                        dot.classList.add('hidden');
                    }
                }
                if (data.latest && data.latest.length > 0) {
                    const newest = data.latest[0];
                    const listEl = document.getElementById('dokter-notif-dropdown-list');
                    if (listEl) {
                        listEl.innerHTML = data.latest.map(item => `
                            <a href="${escapeDokterHtml(item.read_url)}"
                               class="block p-2.5 rounded-lg text-xs transition hover:bg-emerald-50 bg-emerald-50/60 text-slate-900 font-semibold">
                                <p class="font-bold text-slate-800">${escapeDokterHtml(item.title)}</p>
                                <p class="mt-0.5 line-clamp-2">${escapeDokterHtml(item.payload)}</p>
                                <span class="text-[10px] text-slate-400 mt-1 block">${escapeDokterHtml(item.sent_at_human)}</span>
                            </a>
                        `).join('');
                    }
                    if (lastSeenDokterNotifId !== null && newest.id !== lastSeenDokterNotifId) {
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(newest.title, { body: newest.payload });
                        }
                    }
                    lastSeenDokterNotifId = newest.id;
                }
            })
            .catch(() => {});
        }, 15000);
    </script>
    @stack('scripts')
</body>
</html>

