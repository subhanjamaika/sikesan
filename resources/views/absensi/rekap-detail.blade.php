@extends('layouts.app')
@section('title', 'Detail Rekapitulasi: ' . $kegiatan->nama_kegiatan)

@section('content')
<div class="max-w-5xl mx-auto w-full" x-data="riwayatDrilldown()">
    {{-- Header Container --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-900/30 text-violet-500 flex items-center justify-center flex-shrink-0 shadow-sm border border-violet-200 dark:border-violet-700/40">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 dark:text-stone-100 flex items-center gap-2">
                    Detail Rekap: <span class="text-violet-600 dark:text-violet-400">{{ $kegiatan->nama_kegiatan }}</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Periode: {{ date('F Y', mktime(0, 0, 0, $bulan, 10, $tahun)) }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('absensi.export.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'daerah_id' => $daerahId, 'kamar_id' => $kamarId, 'kegiatan_id' => $kegiatan->id]) }}" class="btn px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2" target="_blank">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            <a href="{{ route('absensi.export.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'daerah_id' => $daerahId, 'kamar_id' => $kamarId, 'kegiatan_id' => $kegiatan->id]) }}" class="btn px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-sm transition-colors text-sm flex items-center gap-2" target="_blank">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('absensi.rekap', ['bulan' => $bulan, 'tahun' => $tahun, 'daerah_id' => $daerahId]) }}" class="btn btn-secondary text-sm flex-shrink-0 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-slate-100/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-4 mb-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('absensi.rekap.detail') }}" class="flex flex-wrap items-center gap-3 w-full">
            <input type="hidden" name="kegiatan_id" value="{{ $kegiatan->id }}">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            
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
    </div>

    {{-- Tabel Minimalis --}}
    <div class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-slate-200 dark:border-stone-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-slate-50 dark:bg-[#1A1817] text-slate-600 dark:text-stone-300 text-[10px] uppercase tracking-wider font-bold">
                    <tr>
                        <th class="py-4 px-6 text-left border-b border-slate-200 dark:border-stone-700/50">Identitas Santri</th>
                        <th class="py-4 px-3 text-center w-14 border-b border-slate-200 dark:border-stone-700/50" title="Hadir">H</th>
                        <th class="py-4 px-3 text-center w-14 border-b border-slate-200 dark:border-stone-700/50" title="Izin">I</th>
                        <th class="py-4 px-3 text-center w-14 border-b border-slate-200 dark:border-stone-700/50" title="Sakit">S</th>
                        <th class="py-4 px-3 text-center w-14 border-b border-slate-200 dark:border-stone-700/50" title="Alpa">A</th>
                        <th class="py-4 px-6 text-right w-28 border-b border-slate-200 dark:border-stone-700/50">Status</th>
                        <th class="py-4 px-4 text-center w-20 border-b border-slate-200 dark:border-stone-700/50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-stone-700/40 text-sm">
                    @forelse($detailData as $item)
                        <tr class="hover:bg-violet-50/40 dark:hover:bg-violet-900/10 transition-colors cursor-pointer group"
                            @click="openModal('{{ $item['id'] }}', '{{ addslashes($item['nama']) }}')">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-slate-800 dark:text-stone-100 text-[13px] flex items-center gap-1.5">
                                            {{ $item['nama'] }}
                                            <svg class="w-3.5 h-3.5 text-violet-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-stone-500 mt-0.5 font-medium flex items-center gap-1.5">
                                            <span class="font-mono">{{ $item['nis'] }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-stone-600"></span>
                                            <span>{{ $item['daerah'] }} / {{ $item['kamar'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-center text-xs font-extrabold text-emerald-600">{{ $item['hadir'] }}</td>
                            <td class="py-3.5 px-3 text-center text-xs font-extrabold text-amber-600">{{ $item['izin'] }}</td>
                            <td class="py-3.5 px-3 text-center text-xs font-extrabold text-blue-600">{{ $item['sakit'] }}</td>
                            <td class="py-3.5 px-3 text-center text-xs font-extrabold text-rose-600">{{ $item['alpa'] }}</td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex flex-col items-end gap-1">
                                    <span class="text-xs font-extrabold text-slate-700 dark:text-stone-200">{{ $item['persentase'] }}%</span>
                                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded inline-block
                                        @if($item['total'] == 0) bg-slate-50 text-slate-400 dark:bg-stone-800 dark:text-stone-500
                                        @elseif($item['persentase'] >= 80) bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20
                                        @elseif($item['persentase'] >= 50) bg-amber-50 text-amber-600 dark:bg-amber-900/20
                                        @else bg-rose-50 text-rose-600 dark:bg-rose-900/20
                                        @endif
                                    ">
                                        @if($item['total'] == 0) -
                                        @elseif($item['persentase'] >= 80) Baik
                                        @elseif($item['persentase'] >= 50) Cukup
                                        @else Kurang
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-violet-100 text-violet-700 hover:bg-violet-200 dark:bg-violet-900/40 dark:text-violet-300 dark:hover:bg-violet-900/60 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 text-slate-500 dark:text-stone-400">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-stone-800 text-slate-400 dark:text-stone-500 mb-3">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="text-sm font-medium">Belum ada data absensi untuk kegiatan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal Riwayat Absensi Per Santri           --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] overflow-y-auto"
         aria-labelledby="modal-riwayat-santri"
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
                 class="relative bg-white dark:bg-[#1E1C1A] rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col border border-gray-100 dark:border-stone-700/50 overflow-hidden z-10">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-stone-700/50 bg-gradient-to-r from-violet-50 to-indigo-50 dark:from-[#272422] dark:to-[#272422] flex-shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-slate-800 dark:text-stone-100 truncate" x-text="modalSantriNama"></h3>
                            <p class="text-xs text-slate-500 dark:text-stone-400">Riwayat Absensi — {{ $kegiatan->nama_kegiatan }} · {{ date('F Y', mktime(0, 0, 0, $bulan, 10, $tahun)) }}</p>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-stone-800 flex items-center justify-center transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-500 dark:text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body (scrollable) --}}
                <div class="flex-1 min-h-0 overflow-y-auto p-6">
                    {{-- Loading --}}
                    <div x-show="loading" class="flex flex-col items-center justify-center py-16">
                        <div class="w-10 h-10 border-3 border-violet-200 border-t-violet-500 rounded-full animate-spin mb-4" style="border-width: 3px;"></div>
                        <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Memuat riwayat absensi...</p>
                    </div>

                    {{-- Santri Info Card --}}
                    <div x-show="!loading && santriInfo" style="display: none;">
                        <div class="bg-slate-50 dark:bg-[#1A1817] rounded-xl p-3 mb-4 flex items-center gap-3 border border-slate-100 dark:border-stone-700/40">
                            <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 text-violet-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 dark:text-stone-100 truncate" x-text="santriInfo?.nama"></div>
                                <div class="text-[11px] text-slate-400 dark:text-stone-500 font-medium flex items-center gap-1.5">
                                    <span class="font-mono" x-text="santriInfo?.nis || '-'"></span>
                                    <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-stone-600"></span>
                                    <span x-text="(santriInfo?.daerah || '-') + ' / ' + (santriInfo?.kamar || '-')"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Riwayat --}}
                    <div x-show="!loading && riwayatData.length > 0" style="display: none;">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-[#1A1817] text-slate-600 dark:text-stone-300 text-[10px] uppercase tracking-wider font-bold sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 text-left border-b border-slate-200 dark:border-stone-700/50 w-10">No</th>
                                    <th class="py-3 px-4 text-left border-b border-slate-200 dark:border-stone-700/50">Tanggal</th>
                                    <th class="py-3 px-4 text-center border-b border-slate-200 dark:border-stone-700/50 w-24">Status</th>
                                    <th class="py-3 px-4 text-left border-b border-slate-200 dark:border-stone-700/50">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-stone-700/40 text-sm">
                                <template x-for="(row, idx) in riwayatData" :key="idx">
                                    <tr class="hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors">
                                        <td class="py-2.5 px-4 text-slate-400 dark:text-stone-500 text-xs" x-text="idx + 1"></td>
                                        <td class="py-2.5 px-4">
                                            <div class="font-semibold text-slate-800 dark:text-stone-100 text-[13px]" x-text="row.tanggal_label"></div>
                                            <div class="text-[10px] text-slate-400 dark:text-stone-500 font-medium" x-text="row.hari"></div>
                                        </td>
                                        <td class="py-2.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide"
                                                  :class="{
                                                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400': row.status === 'hadir',
                                                      'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400': row.status === 'izin',
                                                      'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400': row.status === 'sakit',
                                                      'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400': row.status === 'alpa',
                                                  }"
                                                  x-text="row.status_label">
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-slate-500 dark:text-stone-400 text-xs" x-text="row.keterangan || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Empty --}}
                    <div x-show="!loading && riwayatData.length === 0 && !errorMsg" class="text-center py-12" style="display: none;">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-stone-800 text-slate-400 dark:text-stone-500 mb-3">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-stone-400 font-medium">Belum ada riwayat absensi pada periode ini.</p>
                    </div>

                    {{-- Error --}}
                    <div x-show="errorMsg" class="text-center py-12" style="display: none;">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-400 mb-3">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <p class="text-sm text-rose-600 dark:text-rose-400 font-medium" x-text="errorMsg"></p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200 dark:border-stone-700/50 bg-slate-50 dark:bg-[#1A1817] flex-shrink-0">
                    <p class="text-xs text-slate-500 dark:text-stone-400">
                        <span x-text="riwayatData.length"></span> catatan kehadiran
                    </p>
                    <button @click="closeModal()" class="btn btn-outline text-sm py-1.5 px-4 rounded-lg">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function riwayatDrilldown() {
    return {
        showModal: false,
        loading: false,
        modalSantriNama: '',
        santriInfo: null,
        riwayatData: [],
        errorMsg: '',

        openModal(santriId, namaSantri) {
            this.showModal = true;
            this.loading = true;
            this.modalSantriNama = namaSantri;
            this.santriInfo = null;
            this.riwayatData = [];
            this.errorMsg = '';

            const url = new URL('{{ route("absensi.rekap.riwayat-santri") }}', window.location.origin);
            url.searchParams.set('santri_id', santriId);
            url.searchParams.set('kegiatan_id', '{{ $kegiatan->id }}');
            url.searchParams.set('bulan', '{{ $bulan }}');
            url.searchParams.set('tahun', '{{ $tahun }}');

            fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Gagal memuat data');
                return r.json();
            })
            .then(json => {
                this.santriInfo = json.santri || null;
                this.riwayatData = json.riwayat || [];
                this.loading = false;
            })
            .catch((err) => {
                this.errorMsg = err.message || 'Terjadi kesalahan saat memuat data.';
                this.loading = false;
            });
        },

        closeModal() {
            this.showModal = false;
            this.riwayatData = [];
            this.santriInfo = null;
            this.errorMsg = '';
        }
    };
}
</script>
@endpush
@endsection
