@extends('layouts.app')
@section('title', 'Rekapitulasi Absensi Santri')

@section('content')
    <style>
        /* Menggunakan warna pastel yang jauh lebih pekat (level 200-300 Tailwind) agar benar-benar terlihat berwarna */
        .card-bg-violet { background: linear-gradient(135deg, #d8b4fe 0%, #e9d5ff 100%) !important; }
        .card-bg-teal { background: linear-gradient(135deg, #5eead4 0%, #99f6e4 100%) !important; }
        .card-bg-amber { background: linear-gradient(135deg, #fcd34d 0%, #fde68a 100%) !important; }
        .card-bg-rose { background: linear-gradient(135deg, #fda4af 0%, #fecdd3 100%) !important; }
        .card-bg-sky { background: linear-gradient(135deg, #7dd3fc 0%, #bae6fd 100%) !important; }
        .card-bg-emerald { background: linear-gradient(135deg, #6ee7b7 0%, #a7f3d0 100%) !important; }

        .dark .card-bg-violet { background: linear-gradient(135deg, rgba(139, 92, 246, 0.35) 0%, rgba(109, 40, 217, 0.5) 100%) !important; }
        .dark .card-bg-teal { background: linear-gradient(135deg, rgba(20, 184, 166, 0.35) 0%, rgba(15, 118, 110, 0.5) 100%) !important; }
        .dark .card-bg-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.35) 0%, rgba(180, 83, 9, 0.5) 100%) !important; }
        .dark .card-bg-rose { background: linear-gradient(135deg, rgba(244, 63, 94, 0.35) 0%, rgba(190, 18, 60, 0.5) 100%) !important; }
        .dark .card-bg-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.35) 0%, rgba(4, 120, 87, 0.5) 100%) !important; }

        /* Custom Colors for Arsip & Tutup Buku buttons */
        .btn-arsip { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25) !important; }
        .btn-arsip:hover { background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35) !important; transform: translateY(-1px); }

        .btn-tutup { background: linear-gradient(135deg, #475569 0%, #334155 100%) !important; color: #ffffff !important; border: none !important; box-shadow: 0 4px 14px rgba(71, 85, 105, 0.25) !important; }
        .btn-tutup:hover { background: linear-gradient(135deg, #334155 0%, #1e293b 100%) !important; box-shadow: 0 6px 20px rgba(71, 85, 105, 0.35) !important; transform: translateY(-1px); }
    </style>

<div class="-m-6 p-6">

    {{-- Tabs --}}
    <div class="flex gap-2 mb-4 flex-shrink-0 border-b border-slate-200 dark:border-stone-700/50 pb-2">
        @if(!auth()->user()->hasRole('dewan_pemangku'))
        <a href="{{ route('absensi.index') }}" class="px-5 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-slate-600 dark:text-stone-300 hover:bg-slate-100 border border-slate-200 dark:border-stone-700/50">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            Input Absensi Harian
        </a>
        @endif
        <a href="{{ route('absensi.rekap') }}" class="px-5 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 btn-primary border-none">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Rekapitulasi & Laporan
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 flex-shrink-0">
        <div class="card-bg-violet rounded-2xl shadow-sm border border-violet-200 dark:border-violet-800/80 p-4 flex items-center gap-4 transition-transform hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center dark:bg-violet-900/30 dark:text-violet-400 shadow-sm border border-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-violet-600/70 dark:text-violet-400/70 mb-0.5">Total Santri Terdata</div>
                <div class="text-2xl font-black text-slate-800 dark:text-stone-100">{{ $totalSantri }}</div>
            </div>
        </div>
        <div class="card-bg-teal rounded-2xl shadow-sm border border-teal-200 dark:border-teal-800/80 p-4 flex items-center gap-4 transition-transform hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center dark:bg-teal-900/30 dark:text-teal-400 shadow-sm border border-teal-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-teal-600/70 dark:text-teal-400/70 mb-0.5">Rata-rata Kehadiran</div>
                <div class="text-2xl font-black text-slate-800 dark:text-stone-100">{{ $avgKehadiran }}%</div>
            </div>
        </div>
        <div class="card-bg-rose rounded-2xl shadow-sm border border-rose-200 dark:border-rose-800/80 p-4 flex items-center gap-4 transition-transform hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center dark:bg-rose-900/30 dark:text-rose-400 shadow-sm border border-rose-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-rose-600/70 dark:text-rose-400/70 mb-0.5">Total Alpa Bulan Ini</div>
                <div class="text-2xl font-black text-slate-800 dark:text-stone-100">{{ $totalAlpaAll }} Kali</div>
            </div>
        </div>
    </div>

    {{-- Filter & Export Bar --}}
    <div class="bg-slate-100/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 mb-5 flex-shrink-0 flex flex-col sm:flex-row justify-between gap-4">
        <form method="GET" action="{{ route('absensi.rekap') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Bulan:</label>
                <select name="bulan" class="form-select text-sm py-1.5 pl-3 rounded-lg border-slate-200 dark:border-stone-700/50" style="padding-right: 2.5rem; min-width: 140px;" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Tahun:</label>
                <select name="tahun" class="form-select text-sm py-1.5 pl-3 rounded-lg border-slate-200 dark:border-stone-700/50" style="padding-right: 2.5rem; min-width: 100px;" onchange="this.form.submit()">
                    @for($y=date('Y'); $y>=date('Y')-5; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            @if($showDaerahFilter)
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Daerah:</label>
                <select name="daerah_id" class="form-select text-sm py-1.5 pl-3 rounded-lg border-slate-200 dark:border-stone-700/50 w-36 sm:w-48" style="padding-right: 2.5rem;" onchange="if(this.form.kamar_id) this.form.kamar_id.value=''; this.form.submit()">
                    <option value="">Semua Daerah</option>
                    @foreach($daerahList as $daerah)
                        <option value="{{ $daerah->id }}" {{ $daerahId == $daerah->id ? 'selected' : '' }}>{{ $daerah->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Kamar:</label>
                <select name="kamar_id" class="form-select text-sm py-1.5 pl-3 rounded-lg border-slate-200 dark:border-stone-700/50 w-36 sm:w-48" style="padding-right: 2.5rem;" onchange="this.form.submit()">
                    <option value="">Semua Kamar</option>
                    @foreach($kamarsForSelectedDaerah as $kamar)
                        <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('absensi.export.excel', request()->query()) }}" class="btn px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2" target="_blank">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            <a href="{{ route('absensi.export.pdf', request()->query()) }}" class="btn px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2" target="_blank">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('arsip-absensi.index') }}" class="btn btn-arsip px-4 py-2 font-bold rounded-xl shadow-sm text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Arsip
            </a>
            @if(!auth()->user()->hasAnyRole(['dewan_pemangku', 'wali_santri']))
            <div x-data="{ showConfirm: false }">
                <button @click="showConfirm = true" type="button" class="btn btn-tutup px-4 py-2 font-bold rounded-xl shadow-sm text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Tutup Buku
                </button>
                {{-- Confirmation Modal --}}
                <template x-teleport="body">
                    <div x-show="showConfirm" x-cloak x-transition
                         class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirm = false"></div>
                        <div class="relative bg-white dark:bg-[#1E1C1A] rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-stone-700/50 overflow-hidden z-10">
                            <div class="p-6 text-center">
                                <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-stone-100 mb-2">Tutup Buku Bulan {{ date('F', mktime(0, 0, 0, $bulan, 10)) }} {{ $tahun }}?</h3>
                                <p class="text-sm text-slate-500 dark:text-stone-400 mb-6">Semua data absensi pada bulan ini akan diarsipkan dan tidak akan tampil lagi di halaman aktif. Data tetap dapat diakses melalui <strong>Menu Arsip</strong>.</p>
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="showConfirm = false" type="button" class="btn btn-outline text-sm py-2 px-5 rounded-xl">Batal</button>
                                    <form action="{{ route('absensi.tutup-buku') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                                        <button type="submit" class="btn btn-tutup px-5 py-2 font-bold rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Ya, Tutup Buku
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 rounded-r-xl shadow-sm flex-shrink-0">
            <ul class="list-disc list-inside text-xs text-rose-700 dark:text-rose-300">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 rounded-r-xl shadow-sm flex-shrink-0">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-emerald-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm flex items-center gap-2 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Folder Kegiatan Grid --}}
    <div class="mb-4">
        <h2 class="text-sm font-bold text-slate-700 dark:text-stone-200 flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            Pilih Folder Kegiatan — {{ date('F Y', mktime(0, 0, 0, $bulan, 10, $tahun)) }}
        </h2>

        @if(count($kegiatanStats) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @php
                $cardColors = [
                    ['bg' => 'card-bg-violet', 'border' => 'border-violet-200 dark:border-violet-800/60', 'icon' => 'text-violet-600 dark:text-violet-400', 'iconBg' => 'bg-violet-100 dark:bg-violet-900/40 border border-violet-200/50 dark:border-violet-800/50 shadow-sm', 'ring' => '#8b5cf6', 'hover' => 'hover:border-violet-400 dark:hover:border-violet-500/60 hover:shadow-violet-200/50'],
                    ['bg' => 'card-bg-teal', 'border' => 'border-teal-200 dark:border-teal-800/60', 'icon' => 'text-teal-600 dark:text-teal-400', 'iconBg' => 'bg-teal-100 dark:bg-teal-900/40 border border-teal-200/50 dark:border-teal-800/50 shadow-sm', 'ring' => '#14b8a6', 'hover' => 'hover:border-teal-400 dark:hover:border-teal-500/60 hover:shadow-teal-200/50'],
                    ['bg' => 'card-bg-amber', 'border' => 'border-amber-200 dark:border-amber-800/60', 'icon' => 'text-amber-600 dark:text-amber-400', 'iconBg' => 'bg-amber-100 dark:bg-amber-900/40 border border-amber-200/50 dark:border-amber-800/50 shadow-sm', 'ring' => '#f59e0b', 'hover' => 'hover:border-amber-400 dark:hover:border-amber-500/60 hover:shadow-amber-200/50'],
                    ['bg' => 'card-bg-rose', 'border' => 'border-rose-200 dark:border-rose-800/60', 'icon' => 'text-rose-600 dark:text-rose-400', 'iconBg' => 'bg-rose-100 dark:bg-rose-900/40 border border-rose-200/50 dark:border-rose-800/50 shadow-sm', 'ring' => '#f43f5e', 'hover' => 'hover:border-rose-400 dark:hover:border-rose-500/60 hover:shadow-rose-200/50'],
                    ['bg' => 'card-bg-sky', 'border' => 'border-sky-200 dark:border-sky-800/60', 'icon' => 'text-sky-600 dark:text-sky-400', 'iconBg' => 'bg-sky-100 dark:bg-sky-900/40 border border-sky-200/50 dark:border-sky-800/50 shadow-sm', 'ring' => '#0ea5e9', 'hover' => 'hover:border-sky-400 dark:hover:border-sky-500/60 hover:shadow-sky-200/50'],
                    ['bg' => 'card-bg-emerald', 'border' => 'border-emerald-200 dark:border-emerald-800/60', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-200/50 dark:border-emerald-800/50 shadow-sm', 'ring' => '#10b981', 'hover' => 'hover:border-emerald-400 dark:hover:border-emerald-500/60 hover:shadow-emerald-200/50'],
                ];
            @endphp

            @foreach($kegiatanStats as $index => $stat)
                @php
                    $color = $cardColors[$index % count($cardColors)];
                    $pct = $stat->persentase;
                    $circumference = 2 * 3.14159 * 16;
                    $dashOffset = $circumference - ($pct / 100) * $circumference;
                @endphp
                <a href="{{ route('absensi.rekap.detail', ['kegiatan_id' => $stat->id, 'bulan' => $bulan, 'tahun' => $tahun, 'daerah_id' => $daerahId, 'kamar_id' => $kamarId]) }}"
                        class="block group {{ $color['bg'] }} rounded-2xl shadow-sm border {{ $color['border'] }} px-4 py-3 text-left transition-all duration-200 {{ $color['hover'] }} hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-[#1A1817]">

                    {{-- Top: Ikon + Nama --}}
                    <div class="flex items-start justify-between mb-2.5">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $color['iconBg'] }} {{ $color['icon'] }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-800 dark:text-stone-100 truncate">{{ $stat->nama_kegiatan }}</h3>
                                @if($stat->jam_pelaksanaan)
                                    <p class="text-[11px] text-slate-400 dark:text-stone-500 font-medium">{{ $stat->jam_pelaksanaan }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- SVG Circular Progress --}}
                        <div class="relative flex-shrink-0 w-10 h-10">
                            <svg class="w-10 h-10 -rotate-90" viewBox="0 0 40 40">
                                <circle cx="20" cy="20" r="16" fill="none" stroke-width="3" class="stroke-slate-100 dark:stroke-stone-700/60"/>
                                <circle cx="20" cy="20" r="16" fill="none" stroke-width="3" stroke="{{ $color['ring'] }}" stroke-linecap="round"
                                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}"
                                        class="transition-all duration-700"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-extrabold text-slate-700 dark:text-stone-200">{{ $pct }}%</span>
                        </div>
                    </div>

                    {{-- Bottom: Mini Stats --}}
                    <div class="flex items-center justify-between gap-1.5 mt-2">
                        <div class="flex-1 flex items-center justify-center gap-1 rounded-md bg-emerald-50 dark:bg-emerald-900/20 py-1">
                            <span class="text-[10px] font-bold text-emerald-500 uppercase">H:</span>
                            <span class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-400">{{ $stat->hadir }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-center gap-1 rounded-md bg-amber-50 dark:bg-amber-900/20 py-1">
                            <span class="text-[10px] font-bold text-amber-500 uppercase">I:</span>
                            <span class="text-[11px] font-extrabold text-amber-700 dark:text-amber-400">{{ $stat->izin }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-center gap-1 rounded-md bg-blue-50 dark:bg-blue-900/20 py-1">
                            <span class="text-[10px] font-bold text-blue-500 uppercase">S:</span>
                            <span class="text-[11px] font-extrabold text-blue-700 dark:text-blue-400">{{ $stat->sakit }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-center gap-1 rounded-md bg-rose-50 dark:bg-rose-900/20 py-1">
                            <span class="text-[10px] font-bold text-rose-500 uppercase">A:</span>
                            <span class="text-[11px] font-extrabold text-rose-700 dark:text-rose-400">{{ $stat->alpa }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        @else
            <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-12 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-stone-800 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-slate-400 dark:text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Belum ada kegiatan yang terdaftar pada scope Anda.</p>
            </div>
        @endif
    </div>

</div>
@endsection
