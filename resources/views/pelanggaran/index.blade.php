@extends('layouts.app')
@section('title', 'Data Pelanggaran')

@section('header-actions')
    @if(auth()->check() && !auth()->user()->hasRole('dewan_pemangku'))
    @can('create', App\Models\Pelanggaran::class)
    <a href="{{ route('pelanggaran.create') }}" class="btn btn-primary text-xs py-2 px-3">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Catat Pelanggaran
    </a>
    @endcan
    @endif
@endsection

@section('content')
<style>
    /* Gradient Khusus untuk Pelanggaran (Tema Rose/Violet) */
    .filter-bg-gradient { background: linear-gradient(135deg, #fecdd3 0%, #e9d5ff 100%) !important; border-color: #fbcfe8 !important; }
    .table-head-gradient { background: linear-gradient(90deg, #fff1f2 0%, #faf5ff 100%) !important; }
    
    .dark .filter-bg-gradient { background: linear-gradient(135deg, rgba(225, 29, 72, 0.2) 0%, rgba(124, 58, 237, 0.2) 100%) !important; border-color: rgba(225, 29, 72, 0.3) !important; }
    .dark .table-head-gradient { background: linear-gradient(90deg, rgba(225, 29, 72, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%) !important; }
</style>

<div class="flex flex-col h-full space-y-4" x-data="{ showFilter: {{ request()->anyFilled(['daerah_id', 'kamar_id', 'kategori', 'tingkat']) ? 'true' : 'false' }} }">

    {{-- Filter Bar dengan Toggle & Pencarian Otomatis --}}
    <div class="filter-bg-gradient backdrop-blur-md rounded-2xl shadow-md border p-4 sm:p-5 flex-shrink-0 transition-all">
        <form method="GET" action="{{ route('pelanggaran.index') }}" id="filterForm">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                {{-- Status Info --}}
                <div class="text-slate-600 dark:text-stone-300 font-medium text-xs sm:text-sm flex items-center gap-2 flex-wrap">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span>Menampilkan <strong class="text-slate-800 dark:text-stone-100">{{ $pelanggaran->total() }}</strong> Data Pelanggaran Santri</span>
                    @if(request('kategori'))
                        <span class="badge badge-yellow">Kategori: {{ ucfirst(request('kategori')) }}</span>
                    @endif
                    @if(request('tingkat'))
                        <span class="badge badge-blue">Tingkat: {{ ucfirst(request('tingkat')) }}</span>
                    @endif
                    @if(request('daerah_id'))
                        @php $selectedDaerahNama = $daerahList->firstWhere('id', request('daerah_id'))->nama ?? ''; @endphp
                        <span class="badge badge-teal">Daerah: {{ $selectedDaerahNama }}</span>
                    @endif
                    @if(request('kamar_id'))
                        @php $selectedKamarNama = $kamarsForSelectedDaerah->firstWhere('id', request('kamar_id'))->nama ?? ''; @endphp
                        <span class="badge badge-indigo">Kamar: {{ $selectedKamarNama }}</span>
                    @endif
                </div>

                {{-- Search Box Otomatis + Toggle Filter Button --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    @if(!Auth::user()->hasRole('wali_santri'))
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
                                <a href="{{ route('pelanggaran.index', request()->except('search')) }}"
                                   class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 dark:text-stone-500 hover:text-rose-500"
                                   title="Bersihkan Pencarian">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif

                    <button type="button"
                            @click="showFilter = !showFilter"
                            class="btn text-xs py-2 px-3.5 rounded-xl flex items-center gap-1.5 transition-all flex-shrink-0"
                            :class="showFilter || {{ request()->anyFilled(['daerah_id', 'kamar_id', 'kategori', 'tingkat']) ? 'true' : 'false' }} ? 'btn-primary' : 'btn-outline'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['daerah_id', 'kamar_id', 'kategori', 'tingkat']))
                            <span class="w-2 h-2 rounded-full bg-teal-300 animate-pulse"></span>
                        @endif
                    </button>
                </div>

            </div>

            {{-- Collapsible Filter Panel --}}
            <div x-show="showFilter"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                 class="pt-4 mt-4 border-t border-rose-300/50 dark:border-rose-800/40"
                 style="display: none;">
                
                <div class="flex flex-wrap items-end gap-3.5">
                    {{-- Dropdown Daerah --}}
                    <div class="w-full sm:w-60">
                        <label for="daerah_id" class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Daerah</label>
                        <select id="daerah_id" name="daerah_id" class="form-select text-sm py-2" onchange="this.form.submit();">
                            <option value="">Semua Daerah</option>
                            @foreach($daerahList as $daerah)
                                <option value="{{ $daerah->id }}" {{ request('daerah_id') == $daerah->id ? 'selected' : '' }}>
                                    {{ $daerah->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Kamar --}}
                    <div class="w-full sm:w-48">
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

                    {{-- Kategori --}}
                    <div class="w-full sm:w-40">
                        <label class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Kategori</label>
                        <select name="kategori" class="form-select text-sm py-2" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <option value="umum" {{ request('kategori') == 'umum' ? 'selected' : '' }}>Umum</option>
                            <option value="devisi" {{ request('kategori') == 'devisi' ? 'selected' : '' }}>Devisi</option>
                        </select>
                    </div>

                    {{-- Tingkat --}}
                    <div class="w-full sm:w-40">
                        <label class="form-label text-xs font-semibold text-slate-700 dark:text-stone-200 mb-1.5">Tingkat</label>
                        <select name="tingkat" class="form-select text-sm py-2" onchange="this.form.submit()">
                            <option value="">Semua Tingkat</option>
                            <option value="sektor" {{ request('tingkat') == 'sektor' ? 'selected' : '' }}>Sektor</option>
                            <option value="daerah" {{ request('tingkat') == 'daerah' ? 'selected' : '' }}>Daerah</option>
                        </select>
                    </div>

                    @if(request()->anyFilled(['search', 'daerah_id', 'kamar_id', 'kategori', 'tingkat']))
                        <div>
                            <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline text-xs py-2 px-3.5 flex items-center gap-1.5 text-slate-600 dark:text-stone-300 hover:text-rose-600">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- Data Table (One-Screen Fit) --}}
    <div class="flex-1 min-h-0 bg-white/90 dark:bg-[#1A1817]/90 backdrop-blur-md rounded-2xl shadow-sm border border-rose-100 dark:border-rose-900/30 flex flex-col overflow-hidden">
        <div class="flex-1 min-h-0 overflow-y-auto">
            <table class="data-table">
                <thead class="sticky top-0 z-10 table-head-gradient shadow-xs">
                    <tr>
                        <th class="w-12 text-center py-3">#</th>
                        <th class="w-36 py-3">No. Induk Santri</th>
                        <th class="py-3">Nama Santri</th>
                        <th class="w-36 py-3">Kamar</th>
                        <th class="w-44 py-3">Daerah</th>
                        <th class="py-3">Kategori & Tingkat</th>
                        <th class="py-3">Tanggal (dd/mm/yyyy)</th>
                        <th class="py-3">Status Pembinaan</th>
                        <th class="text-right py-3">{{ auth()->user()->hasRole('dewan_pemangku') ? 'Detail' : 'Aksi' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggaran as $index => $p)
                    @php
                        $kamarNama = $p->santri->unitOrganisasi->nama ?? '-';
                        $daerahNama = $p->santri->unitOrganisasi->parent->nama ?? '-';
                    @endphp
                    <tr class="hover:bg-violet-50/40 transition-colors">
                        <td class="text-center font-medium text-slate-400 dark:text-stone-500 text-xs py-2.5">
                            {{ $pelanggaran->firstItem() + $index }}
                        </td>
                        <td class="py-2.5">
                            <div class="font-mono text-xs font-semibold text-slate-700 dark:text-stone-300 bg-slate-100 dark:bg-[#1A1817] px-2 py-0.5 rounded inline-block">
                                {{ $p->santri->nis_induk }}
                            </div>
                        </td>
                        <td class="py-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-400 to-teal-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-xs">
                                    {{ strtoupper(substr($p->santri->nama, 0, 1)) }}
                                </div>
                                <div class="font-bold text-slate-800 dark:text-stone-100 text-xs truncate">{{ $p->santri->nama }}</div>
                            </div>
                        </td>
                        <td class="py-2.5">
                            <span class="badge badge-indigo flex-inline items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                {{ $kamarNama }}
                            </span>
                        </td>
                        <td class="py-2.5">
                            <span class="badge badge-teal flex-inline items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 012-2h2a2 2 0 012 2v5m-6 0h6"/></svg>
                                {{ $daerahNama }}
                            </span>
                        </td>
                        <td class="py-2.5">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="badge badge-{{ $p->kategori == 'umum' ? 'gray' : 'yellow' }}">{{ ucfirst($p->kategori) }}</span>
                                <span class="text-xs text-slate-500 dark:text-stone-400">{{ ucfirst($p->tingkat) }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600 dark:text-stone-300 truncate max-w-[180px]" title="{{ $p->deskripsi }}">{{ Str::limit($p->deskripsi, 25) }}</div>
                        </td>
                        <td class="py-2.5 text-xs text-slate-700 dark:text-stone-200 font-mono">{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td class="py-2.5">
                            @if($p->pembinaan->count() > 0)
                                <span class="badge badge-green">{{ $p->pembinaan->count() }} Kali Dibina</span>
                            @else
                                <span class="badge badge-red">Belum Dibina</span>
                            @endif
                        </td>
                        <td class="text-right py-2.5">
                            <a href="{{ route('pelanggaran.show', $p) }}" class="text-violet-600 hover:text-violet-800 font-medium text-xs inline-block py-1">Detail & Pembinaan</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-slate-500 dark:text-stone-400">Belum ada data pelanggaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pelanggaran->hasPages())
            <div class="px-4 py-2.5 border-t border-rose-100 dark:border-rose-900/30 table-head-gradient flex-shrink-0">
                {{ $pelanggaran->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
