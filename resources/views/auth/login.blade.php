<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIMONSIP — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-slate-100 flex items-center justify-center p-4 sm:p-8">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur">
        <!-- Left Column: Hospital Branding & System Highlights -->
        <div class="lg:col-span-6 p-8 sm:p-10 bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-900 flex flex-col justify-between">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-bold tracking-wide uppercase text-emerald-200 mb-6">
                    <span>🏥</span> Pemerintah Provinsi Kalimantan Selatan
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    SIMONSIP DOKTER
                </h1>
                <p class="text-emerald-100 font-semibold text-base mt-1">
                    RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
                </p>
                <p class="text-sm text-emerald-100/80 mt-4 leading-relaxed">
                    Sistem Informasi Monitoring Surat Izin Praktik (SIP) Dokter berbasis <strong>Early Warning Web Notification</strong>, <strong>1-Step Renewal Portal</strong>, dan <strong>5 Modul Laporan Eksekutif Akreditasi STARKES</strong>.
                </p>

                <div class="mt-8 space-y-3 text-xs text-emerald-50">
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
            </div>

            <div class="mt-8 pt-4 border-t border-white/15 text-xs text-emerald-200/80 flex items-center justify-between">
                <span>Jl. Brigjend H. Hasan Basry No.1, Banjarmasin</span>
                <span>Password Default: <code class="font-mono font-bold text-white">Ansari@2026</code></span>
            </div>
        </div>

        <!-- Right Column: Login Form + 1-Click Quick Demo Access -->
        <div class="lg:col-span-6 p-8 sm:p-10 bg-white text-slate-800 flex flex-col justify-between">
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
</body>
</html>

