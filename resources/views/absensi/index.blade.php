@extends('layouts.app')
@section('title', 'Absensi Terpadu Santri')

@section('content')
<div class="-m-6 p-6" 
     x-data="{ 
         showFilter: {{ request()->anyFilled(['daerah_id', 'kamar_id', 'tanggal', 'kegiatan_id', 'search']) ? 'true' : 'false' }}, 
         activeTab: '{{ request('tab', 'kegiatan') }}' 
     }">

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm flex items-center gap-2 flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm flex items-center gap-2 flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-2 mb-2 font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Terdapat kesalahan pengisian:
            </div>
            <ul class="list-disc pl-8 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Tabs --}}
    <div class="flex gap-2 mb-4 flex-shrink-0 border-b border-slate-200 dark:border-stone-700/50 pb-2">
        @if(!auth()->user()->hasRole('dewan_pemangku'))
        <a href="{{ route('absensi.index') }}" class="px-5 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 btn-primary border-none">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            Input Absensi Harian
        </a>
        @endif
        <a href="{{ route('absensi.rekap') }}" class="px-5 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 {{ auth()->user()->hasRole('dewan_pemangku') ? 'btn-primary border-none' : 'bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-slate-600 dark:text-stone-300 hover:bg-slate-100 border border-slate-200 dark:border-stone-700/50' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Rekapitulasi & Laporan
        </a>
    </div>

    {{-- Filter Bar & Tabs --}}
    <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md rounded-2xl shadow-sm border border-white/60 dark:border-stone-700/40 p-4 mb-4 flex-shrink-0 flex flex-col gap-4">
        
        {{-- Top Row: Tabs & Search --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-stone-700/50 pb-3">
            {{-- Tabs --}}
            <div class="flex gap-2">
                <button @click="activeTab = 'kegiatan'; document.querySelector('input[name=tab]').value = 'kegiatan';"
                        :class="activeTab === 'kegiatan' ? 'btn-primary' : 'bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-slate-600 dark:text-stone-300 hover:bg-slate-100 border border-slate-200 dark:border-stone-700/50'"
                        class="px-5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                    Absensi Kegiatan
                </button>
                <button @click="activeTab = 'puasa'; document.querySelector('input[name=tab]').value = 'puasa';"
                        :class="activeTab === 'puasa' ? 'btn-primary' : 'bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-slate-600 dark:text-stone-300 hover:bg-slate-100 border border-slate-200 dark:border-stone-700/50'"
                        class="px-5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Absensi Puasa
                </button>
            </div>

            {{-- Search Box --}}
            <form method="GET" action="{{ route('absensi.index') }}" id="searchForm" class="relative w-full sm:w-72">
                <input type="hidden" name="tab" :value="activeTab">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                <input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">
                <input type="hidden" name="kamar_id" value="{{ request('kamar_id') }}">
                <input type="hidden" name="show_table" value="1">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIS..." class="form-input text-sm px-4 py-2 w-full rounded-xl bg-white/50 dark:bg-[#1A1817]/80 backdrop-blur-sm focus:bg-white dark:bg-[#272422]" @search="$el.form.submit()">
            </form>
        </div>

        {{-- Bottom Row: Filter Inline --}}
        <form method="GET" action="{{ route('absensi.index') }}" id="filterForm" class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-3">
            <input type="hidden" name="tab" :value="activeTab">
            <input type="hidden" name="search" value="{{ request('search') }}">
            
            <div class="grid grid-cols-2 sm:flex items-center gap-3 w-full sm:w-auto">
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-stone-400">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input text-sm py-1.5 px-3 rounded-lg w-full sm:w-36 bg-white/50 dark:bg-[#1A1817]/80 backdrop-blur-sm" onchange="this.form.submit()">
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2" x-show="activeTab === 'kegiatan'">
                    <label class="text-xs font-semibold text-slate-500 dark:text-stone-400">Kegiatan</label>
                    <select name="kegiatan_id" class="form-select text-sm py-1.5 px-3 rounded-lg w-full sm:w-40 bg-white/50 dark:bg-[#1A1817]/80 backdrop-blur-sm border-slate-200 dark:border-stone-700/50 text-slate-700 dark:text-stone-100 focus:ring-violet-500" onchange="this.form.submit()">
                        @foreach($jenisKegiatanList as $kegiatan)
                            <option value="{{ $kegiatan->id }}" {{ $selectedJenisKegiatanId == $kegiatan->id ? 'selected' : '' }}>{{ $kegiatan->nama_kegiatan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:flex items-center gap-3 w-full sm:w-auto">
                @if($showDaerahFilter)
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-stone-400">Daerah</label>
                    <select name="daerah_id" class="form-select text-sm py-1.5 px-3 rounded-lg w-full sm:w-36 bg-white/50 dark:bg-[#1A1817]/80 backdrop-blur-sm" onchange="if(this.form.kamar_id) this.form.kamar_id.value=''; this.form.submit()">
                        <option value="">Semua Daerah</option>
                        @foreach($daerahList as $daerah)
                            <option value="{{ $daerah->id }}" {{ request('daerah_id') == $daerah->id ? 'selected' : '' }}>{{ $daerah->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-stone-400">Kamar</label>
                    <select name="kamar_id" class="form-select text-sm py-1.5 px-3 rounded-lg w-full sm:w-36 bg-white/50 dark:bg-[#1A1817]/80 backdrop-blur-sm">
                        <option value="">Semua Kamar</option>
                        @foreach($kamarsForSelectedDaerah as $kamar)
                            <option value="{{ $kamar->id }}" {{ request('kamar_id') == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto sm:ml-auto mt-2 sm:mt-0">
                @if(request()->anyFilled(['search', 'kamar_id']))
                    <a href="{{ route('absensi.index', ['tanggal' => $tanggal, 'kegiatan_id' => $kegiatanId]) }}" class="btn btn-outline text-xs py-1.5 px-3 flex items-center justify-center gap-1.5 rounded-lg flex-1 sm:flex-none bg-white/50 dark:bg-[#1A1817]/80 hover:bg-white dark:bg-[#272422] hover:text-rose-500 hover:border-rose-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reset Filter
                    </a>
                @endif
                @if(!auth()->user()->hasRole('dewan_pemangku'))
                    @php
                        $selectedDaerah = request('daerah_id') ? $daerahList->where('id', request('daerah_id'))->first() : null;
                        if (!$selectedDaerah && (empty($showDaerahFilter) || !$showDaerahFilter)) {
                            $selectedDaerah = $daerahList->first();
                        }
                        $hideMulaiAbsen = (request('tab', 'kegiatan') === 'puasa' && $selectedDaerah && !$selectedDaerah->is_puasa_wajib);
                    @endphp
                    @if(!$hideMulaiAbsen)
                    <button type="submit" name="show_table" value="1" class="btn btn-primary text-xs py-1.5 px-4 shadow-sm flex-1 sm:flex-none justify-center">
                        Mulai Absen
                    </button>
                    @endif
                @endif
            </div>
        </form>
    </div>

    @if(auth()->user()->hasRole('dewan_pemangku'))
        {{-- Dewan Pemangku: Mode Monitoring (Read-Only) --}}
        <div class="flex flex-col items-center justify-center bg-gradient-to-br from-violet-50/60 to-teal-50/60 dark:from-[#272422]/80 dark:to-[#1A1817]/80 backdrop-blur-md rounded-2xl border border-violet-200/50 dark:border-stone-700/40 text-center p-12 mb-6 shadow-sm">
            <div class="w-16 h-16 bg-gradient-to-br from-violet-100 to-teal-100 dark:from-violet-900/30 dark:to-teal-900/30 text-violet-600 dark:text-violet-400 rounded-full flex items-center justify-center mb-4 shadow-inner">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-stone-200 mb-1">Mode Pemantauan (Read-Only)</h3>
            <p class="text-sm text-slate-500 dark:text-stone-400 max-w-md mb-4">Sebagai Dewan Pemangku, Anda dapat memantau data absensi melalui halaman <strong class="text-violet-600 dark:text-violet-400">Rekapitulasi & Laporan</strong>. Fitur input absensi harian hanya tersedia untuk Pengurus.</p>
            <a href="{{ route('absensi.rekap') }}" class="btn btn-primary text-sm py-2.5 px-6 rounded-xl flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Buka Rekapitulasi & Laporan
            </a>
        </div>
    @elseif(request('show_table') || session('success') || request()->filled('search'))
        {{-- Main Table Area --}}
        <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 dark:border-stone-700/40 overflow-hidden relative mb-6">
        
        {{-- TAB: KEGIATAN --}}
        <form id="form-kegiatan" action="{{ route('absensi.store') }}" method="POST" x-show="activeTab === 'kegiatan'" class="flex flex-col h-full" autocomplete="off">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="jenis_kegiatan_id" value="{{ $selectedJenisKegiatanId }}">
            <input type="hidden" name="daerah_id" value="{{ request('daerah_id') }}">
            <input type="hidden" name="kamar_id" value="{{ request('kamar_id') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            
            <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md p-3 border-b border-slate-100 dark:border-stone-700/50 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 flex-shrink-0 z-20">
                <div class="grid grid-cols-2 sm:flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('#form-kegiatan input[value=\'hadir\']').forEach(e => e.checked = true)" class="btn btn-outline text-xs px-2 sm:px-3 py-2 sm:py-1.5 rounded-lg text-emerald-600 border-emerald-200 hover:bg-emerald-50 text-center justify-center leading-tight">Set Semua<br class="sm:hidden"> Hadir</button>
                    <button type="button" onclick="document.querySelectorAll('#form-kegiatan input[type=\'radio\']').forEach(e => e.checked = false)" class="btn btn-outline text-xs px-2 sm:px-3 py-2 sm:py-1.5 rounded-lg text-slate-500 dark:text-stone-400 border-slate-200 dark:border-stone-700/50 hover:bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-center justify-center leading-tight">Kosongkan<br class="sm:hidden"> Pilihan</button>
                </div>
                <button type="submit" class="btn btn-primary text-sm px-6 py-2 shadow-md justify-center w-full sm:w-auto">Simpan Absensi</button>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[800px]">
                    <thead class="sticky top-0 z-10 bg-gradient-to-r from-violet-50/90 to-teal-50/90 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 backdrop-blur-md">
                        <tr>
                            <th class="w-12 text-center py-3">#</th>
                            <th class="py-3 px-4">Nama & Daerah</th>
                            <th class="text-center py-3 px-4">Status Kehadiran</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($santriList as $index => $santri)
                        @php
                            $status = old("absensi.{$index}.status");
                            $keterangan = old("absensi.{$index}.keterangan");
                        @endphp
                        <tr>
                            <td class="text-center font-medium text-slate-400 dark:text-stone-500 text-xs py-3">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">
                                <input type="hidden" name="absensi[{{ $index }}][santri_id]" value="{{ $santri->id }}">
                                <div class="font-bold text-slate-800 dark:text-stone-100 text-sm">{{ $santri->nama }}</div>
                                <div class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">{{ $santri->nis_induk }} &bull; {{ $santri->unitOrganisasi->nama ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if(isset($santriIzinIds[$santri->id]))
                                    @php
                                        $larizan = $santriIzinIds[$santri->id];
                                        $larizanStatus = $larizan['status'];
                                    @endphp
                                    <div class="flex items-center justify-center gap-2">
                                        <label class="inline-flex items-center cursor-not-allowed text-xs font-medium bg-slate-50 text-slate-400 px-2.5 py-1.5 rounded-lg border border-slate-200 opacity-60">
                                            <input type="radio" disabled class="mr-1.5 w-3.5 h-3.5"> Hadir
                                        </label>
                                        <label class="inline-flex items-center cursor-not-allowed text-xs font-medium {{ $larizanStatus == 'izin' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-slate-50 text-slate-400 border-slate-200' }} px-2.5 py-1.5 rounded-lg border opacity-90">
                                            <input type="radio" disabled class="mr-1.5 w-3.5 h-3.5" {{ $larizanStatus == 'izin' ? 'checked' : '' }}> Izin
                                        </label>
                                        <label class="inline-flex items-center cursor-not-allowed text-xs font-medium {{ $larizanStatus == 'sakit' ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-slate-50 text-slate-400 border-slate-200' }} px-2.5 py-1.5 rounded-lg border opacity-90">
                                            <input type="radio" disabled class="mr-1.5 w-3.5 h-3.5" {{ $larizanStatus == 'sakit' ? 'checked' : '' }}> Sakit
                                        </label>
                                        <label class="inline-flex items-center cursor-not-allowed text-xs font-medium bg-slate-50 text-slate-400 px-2.5 py-1.5 rounded-lg border border-slate-200 opacity-60">
                                            <input type="radio" disabled class="mr-1.5 w-3.5 h-3.5"> Alpa
                                        </label>
                                    </div>
                                    <input type="hidden" name="absensi[{{ $index }}][status]" value="{{ $larizanStatus }}">
                                @else
                                    <div class="flex items-center justify-center gap-2">
                                        <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1.5 rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                                            <input type="radio" name="absensi[{{ $index }}][status]" value="hadir" class="mr-1.5 accent-emerald-500 w-3.5 h-3.5" {{ $status == 'hadir' ? 'checked' : '' }} required> Hadir
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-amber-50 text-amber-700 px-2.5 py-1.5 rounded-lg border border-amber-100 hover:bg-amber-100 transition-colors shadow-sm">
                                            <input type="radio" name="absensi[{{ $index }}][status]" value="izin" class="mr-1.5 accent-amber-500 w-3.5 h-3.5" {{ $status == 'izin' ? 'checked' : '' }}> Izin
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1.5 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors shadow-sm">
                                            <input type="radio" name="absensi[{{ $index }}][status]" value="sakit" class="mr-1.5 accent-blue-500 w-3.5 h-3.5" {{ $status == 'sakit' ? 'checked' : '' }}> Sakit
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-rose-50 text-rose-700 px-2.5 py-1.5 rounded-lg border border-rose-100 hover:bg-rose-100 transition-colors shadow-sm">
                                            <input type="radio" name="absensi[{{ $index }}][status]" value="alpa" class="mr-1.5 accent-rose-500 w-3.5 h-3.5" {{ $status == 'alpa' ? 'checked' : '' }}> Alpa
                                        </label>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if(isset($santriIzinIds[$santri->id]))
                                    <input type="text" name="absensi[{{ $index }}][keterangan]" value="{{ $santriIzinIds[$santri->id]['keterangan'] }}" readonly class="form-input text-xs w-full py-1.5 bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200">
                                @else
                                    <input type="text" name="absensi[{{ $index }}][keterangan]" value="{{ $keterangan }}" placeholder="Opsional..." class="form-input text-xs w-full py-1.5 bg-white/50 dark:bg-[#1A1817]/80">
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-10 text-slate-400 dark:text-stone-500">Tidak ada santri.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- TAB: PUASA --}}
        <form id="form-puasa" action="{{ route('absensi.store-puasa') }}" method="POST" x-show="activeTab === 'puasa'" class="flex flex-col h-full" style="display: none;" autocomplete="off">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="daerah_id" value="{{ request('daerah_id') }}">
            <input type="hidden" name="kamar_id" value="{{ request('kamar_id') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            
            @if(!$isSeninKamis)
                <div class="p-6 text-center h-full flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full flex items-center justify-center text-amber-500 mb-4 shadow-sm border border-amber-200/50">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-stone-100">Bukan Hari Puasa Sunnah</h3>
                    <p class="text-sm text-slate-500 dark:text-stone-400 mt-2">Tanggal yang dipilih (<span class="font-bold text-slate-700 dark:text-stone-200">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>) bukan hari Senin atau Kamis.</p>
                </div>
            @else
                <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md p-3 border-b border-slate-100 dark:border-stone-700/50 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 flex-shrink-0 z-20">
                    <div class="grid grid-cols-2 sm:flex gap-2">
                        <button type="button" onclick="document.querySelectorAll('#form-puasa input[value=\'puasa\']').forEach(e => e.checked = true)" class="btn btn-outline text-xs px-2 sm:px-3 py-2 sm:py-1.5 rounded-lg text-emerald-600 border-emerald-200 hover:bg-emerald-50 text-center justify-center leading-tight">Set Semua<br class="sm:hidden"> Puasa</button>
                        <button type="button" onclick="document.querySelectorAll('#form-puasa input[type=\'radio\']').forEach(e => e.checked = false)" class="btn btn-outline text-xs px-2 sm:px-3 py-2 sm:py-1.5 rounded-lg text-slate-500 dark:text-stone-400 border-slate-200 dark:border-stone-700/50 hover:bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-center justify-center leading-tight">Kosongkan<br class="sm:hidden"> Pilihan</button>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                        @php
                            $selectedDaerah = null;
                            if (request('daerah_id')) {
                                $selectedDaerah = $daerahList->where('id', request('daerah_id'))->first();
                            } elseif (empty($showDaerahFilter) || !$showDaerahFilter) {
                                // Jika Pengurus Daerah (filter dropdown tidak muncul), pakai daerah miliknya
                                $selectedDaerah = $daerahList->first();
                            }
                        @endphp
                        @if($selectedDaerah && auth()->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'pengurus_daerah']))
                            <button type="button" onclick="document.getElementById('form-toggle-wajib').submit()" class="btn text-xs px-4 py-2 rounded-xl border {{ $selectedDaerah->is_puasa_wajib ? 'bg-amber-100 text-amber-700 border-amber-300 hover:bg-amber-200' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200' }} shadow-sm transition-colors text-center justify-center">
                                {!! $selectedDaerah->is_puasa_wajib ? '<span class="font-bold">&check; Wajib Puasa Aktif</span>' : 'Set Wajib Puasa' !!}
                            </button>
                        @endif
                        
                        <button type="submit" class="btn px-6 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold rounded-xl shadow-md transition-colors text-sm justify-center flex-1 sm:flex-none">Simpan Absensi Puasa</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table w-full min-w-[800px]">
                        <thead class="sticky top-0 z-10 bg-gradient-to-r from-amber-50/90 to-orange-50/90 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 backdrop-blur-md">
                            <tr>
                                <th class="w-12 text-center py-3">#</th>
                                <th class="py-3 px-4">Nama & Daerah</th>
                                <th class="text-center py-3 px-4">Status Puasa</th>
                                <th class="py-3 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($santriList as $index => $santri)
                            @php
                                $status = old("absensi.{$index}.status_puasa");
                                $keterangan = old("absensi.{$index}.keterangan");
                            @endphp
                            <tr>
                                <td class="text-center font-medium text-slate-400 dark:text-stone-500 text-xs py-3">{{ $index + 1 }}</td>
                                <td class="py-3 px-4">
                                    <input type="hidden" name="absensi[{{ $index }}][santri_id]" value="{{ $santri->id }}">
                                    <div class="font-bold text-slate-800 dark:text-stone-100 text-sm">{{ $santri->nama }}</div>
                                    <div class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">{{ $santri->nis_induk }} &bull; {{ $santri->unitOrganisasi->nama ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        @if(isset($santriIzinIds[$santri->id]))
                                            @php
                                                $larizan = $santriIzinIds[$santri->id];
                                                $larizanStatus = $larizan['status'];
                                            @endphp
                                            <label class="inline-flex items-center cursor-not-allowed text-xs font-medium bg-slate-50 text-slate-400 px-2 py-1.5 rounded-lg border border-slate-200 opacity-60">
                                                <input type="radio" disabled class="mr-1 w-3.5 h-3.5"> Puasa
                                            </label>
                                            <label class="inline-flex items-center cursor-not-allowed text-xs font-medium bg-slate-50 text-slate-400 px-2 py-1.5 rounded-lg border border-slate-200 opacity-60">
                                                <input type="radio" disabled class="mr-1 w-3.5 h-3.5"> Tidak
                                            </label>
                                            @if(strtolower($santri->jenis_kelamin) == 'perempuan' || strtolower($santri->jenis_kelamin) == 'p')
                                            <label class="inline-flex items-center cursor-not-allowed text-xs font-medium bg-slate-50 text-slate-400 px-2 py-1.5 rounded-lg border border-slate-200 opacity-60">
                                                <input type="radio" disabled class="mr-1 w-3.5 h-3.5"> Haid
                                            </label>
                                            @endif
                                            <label class="inline-flex items-center cursor-not-allowed text-xs font-medium {{ $larizanStatus == 'sakit' ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-slate-50 text-slate-400 border-slate-200' }} px-2 py-1.5 rounded-lg border opacity-90">
                                                <input type="radio" disabled class="mr-1 w-3.5 h-3.5" {{ $larizanStatus == 'sakit' ? 'checked' : '' }}> Sakit
                                            </label>
                                            <label class="inline-flex items-center cursor-not-allowed text-xs font-medium {{ $larizanStatus == 'izin' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-slate-50 text-slate-400 border-slate-200' }} px-2 py-1.5 rounded-lg border opacity-90">
                                                <input type="radio" disabled class="mr-1 w-3.5 h-3.5" {{ $larizanStatus == 'izin' ? 'checked' : '' }}> Izin
                                            </label>
                                            <input type="hidden" name="absensi[{{ $index }}][status_puasa]" value="{{ $larizanStatus }}">
                                        @elseif(isset($selectedDaerah) && !$selectedDaerah->is_puasa_wajib)
                                            <!-- Simple Checkbox if Not Wajib -->
                                            <input type="hidden" name="absensi[{{ $index }}][status_puasa]" value="tidak_puasa">
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium {{ $status == 'puasa' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }} px-3 py-1.5 rounded-lg border transition-colors shadow-sm" onclick="this.classList.toggle('bg-emerald-100'); this.classList.toggle('text-emerald-800'); this.classList.toggle('border-emerald-300'); this.classList.toggle('bg-slate-50'); this.classList.toggle('text-slate-600'); this.classList.toggle('border-slate-200');">
                                                <input type="checkbox" name="absensi[{{ $index }}][status_puasa]" value="puasa" class="mr-1.5 accent-emerald-500 w-4 h-4 rounded border-slate-300" {{ $status == 'puasa' ? 'checked' : '' }}> Centang Jika Puasa
                                            </label>
                                        @else
                                            <!-- Full Radio Buttons if Wajib -->
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-1.5 rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                                                <input type="radio" name="absensi[{{ $index }}][status_puasa]" value="puasa" class="mr-1 accent-emerald-500 w-3.5 h-3.5" {{ $status == 'puasa' ? 'checked' : '' }} required> Puasa
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 text-slate-600 dark:text-stone-300 px-2 py-1.5 rounded-lg border border-slate-200 dark:border-stone-700/50 hover:bg-slate-100 transition-colors shadow-sm">
                                                <input type="radio" name="absensi[{{ $index }}][status_puasa]" value="tidak_puasa" class="mr-1 accent-slate-500 w-3.5 h-3.5" {{ $status == 'tidak_puasa' ? 'checked' : '' }}> Tidak
                                            </label>
                                            @if(strtolower($santri->jenis_kelamin) == 'perempuan' || strtolower($santri->jenis_kelamin) == 'p')
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-rose-50 text-rose-700 px-2 py-1.5 rounded-lg border border-rose-100 hover:bg-rose-100 transition-colors shadow-sm">
                                                <input type="radio" name="absensi[{{ $index }}][status_puasa]" value="haid" class="mr-1 accent-rose-500 w-3.5 h-3.5" {{ $status == 'haid' ? 'checked' : '' }}> Haid
                                            </label>
                                            @endif
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-blue-50 text-blue-700 px-2 py-1.5 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors shadow-sm">
                                                <input type="radio" name="absensi[{{ $index }}][status_puasa]" value="sakit" class="mr-1 accent-blue-500 w-3.5 h-3.5" {{ $status == 'sakit' ? 'checked' : '' }}> Sakit
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer text-xs font-medium bg-amber-50 text-amber-700 px-2 py-1.5 rounded-lg border border-amber-100 hover:bg-amber-100 transition-colors shadow-sm">
                                                <input type="radio" name="absensi[{{ $index }}][status_puasa]" value="izin" class="mr-1 accent-amber-500 w-3.5 h-3.5" {{ $status == 'izin' ? 'checked' : '' }}> Izin
                                            </label>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if(isset($santriIzinIds[$santri->id]))
                                        <input type="text" name="absensi[{{ $index }}][keterangan]" value="{{ $santriIzinIds[$santri->id]['keterangan'] }}" readonly class="form-input text-xs w-full py-1.5 bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200">
                                    @else
                                        <input type="text" name="absensi[{{ $index }}][keterangan]" value="{{ $keterangan }}" placeholder="Opsional..." class="form-input text-xs w-full py-1.5 bg-white/50 dark:bg-[#1A1817]/80">
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-10">
                                    <div class="flex flex-col items-center justify-center text-slate-400 dark:text-stone-500">
                                        <svg class="w-12 h-12 mb-3 text-slate-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @if(isset($selectedDaerah) && !$selectedDaerah->is_puasa_wajib && !request('search'))
                                            <p class="font-medium text-slate-600 dark:text-stone-400">Puasa Sunnah Tidak Wajib</p>
                                            <p class="text-sm mt-1">Gunakan kotak pencarian di atas untuk mencari dan mengabsen santri tertentu yang berpuasa.</p>
                                        @else
                                            <p>Tidak ada data santri ditemukan.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </form>
        
        {{-- Form Hidden untuk Toggle Wajib Puasa --}}
        @if(isset($selectedDaerah) && $selectedDaerah && auth()->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'pengurus_daerah']))
        <form id="form-toggle-wajib" action="{{ route('absensi.toggle-puasa-wajib', $selectedDaerah->id) }}" method="POST" class="hidden">
            @csrf
        </form>
        @endif
    </div>
    @else
    {{-- Placeholder when not showing table --}}
    <div class="flex flex-col items-center justify-center bg-white/40 backdrop-blur-md rounded-2xl border border-dashed border-violet-200 text-center p-12 mb-6">
        <div class="w-16 h-16 bg-violet-100 text-violet-500 rounded-full flex items-center justify-center mb-4 shadow-inner">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
        </div>
        <h3 class="text-lg font-bold text-slate-700 dark:text-stone-200 mb-1">Siap Memulai Absensi?</h3>
        <p class="text-sm text-slate-500 dark:text-stone-400 max-w-md">Silakan atur filter tanggal, kegiatan, dan wilayah jika diperlukan, kemudian klik tombol <strong class="text-violet-600">"Mulai Absen"</strong> di atas untuk memuat data santri.</p>
    </div>
    @endif
</div>
@endsection
