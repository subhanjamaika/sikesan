@extends('layouts.app')
@section('title', 'Riwayat Anak')

@section('content')
<div class="space-y-6">

    <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-slate-800 dark:text-stone-100 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Anak (Santri) Anda
        </h2>
        
        @if($santriWali->count() > 0)
            <div class="flex flex-wrap gap-4 mb-8">
                @foreach($santriWali as $anak)
                    <div class="bg-gradient-to-r from-violet-50/60 to-teal-50/60 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40 border border-violet-200/40 rounded-xl p-4 flex items-center gap-4 min-w-[250px]">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-100 to-violet-100 flex items-center justify-center text-violet-700 font-bold text-lg">
                            {{ substr($anak->nama, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-stone-100">{{ $anak->nama }}</div>
                            <div class="text-xs text-slate-500 dark:text-stone-400">{{ $anak->nis_induk }} • {{ $anak->unitOrganisasi->nama }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-800 mb-8 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-sm">
                    <strong>Email tidak terhubung dengan data Santri.</strong><br>
                    Pastikan email akun Anda ({{ Auth::user()->email }}) sama dengan kontak wali santri yang terdaftar pada profil anak Anda di database. Silakan hubungi admin jika butuh penyesuaian.
                </div>
            </div>
        @endif

        {{-- Tabs --}}
        <div x-data="{ tab: 'kehadiran' }">
            <div class="border-b border-violet-100/60 dark:border-stone-700/40 mb-6 flex flex-wrap gap-4">
                <button @click="tab = 'kehadiran'" :class="{'border-violet-500 text-violet-700 font-semibold': tab === 'kehadiran', 'border-transparent text-slate-500 dark:text-stone-400 hover:text-slate-700 dark:text-stone-200 hover:border-slate-300': tab !== 'kehadiran'}" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                    Riwayat Kehadiran
                </button>
                <button @click="tab = 'rapor'" :class="{'border-violet-500 text-violet-700 font-semibold': tab === 'rapor', 'border-transparent text-slate-500 dark:text-stone-400 hover:text-slate-700 dark:text-stone-200 hover:border-slate-300': tab !== 'rapor'}" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                    Rapor Keseharian
                </button>
                <button @click="tab = 'pelanggaran'" :class="{'border-violet-500 text-violet-700 font-semibold': tab === 'pelanggaran', 'border-transparent text-slate-500 dark:text-stone-400 hover:text-slate-700 dark:text-stone-200 hover:border-slate-300': tab !== 'pelanggaran'}" class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                    Riwayat Catatan Disiplin
                </button>
            </div>

            {{-- Tab Kehadiran --}}
            <div x-show="tab === 'kehadiran'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($riwayatKehadiran as $rekap)
                        <!-- Card Folder Kegiatan -->
                        <div x-data="{ openDetail: false }" class="bg-white dark:bg-[#272422] rounded-2xl shadow-sm border border-slate-200 dark:border-stone-700/50 p-5 hover:shadow-md transition-shadow cursor-pointer relative" @click="openDetail = true">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 text-violet-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 dark:text-stone-100">{{ $rekap->nama_kegiatan }}</h3>
                                        <p class="text-xs text-slate-500 dark:text-stone-400">{{ $rekap->total }} Total Pertemuan</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 rounded-md text-xs font-bold">{{ $rekap->persentase }}%</span>
                            </div>

                            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                <div class="bg-emerald-50 dark:bg-emerald-900/10 p-2 rounded-lg">
                                    <div class="font-bold text-emerald-600">{{ $rekap->hadir }}</div>
                                    <div class="text-[10px] text-emerald-600/70 font-medium uppercase mt-0.5">Hadir</div>
                                </div>
                                <div class="bg-amber-50 dark:bg-amber-900/10 p-2 rounded-lg">
                                    <div class="font-bold text-amber-600">{{ $rekap->izin }}</div>
                                    <div class="text-[10px] text-amber-600/70 font-medium uppercase mt-0.5">Izin</div>
                                </div>
                                <div class="bg-blue-50 dark:bg-blue-900/10 p-2 rounded-lg">
                                    <div class="font-bold text-blue-600">{{ $rekap->sakit }}</div>
                                    <div class="text-[10px] text-blue-600/70 font-medium uppercase mt-0.5">Sakit</div>
                                </div>
                                <div class="bg-rose-50 dark:bg-rose-900/10 p-2 rounded-lg">
                                    <div class="font-bold text-rose-600">{{ $rekap->alpa }}</div>
                                    <div class="text-[10px] text-rose-600/70 font-medium uppercase mt-0.5">Alpa</div>
                                </div>
                            </div>

                            <!-- Modal Detail Alpine -->
                            <template x-teleport="body">
                                <div x-show="openDetail" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden" @click.stop>
                                    <div class="absolute inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm" @click="openDetail = false" x-transition.opacity></div>
                                    
                                    <div class="relative w-full max-w-2xl max-h-[85vh] bg-white dark:bg-[#1A1817] rounded-2xl shadow-xl flex flex-col m-4" x-transition.scale.origin.bottom>
                                        <!-- Modal Header -->
                                        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-stone-800">
                                            <h3 class="font-bold text-lg text-slate-800 dark:text-stone-100 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                Rincian: <span x-text="'{{ $rekap->nama_kegiatan }}'"></span>
                                            </h3>
                                            <button @click="openDetail = false" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <!-- Modal Body -->
                                        <div class="p-0 overflow-y-auto overflow-x-auto custom-scrollbar flex-1">
                                            <table class="w-full text-left border-collapse min-w-[400px]">
                                                <thead class="bg-slate-50 dark:bg-[#272422] text-slate-600 dark:text-stone-300 text-[11px] uppercase tracking-wider font-bold sticky top-0 z-10">
                                                    <tr>
                                                        <th class="py-3 px-5 border-b border-slate-200 dark:border-stone-700">Tanggal</th>
                                                        <th class="py-3 px-5 border-b border-slate-200 dark:border-stone-700">Status</th>
                                                        <th class="py-3 px-5 border-b border-slate-200 dark:border-stone-700">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-stone-800 text-sm">
                                                    @foreach($rekap->detail as $item)
                                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-stone-800/50 transition-colors">
                                                            <td class="py-3 px-5 font-medium text-slate-700 dark:text-stone-200">
                                                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                                                            </td>
                                                            <td class="py-3 px-5">
                                                                <span class="px-2.5 py-1 text-xs font-bold rounded-md inline-block
                                                                    @if($item->status === 'hadir') bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20
                                                                    @elseif($item->status === 'izin') bg-amber-50 text-amber-600 dark:bg-amber-900/20
                                                                    @elseif($item->status === 'sakit') bg-blue-50 text-blue-600 dark:bg-blue-900/20
                                                                    @else bg-rose-50 text-rose-600 dark:bg-rose-900/20
                                                                    @endif
                                                                ">
                                                                    {{ ucfirst($item->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="py-3 px-5 text-slate-500 dark:text-stone-400 text-xs">
                                                                {{ $item->keterangan ?? '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-500 dark:text-stone-400 bg-white dark:bg-[#272422] rounded-2xl border border-dashed border-slate-200 dark:border-stone-700/50">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-stone-800 text-slate-400 mb-3">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="font-medium text-sm">Belum ada riwayat kehadiran kegiatan untuk anak Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tab Rapor --}}
            <div x-show="tab === 'rapor'" x-cloak class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Anak</th>
                            <th>Periode Penilaian</th>
                            <th>Nilai Akhir</th>
                            <th>Predikat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penilaian as $nilai)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-800 dark:text-stone-100">{{ $nilai->santri->nama }}</div>
                            </td>
                            <td class="font-semibold text-slate-700 dark:text-stone-200">{{ $nilai->periode }}</td>
                            <td class="font-bold text-slate-800 dark:text-stone-100">{{ $nilai->nilai_akhir }}</td>
                            <td>
                                <span class="badge badge-{{ $nilai->badge_color }}">{{ $nilai->kategori_label }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('penilaian-keseharian.show', $nilai) }}" class="text-violet-600 hover:text-violet-800 font-medium text-sm inline-block py-1">Lihat Rapor Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500 dark:text-stone-400">Belum ada rapor keseharian yang dipublikasikan untuk anak Anda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tab Pelanggaran & Disiplin --}}
            <div x-show="tab === 'pelanggaran'" x-cloak class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Anak</th>
                            <th>Tanggal</th>
                            <th>Kategori & Tingkat</th>
                            <th>Catatan Disiplin</th>
                            <th>Status Pembinaan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggaran as $p)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-800 dark:text-stone-100">{{ $p->santri->nama }}</div>
                            </td>
                            <td>{{ $p->tanggal->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $p->kategori == 'umum' ? 'gray' : 'yellow' }}">{{ ucfirst($p->kategori) }}</span>
                                <span class="text-xs text-slate-500 dark:text-stone-400 ml-1">{{ ucfirst($p->tingkat) }}</span>
                            </td>
                            <td>
                                <div class="text-sm text-slate-700 dark:text-stone-200 max-w-[250px] line-clamp-2" title="{{ $p->deskripsi }}">{{ $p->deskripsi }}</div>
                            </td>
                            <td>
                                @if($p->pembinaan->count() > 0)
                                    <span class="badge badge-green">{{ $p->pembinaan->count() }} Kali Dibina</span>
                                @else
                                    <span class="badge badge-amber">Belum Dibina</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('pelanggaran.show', $p) }}" class="text-violet-600 hover:text-violet-800 font-medium text-sm inline-block py-1">Detail Pelanggaran</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-emerald-600 font-medium bg-emerald-50/50 rounded-xl">
                                ✨ Alhamdulillāh, tidak ada catatan pelanggaran disiplin untuk anak Anda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Alpine.js for Tabs --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
