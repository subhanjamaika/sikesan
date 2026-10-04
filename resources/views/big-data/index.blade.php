@extends('layouts.app')
@section('title', 'Data Santri — Wilayah Zaid bin Tsabit')

@section('content')
<div class="flex flex-col h-full space-y-4" x-data="{ showFilter: {{ request()->anyFilled(['daerah_id', 'kamar_id', 'program_id']) ? 'true' : 'false' }} }">

    {{-- Header Card dengan Baris Pencarian Otomatis (Tanpa Tombol Cari) & Toggle Filter --}}
    <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-4 sm:p-5 flex-shrink-0">
        <form method="GET" action="{{ route('big-data.index') }}" id="filterForm">
            
            {{-- Baris Atas: Input Pencarian Otomatis + Tombol Toggle Filter --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                {{-- Info Ringkas / Status Data --}}
                <div class="text-slate-600 dark:text-stone-300 font-medium text-xs sm:text-sm flex items-center gap-2 flex-wrap">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                    <span>Menampilkan <strong class="text-slate-800 dark:text-stone-100">{{ $santriList->total() }}</strong> Data Pokok Santri</span>
                    
                    @if(request('daerah_id'))
                        @php
                            $selectedDaerahNama = $daerahList->firstWhere('id', request('daerah_id'))->nama ?? '';
                        @endphp
                        <span class="badge badge-teal">Daerah: {{ $selectedDaerahNama }}</span>
                    @endif
                    @if(request('kamar_id'))
                        @php
                            $selectedKamarNama = $kamarsForSelectedDaerah->firstWhere('id', request('kamar_id'))->nama ?? '';
                        @endphp
                        <span class="badge badge-indigo">Kamar: {{ $selectedKamarNama }}</span>
                    @endif
                </div>

                {{-- Baris Pencarian Otomatis + Tombol Toggle Filter --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-80">
                        <input type="search"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Ketik Nama / NIS (Otomatis Mencari)..."
                               @input.debounce.400ms="$el.form.submit()"
                               @search="$el.form.submit()"
                               class="form-input text-sm pl-9 pr-8 py-2 rounded-xl border-slate-200 dark:border-stone-700/50 focus:border-teal-400 focus:ring-teal-400 bg-white dark:bg-[#272422]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-stone-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('big-data.index', request()->except('search')) }}"
                               class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 dark:text-stone-500 hover:text-rose-500"
                               title="Bersihkan Pencarian">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>

                    <button type="button"
                            @click="showFilter = !showFilter"
                            class="btn text-xs py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition-all flex-shrink-0"
                            :class="showFilter || {{ request()->anyFilled(['daerah_id', 'kamar_id', 'program_id']) ? 'true' : 'false' }} ? 'btn-primary' : 'btn-outline'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['daerah_id', 'kamar_id', 'program_id']))
                            <span class="w-2 h-2 rounded-full bg-teal-300 animate-pulse"></span>
                        @endif
                    </button>
                </div>

            </div>

            {{-- Panel Collapsible Dropdown Filter --}}
            <div x-show="showFilter"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                 class="pt-4 mt-4 border-t border-violet-100/70 dark:border-stone-700/40"
                 style="display: none;">
                
                <div class="flex flex-wrap items-end gap-3.5">
                    
                    {{-- Dropdown Daerah --}}
                    @if($showDaerahFilter)
                    <div class="w-full sm:w-60">
                        <label for="daerah_id" class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Daerah</label>
                        <select id="daerah_id" name="daerah_id" class="form-select text-sm py-2" onchange="if(document.getElementById('kamar_id')) document.getElementById('kamar_id').value=''; this.form.submit();">
                            <option value="">Semua Daerah</option>
                            @foreach($daerahList as $daerah)
                                <option value="{{ $daerah->id }}" {{ request('daerah_id') == $daerah->id ? 'selected' : '' }}>
                                    {{ $daerah->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Dropdown Kamar --}}
                    <div class="w-full sm:w-52">
                        <label for="kamar_id" class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Kamar</label>
                        <select id="kamar_id" name="kamar_id" class="form-select text-sm py-2" onchange="this.form.submit();">
                            <option value="">Semua Kamar</option>
                            @foreach($kamarsForSelectedDaerah as $kamar)
                                <option value="{{ $kamar->id }}" {{ request('kamar_id') == $kamar->id ? 'selected' : '' }}>
                                    {{ $kamar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Program --}}
                    @if(isset($programList))
                    <div class="w-full sm:w-52">
                        <label for="program_id" class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Program Diniyah</label>
                        <select id="program_id" name="program_id" class="form-select text-sm py-2" onchange="this.form.submit();">
                            <option value="">Semua Program</option>
                            @foreach($programList as $prog)
                                <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>
                                    {{ $prog->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Tombol Reset Filter --}}
                    @if(request()->anyFilled(['search', 'daerah_id', 'kamar_id', 'program_id']))
                        <div>
                            <a href="{{ request()->url() }}" class="btn btn-outline text-xs py-2 px-3.5 flex items-center gap-1.5 text-slate-600 dark:text-stone-300 hover:text-rose-600">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset Filter
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        </form>
    </div>

    {{-- Tabel Pokok Santri (One-Screen Fit dengan Internal Table Scrollbar) --}}
    <div class="flex-1 min-h-0 bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 flex flex-col overflow-hidden">
        <div class="flex-1 min-h-0 overflow-y-auto">
            <table class="data-table">
                <thead class="sticky top-0 z-10 bg-gradient-to-r from-violet-50 to-teal-50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 shadow-xs">
                    <tr>
                        <th class="w-12 text-center py-3">#</th>
                        <th class="w-32 py-3">NIUP</th>
                        <th class="py-3">NAMA</th>
                        <th class="w-48 py-3">DAERAH</th>
                        <th class="w-40 py-3">KAMAR</th>
                        <th class="w-48 py-3">PROGRAM</th>
                        @if(!auth()->user()->hasRole('dewan_pemangku'))
                        <th class="w-24 py-3 text-right pr-4">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($santriList as $index => $santri)
                        <tr class="hover:bg-violet-50/40 transition-colors">
                            <td class="text-center font-medium text-slate-400 dark:text-stone-500 text-xs py-2.5">
                                {{ $santriList->firstItem() + $index }}
                            </td>
                            <td class="py-2.5">
                                <div class="font-mono text-xs font-semibold text-slate-700 dark:text-stone-200 bg-slate-100 dark:bg-[#1A1817] px-2 py-0.5 rounded inline-block">
                                    {{ $santri['niup'] ?? '-' }}
                                </div>
                            </td>
                            <td class="py-2.5">
                                <div class="flex items-center gap-3">
                                    @if(!empty($santri['foto_signed_url']))
                                        <img src="{{ $santri['foto_signed_url'] }}" alt="{{ $santri['nama'] ?? 'Foto' }}" class="w-8 h-8 rounded-full object-cover shadow-xs border border-slate-100 dark:border-stone-700">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-400 to-teal-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-xs">
                                            {{ strtoupper(substr($santri['nama'] ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 dark:text-stone-100 text-xs sm:text-sm truncate">{{ $santri['nama'] ?? '-' }}</div>
                                        @if(!empty($santri['pendidikan_aktif']))
                                            <div class="text-[10px] text-slate-500 dark:text-stone-400 truncate">
                                                @foreach($santri['pendidikan_aktif'] as $pend)
                                                    {{ $pend['lembaga'] ?? '' }} {{ $pend['kelas'] ?? '' }} {{ $pend['rombel'] ?? '' }}@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5">
                                <span class="badge badge-teal flex-inline items-center gap-1 mb-1">
                                    <svg class="w-3 h-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 012-2h2a2 2 0 012 2v5m-6 0h6"/></svg>
                                    {{ $santri['unit_organisasi']['nama'] ?? '-' }}
                                </span>
                                @if(!empty($santri['kecamatan']))
                                    <div class="text-[10px] text-slate-500 dark:text-stone-400 leading-tight max-w-[150px] truncate" title="Asal: {{ $santri['kecamatan']['nama'] ?? '' }}@if(isset($santri['kecamatan']['parent'])), {{ $santri['kecamatan']['parent']['nama'] ?? '' }}@endif">
                                        Asal: {{ $santri['kecamatan']['nama'] ?? '' }}@if(isset($santri['kecamatan']['parent'])), {{ $santri['kecamatan']['parent']['nama'] ?? '' }}@endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5">
                                <span class="badge badge-indigo flex-inline items-center gap-1">
                                    <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    {{ $santri['kamar']['nama'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                @if(!empty($santri['program_aktif']))
                                    <div class="flex flex-col gap-1">
                                        @foreach($santri['program_aktif'] as $prog)
                                            <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 flex-inline items-center gap-1 w-max">
                                                <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                {{ $prog['program']['nama'] ?? '-' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-stone-500">-</span>
                                @endif
                            </td>
                            {{-- Kolom Aksi tersembunyi untuk role Read-Only (Dewan Pemangku) --}}
                            @if(!auth()->user()->hasRole('dewan_pemangku'))
                            <td class="py-2.5 text-right pr-4">
                                <button type="button" class="btn btn-outline py-1 px-2 text-[10px] rounded hover:bg-slate-50 dark:hover:bg-stone-800 transition-colors">
                                    Detail
                                </button>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <h4 class="text-slate-700 dark:text-stone-200 font-bold text-sm mb-0.5">Data Santri Tidak Ditemukan</h4>
                                <p class="text-slate-500 dark:text-stone-400 text-xs max-w-sm mx-auto">
                                    Tidak ada data santri yang cocok dengan kriteria filter atau pencarian Anda. Silakan coba ubah filter.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($santriList->hasPages())
            <div class="px-4 py-2.5 border-t border-violet-100 dark:border-stone-700/40 bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300/50 flex-shrink-0">
                {{ $santriList->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

</div>
@endsection
