@extends('layouts.app')
@section('title', 'Master Jenis Kegiatan Daerah')

@section('header-actions')
    @if(!auth()->user()->hasRole('dewan_pemangku'))
    <button type="button" 
            onclick="openCreateModal()" 
            class="btn btn-primary text-xs py-2 px-3 flex items-center gap-1.5 shadow-xs">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        + Tambah Kegiatan Baru
    </button>
    @endif
@endsection

@section('content')
<div class="flex flex-col h-full space-y-4"
     x-data="{ 
         openModal: false,
         isEdit: false,
         editUrl: '',
         formData: {
             nama_kegiatan: '',
             jam_pelaksanaan: '',
             urutan: '',
             aktif: true,
             program_id: ''
         }
     }">

    {{-- Info Card --}}
    <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-4 sm:p-5 flex-shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-teal-500 flex items-center justify-center text-white font-bold shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-stone-100">Master Jenis Kegiatan Daerah</h2>
                <p class="text-xs text-slate-500 dark:text-stone-400">Kelola kegiatan rutin maupun kegiatan tambahan dinamis untuk pengisian absensi santri.</p>
            </div>
        </div>
        <div class="text-xs text-slate-500 dark:text-stone-400 font-medium">
            Total Kegiatan: <strong class="text-slate-800 dark:text-stone-100 font-bold">{{ count($jenisKegiatanList) }}</strong>
        </div>
    </div>

    {{-- Data Table Jenis Kegiatan --}}
    <div class="flex-1 min-h-0 bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 flex flex-col overflow-hidden">
        <div class="flex-1 min-h-0 overflow-y-auto">
            <table class="data-table">
                <thead class="sticky top-0 z-10 bg-gradient-to-r from-violet-50 to-teal-50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 shadow-xs">
                    <tr>
                        <th class="w-16 text-center py-3">Urutan</th>
                        <th class="py-3">Nama Kegiatan</th>
                        <th class="w-44 py-3">Jam Pelaksanaan</th>
                        <th class="w-36 text-center py-3">Status</th>
                        <th class="w-48 text-center py-3">Riwayat Absensi</th>
                        @if(!auth()->user()->hasRole('dewan_pemangku'))
                        <th class="text-right w-44 py-3">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenisKegiatanList as $jk)
                        <tr class="hover:bg-violet-50/40 transition-colors">
                            <td class="text-center font-mono font-bold text-slate-500 dark:text-stone-400 text-xs py-3">
                                {{ $jk->urutan }}
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 border border-teal-100 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($jk->nama_kegiatan, 0, 1)) }}
                                    </div>
                                    <div class="font-bold text-slate-800 dark:text-stone-100 text-sm">
                                        {{ $jk->nama_kegiatan }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                @if($jk->jam_pelaksanaan)
                                    <span class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-violet-700 bg-violet-50 px-2.5 py-1 rounded-lg border border-violet-100 dark:border-stone-700/40">
                                        <svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ \Carbon\Carbon::parse($jk->jam_pelaksanaan)->format('H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-stone-500 italic">Fleksibel / Sesuai Jadwal</span>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                <div class="flex flex-col items-center space-y-1">
                                    @if($jk->aktif)
                                        <span class="badge badge-green w-max">Aktif</span>
                                    @else
                                        <span class="badge badge-gray w-max">Non-Aktif</span>
                                    @endif
                                    
                                    @if($jk->program_id)
                                        <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 w-max">{{ $jk->program_id }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-stone-300 bg-slate-100 px-2.5 py-1 rounded-full">
                                    {{ $jk->absensi_kegiatan_count }} Catatan
                                </span>
                            </td>
                            @if(!auth()->user()->hasRole('dewan_pemangku'))
                            <td class="text-right py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            @click="
                                                isEdit = true;
                                                editUrl = '{{ route('jenis-kegiatan.update', $jk) }}';
                                                formData = {
                                                    nama_kegiatan: '{{ addslashes($jk->nama_kegiatan) }}',
                                                    jam_pelaksanaan: '{{ $jk->jam_pelaksanaan ? \Carbon\Carbon::parse($jk->jam_pelaksanaan)->format('H:i') : '' }}',
                                                    urutan: '{{ $jk->urutan }}',
                                                    aktif: {{ $jk->aktif ? 'true' : 'false' }},
                                                    program_id: '{{ $jk->program_id ?? '' }}'
                                                };
                                                openModal = true;
                                            "
                                            class="btn btn-outline text-xs py-1.5 px-2.5 rounded-lg text-violet-700 hover:bg-violet-50 border-violet-200">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('jenis-kegiatan.destroy', $jk) }}" onsubmit="return confirm('Apakah Anda yakin ingin {{ $jk->absensi_kegiatan_count > 0 ? 'menonaktifkan' : 'menghapus' }} kegiatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline text-xs py-1.5 px-2.5 rounded-lg text-rose-600 hover:bg-rose-50 border-rose-200">
                                            {{ $jk->absensi_kegiatan_count > 0 ? 'Nonaktifkan' : 'Hapus' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('dewan_pemangku') ? 5 : 6 }}" class="text-center py-12">
                                <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h4 class="text-slate-700 dark:text-stone-200 font-bold text-sm mb-0.5">Belum Ada Jenis Kegiatan</h4>
                                <p class="text-slate-500 dark:text-stone-400 text-xs max-w-sm mx-auto">
                                    Klik "+ Tambah Kegiatan Baru" untuk menambahkan jenis kegiatan absensi.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(!auth()->user()->hasRole('dewan_pemangku'))
    {{-- MODAL POP-UP TAMBAH / EDIT JENIS KEGIATAN --}}
    <div x-show="openModal"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div x-show="openModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
             @click="openModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#272422] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-violet-100 dark:border-stone-700/40">
                
                <form :action="isEdit ? editUrl : '{{ route('jenis-kegiatan.store') }}'" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="bg-gradient-to-r from-violet-50 to-teal-50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 px-6 py-4 border-b border-violet-100/60 dark:border-stone-700/40 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-stone-100" x-text="isEdit ? 'Edit Jenis Kegiatan' : 'Tambah Jenis Kegiatan Baru'"></h3>
                        <button type="button" @click="openModal = false" class="text-slate-400 dark:text-stone-500 hover:text-slate-600 dark:text-stone-300 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label for="nama_kegiatan" class="form-label text-xs">Nama Kegiatan <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   id="nama_kegiatan"
                                   name="nama_kegiatan"
                                   x-model="formData.nama_kegiatan"
                                   placeholder="Contoh: Shalat Subuh Jamaah, Muhadharah, dll"
                                   class="form-input text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="jam_pelaksanaan" class="form-label text-xs">Jam Pelaksanaan (Opsional)</label>
                            <input type="time"
                                   id="jam_pelaksanaan"
                                   name="jam_pelaksanaan"
                                   x-model="formData.jam_pelaksanaan"
                                   class="form-input text-sm">
                            <p class="text-[11px] text-slate-400 dark:text-stone-500 mt-1">Kosongkan jika waktu pelaksanaan fleksibel.</p>
                        </div>

                        <div>
                            <label for="urutan" class="form-label text-xs">Urutan Tampilan</label>
                            <input type="number"
                                   id="urutan"
                                   name="urutan"
                                   x-model="formData.urutan"
                                   placeholder="Otomatis urutan terakhir jika kosong"
                                   class="form-input text-sm">
                        </div>

                        <div>
                            <label for="program_id" class="form-label text-xs">Target Program (Opsional)</label>
                            <input type="text"
                                   id="program_id"
                                   name="program_id"
                                   x-model="formData.program_id"
                                   @input="formData.program_id = $event.target.value.toLowerCase().replace(/[^a-z0-9_']+/g, '_')"
                                   placeholder="Contoh: wustho_tahsin, i'dadiyah"
                                   class="form-input text-sm">
                            <p class="text-[11px] text-slate-400 dark:text-stone-500 mt-1">Biarkan kosong untuk 'Semua Program'. Input otomatis menjadi huruf kecil dan bersambung underscore.</p>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox"
                                   id="aktif"
                                   name="aktif"
                                   value="1"
                                   x-model="formData.aktif"
                                   class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <label for="aktif" class="text-xs font-semibold text-slate-700 dark:text-stone-200 cursor-pointer">Status Aktif (Dapat digunakan untuk absensi)</label>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300/80 px-6 py-3.5 border-t border-slate-100 dark:border-stone-700/50 flex items-center justify-end gap-2">
                        <button type="button" @click="openModal = false" class="btn btn-outline text-xs py-2 px-4 rounded-xl">Batal</button>
                        <button type="submit" class="btn btn-primary text-xs py-2 px-4 rounded-xl" x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Kegiatan'"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

</div>

@if(!auth()->user()->hasRole('dewan_pemangku'))
<script>
    function openCreateModal() {
        const alpineData = Alpine.$data(document.querySelector('[x-data]'));
        alpineData.isEdit = false;
        alpineData.editUrl = '';
        alpineData.formData = {
            nama_kegiatan: '',
            jam_pelaksanaan: '',
            urutan: '',
            aktif: true,
            program_id: ''
        };
        alpineData.openModal = true;
    }
</script>
@endif
@endsection
