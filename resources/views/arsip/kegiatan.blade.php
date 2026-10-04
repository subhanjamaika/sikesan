@extends('layouts.app')
@section('title', "Arsip Kegiatan — {$namaBulan} {$tahun}")

@section('content')
<div class="-m-6 p-6" x-data="arsipDetail()">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-stone-400 mb-4 flex-shrink-0">
        <a href="{{ route('arsip-absensi.index') }}" class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors font-medium">Arsip Absensi</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="font-semibold text-slate-700 dark:text-stone-200">{{ $namaBulan }} {{ $tahun }}</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 flex-shrink-0">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-stone-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                Arsip {{ $namaBulan }} {{ $tahun }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-stone-400 mt-1 ml-[52px]">Data kegiatan yang sudah diarsipkan pada periode ini.</p>
        </div>
        <a href="{{ route('arsip-absensi.index') }}" class="btn btn-outline text-sm py-2 px-4 rounded-xl flex items-center gap-2 self-start">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Kembali
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5 flex-shrink-0">
        <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center dark:bg-violet-900/30 dark:text-violet-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 dark:text-stone-400">Total Santri</div>
                <div class="text-xl font-bold text-slate-800 dark:text-stone-100">{{ $totalSantri }}</div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-teal-100 dark:border-stone-700/40 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center dark:bg-teal-900/30 dark:text-teal-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 dark:text-stone-400">Rata-rata Kehadiran</div>
                <div class="text-xl font-bold text-slate-800 dark:text-stone-100">{{ $avgKehadiran }}%</div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-rose-100 dark:border-stone-700/40 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center dark:bg-rose-900/30 dark:text-rose-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 dark:text-stone-400">Total Alpa</div>
                <div class="text-xl font-bold text-slate-800 dark:text-stone-100">{{ $totalAlpaAll }} Kali</div>
            </div>
        </div>
    </div>

    {{-- Filter Daerah --}}
    @if($showDaerahFilter)
    <div class="bg-white/90 dark:bg-[#272422]/90 backdrop-blur-sm rounded-2xl shadow-sm border border-amber-100 dark:border-stone-700/40 p-4 mb-5 flex-shrink-0">
        <form method="GET" action="{{ route('arsip-absensi.kegiatan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 dark:text-stone-300">Daerah:</label>
                <select name="daerah_id" class="form-select text-sm py-1.5 pl-3 rounded-lg border-slate-200 dark:border-stone-700/50 w-48" style="padding-right: 2.5rem; min-width: 140px;" onchange="this.form.submit()">
                    <option value="">Semua Daerah</option>
                    @foreach($daerahList as $daerah)
                        <option value="{{ $daerah->id }}" {{ $daerahId == $daerah->id ? 'selected' : '' }}>{{ $daerah->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    {{-- Folder Kegiatan Grid --}}
    @if(count($kegiatanStats) > 0)
    <div class="mb-4">
        <h2 class="text-sm font-bold text-slate-700 dark:text-stone-200 flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            Folder Kegiatan — Arsip {{ $namaBulan }} {{ $tahun }}
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @php
                $cardColors = [
                    ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'border' => 'border-violet-200 dark:border-violet-700/40', 'icon' => 'text-violet-500', 'iconBg' => 'bg-violet-100 dark:bg-violet-800/40', 'ring' => '#8b5cf6', 'hover' => 'hover:border-violet-300 dark:hover:border-violet-600/60 hover:shadow-violet-100/50'],
                    ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'border' => 'border-teal-200 dark:border-teal-700/40', 'icon' => 'text-teal-500', 'iconBg' => 'bg-teal-100 dark:bg-teal-800/40', 'ring' => '#14b8a6', 'hover' => 'hover:border-teal-300 dark:hover:border-teal-600/60 hover:shadow-teal-100/50'],
                    ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'border' => 'border-amber-200 dark:border-amber-700/40', 'icon' => 'text-amber-500', 'iconBg' => 'bg-amber-100 dark:bg-amber-800/40', 'ring' => '#f59e0b', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-600/60 hover:shadow-amber-100/50'],
                    ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'border' => 'border-rose-200 dark:border-rose-700/40', 'icon' => 'text-rose-500', 'iconBg' => 'bg-rose-100 dark:bg-rose-800/40', 'ring' => '#f43f5e', 'hover' => 'hover:border-rose-300 dark:hover:border-rose-600/60 hover:shadow-rose-100/50'],
                    ['bg' => 'bg-sky-50 dark:bg-sky-900/20', 'border' => 'border-sky-200 dark:border-sky-700/40', 'icon' => 'text-sky-500', 'iconBg' => 'bg-sky-100 dark:bg-sky-800/40', 'ring' => '#0ea5e9', 'hover' => 'hover:border-sky-300 dark:hover:border-sky-600/60 hover:shadow-sky-100/50'],
                    ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-700/40', 'icon' => 'text-emerald-500', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-800/40', 'ring' => '#10b981', 'hover' => 'hover:border-emerald-300 dark:hover:border-emerald-600/60 hover:shadow-emerald-100/50'],
                ];
            @endphp

            @foreach($kegiatanStats as $index => $stat)
                @php
                    $color = $cardColors[$index % count($cardColors)];
                    $pct = $stat->persentase;
                    $circumference = 2 * 3.14159 * 16;
                    $dashOffset = $circumference - ($pct / 100) * $circumference;
                @endphp
                <button @click="openModal('{{ $stat->id }}', '{{ addslashes($stat->nama_kegiatan) }}')"
                        class="block w-full group bg-white dark:bg-[#272422] rounded-2xl shadow-sm border {{ $color['border'] }} px-4 py-3 text-left transition-all duration-200 {{ $color['hover'] }} hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-[#1A1817]">

                    {{-- Top: Icon + Name --}}
                    <div class="flex items-start justify-between mb-2.5">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $color['iconBg'] }} {{ $color['icon'] }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[13px] font-bold text-slate-800 dark:text-stone-100 truncate">{{ $stat->nama_kegiatan }}</h3>
                                @if($stat->jam_pelaksanaan)
                                    <p class="text-[10px] text-slate-400 dark:text-stone-500 font-medium">{{ $stat->jam_pelaksanaan }}</p>
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
            @endforeach
        </div>
    </div>
    @else
        <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-amber-100 dark:border-stone-700/40 p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-stone-800 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-slate-400 dark:text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Tidak ditemukan data kegiatan arsip pada periode ini.</p>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Level 3: Modal Detail Santri (Alpine.js)   --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] overflow-y-auto"
         aria-labelledby="modal-arsip-detail"
         role="dialog"
         aria-modal="true"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            {{-- Backdrop --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"
                 @click="closeModal()"
                 aria-hidden="true"></div>

            {{-- Modal Panel --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-[#1E1C1A] rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col border border-gray-100 dark:border-stone-700/50 overflow-hidden z-10">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-stone-700/50 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-[#272422] dark:to-[#272422] flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-stone-100" x-text="modalTitle"></h3>
                            <p class="text-xs text-slate-500 dark:text-stone-400">Arsip {{ $namaBulan }} {{ $tahun }} — Detail per Santri</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-stone-800 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-slate-500 dark:text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body (scrollable) --}}
                <div class="flex-1 min-h-0 overflow-y-auto p-6">
                    {{-- Loading --}}
                    <div x-show="loading" class="flex flex-col items-center justify-center py-16">
                        <div class="w-10 h-10 border-3 border-amber-200 border-t-amber-500 rounded-full animate-spin mb-4"></div>
                        <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Memuat data arsip...</p>
                    </div>

                    {{-- Table --}}
                    <div x-show="!loading && detailData.length > 0" style="display: none;">
                        <table class="data-table w-full">
                            <thead class="sticky top-0 z-10 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-[#272422] dark:to-[#272422]">
                                <tr>
                                    <th class="w-12 text-center">No</th>
                                    <th>Nama Santri</th>
                                    <th>NIS</th>
                                    <th>Daerah</th>
                                    <th class="text-center w-14">H</th>
                                    <th class="text-center w-14">I</th>
                                    <th class="text-center w-14">S</th>
                                    <th class="text-center w-14">A</th>
                                    <th class="text-center w-16">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in detailData" :key="idx">
                                    <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-900/10 transition-colors">
                                        <td class="text-center text-slate-500 dark:text-stone-400" x-text="idx + 1"></td>
                                        <td class="font-semibold text-slate-800 dark:text-stone-100" x-text="row.nama"></td>
                                        <td class="text-slate-600 dark:text-stone-300" x-text="row.nis || '-'"></td>
                                        <td class="text-slate-600 dark:text-stone-300" x-text="row.daerah"></td>
                                        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-xs font-bold text-emerald-700 dark:text-emerald-400" x-text="row.hadir"></span></td>
                                        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-xs font-bold text-amber-700 dark:text-amber-400" x-text="row.izin"></span></td>
                                        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs font-bold text-blue-700 dark:text-blue-400" x-text="row.sakit"></span></td>
                                        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-xs font-bold text-rose-700 dark:text-rose-400" x-text="row.alpa"></span></td>
                                        <td class="text-center font-bold text-slate-700 dark:text-stone-200" x-text="row.persentase + '%'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Empty --}}
                    <div x-show="!loading && detailData.length === 0" class="text-center py-12" style="display: none;">
                        <p class="text-sm text-slate-500 dark:text-stone-400">Tidak ada data santri ditemukan pada arsip kegiatan ini.</p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200 dark:border-stone-700/50 bg-slate-50 dark:bg-[#1A1817] flex-shrink-0">
                    <p class="text-xs text-slate-500 dark:text-stone-400">
                        <span x-text="detailData.length"></span> santri ditampilkan
                    </p>
                    <button @click="closeModal()" class="btn btn-outline text-sm py-1.5 px-4 rounded-lg">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function arsipDetail() {
    return {
        showModal: false,
        loading: false,
        modalTitle: '',
        detailData: [],

        openModal(kegiatanId, namaKegiatan) {
            this.showModal = true;
            this.loading = true;
            this.modalTitle = namaKegiatan;
            this.detailData = [];

            const url = new URL('{{ route("arsip-absensi.detail-kegiatan") }}', window.location.origin);
            url.searchParams.set('kegiatan_id', kegiatanId);
            url.searchParams.set('bulan', '{{ $bulan }}');
            url.searchParams.set('tahun', '{{ $tahun }}');

            fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(json => {
                this.detailData = json.data || [];
                this.loading = false;
            })
            .catch(() => {
                this.detailData = [];
                this.loading = false;
            });
        },

        closeModal() {
            this.showModal = false;
            this.detailData = [];
        }
    };
}
</script>
@endpush
@endsection
