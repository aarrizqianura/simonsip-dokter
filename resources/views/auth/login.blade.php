<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIMONSIP — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    <link rel="icon" type="image/avif" href="{{ asset('images/logo-rsud.avif') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-slate-100 flex items-center justify-center p-3 sm:p-6 lg:p-8">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur">
        <!-- Left Column (Desktop) / Compact Collapsible Top Banner (Mobile): Hospital Branding & System Highlights -->
        <div class="lg:col-span-6 p-5 sm:p-7 lg:p-10 bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-900 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-white/10">
            <div>
                <!-- Header Ringkas: Logo RSUD & Judul Utama (Selalu Tampil di Mobile & Desktop) -->
                <div class="flex items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold tracking-wide uppercase text-emerald-200">
                        <span>🏥</span> Pemerintah Provinsi Kalimantan Selatan
                    </div>

                    <!-- Tombol Toggle Collapsible khusus Mobile -->
                    <button type="button"
                            id="btn-toggle-system-info"
                            onclick="toggleSystemInfo()"
                            aria-expanded="false"
                            class="lg:hidden inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-xs font-bold text-white transition shrink-0 shadow-sm">
                        <span>ℹ️ Informasi Sistem</span>
                        <svg id="icon-chevron-info" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 lg:mt-6 flex items-center gap-3.5">
                    <!-- Logo RSUD -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white p-1.5 border border-white/30 flex items-center justify-center shadow-md shrink-0">
                        <img src="{{ asset('images/logo-rsud.avif') }}"
                             alt="Logo RSUD Dr. H. Moch. Ansari Saleh"
                             class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight">
                                SIMONSIP DOKTER
                            </h1>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/30 border border-emerald-300/30 text-emerald-100 rounded-md">
                                STARKES
                            </span>
                        </div>
                        <p class="text-emerald-100 font-semibold text-xs sm:text-sm lg:text-base mt-0.5">
                            RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
                        </p>
                    </div>
                </div>

                <!-- Detail Fitur STARKES (Collapsible di Mobile, Selalu Tampil di Desktop) -->
                <div id="system-info-collapsible" class="hidden lg:block mt-5 lg:mt-6 pt-4 lg:pt-0 border-t border-white/15 lg:border-t-0">
                    <p class="text-xs sm:text-sm text-emerald-100/85 leading-relaxed">
                        Sistem Informasi Monitoring Surat Izin Praktik (SIP) Dokter berbasis <strong>Early Warning Web Notification</strong>, <strong>1-Step Renewal Portal</strong>, dan <strong>5 Modul Laporan Eksekutif Akreditasi STARKES</strong>.
                    </p>

                    <div class="mt-5 lg:mt-7 space-y-3 text-xs text-emerald-50">
                        <div class="flex items-start gap-3 bg-black/20 p-3 rounded-xl border border-white/10">
                            <span class="text-base">🔔</span>
                            <div>
                                <p class="font-bold text-white">Early Warning Engine (Cron Harian 07:00 WITA)</p>
                                <p class="text-emerald-100/80">Indikator Traffic Light otomatis: 🟢 Aman (&gt;90 hr), 🟡 Peringatan (61-90 hr), 🟠 Mendesak (31-60 hr), 🔴 Kadaluarsa (≤30 hr).</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 bg-black/20 p-3 rounded-xl border border-white/10">
                            <span class="text-base">📤</span>
                            <div>
                                <p class="font-bold text-white">1-Step Renewal & Approval Queue SDM</p>
                                <p class="text-emerald-100/80">Unggah perpanjangan Multi-SIP (Praktik Ke-1, 2, 3) & pembedaan STR Seumur Hidup vs SIP 5 Tahun.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 bg-black/20 p-3 rounded-xl border border-white/10">
                            <span class="text-base">📈</span>
                            <div>
                                <p class="font-bold text-white">5 Modul Laporan Eksekutif Kop Resmi RSUD</p>
                                <p class="text-emerald-100/80">Ekspor PDF Siap Cetak Kop RSUD & Excel real-time untuk Direktur, SDM, dan Komite Medik.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-white/15 text-xs text-emerald-200/80 flex flex-wrap items-center justify-between gap-2 lg:hidden">
                        <span>Jl. Brigjend H. Hasan Basry No.1, Banjarmasin</span>
                        <span>Password Default: <code class="font-mono font-bold text-white">Ansari@2026</code></span>
                    </div>
                </div>
            </div>

            <!-- Footer Info Desktop -->
            <div class="hidden lg:flex mt-8 pt-4 border-t border-white/15 text-xs text-emerald-200/80 items-center justify-between">
                <span>Jl. Brigjend H. Hasan Basry No.1, Banjarmasin</span>
                <span>Password Default: <code class="font-mono font-bold text-white">Ansari@2026</code></span>
            </div>
        </div>

        <!-- Right Column: Login Form + 1-Click Quick Demo Access -->
        <div class="lg:col-span-6 p-6 sm:p-8 lg:p-10 bg-white text-slate-800 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Masuk ke Portal SIMONSIP</h2>
                <p class="text-xs text-slate-500 mt-1">Gunakan Username Admin atau NIP/NIK Dokter beserta Password Anda.</p>

                @if(session('success'))
                    <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Username / NIP / NIK
                        </label>
                        <input type="text" name="username" value="{{ old('username', 'adminsdm') }}" required autofocus
                               placeholder="Masukkan Username atau NIP/NIK Dokter"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Password
                        </label>
                        <input type="password" name="password" value="Ansari@2026" required
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Ingat sesi login saya</span>
                        </label>
                        <span class="text-slate-400">Default: <strong class="text-slate-700">Ansari@2026</strong></span>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/25 transition">
                        Masuk ke Sistem SIMONSIP →
                    </button>
                </form>

                <!-- Quick Demo Login Switcher -->
                <div class="mt-7 pt-6 border-t border-slate-200">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3">
                        ⚡ Akses Cepat Demo Multi-Role (1-Klik Login):
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($demoUsers as $u)
                            <form action="{{ route('login.quick') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="{{ $u->username }}">
                                <button type="submit"
                                        class="w-full text-left p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/60 transition group">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded {{ $u->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : ($u->role === 'admin_sdm' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800') }}">
                                            {{ $u->role }}
                                        </span>
                                        <span class="text-[11px] font-mono text-slate-400 group-hover:text-emerald-700">{{ $u->username }}</span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-800 truncate mt-1">{{ $u->name }}</p>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSystemInfo() {
            const panel = document.getElementById('system-info-collapsible');
            const chevron = document.getElementById('icon-chevron-info');
            const btn = document.getElementById('btn-toggle-system-info');

            if (!panel) return;

            const isHidden = panel.classList.contains('hidden');
            if (isHidden) {
                panel.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            } else {
                panel.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }
    </script>
</body>
</html>

