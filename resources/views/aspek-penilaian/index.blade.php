@extends('layouts.app')
@section('title', 'Master Aspek Penilaian')

@section('content')

    <div class="flex flex-col h-full space-y-4"
         x-data="{ 
            showModal: false, 
            editMode: false, 
            currentId: '', 
            form: {
                nama_aspek: '',
                indikator: '',
                nomor_urut: '',
                unit_organisasi_id: ''
            }
        }"
        @edit-aspek.window="
            editMode = true;
            currentId = $event.detail.id;
            form.nama_aspek = $event.detail.name;
            form.indikator = $event.detail.indikator;
            form.nomor_urut = $event.detail.urutan;
            form.unit_organisasi_id = $event.detail.unit_id;
            showModal = true;
        ">
        <!-- Header Actions -->
        <div class="bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-4 sm:p-5 flex-shrink-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-stone-100">Daftar Aspek Penilaian</h3>
                    <p class="text-sm text-slate-500 dark:text-stone-400">Kelola master parameter nilai keseharian santri.</p>
                </div>
                @if(!auth()->user()->hasRole('dewan_pemangku'))
                <div>
                    <button @click="showModal = true; editMode = false; currentId = ''; form = {nama_aspek: '', indikator: '', nomor_urut: '', unit_organisasi_id: ''};" 
                            class="btn btn-primary text-sm py-2 px-4 rounded-xl flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Kriteria Penilaian
                    </button>
                </div>
                @endif
            </div>

            <!-- Pesan Sukses -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="mt-4 mb-2 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="mt-4 mb-2 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-rose-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <p class="text-sm text-rose-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 mb-2 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <div class="flex items-center mb-1">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm text-red-700 font-medium">Terdapat kesalahan:</p>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 ml-7">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Filter Khusus Admin/Wilayah/Dewan Pemangku -->
        @if(auth()->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']))
        <div x-data="{ showFilter: false }" class="mb-2">
            <button type="button" @click="showFilter = !showFilter" class="btn btn-outline text-sm py-1.5 px-3 rounded-lg flex items-center gap-2 mb-2 bg-white/80 dark:bg-[#272422]/80 backdrop-blur-sm border border-violet-100 dark:border-stone-700/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Data
            </button>
            <div x-show="showFilter" x-transition class="bg-white/80 dark:bg-[#272422]/80 backdrop-blur-sm rounded-xl p-4 border border-violet-100 dark:border-stone-700/40 shadow-sm" style="display: none;">
                <form action="{{ route('aspek-penilaian.index') }}" method="GET" class="flex items-end gap-4">
                    <div class="w-64">
                        <label class="form-label text-xs mb-1 text-slate-600 dark:text-stone-300">Pilih Daerah</label>
                        <select name="daerah_id" class="form-select w-full text-sm bg-slate-50 dark:bg-[#1A1817] border-slate-200 dark:border-stone-700/50 rounded-xl" onchange="this.form.submit()">
                            <option value="">Semua Daerah & Global</option>
                            @foreach($daerahs as $d)
                                <option value="{{ $d->id }}" {{ request('daerah_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Table List -->
        <div class="flex-1 min-h-0 bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40 backdrop-blur-md rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto">
                <table class="data-table">
                    <thead class="sticky top-0 z-10 bg-gradient-to-r from-violet-50 to-teal-50 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 shadow-xs">
                        <tr>
                            <th class="w-16 text-center">Urutan</th>
                            <th>Aspek Penilaian</th>
                            <th>Indikator Singkat</th>
                            <th>Lingkup</th>
                            @if(!auth()->user()->hasRole('dewan_pemangku'))
                            <th class="text-right w-32">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aspekList as $aspek)
                            <tr class="hover:bg-violet-50/40 transition-colors">
                                <td class="text-center font-medium">{{ $aspek->nomor_urut }}</td>
                                <td class="font-bold text-slate-800 dark:text-stone-100">
                                    {{ $aspek->nama_aspek }}
                                </td>
                                <td class="text-slate-600 dark:text-stone-300 text-sm">
                                    {{ Str::limit($aspek->indikator, 60) }}
                                </td>
                                <td>
                                    @if(is_null($aspek->unit_organisasi_id))
                                        <span class="badge badge-purple w-max">Global</span>
                                    @else
                                        <span class="badge badge-blue w-max">{{ $aspek->unitOrganisasi->nama ?? 'Daerah Lokal' }}</span>
                                    @endif
                                </td>
                                @if(!auth()->user()->hasRole('dewan_pemangku'))
                                <td class="text-right">
                                    @if($aspek->unit_organisasi_id == auth()->user()->unit_organisasi_id || auth()->user()->hasRole('admin'))
                                        <div class="flex items-center justify-end space-x-3">
                                            <button @click="$dispatch('edit-aspek', { 
                                                id: '{{ $aspek->id }}', 
                                                name: '{{ addslashes($aspek->nama_aspek) }}',
                                                indikator: '{{ addslashes($aspek->indikator) }}',
                                                urutan: '{{ $aspek->nomor_urut }}',
                                                unit_id: '{{ $aspek->unit_organisasi_id ?? '' }}'
                                            })" class="text-violet-600 hover:text-violet-800 transition-colors" title="Edit">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('aspek-penilaian.destroy', $aspek->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Aspek Penilaian ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors" title="Hapus">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-stone-500 text-xs italic">Akses Ditolak</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole('dewan_pemangku') ? 4 : 5 }}" class="text-center py-12 text-slate-500 dark:text-stone-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-slate-300 dark:text-stone-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-800 dark:text-stone-200">Belum ada Kriteria Penilaian</p>
                                        <p class="text-sm mt-1">Silakan tambahkan parameter penilaian baru untuk daerah Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(!auth()->user()->hasRole('dewan_pemangku'))
        <!-- Modal Tambah/Edit -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                     @click="showModal = false" 
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-[#1E1C1A] rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100 dark:border-stone-700/50">
                    <form :action="editMode ? '{{ route('aspek-penilaian.index') }}/' + currentId : '{{ route('aspek-penilaian.store') }}'" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">
                        
                        <div class="bg-white dark:bg-[#1E1C1A] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-violet-100 dark:bg-violet-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-semibold text-slate-800 dark:text-stone-100" id="modal-title" x-text="editMode ? 'Edit Aspek Penilaian' : 'Tambah Kriteria Penilaian'"></h3>
                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <label for="nama_aspek" class="form-label mb-1">Nama Aspek Penilaian</label>
                                            <input type="text" name="nama_aspek" id="nama_aspek" x-model="form.nama_aspek" required
                                                class="form-input w-full"
                                                placeholder="Contoh: Makharijul Huruf">
                                        </div>
                                        
                                        <div>
                                            <label for="indikator" class="form-label mb-1">Indikator Penilaian Singkat</label>
                                            <textarea name="indikator" id="indikator" x-model="form.indikator" required
                                                class="form-input w-full h-20"
                                                placeholder="Contoh: Kelancaran membaca sesuai tajwid..."></textarea>
                                        </div>
                                        
                                        <div>
                                            <label for="nomor_urut" class="form-label mb-1">Urutan (Opsional)</label>
                                            <input type="number" name="nomor_urut" id="nomor_urut" x-model="form.nomor_urut"
                                                class="form-input w-full"
                                                placeholder="Otomatis diletakkan di akhir">
                                        </div>
                                        
                                        @if(auth()->user()->hasAnyRole(['admin', 'pengurus_wilayah']))
                                        <div>
                                            <label for="unit_organisasi_id" class="form-label mb-1">Lingkup Penilaian</label>
                                            <select name="unit_organisasi_id" id="unit_organisasi_id" x-model="form.unit_organisasi_id" class="form-select w-full">
                                                <option value="">Global (Berlaku untuk semua daerah)</option>
                                                @foreach($daerahs as $d)
                                                    <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-[#1A1817] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-xl border-t border-slate-200 dark:border-stone-700/50">
                            <button type="submit" class="btn btn-primary w-full sm:ml-3 sm:w-auto text-sm py-2 px-4">
                                Simpan
                            </button>
                            <button type="button" @click="showModal = false" class="btn btn-outline mt-3 w-full sm:mt-0 sm:ml-3 sm:w-auto text-sm py-2 px-4">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
