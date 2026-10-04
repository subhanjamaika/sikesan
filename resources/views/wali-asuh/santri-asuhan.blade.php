@extends('layouts.app')
@section('title', 'Daftar Santri Asuhan')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    
    {{-- Header Card dengan Pastel Gradient Terpadu --}}
    <div class="banner-header rounded-2xl p-6 sm:p-8 text-white shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold mb-1">Daftar Santri Asuhan Anda</h2>
            <p class="text-teal-100 text-sm">Monitor absensi kegiatan, absensi puasa, pelanggaran, dan penilaian keseharian santri.</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl text-right shrink-0 relative z-10">
            <div class="text-3xl font-bold text-white leading-none">{{ $santriAsuhan->count() }}</div>
            <div class="text-xs text-teal-200 mt-1">Total Santri Asuhan</div>
        </div>
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
    </div>

    {{-- Data Table Baris & Kolom dengan Live Search Input Text --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden">
        {{-- Filter & Search Bar --}}
        <div class="p-4 border-b border-violet-100/60 dark:border-stone-700/40 bg-gradient-to-r from-violet-50/50 to-teal-50/50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-stone-100">Tabel Data Santri Asuhan</h3>
                <p class="text-xs text-slate-500 dark:text-stone-400">Menampilkan santri yang berada di kamar asuhan Anda.</p>
            </div>
            
            {{-- Input Text Live Search --}}
            <div class="relative w-full sm:w-80">
                <input type="text" id="searchSantriTable" placeholder="Cari nama santri, NIS, atau kamar..." class="form-input text-xs pl-9 pr-4 py-2 w-full">
                <svg class="w-4 h-4 text-slate-400 dark:text-stone-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="data-table" id="tableSantriAsuhan">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Santri</th>
                        <th>Kamar / Unit</th>
                        <th class="text-center">Absensi Terakhir</th>
                        <th class="text-center">Status Puasa & Disiplin</th>
                        <th class="text-center">Nilai Rapor Terakhir</th>
                        <th class="text-right w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($santriAsuhan as $index => $santri)
                    @php
                        $santriAbsensi = $absensiKegiatan->where('santri_id', $santri->id)->first();
                        $santriPuasa = $absensiPuasa->where('santri_id', $santri->id)->first();
                        $santriLanggar = $pelanggaran->where('santri_id', $santri->id)->take(3);
                        $santriNilai = $penilaian->where('santri_id', $santri->id)->first();
                    @endphp
                    <tr class="row-santri hover:bg-violet-50/40 transition">
                        <td class="text-center font-medium text-slate-500 dark:text-stone-400">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-100 to-teal-100 text-teal-700 font-bold flex items-center justify-center text-sm border border-teal-200 shrink-0">
                                    {{ strtoupper(substr($santri->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-stone-100 santri-nama">{{ $santri->nama }}</div>
                                    <div class="text-xs text-slate-500 dark:text-stone-400 font-mono santri-nis">{{ $santri->nis_induk }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-blue text-xs santri-kamar">{{ $santri->unitOrganisasi->nama ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($santriAbsensi)
                                <span class="badge badge-{{ $santriAbsensi->status_badge_color }} text-xs">{{ $santriAbsensi->status_label }}</span>
                            @else
                                <span class="text-slate-400 dark:text-stone-500 text-xs">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center flex-wrap gap-1">
                                @if($santriPuasa)
                                    <span class="badge badge-green text-[10px]">Puasa Sunnah</span>
                                @endif
                                @if($santriLanggar->count() > 0)
                                    <span class="badge badge-red text-[10px]">{{ $santriLanggar->count() }} Langgar</span>
                                @endif
                                @if(!$santriPuasa && $santriLanggar->count() === 0)
                                    <span class="text-slate-400 dark:text-stone-500 text-xs">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($santriNilai)
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-stone-100 text-sm">{{ $santriNilai->nilai_akhir }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-stone-500 block">({{ $santriNilai->periode }})</span>
                                </div>
                            @else
                                <span class="text-slate-400 dark:text-stone-500 text-xs italic">Belum dinilai</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('absensi-kegiatan.create') }}" class="btn btn-outline btn-sm text-xs py-1 px-2.5 text-teal-700 border-teal-200 hover:bg-teal-50" title="Absen Kegiatan">
                                    Absen
                                </a>
                                <a href="{{ route('penilaian-keseharian.create', ['santri_id' => $santri->id]) }}" class="btn btn-primary btn-sm text-xs py-1 px-2.5" title="Input Nilai">
                                    Nilai
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-500 dark:text-stone-400">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div class="font-medium text-slate-700 dark:text-stone-200 text-sm">Belum Ada Santri Asuhan</div>
                            <div class="text-xs text-slate-400 dark:text-stone-500 mt-0.5">Anda belum memiliki santri di unit/kamar organisasi Anda.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Live Search Input Text pada Tabel Santri Asuhan
document.getElementById('searchSantriTable').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('.row-santri');

    rows.forEach(row => {
        const nama = row.querySelector('.santri-nama')?.textContent.toLowerCase() || '';
        const nis = row.querySelector('.santri-nis')?.textContent.toLowerCase() || '';
        const kamar = row.querySelector('.santri-kamar')?.textContent.toLowerCase() || '';

        if (nama.includes(filter) || nis.includes(filter) || kamar.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
