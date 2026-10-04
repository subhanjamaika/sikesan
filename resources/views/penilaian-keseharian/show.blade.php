@extends('layouts.app')
@section('title', 'Rapor Penilaian Keseharian')

@section('header-actions')
    <a href="{{ route('penilaian-keseharian.index') }}" class="btn btn-outline">Kembali</a>
    <button onclick="window.print()" class="btn btn-secondary ml-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Cetak Print
    </button>
    <a href="{{ route('penilaian-keseharian.export-pdf', $penilaian) }}" class="btn btn-primary ml-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export PDF
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6 print:m-0 print:space-y-4 print:w-full print:max-w-none">
    
    {{-- Header Rapor --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-8 print:shadow-none print:border-b-2 print:border-slate-800 print:rounded-none">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-stone-100 uppercase tracking-wider">Laporan Penilaian Keseharian Santri</h1>
            <p class="text-slate-500 dark:text-stone-400 mt-1">SIKESAN Terpadu</p>
        </div>
        
        <div class="grid grid-cols-2 gap-x-12 gap-y-4 text-sm">
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">Nama Santri</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ $penilaian->santri->nama }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">Periode</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ \Carbon\Carbon::parse($penilaian->periode)->translatedFormat('F Y') }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">NIS Induk</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ $penilaian->santri->nis_induk }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">Tanggal Penilaian</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ $penilaian->tanggal_penilaian->translatedFormat('d F Y') }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">Unit Organisasi</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ $penilaian->santri->unitOrganisasi->nama }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-slate-500 dark:text-stone-400 font-medium">Wali Asuh</span>
                <span class="font-semibold text-slate-800 dark:text-stone-100">: {{ $penilaian->waliAsuh->nama }}</span>
            </div>
        </div>
    </div>

    {{-- Detail Nilai --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden print:shadow-none print:border print:border-slate-800 print:rounded-none">
        <div class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 px-6 py-4 border-b border-slate-200 dark:border-stone-700/50 print:bg-slate-100 print:border-slate-800">
            <h2 class="font-bold text-slate-800 dark:text-stone-100">A. Skor Aspek Penilaian</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 border-b border-slate-200 dark:border-stone-700/50 print:bg-slate-100 print:border-slate-800 text-left text-slate-600 dark:text-stone-300">
                    <th class="py-3 px-6 font-semibold w-16 text-center print:border-r print:border-slate-800">No</th>
                    <th class="py-3 px-6 font-semibold print:border-r print:border-slate-800">Aspek yang Dinilai</th>
                    <th class="py-3 px-6 font-semibold w-32 text-center">Skor (1-4)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-stone-700/40 print:divide-slate-800">
                @foreach($penilaian->detail as $item)
                <tr>
                    <td class="py-3 px-6 text-center font-medium text-slate-500 dark:text-stone-400 print:border-r print:border-slate-800">{{ $item->aspek->nomor_urut }}</td>
                    <td class="py-3 px-6 print:border-r print:border-slate-800">
                        <div class="font-medium text-slate-800 dark:text-stone-100">{{ $item->aspek->nama_aspek }}</div>
                        <div class="text-xs text-slate-500 dark:text-stone-400 mt-1 print:text-slate-600 dark:text-stone-300">{{ $item->aspek->deskripsi }}</div>
                    </td>
                    <td class="py-3 px-6 text-center font-bold text-slate-800 dark:text-stone-100 text-base">
                        {{ $item->skor }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 border-t-2 border-slate-200 dark:border-stone-700/50 print:border-t-4 print:border-slate-800 print:bg-slate-100">
                <tr>
                    <td colspan="2" class="py-4 px-6 text-right font-bold text-slate-800 dark:text-stone-100 print:border-r print:border-slate-800">Nilai Akhir (Skala 100)</td>
                    <td class="py-4 px-6 text-center text-xl font-bold text-blue-700">{{ $penilaian->nilai_akhir }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="py-3 px-6 text-right font-bold text-slate-800 dark:text-stone-100 print:border-r print:border-slate-800">Kategori Predikat</td>
                    <td class="py-3 px-6 text-center">
                        <span class="badge badge-{{ $penilaian->badge_color }} text-sm px-3 py-1 print:border print:border-slate-800">{{ $penilaian->kategori }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Catatan Kualitatif --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden print:shadow-none print:border print:border-slate-800 print:rounded-none">
        <div class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 px-6 py-4 border-b border-slate-200 dark:border-stone-700/50 print:bg-slate-100 print:border-slate-800">
            <h2 class="font-bold text-slate-800 dark:text-stone-100">B. Catatan Perkembangan</h2>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <h3 class="font-semibold text-emerald-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    Kelebihan & Hal Positif
                </h3>
                <div class="bg-emerald-50 text-emerald-900 p-4 rounded-lg text-sm whitespace-pre-wrap print:bg-transparent print:border print:border-emerald-800">{{ $penilaian->catatan_kelebihan }}</div>
            </div>
            
            <div>
                <h3 class="font-semibold text-amber-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Area Perlu Peningkatan
                </h3>
                <div class="bg-amber-50 text-amber-900 p-4 rounded-lg text-sm whitespace-pre-wrap print:bg-transparent print:border print:border-amber-800">{{ $penilaian->catatan_peningkatan }}</div>
            </div>

            <div>
                <h3 class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Rencana Tindak Lanjut Wali Asuh
                </h3>
                <div class="bg-blue-50 text-blue-900 p-4 rounded-lg text-sm whitespace-pre-wrap print:bg-transparent print:border print:border-blue-800">{{ $penilaian->rencana_tindak_lanjut }}</div>
            </div>

            @if($penilaian->catatan_pemangku)
            <div>
                <h3 class="font-semibold text-violet-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Catatan Khusus Dewan Pemangku
                </h3>
                <div class="bg-violet-50 text-violet-900 p-4 rounded-lg text-sm whitespace-pre-wrap print:bg-transparent print:border print:border-violet-800">{{ $penilaian->catatan_pemangku }}</div>
            </div>
            @endif
        </div>
    </div>
    
    @if(auth()->check() && auth()->user()->hasRole('dewan_pemangku'))
    {{-- Form Tambah Catatan Dewan Pemangku --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden print:hidden">
        <div class="bg-violet-50 dark:bg-violet-900/20 px-6 py-4 border-b border-violet-100 dark:border-stone-700/50 flex items-center justify-between">
            <h2 class="font-bold text-violet-800 dark:text-violet-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Catatan Evaluasi Dewan Pemangku
            </h2>
        </div>
        <div class="p-6">
            <form action="{{ route('penilaian-keseharian.catatan-pemangku', $penilaian) }}" method="POST">
                @csrf
                <div class="form-group mb-4">
                    <label class="form-label text-sm font-semibold text-slate-700 dark:text-stone-300 mb-2">Tambahkan Catatan Khusus untuk Rapor Ini</label>
                    <textarea name="catatan_pemangku" rows="3" class="form-textarea w-full bg-slate-50 dark:bg-[#1A1817] border-slate-200 dark:border-stone-700/50 rounded-xl text-sm" placeholder="Ketik catatan atau evaluasi Anda di sini...">{{ old('catatan_pemangku', $penilaian->catatan_pemangku) }}</textarea>
                    <p class="text-xs text-slate-500 mt-2">Catatan ini akan disimpan secara terpisah dan dapat dilihat oleh Wali Santri serta Pengurus terkait.</p>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary text-sm px-6 py-2 rounded-xl shadow-md">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    {{-- Signature (hanya tampil saat diprint) --}}
    <div class="hidden print:flex justify-between mt-12 pt-8 w-full text-center">
        <div class="w-48">
            <p class="mb-16">Mengetahui,<br>Kepala Bagian Daerah</p>
            <p class="font-bold border-b border-slate-800 mb-1">(...................................)</p>
        </div>
        <div class="w-48">
            <p class="mb-16"><br>Wali Asuh</p>
            <p class="font-bold border-b border-slate-800 mb-1">{{ $penilaian->waliAsuh->nama }}</p>
        </div>
    </div>

</div>

<style>
    @media print {
        body { background: white !important; }
        .sidebar, header, .btn { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
        
        /* Fix for cut-off pages: disable h-screen and overflow-hidden during print */
        body, html, .flex, .main-content, main { 
            height: auto !important; 
            overflow: visible !important; 
        }
    }
</style>
@endsection
