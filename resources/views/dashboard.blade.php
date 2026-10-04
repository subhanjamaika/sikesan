@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Card with Unified Pastel Banner Header (PROTECTED — no dark override) --}}
    <div class="banner-header rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold text-teal-100 mb-3">
                Portal SIKESAN v2
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold mb-2">Selamat Datang, {{ $user->nama }}!</h2>
            <p class="text-teal-100 text-sm leading-relaxed">
                Anda masuk sebagai <span class="font-bold text-white uppercase tracking-wider bg-white/10 px-2 py-0.5 rounded">{{ ucwords(str_replace('_', ' ', $role)) }}</span>.
                @if($role === 'wali_asuh')
                    @php
                        $kamarAsuhanNames = $user->penugasanKamar->map(fn($p) => $p->kamar->nama ?? null)->filter()->implode(', ');
                    @endphp
                    @if($kamarAsuhanNames)
                        Kamar asuhan Anda: <span class="font-semibold text-white">{{ $kamarAsuhanNames }}</span>.
                    @endif
                @endif
            </p>
        </div>
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
        <div class="absolute bottom-0 right-16 -mb-8 w-32 h-32 rounded-full bg-teal-400/20 blur-lg"></div>
    </div>

    {{-- Quick Action Cards per Role (PROTECTED — Pastel icon boxes stay bright) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @if($role === 'wali_asuh')
            <a href="{{ route('absensi.index') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-teal-50/80 dark:hover:bg-[#363230] rounded-xl border border-teal-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                {{-- Icon box: PROTECTED pastel --}}
                <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Absensi Kegiatan</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Input Harian</div>
            </a>
            <a href="{{ route('absensi.index') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-indigo-50/80 dark:hover:bg-[#363230] rounded-xl border border-indigo-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Absensi Puasa</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Senin & Kamis</div>
            </a>
            <a href="{{ route('wali-asuh.santri-asuhan') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-violet-50/80 dark:hover:bg-[#363230] rounded-xl border border-violet-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Santri Asuhan</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Daftar Anak</div>
            </a>
            <a href="{{ route('penilaian-keseharian.create') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-purple-50/80 dark:hover:bg-[#363230] rounded-xl border border-purple-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Penilaian Rapor</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Input Keseharian</div>
            </a>
        @elseif(in_array($role, ['admin', 'pengurus_wilayah', 'dewan_pemangku']))

            <a href="{{ route('absensi.index') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-teal-50/80 dark:hover:bg-[#363230] rounded-xl border border-teal-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Rekap Absensi</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Semua Unit</div>
            </a>
            <a href="{{ route('pelanggaran.index') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-rose-50/80 dark:hover:bg-[#363230] rounded-xl border border-rose-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Pelanggaran</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Data Disiplin</div>
            </a>
            <a href="{{ route('penilaian-keseharian.index') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-purple-50/80 dark:hover:bg-[#363230] rounded-xl border border-purple-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Penilaian Rapor</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Semua Santri</div>
            </a>
        @elseif($role === 'wali_santri')
            <a href="{{ route('wali-santri.riwayat-izin') }}" class="p-4 bg-white/90 dark:bg-[#272422] hover:bg-violet-50/80 dark:hover:bg-[#363230] rounded-xl border border-violet-100 dark:border-stone-700/50 shadow-sm transition hover:shadow-md flex flex-col items-center text-center col-span-2">
                <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs">Riwayat & Rapor Anak</div>
                <div class="text-[11px] text-slate-500 dark:text-stone-400 mt-0.5">Pantau Perkembangan Anak Anda</div>
            </a>
        @endif
    </div>

    {{-- Stats Cards (Dynamic per Role) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @if(in_array($role, ['admin', 'pengurus_wilayah', 'dewan_pemangku']))
            <div class="stat-card fade-in">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Total Santri</div>
                <div class="text-3xl font-bold text-slate-800 dark:text-stone-100">{{ number_format($total_santri ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.05s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Absensi Kegiatan (Hari Ini)</div>
                <div class="text-3xl font-bold text-teal-500">{{ number_format($total_absensi_hari_ini ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.1s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Pelanggaran (Bulan Ini)</div>
                <div class="text-3xl font-bold text-rose-400">{{ number_format($total_pelanggaran_bulan_ini ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.15s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Penilaian Keseharian</div>
                <div class="text-3xl font-bold text-violet-500">{{ number_format($total_penilaian_bulan_ini ?? 0) }}</div>
            </div>
        @elseif($role === 'wali_asuh')
            <div class="stat-card fade-in">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Santri Asuhan</div>
                <div class="text-3xl font-bold text-slate-800 dark:text-stone-100">{{ number_format($total_santri_asuhan ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.05s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Absensi Hari Ini</div>
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format(count($absensi_kegiatan_hari_ini ?? [])) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.1s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Rata-rata Nilai (Bulan Ini)</div>
                <div class="text-3xl font-bold text-teal-500">{{ number_format($rata_rata_nilai ?? 0, 1) }}</div>
            </div>
        @elseif(in_array($role, ['pengurus_daerah']))
             <div class="stat-card fade-in">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Santri Lingkup Daerah</div>
                <div class="text-3xl font-bold text-slate-800 dark:text-stone-100">{{ number_format($total_santri ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.05s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Absensi Kegiatan (Hari Ini)</div>
                <div class="text-3xl font-bold text-teal-500">{{ number_format($total_absensi_hari_ini ?? 0) }}</div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.1s">
                <div class="text-slate-500 dark:text-stone-400 text-sm font-medium mb-1">Pelanggaran (Bulan Ini)</div>
                <div class="text-3xl font-bold text-rose-400">{{ number_format($total_pelanggaran_bulan_ini ?? 0) }}</div>
            </div>
        @endif
    </div>

    {{-- Ringkasan Absensi Hari Ini untuk Wali Asuh --}}
    @if($role === 'wali_asuh')
    <div class="bg-white/80 dark:bg-[#272422] backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/50 overflow-hidden">
        <div class="p-4 border-b border-violet-100/6 dark:border-stone-700/400 dark:border-stone-700/40 flex justify-between items-center bg-gradient-to-r from-violet-50/50 to-teal-50/5 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/400 dark:from-stone-800/50 dark:to-stone-700/30">
            <div>
                <h3 class="font-semibold text-slate-800 dark:text-stone-100">Ringkasan Kehadiran Kegiatan Hari Ini</h3>
                <p class="text-xs text-slate-500 dark:text-stone-400">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <a href="{{ route('absensi.index') }}" class="btn btn-primary text-xs">
                Input Absensi Kegiatan
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kegiatan</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi_kegiatan_hari_ini ?? [] as $absensi)
                    <tr>
                        <td class="font-medium text-slate-800 dark:text-stone-100">{{ $absensi->santri->nama ?? '-' }}</td>
                        <td>{{ $absensi->jenisKegiatan->nama_kegiatan ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $absensi->status_badge_color }}">{{ $absensi->status_label }}</span>
                        </td>
                        <td class="text-xs text-slate-600 dark:text-stone-400">{{ $absensi->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-slate-500 dark:text-stone-400">Belum ada absensi kegiatan yang diinput hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Recent Data Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pelanggaran Terbaru --}}
        @if(isset($pelanggaran_terbaru) && count($pelanggaran_terbaru) > 0)
        <div class="bg-white/80 dark:bg-[#272422] backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/50 overflow-hidden">
            <div class="p-4 border-b border-violet-100/6 dark:border-stone-700/400 dark:border-stone-700/40 flex justify-between items-center bg-gradient-to-r from-violet-50/50 to-teal-50/5 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/400 dark:from-stone-800/50 dark:to-stone-700/30">
                <h3 class="font-semibold text-slate-800 dark:text-stone-100">Pelanggaran Terbaru</h3>
                <a href="{{ route('pelanggaran.index') }}" class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-800 dark:hover:text-violet-300 hover:underline transition">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelanggaran_terbaru as $langgar)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-800 dark:text-stone-100">{{ $langgar->santri->nama ?? 'Santri Tidak Ditemukan' }}</div>
                                <div class="text-xs text-slate-500 dark:text-stone-500">{{ $langgar->santri->nis_induk ?? '-' }}</div>
                            </td>
                            <td><span class="badge badge-{{ $langgar->kategori == 'umum' ? 'gray' : 'yellow' }}">{{ ucfirst($langgar->kategori) }}</span></td>
                            <td>{{ $langgar->tanggal->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Penilaian Terbaru (Wali Asuh) --}}
        @if(isset($penilaian_terbaru) && count($penilaian_terbaru) > 0)
        <div class="bg-white/80 dark:bg-[#272422] backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/50 overflow-hidden">
            <div class="p-4 border-b border-violet-100/6 dark:border-stone-700/400 dark:border-stone-700/40 flex justify-between items-center bg-gradient-to-r from-violet-50/50 to-teal-50/5 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/400 dark:from-stone-800/50 dark:to-stone-700/30">
                <h3 class="font-semibold text-slate-800 dark:text-stone-100">Penilaian Keseharian Terbaru</h3>
                <a href="{{ route('penilaian-keseharian.index') }}" class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-800 dark:hover:text-violet-300 hover:underline transition">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Periode</th>
                            <th>Nilai Akhir</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penilaian_terbaru as $nilai)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-800 dark:text-stone-100">{{ $nilai->santri->nama ?? 'Santri Tidak Ditemukan' }}</div>
                                <div class="text-xs text-slate-500 dark:text-stone-500">{{ $nilai->santri->nis_induk ?? '-' }}</div>
                            </td>
                            <td>{{ $nilai->periode }}</td>
                            <td class="font-bold">{{ $nilai->nilai_akhir }}</td>
                            <td><span class="badge badge-{{ $nilai->badge_color }}">{{ $nilai->kategori_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
