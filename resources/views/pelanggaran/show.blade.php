@extends('layouts.app')
@section('title', 'Detail Pelanggaran')

@section('header-actions')
    <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline">Kembali</a>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Card Kiri: Info Pelanggaran --}}
    <div class="md:col-span-1 space-y-6">
        <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-stone-100 mb-6">Info Pelanggaran</h2>
            
            <div class="space-y-4">
                <div>
                    <div class="text-sm font-medium text-slate-500 dark:text-stone-400 mb-1">Santri</div>
                    <div class="font-semibold text-slate-800 dark:text-stone-100">{{ $pelanggaran->santri->nama }}</div>
                    <div class="text-xs text-slate-500 dark:text-stone-400">{{ $pelanggaran->santri->nis_induk }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500 dark:text-stone-400 mb-1">Kategori & Tingkat</div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-{{ $pelanggaran->kategori == 'umum' ? 'gray' : 'yellow' }}">{{ ucfirst($pelanggaran->kategori) }}</span>
                        <span class="badge badge-outline">{{ ucfirst($pelanggaran->tingkat) }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500 dark:text-stone-400 mb-1">Tanggal Kejadian</div>
                    <div class="text-slate-800 dark:text-stone-100 font-medium">{{ $pelanggaran->tanggal->format('d F Y') }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500 dark:text-stone-400 mb-1">Dicatat Oleh</div>
                    <div class="text-sm text-slate-700 dark:text-stone-200 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-500 dark:text-stone-400">
                            {{ substr($pelanggaran->pencatat->nama, 0, 1) }}
                        </div>
                        {{ $pelanggaran->pencatat->nama }}
                    </div>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500 dark:text-stone-400 mb-1">Deskripsi</div>
                    <div class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 rounded-lg p-3 text-sm text-slate-700 dark:text-stone-200 whitespace-pre-wrap">{{ $pelanggaran->deskripsi }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Kanan: Riwayat Pembinaan --}}
    <div class="md:col-span-2 space-y-6">
        <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-800 dark:text-stone-100">Riwayat Pembinaan</h2>
                <span class="badge badge-{{ $pelanggaran->pembinaan->count() > 0 ? 'green' : 'red' }}">{{ $pelanggaran->pembinaan->count() }} Kali Dibina</span>
            </div>

            @if($pelanggaran->pembinaan->count() > 0)
                <div class="space-y-4">
                    @foreach($pelanggaran->pembinaan as $bina)
                        <div class="border-l-4 border-violet-400 pl-4 py-2 relative">
                            <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[1.1rem] top-4 border-4 border-white"></div>
                            <div class="text-sm text-slate-500 dark:text-stone-400 mb-1">{{ $bina->tanggal->format('d M Y') }} • Dibina oleh <span class="font-semibold text-slate-700 dark:text-stone-200">{{ $bina->pembina->nama }}</span></div>
                            <div class="font-medium text-slate-800 dark:text-stone-100 text-lg mb-2">{{ $bina->jenis_pembinaan }}</div>
                            @if($bina->hasil)
                                <div class="bg-violet-50 text-violet-800 p-3 rounded-lg text-sm whitespace-pre-wrap"><strong>Hasil:</strong> {{ $bina->hasil }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 rounded-xl border border-dashed border-slate-300">
                    <p class="text-slate-500 dark:text-stone-400">Belum ada pembinaan untuk pelanggaran ini.</p>
                </div>
            @endif
        </div>

        {{-- Form Tambah Pembinaan --}}
        @if(auth()->check() && !auth()->user()->hasRole('dewan_pemangku'))
        @can('addPembinaan', $pelanggaran)
        <div class="bg-gradient-to-r from-slate-50 to-white rounded-2xl shadow-sm border border-slate-200 dark:border-stone-700/50 p-6">
            <h3 class="font-semibold text-slate-800 dark:text-stone-100 mb-4">Tambah Pembinaan Baru</h3>
            <form action="{{ route('pembinaan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pelanggaran_id" value="{{ $pelanggaran->id }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="jenis_pembinaan">Jenis Pembinaan <span class="text-red-500">*</span></label>
                        <input type="text" name="jenis_pembinaan" id="jenis_pembinaan" class="form-input @error('jenis_pembinaan') border-red-500 @enderror" value="{{ old('jenis_pembinaan') }}" required placeholder="Contoh: Teguran lisan, Hukuman fisik ringan...">
                        @error('jenis_pembinaan') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tanggal">Tanggal Pembinaan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" class="form-input @error('tanggal') border-red-500 @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        @error('tanggal') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="hasil">Hasil Pembinaan <span class="text-blue-500 text-xs font-normal">(Disarankan diisi - Tampil di Sistem 1)</span></label>
                    <textarea name="hasil" id="hasil" rows="3" class="form-textarea @error('hasil') border-red-500 @enderror" placeholder="Catat respon atau komitmen santri setelah dibina...">{{ old('hasil') }}</textarea>
                    @error('hasil') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Simpan Pembinaan
                    </button>
                </div>
            </form>
        </div>
        @endcan
        @endif
    </div>
</div>
@endsection
