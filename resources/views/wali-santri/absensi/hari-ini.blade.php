@extends('layouts.app')

@section('title', 'Rekap Kehadiran Hari Ini')

@section('content')
<div class="flex flex-col h-full space-y-6">
    <!-- Header Minimalis & Elegan -->
    <div class="banner-header p-6 flex flex-col sm:flex-row items-center justify-between gap-4 flex-shrink-0">
        <div>
            <h2 class="text-2xl font-bold text-white mb-1">Ahlan wa Sahlan, Bapak/Ibu Wali!</h2>
            <p class="text-violet-100 text-sm">
                Berikut adalah rekapitulasi kegiatan Ananda <span class="font-bold text-white">{{ $santri->nama }}</span> untuk hari ini.
            </p>
        </div>
        <div class="bg-white/20 backdrop-blur-md border border-white/30 px-5 py-2.5 rounded-xl text-center shadow-inner">
            <span class="block text-xs text-violet-100 uppercase tracking-wider mb-0.5">Tanggal</span>
            <span class="block font-bold text-white whitespace-nowrap">{{ \Carbon\Carbon::parse($tanggalHariIni)->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Konten Scrollable -->
    <div class="flex-1 overflow-y-auto min-h-0 pr-2">
        @if($jenisKegiatanList->isEmpty() && !($isSeninKamis && ($isPuasaWajib || $absensiPuasa)))
            <div class="stat-card flex flex-col items-center justify-center p-12 text-center h-full">
                <div class="w-16 h-16 bg-violet-100 text-violet-500 rounded-full flex items-center justify-center mb-4 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-stone-100 mb-1">Tidak Ada Jadwal Kegiatan</h3>
                <p class="text-sm text-slate-500 dark:text-stone-400">Belum ada jadwal kegiatan yang diatur untuk ananda pada hari ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-6">
                
                @if($isSeninKamis && ($isPuasaWajib || $absensiPuasa))
                    <!-- Card Puasa Sunnah -->
                    <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-slate-200 dark:border-stone-700/50 p-5 hover:shadow-md transition-shadow relative">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-stone-100">Puasa Sunnah</h3>
                                </div>
                            </div>
                            @if($absensiPuasa)
                                @if($absensiPuasa->status_puasa === 'puasa')
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 rounded-md text-xs font-bold">Berpuasa</span>
                                @else
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 dark:bg-stone-800 dark:text-stone-400 rounded-md text-xs font-bold">Tidak Puasa</span>
                                @endif
                            @else
                                <span class="px-2 py-1 bg-slate-50 text-slate-400 border border-dashed border-slate-200 dark:bg-stone-800 dark:border-stone-700 rounded-md text-xs font-bold">Belum Dicatat</span>
                            @endif
                        </div>
                        
                        <div class="pt-3 border-t border-slate-100 dark:border-stone-700/50 flex flex-col gap-1.5 text-xs text-slate-500 dark:text-stone-400">
                            @if($absensiPuasa && $absensiPuasa->keterangan)
                                <div class="flex items-start">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="italic">{{ $absensiPuasa->keterangan }}</span>
                                </div>
                            @else
                                <span>Informasi puasa sunnah Senin/Kamis ananda hari ini.</span>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach($jenisKegiatanList as $kegiatan)
                    @php
                        $abs = $absensiKegiatans->get($kegiatan->id);
                    @endphp
                    <!-- Card Folder Kegiatan -->
                    <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-slate-200 dark:border-stone-700/50 p-5 hover:shadow-md transition-shadow relative flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 text-violet-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <div class="pr-2">
                                        <h3 class="font-bold text-slate-800 dark:text-stone-100 leading-tight">{{ $kegiatan->nama_kegiatan }}</h3>
                                    </div>
                                </div>
                                @if($abs)
                                    @if($abs->status === 'hadir')
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 rounded-md text-xs font-bold flex-shrink-0">Hadir</span>
                                    @elseif($abs->status === 'izin')
                                        <span class="px-2 py-1 bg-amber-50 text-amber-600 dark:bg-amber-900/20 rounded-md text-xs font-bold flex-shrink-0">Izin</span>
                                    @elseif($abs->status === 'sakit')
                                        <span class="px-2 py-1 bg-blue-50 text-blue-600 dark:bg-blue-900/20 rounded-md text-xs font-bold flex-shrink-0">Sakit</span>
                                    @elseif($abs->status === 'alpa')
                                        <span class="px-2 py-1 bg-rose-50 text-rose-600 dark:bg-rose-900/20 rounded-md text-xs font-bold flex-shrink-0">Alpa</span>
                                    @else
                                        <span class="px-2 py-1 bg-slate-100 text-slate-500 dark:bg-stone-800 rounded-md text-xs font-bold flex-shrink-0">{{ ucfirst($abs->status) }}</span>
                                    @endif
                                @else
                                    <span class="px-2 py-1 bg-slate-50 text-slate-400 border border-dashed border-slate-200 dark:bg-stone-800 dark:border-stone-700 rounded-md text-xs font-bold flex-shrink-0 whitespace-nowrap">Belum Dicatat</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-stone-700/50 mt-2 flex flex-col gap-1.5">
                            <div class="flex items-center text-xs text-slate-500 dark:text-stone-400">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400 dark:text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Jam: <span class="font-medium text-slate-600 dark:text-stone-300">{{ $kegiatan->jam_pelaksanaan ?? '-' }}</span></span>
                            </div>
                            @if($abs && $abs->keterangan)
                                <div class="flex items-start text-xs text-slate-500 dark:text-stone-400">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400 dark:text-stone-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="italic">{{ $abs->keterangan }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
