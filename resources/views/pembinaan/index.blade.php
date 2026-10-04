@extends('layouts.app')
@section('title', 'Riwayat Pembinaan')

@section('content')
<div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden">
    {{-- Filter Bar --}}
    <div class="p-4 border-b border-violet-100/60 dark:border-stone-700/40 bg-gradient-to-r from-violet-50/50 to-teal-50/50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40">
        <form method="GET" action="{{ route('pembinaan.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[300px]" x-data="{
                search: '{{ request('santri_nama_nis') }}',
                santris: {{ \App\Models\Santri::orderBy('nama')->get()->map(fn($s) => ['id' => $s->id, 'text' => $s->nis_induk . ' - ' . $s->nama])->toJson() }},
                get selectedId() {
                    let found = this.santris.find(s => s.text === this.search);
                    return found ? found.id : '';
                }
            }">
                <label class="form-label text-xs">Pilih Santri</label>
                <input type="hidden" name="santri_id" :value="selectedId">
                <input type="text" name="santri_nama_nis" x-model="search" list="santri-list" class="form-input" placeholder="Ketik/Pilih NIS atau Nama..." autocomplete="off">
                <datalist id="santri-list">
                    <template x-for="s in santris" :key="s.id">
                        <option :value="s.text"></option>
                    </template>
                </datalist>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->filled('santri_id'))
                    <a href="{{ route('pembinaan.index') }}" class="btn btn-outline ml-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Santri</th>
                    <th>Jenis Pembinaan & Hasil</th>
                    <th>Pembina</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembinaan as $bina)
                <tr>
                    <td class="whitespace-nowrap">{{ $bina->tanggal->format('d M Y') }}</td>
                    <td>
                        <div class="font-medium text-slate-800 dark:text-stone-100">{{ $bina->pelanggaran?->santri?->nama ?? 'Data Santri Dihapus' }}</div>
                        <div class="text-xs text-slate-500 dark:text-stone-400">{{ $bina->pelanggaran?->santri?->nis_induk ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="font-semibold text-slate-800 dark:text-stone-100">{{ $bina->jenis_pembinaan }}</div>
                        @if($bina->hasil)
                            <div class="text-sm text-slate-600 dark:text-stone-300 mt-1 line-clamp-2" title="{{ $bina->hasil }}">{{ $bina->hasil }}</div>
                        @else
                            <div class="text-xs text-slate-400 dark:text-stone-500 italic mt-1">Tidak ada catatan hasil</div>
                        @endif
                    </td>
                    <td>{{ $bina->pembina?->nama ?? 'Tidak Ditemukan' }}</td>
                    <td class="text-right">
                        @if($bina->pelanggaran_id)
                            <a href="{{ route('pelanggaran.show', $bina->pelanggaran_id) }}" class="text-violet-600 hover:text-violet-800 font-medium text-sm inline-block py-1">Lihat Pelanggaran</a>
                        @else
                            <span class="text-slate-400 text-sm">Tidak Ada</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-500 dark:text-stone-400">Belum ada riwayat pembinaan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-violet-100/60 dark:border-stone-700/40">
        {{ $pembinaan->links() }}
    </div>
</div>
@endsection
