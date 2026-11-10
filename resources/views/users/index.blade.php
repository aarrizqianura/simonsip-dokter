@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', '⚙️ Manajemen Pengguna & Hak Akses Role (Khusus Super Admin)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Left: Add New Admin / User -->
    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-fit">
        <div class="px-5 py-4 bg-slate-900 text-white">
            <h3 class="text-sm font-extrabold">➕ Tambah Akun Pengguna Baru</h3>
            <p class="text-xs text-slate-400">Otoritas Super Admin (IT / Manajemen RSUD)</p>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Jabatan</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso, S.Kom (Admin SDM)"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Username Login</label>
                <input type="text" name="username" required placeholder="Contoh: adminsdm3"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Peran Hak Akses (Role)</label>
                <select name="role" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="admin_sdm">Admin SDM (Staf Kepegawaian)</option>
                    <option value="super_admin">Super Admin (IT / Manajemen)</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">
                    💡 Untuk membuat akun <strong>Dokter</strong> beserta biodata &amp; SIP, gunakan menu <a href="{{ route('dokter.create') }}" class="text-emerald-700 font-bold hover:underline">Input Dokter Baru + Auto Akun →</a>
                </p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password</label>
                <input type="text" name="password" value="Ansari@2026" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                Simpan Akun Pengguna
            </button>
        </form>
    </div>

    <!-- Right: Users Table -->
    <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-extrabold text-slate-900">Daftar Akun Pengguna Sistem</h3>

            <form action="{{ route('users.index') }}" method="GET" class="flex items-center gap-2 text-xs">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / username..."
                       class="px-3 py-1.5 rounded-xl border border-slate-300">
                <select name="role" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white">
                    <option value="">Semua Role</option>
                    <option value="super_admin" @selected($roleFilter === 'super_admin')>Super Admin</option>
                    <option value="admin_sdm" @selected($roleFilter === 'admin_sdm')>Admin SDM</option>
                    <option value="dokter" @selected($roleFilter === 'dokter')>Dokter</option>
                </select>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-600 border-b border-slate-200">
                        <th class="py-3 px-4">Nama Pengguna & Username</th>
                        <th class="py-3 px-4">Role / Hak Akses</th>
                        <th class="py-3 px-4">Reset Password</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <form action="{{ route('users.update', $u) }}" method="POST" class="space-y-1" id="form-user-{{ $u->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $u->name }}"
                                           class="font-bold text-slate-900 border border-slate-200 rounded px-2 py-1 w-full">
                                    <div class="flex items-center gap-1 text-[11px] text-slate-500">
                                        <span>Username:</span>
                                        <input type="text" name="username" value="{{ $u->username }}"
                                               class="font-mono text-emerald-700 border border-slate-200 rounded px-1.5 py-0.5">
                                    </div>
                                </form>
                            </td>
                            <td class="py-3 px-4">
                                @if($u->role === 'dokter')
                                    <input type="hidden" name="role" value="dokter" form="form-user-{{ $u->id }}">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200 font-bold text-xs">
                                        👨‍⚕️ Dokter (Staf Medis)
                                    </span>
                                    @if($u->dokter)
                                        <a href="{{ route('dokter.show', $u->dokter) }}" class="block text-[11px] text-emerald-700 font-semibold hover:underline mt-1">
                                            Lihat Profil &amp; SIP →
                                        </a>
                                    @endif
                                @else
                                    <select name="role" form="form-user-{{ $u->id }}"
                                            class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white font-bold text-xs">
                                        <option value="super_admin" @selected($u->role === 'super_admin')>Super Admin</option>
                                        <option value="admin_sdm" @selected($u->role === 'admin_sdm')>Admin SDM</option>
                                    </select>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="password" form="form-user-{{ $u->id }}"
                                       placeholder="Password baru (opsional)"
                                       class="px-2.5 py-1.5 rounded-lg border border-slate-200 font-mono text-xs w-40">
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <button type="submit" form="form-user-{{ $u->id }}"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                                    Simpan
                                </button>
                                @if(auth()->id() !== $u->id)
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px]">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

