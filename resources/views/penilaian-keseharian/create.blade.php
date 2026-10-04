@extends('layouts.app')
@section('title', 'Input Penilaian Keseharian')

@section('header-actions')
    <a href="{{ route('penilaian-keseharian.index') }}" class="btn btn-outline">Kembali</a>
@endsection

@section('content')
<form method="POST" action="{{ route('penilaian-keseharian.store') }}" class="space-y-6">
    @csrf

    {{-- Info Utama --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6 sm:p-8">
        <h3 class="font-semibold text-lg text-slate-800 dark:text-stone-100 mb-6">Informasi Penilaian</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="form-group" x-data="{
                search: '{{ old('nama_santri') }}',
                santris: {{ $santriList->map(fn($s) => ['id' => $s->id, 'text' => $s->nis_induk . ' - ' . $s->nama])->toJson() }},
                get selectedId() {
                    let found = this.santris.find(s => s.text === this.search);
                    return found ? found.id : '';
                }
            }">
                <label class="form-label" for="nama_santri">Santri Asuhan <span class="text-red-500">*</span></label>
                <input type="hidden" name="santri_id" :value="selectedId">
                <input type="text" name="nama_santri" id="nama_santri" x-model="search" list="santri-list"
                       class="form-input w-full @error('nama_santri') border-red-500 @enderror @error('santri_id') border-red-500 @enderror" 
                       placeholder="Ketik/Pilih NIS atau Nama..." 
                       required autocomplete="off">
                <datalist id="santri-list">
                    <template x-for="s in santris" :key="s.id">
                        <option :value="s.text"></option>
                    </template>
                </datalist>
                @error('nama_santri') <div class="form-error">{{ $message }}</div> @enderror
                @error('santri_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="periode">Periode (Bulan/Tahun) <span class="text-red-500">*</span></label>
                <input type="month" name="periode" id="periode" class="form-input @error('periode') border-red-500 @enderror" value="{{ old('periode', date('Y-m')) }}" required>
                @error('periode') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="tanggal_penilaian">Tanggal Penilaian <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_penilaian" id="tanggal_penilaian" class="form-input @error('tanggal_penilaian') border-red-500 @enderror" value="{{ old('tanggal_penilaian', date('Y-m-d')) }}" required>
                @error('tanggal_penilaian') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Form Aspek Penilaian --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <div>
                <h3 class="font-semibold text-lg text-slate-800 dark:text-stone-100">Skor Aspek Penilaian</h3>
                <div class="text-xs text-slate-500 dark:text-stone-400 mt-1">
                    Gunakan tombol <kbd class="px-1.5 py-0.5 bg-slate-200 dark:bg-stone-700 rounded text-slate-800 dark:text-stone-200 font-mono">1</kbd> - <kbd class="px-1.5 py-0.5 bg-slate-200 dark:bg-stone-700 rounded text-slate-800 dark:text-stone-200 font-mono">4</kbd> di keyboard untuk mengisi cepat & lanjut ke baris berikutnya.
                </div>
            </div>
            
            <div class="relative w-full sm:w-64 flex-shrink-0">
                <input type="text" id="searchAspek" onkeyup="filterAspek()" placeholder="Cari aspek penilaian..." class="form-input text-sm pl-9 pr-4 py-2 rounded-xl border-slate-200 dark:border-stone-700/50 bg-slate-50 dark:bg-[#1A1817] w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Toolbar Isi Cepat --}}
        <div class="bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-900/30 rounded-xl p-3 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm font-semibold text-violet-700 dark:text-violet-400 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Tombol Isi Cepat (Otomatis)
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="document.querySelectorAll('.skor-select').forEach(s => s.value = '4')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">Semua Sangat Baik (4)</button>
                <button type="button" onclick="document.querySelectorAll('.skor-select').forEach(s => s.value = '3')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">Semua Baik (3)</button>
                <button type="button" onclick="document.querySelectorAll('.skor-select').forEach(s => s.value = '2')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors">Semua Cukup (2)</button>
                <button type="button" onclick="document.querySelectorAll('.skor-select').forEach(s => s.value = '')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-stone-700 dark:text-stone-300 transition-colors">Kosongkan</button>
            </div>
        </div>

        <div class="overflow-x-auto mb-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-200 dark:border-stone-700/50 text-left text-slate-500 dark:text-stone-400">
                        <th class="py-3 px-4 font-semibold w-16 text-center">No</th>
                        <th class="py-3 px-4 font-semibold">Aspek Penilaian</th>
                        <th class="py-3 px-4 font-semibold w-40 text-center">Skor (1-4)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-stone-700/40">
                    @foreach($aspekList as $index => $aspek)
                    <tr class="aspek-row hover:bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300 transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-400 dark:text-stone-500">{{ $aspek->nomor_urut }}</td>
                        <td class="py-3 px-4 aspek-name-col">
                            <div class="font-medium text-slate-800 dark:text-stone-100">{{ $aspek->nama_aspek }}</div>
                            <div class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">{{ $aspek->indikator }}</div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <select name="skor[{{ $aspek->id }}]" class="form-select text-center font-semibold skor-select" required>
                                <option value="">-</option>
                                <option value="4" {{ old("skor.{$aspek->id}") == '4' ? 'selected' : '' }}>4 - Sangat Baik</option>
                                <option value="3" {{ old("skor.{$aspek->id}") == '3' ? 'selected' : '' }}>3 - Baik</option>
                                <option value="2" {{ old("skor.{$aspek->id}") == '2' ? 'selected' : '' }}>2 - Cukup</option>
                                <option value="1" {{ old("skor.{$aspek->id}") == '1' ? 'selected' : '' }}>1 - Kurang</option>
                            </select>
                            @error("skor.{$aspek->id}") <div class="text-red-500 text-xs mt-1">Isi skor</div> @enderror
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @error('skor') <div class="form-error text-center mt-2">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Catatan Kualitatif --}}
    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6 sm:p-8">
        <h3 class="font-semibold text-lg text-slate-800 dark:text-stone-100 mb-6">Catatan Kualitatif</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="form-group">
                <label class="form-label font-semibold text-emerald-700" for="catatan_kelebihan">Kelebihan / Hal Positif <span class="text-red-500">*</span></label>
                <textarea name="catatan_kelebihan" id="catatan_kelebihan" rows="4" class="form-textarea border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/20" required placeholder="Contoh: Sangat rajin sholat jamaah, membantu teman yang sakit...">{{ old('catatan_kelebihan') }}</textarea>
                @error('catatan_kelebihan') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-amber-700" for="catatan_peningkatan">Area Perlu Peningkatan <span class="text-red-500">*</span></label>
                <textarea name="catatan_peningkatan" id="catatan_peningkatan" rows="4" class="form-textarea border-amber-200 focus:border-amber-500 focus:ring-amber-500/20" required placeholder="Contoh: Kurang disiplin waktu bangun pagi, sering terlambat halaqoh...">{{ old('catatan_peningkatan') }}</textarea>
                @error('catatan_peningkatan') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label font-semibold text-blue-700" for="rencana_tindak_lanjut">Rencana Tindak Lanjut <span class="text-red-500">*</span></label>
            <textarea name="rencana_tindak_lanjut" id="rencana_tindak_lanjut" rows="3" class="form-textarea border-blue-200 focus:border-violet-400 focus:ring-blue-500/20" required placeholder="Langkah pembinaan selanjutnya dari Wali Asuh...">{{ old('rencana_tindak_lanjut') }}</textarea>
            @error('rencana_tindak_lanjut') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="flex justify-end gap-4 pb-12">
        <a href="{{ route('penilaian-keseharian.index') }}" class="btn btn-outline py-3 px-6">Batal</a>
        <button type="submit" class="btn btn-primary py-3 px-8 text-base">
            Simpan Penilaian & Generate Rapor
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Fitur 1: Pencarian Aspek (Cari Spesifik)
    function filterAspek() {
        let input = document.getElementById('searchAspek').value.toLowerCase();
        let rows = document.querySelectorAll('.aspek-row');
        
        rows.forEach(row => {
            let text = row.querySelector('.aspek-name-col').textContent.toLowerCase();
            if (text.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Fitur 2: Keyboard Auto-Advance (1, 2, 3, 4)
    document.addEventListener('DOMContentLoaded', function() {
        const selects = Array.from(document.querySelectorAll('.skor-select'));
        
        selects.forEach((select, index) => {
            select.addEventListener('keydown', function(e) {
                const key = e.key;
                
                // Jika user menekan angka 1, 2, 3, atau 4
                if (['1', '2', '3', '4'].includes(key)) {
                    e.preventDefault(); // Mencegah perilaku default browser
                    
                    // Set nilai select
                    this.value = key;
                    
                    // Lanjut ke select berikutnya yang sedang tidak disembunyikan (visible)
                    let nextIndex = index + 1;
                    while (nextIndex < selects.length) {
                        // Cek apakah baris dari select berikutnya sedang disembunyikan oleh pencarian
                        if (selects[nextIndex].closest('tr').style.display !== 'none') {
                            selects[nextIndex].focus();
                            break;
                        }
                        nextIndex++;
                    }
                }
                
                // Navigasi panah atas/bawah
                if (key === 'ArrowDown') {
                    e.preventDefault();
                    let nextIndex = index + 1;
                    while (nextIndex < selects.length) {
                        if (selects[nextIndex].closest('tr').style.display !== 'none') {
                            selects[nextIndex].focus();
                            break;
                        }
                        nextIndex++;
                    }
                } else if (key === 'ArrowUp') {
                    e.preventDefault();
                    let prevIndex = index - 1;
                    while (prevIndex >= 0) {
                        if (selects[prevIndex].closest('tr').style.display !== 'none') {
                            selects[prevIndex].focus();
                            break;
                        }
                        prevIndex--;
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection


