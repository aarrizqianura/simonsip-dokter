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
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col justify-between">

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

            <!-- Main Navigation Links -->
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

            <!-- User Profile & Web Notification Bell Dropdown -->
            <div class="flex items-center space-x-3">
                
                <!-- Web Notification Dropdown Icon -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full relative focus:outline-none transition" title="Pemberitahuan Sistem">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/></svg>
                        
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span id="dokter-bell-dot" class="absolute top-0 right-0 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                        @else
                            <span id="dokter-bell-dot" class="hidden absolute top-0 right-0 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                        @endif
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-slate-100 p-4 z-50">
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
                        
                        <div class="space-y-2 max-h-64 overflow-y-auto">
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

                <!-- Doctor Profile & Logout -->
                <div class="flex items-center space-x-2 pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">
                        dr
                    </div>
                    <span class="text-xs font-semibold text-slate-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                    
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Keluar Aplikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Mobile Navigation Links -->
        <nav class="flex md:hidden border-t border-slate-100 px-4 py-2 space-x-2 overflow-x-auto bg-slate-50/70">
            <a href="{{ route('dokter.dashboard') }}" 
               class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->routeIs('dokter.dashboard', 'portal.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Dashboard Saya
            </a>
            <a href="{{ route('dokter.renewal') }}" 
               class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->routeIs('dokter.renewal*', 'portal.renewal.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Unggah Perpanjangan SIP
            </a>
            <a href="{{ route('dokter.riwayat') }}" 
               class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->routeIs('dokter.riwayat', 'portal.riwayat') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Riwayat SIP
            </a>
        </nav>
    </header>

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

        // Real-time polling for unread notification badge every 15 seconds
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

