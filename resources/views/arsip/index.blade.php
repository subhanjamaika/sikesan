@extends('layouts.app')
@section('title', 'Arsip Absensi')

@section('content')
<div class="-m-6 p-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 flex-shrink-0">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-stone-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                Arsip Absensi Bulanan
            </h1>
            <p class="text-sm text-slate-500 dark:text-stone-400 mt-1 ml-[52px]">Data absensi yang sudah ditutup buku dan diarsipkan secara permanen.</p>
        </div>
        <a href="{{ route('absensi.rekap') }}" class="btn btn-outline text-sm py-2 px-4 rounded-xl flex items-center gap-2 self-start">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Kembali ke Rekap
        </a>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 rounded-r-xl shadow-sm flex-shrink-0">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-emerald-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Filter Daerah --}}
    @if($showDaerahFilter)
    <div class="bg-white/90 dark:bg-[#272422]/90 backdrop-blur-sm rounded-2xl shadow-sm border border-amber-100 dark:border-stone-700/40 p-4 mb-5 flex-shrink-0">
        <form method="GET" action="{{ route('arsip-absensi.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Daerah:</label>
                <select name="daerah_id" class="form-select text-sm py-1.5 px-3 rounded-lg border-slate-200 dark:border-stone-700/50 w-48" onchange="this.form.submit()">
                    <option value="">Semua Daerah</option>
                    @foreach($daerahList as $daerah)
                        <option value="{{ $daerah->id }}" {{ $daerahId == $daerah->id ? 'selected' : '' }}>{{ $daerah->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    {{-- Period Cards Grid --}}
    @if($periodes->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @php
            $periodColors = [
                ['gradient' => 'from-amber-500 to-orange-500', 'bg' => 'bg-amber-50 dark:bg-amber-900/15', 'border' => 'border-amber-200 dark:border-amber-800/40', 'text' => 'text-amber-700 dark:text-amber-300'],
                ['gradient' => 'from-violet-500 to-purple-500', 'bg' => 'bg-violet-50 dark:bg-violet-900/15', 'border' => 'border-violet-200 dark:border-violet-800/40', 'text' => 'text-violet-700 dark:text-violet-300'],
                ['gradient' => 'from-teal-500 to-emerald-500', 'bg' => 'bg-teal-50 dark:bg-teal-900/15', 'border' => 'border-teal-200 dark:border-teal-800/40', 'text' => 'text-teal-700 dark:text-teal-300'],
                ['gradient' => 'from-rose-500 to-pink-500', 'bg' => 'bg-rose-50 dark:bg-rose-900/15', 'border' => 'border-rose-200 dark:border-rose-800/40', 'text' => 'text-rose-700 dark:text-rose-300'],
                ['gradient' => 'from-sky-500 to-blue-500', 'bg' => 'bg-sky-50 dark:bg-sky-900/15', 'border' => 'border-sky-200 dark:border-sky-800/40', 'text' => 'text-sky-700 dark:text-sky-300'],
                ['gradient' => 'from-emerald-500 to-green-500', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/15', 'border' => 'border-emerald-200 dark:border-emerald-800/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
            ];
        @endphp

        @foreach($periodes as $index => $periode)
            @php
                $color = $periodColors[$index % count($periodColors)];
                $pct = $periode->persentase;
                $circumference = 2 * 3.14159 * 22;
                $dashOffset = $circumference - ($pct / 100) * $circumference;
            @endphp
            <div class="group relative" x-data="{ showActions: false }">
                <a href="{{ route('arsip-absensi.kegiatan', ['bulan' => $periode->bulan, 'tahun' => $periode->tahun, 'daerah_id' => $daerahId]) }}"
                   class="block bg-white dark:bg-[#272422] rounded-2xl shadow-sm border {{ $color['border'] }} overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-[#1A1817]">
                    
                    {{-- Gradient Header --}}
                    <div class="h-2 bg-gradient-to-r {{ $color['gradient'] }}"></div>
                    
                    <div class="p-5">
                        {{-- Title & Ring --}}
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-stone-100">{{ $periode->nama_bulan }}</h3>
                                <p class="text-sm font-semibold {{ $color['text'] }}">{{ $periode->tahun }}</p>
                            </div>
                            <div class="relative flex-shrink-0 w-14 h-14">
                                <svg class="w-14 h-14 -rotate-90" viewBox="0 0 56 56">
                                    <circle cx="28" cy="28" r="22" fill="none" stroke-width="4" class="stroke-slate-100 dark:stroke-stone-700/60"/>
                                    <circle cx="28" cy="28" r="22" fill="none" stroke-width="4" stroke="currentColor" stroke-linecap="round"
                                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}"
                                            class="{{ $color['text'] }} transition-all duration-700"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-extrabold text-slate-700 dark:text-stone-200">{{ $pct }}%</span>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-3 text-xs">
                            <div class="flex items-center gap-1.5 {{ $color['text'] }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-bold">{{ number_format($periode->total_record) }}</span>
                                <span class="text-slate-500 dark:text-stone-400">record</span>
                            </div>
                        </div>

                        {{-- Hover Arrow --}}
                        <div class="mt-3 flex items-center justify-center gap-1.5 text-[11px] font-semibold {{ $color['text'] }} opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            Lihat detail kegiatan
                        </div>
                    </div>
                </a>

                {{-- Buka Kembali Button (Admin only) --}}
                @if(!auth()->user()->hasAnyRole(['dewan_pemangku', 'wali_santri']))
                <div class="absolute top-4 right-4 z-10">
                    <button @click.prevent="showActions = !showActions" class="w-7 h-7 rounded-lg bg-white/80 dark:bg-stone-800/80 border border-slate-200 dark:border-stone-700/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm hover:bg-slate-50 dark:hover:bg-stone-700">
                        <svg class="w-4 h-4 text-slate-500 dark:text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                    </button>
                    <div x-show="showActions" @click.outside="showActions = false" x-transition
                         class="absolute right-0 mt-1 w-48 bg-white dark:bg-[#1E1C1A] rounded-xl shadow-xl border border-slate-200 dark:border-stone-700/50 py-1 z-20" style="display: none;">
                        <form action="{{ route('absensi.buka-buku') }}" method="POST"
                              onsubmit="return confirm('Yakin ingin membuka kembali arsip {{ $periode->nama_bulan }} {{ $periode->tahun }}? Data akan kembali ke halaman aktif.');">
                            @csrf
                            <input type="hidden" name="bulan" value="{{ $periode->bulan }}">
                            <input type="hidden" name="tahun" value="{{ $periode->tahun }}">
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex items-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Buka Kembali Arsip
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        @endforeach
    </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-amber-100 dark:border-stone-700/40 p-16 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-400 dark:text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-stone-200 mb-1">Arsip Masih Kosong</h3>
            <p class="text-sm text-slate-500 dark:text-stone-400 max-w-md mx-auto">
                Belum ada data absensi yang diarsipkan. Gunakan tombol <strong>"Tutup Buku"</strong> di halaman Rekapitulasi untuk mengarsipkan data bulanan.
            </p>
            <a href="{{ route('absensi.rekap') }}" class="inline-flex items-center gap-2 mt-5 btn btn-primary text-sm py-2 px-5 rounded-xl">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Buka Halaman Rekapitulasi
            </a>
        </div>
    @endif

</div>
@endsection
