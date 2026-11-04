@if($tab === 'kepatuhan')
    <table class="w-full text-left border-collapse text-xs" border="1">
        <thead>
            <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-700 border-b border-slate-300">
                <th class="py-3 px-3 border border-slate-300 text-center">No</th>
                <th class="py-3 px-3 border border-slate-300">Nama Dokter & Gelar</th>
                <th class="py-3 px-3 border border-slate-300">NIP / NIK</th>
                <th class="py-3 px-3 border border-slate-300">KSM & Spesialisasi</th>
                <th class="py-3 px-3 border border-slate-300">No. STR & Masa Berlaku</th>
                <th class="py-3 px-3 border border-slate-300">No. SIP</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Praktik Ke-</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Tgl Terbit</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Tgl Kadaluarsa</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Sisa Hari</th>
                <th class="py-3 px-3 border border-slate-300">Status Kepatuhan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rows as $idx => $sip)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold text-slate-900">{{ $sip->dokter?->nama_lengkap }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $sip->dokter?->nip_nik }}</td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-bold">{{ $sip->dokter?->ksm?->nama_ksm }}</div>
                        <div class="text-[11px] text-slate-500">{{ $sip->dokter?->spesialisasi?->nama_spesialisasi }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-mono font-semibold">{{ $sip->dokter?->no_str }}</div>
                        <div class="text-[10px] font-bold text-teal-700">
                            {{ $sip->dokter?->is_str_seumur_hidup ? 'Seumur Hidup' : ($sip->dokter?->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-') }}
                        </div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono font-bold">{{ $sip->no_sip }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">Ke-{{ $sip->tempat_praktik_ke }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center">{{ $sip->tgl_terbit->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $sip->tgl_kadaluarsa->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-extrabold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $sip->sisa_hari }} Hari
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold">
                        <span class="px-2 py-0.5 rounded text-[11px] border {{ $sip->status_badge_class }}">
                            {{ $sip->status_label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="py-8 text-center text-slate-400">Tidak ada data kepatuhan SIP pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@elseif($tab === 'notifikasi')
    <table class="w-full text-left border-collapse text-xs" border="1">
        <thead>
            <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-700 border-b border-slate-300">
                <th class="py-3 px-3 border border-slate-300 text-center">No</th>
                <th class="py-3 px-3 border border-slate-300">Waktu Notifikasi (WITA)</th>
                <th class="py-3 px-3 border border-slate-300">Penerima (Dokter / SDM)</th>
                <th class="py-3 px-3 border border-slate-300">KSM</th>
                <th class="py-3 px-3 border border-slate-300">Jenis Notifikasi</th>
                <th class="py-3 px-3 border border-slate-300">Judul & Isi Ringkasan Pesan</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Status (Terkirim / Dibaca)</th>
                <th class="py-3 px-3 border border-slate-300">Waktu Dibaca (read_at)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rows as $idx => $notif)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $notif->sent_at?->format('d/m/Y H:i') }} WITA</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold text-slate-900">
                        {{ $notif->recipient?->name }}
                        <span class="block text-[10px] font-normal text-slate-500">ID: {{ $notif->recipient?->username }}</span>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        {{ $notif->recipient?->dokter?->ksm?->nama_ksm ?? 'Manajemen / SDM' }}
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $notif->type_badge_class }}">
                            {{ $notif->message_type_label }}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 max-w-md">
                        <div class="font-bold text-slate-900">{{ $notif->title }}</div>
                        <div class="text-[11px] text-slate-600 mt-0.5">{{ $notif->payload }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">
                        @if($notif->status === 'read')
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Dibaca (Read)</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800">Terkirim (Unread)</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">
                        {{ $notif->read_at ? $notif->read_at->format('d/m/Y H:i') . ' WITA' : 'Belum Dibaca' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data audit notifikasi web.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@elseif($tab === 'verifikasi')
    <table class="w-full text-left border-collapse text-xs" border="1">
        <thead>
            <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-700 border-b border-slate-300">
                <th class="py-3 px-3 border border-slate-300 text-center">No</th>
                <th class="py-3 px-3 border border-slate-300">Tgl Pengajuan</th>
                <th class="py-3 px-3 border border-slate-300">Tgl Diputuskan</th>
                <th class="py-3 px-3 border border-slate-300">Nama Dokter & KSM</th>
                <th class="py-3 px-3 border border-slate-300">No. SIP & Praktik Ke-</th>
                <th class="py-3 px-3 border border-slate-300">Keputusan Status</th>
                <th class="py-3 px-3 border border-slate-300">Admin SDM Verifikator</th>
                <th class="py-3 px-3 border border-slate-300">Catatan Verifikasi / Alasan Penolakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rows as $idx => $rec)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $rec->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">
                        {{ $rec->verified_at ? $rec->verified_at->format('d/m/Y H:i') : 'Menunggu' }}
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-bold text-slate-900">{{ $rec->dokter?->nama_lengkap }}</div>
                        <div class="text-[11px] text-slate-500">{{ $rec->dokter?->ksm?->nama_ksm }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-mono font-bold">{{ $rec->no_sip }}</div>
                        <div class="text-[11px] text-slate-500">Praktik Ke-{{ $rec->tempat_praktik_ke }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold">
                        @if($rec->status === 'menunggu_verifikasi')
                            <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800">Menunggu Verifikasi</span>
                        @elseif($rec->status === 'ditolak')
                            <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800">Ditolak / Revisi</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Disetujui (Approved)</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-semibold">
                        {{ $rec->verifier?->name ?? 'Belum Diverifikasi' }}
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 max-w-xs">
                        {{ $rec->catatan_verifikasi ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data verifikasi berkas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@elseif($tab === 'histori')
    <table class="w-full text-left border-collapse text-xs" border="1">
        <thead>
            <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-700 border-b border-slate-300">
                <th class="py-3 px-3 border border-slate-300 text-center">No</th>
                <th class="py-3 px-3 border border-slate-300">Nama Dokter & NIP</th>
                <th class="py-3 px-3 border border-slate-300">KSM & Spesialisasi</th>
                <th class="py-3 px-3 border border-slate-300">Periode Pengajuan</th>
                <th class="py-3 px-3 border border-slate-300">No. SIP</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Praktik Ke-</th>
                <th class="py-3 px-3 border border-slate-300">Masa Berlaku</th>
                <th class="py-3 px-3 border border-slate-300">Tgl Pengajuan</th>
                <th class="py-3 px-3 border border-slate-300">Status Akhir</th>
                <th class="py-3 px-3 border border-slate-300">Verifikator & Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rows as $idx => $rec)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-bold text-slate-900">{{ $rec->dokter?->nama_lengkap }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $rec->dokter?->nip_nik }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-bold">{{ $rec->dokter?->ksm?->nama_ksm }}</div>
                        <div class="text-[11px] text-slate-500">{{ $rec->dokter?->spesialisasi?->nama_spesialisasi }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-semibold">
                        Periode {{ $rec->tgl_terbit->format('Y') }}–{{ $rec->tgl_kadaluarsa->format('Y') }}
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono font-bold">{{ $rec->no_sip }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">Ke-{{ $rec->tempat_praktik_ke }}</td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        {{ $rec->tgl_terbit->format('d/m/Y') }} s/d <strong>{{ $rec->tgl_kadaluarsa->format('d/m/Y') }}</strong>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $rec->created_at?->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold">
                        <span class="px-2 py-0.5 rounded text-[10px] border {{ $rec->status_badge_class }}">
                            {{ $rec->status_label }}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 max-w-xs">
                        <div class="font-semibold">{{ $rec->verifier?->name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $rec->catatan_verifikasi }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada data histori pengajuan SIP.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@elseif($tab === 'sla')
    <table class="w-full text-left border-collapse text-xs" border="1">
        <thead>
            <tr class="bg-slate-100 text-[11px] font-extrabold uppercase text-slate-700 border-b border-slate-300">
                <th class="py-3 px-3 border border-slate-300 text-center">No</th>
                <th class="py-3 px-3 border border-slate-300">Tgl & Jam Pengajuan</th>
                <th class="py-3 px-3 border border-slate-300">Nama Dokter</th>
                <th class="py-3 px-3 border border-slate-300">NIP / NIK</th>
                <th class="py-3 px-3 border border-slate-300">KSM & Spesialisasi</th>
                <th class="py-3 px-3 border border-slate-300">No. SIP Baru</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Praktik Ke-</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Tgl Terbit</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Tgl Kadaluarsa</th>
                <th class="py-3 px-3 border border-slate-300">Status Saat Ini</th>
                <th class="py-3 px-3 border border-slate-300">Tgl Verifikasi SDM</th>
                <th class="py-3 px-3 border border-slate-300 text-center">Durasi Proses (SLA)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rows as $idx => $rec)
                <tr class="hover:bg-slate-50">
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $rec->created_at?->format('d/m/Y H:i') }} WITA</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold text-slate-900">{{ $rec->dokter?->nama_lengkap }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">{{ $rec->dokter?->nip_nik }}</td>
                    <td class="py-2.5 px-3 border border-slate-200">
                        <div class="font-bold">{{ $rec->dokter?->ksm?->nama_ksm }}</div>
                        <div class="text-[11px] text-slate-500">{{ $rec->dokter?->spesialisasi?->nama_spesialisasi }}</div>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono font-bold">{{ $rec->no_sip }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-bold">Ke-{{ $rec->tempat_praktik_ke }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center">{{ $rec->tgl_terbit->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center">{{ $rec->tgl_kadaluarsa->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 border border-slate-200 font-bold">
                        <span class="px-2 py-0.5 rounded text-[10px] border {{ $rec->status_badge_class }}">
                            {{ $rec->status_label }}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 font-mono">
                        {{ $rec->verified_at ? $rec->verified_at->format('d/m/Y H:i') . ' WITA' : 'Menunggu' }}
                    </td>
                    <td class="py-2.5 px-3 border border-slate-200 text-center font-extrabold text-emerald-700">
                        {{ $rec->durasi_proses_label }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="py-8 text-center text-slate-400">Tidak ada data pengajuan SIP pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif

