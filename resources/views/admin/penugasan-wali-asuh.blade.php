@extends('layouts.app')
@section('title', 'Penugasan Kamar Wali Asuh')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header Banner dengan Pastel Gradient Terpadu --}}
    <div class="banner-header rounded-2xl p-6 sm:p-8 text-white shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold mb-1">Penugasan Kamar Eksklusif Wali Asuh</h2>
            <p class="text-teal-100 text-sm">Satu kamar hanya dapat ditugaskan secara eksklusif kepada maksimal 1 Wali Asuh.</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-xl text-xs font-medium text-teal-100 shrink-0 relative z-10">
            Total Wali Asuh: <span class="font-bold text-white text-base ml-1">{{ count($waliAsuhList) }}</span>
        </div>
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
            <div class="font-semibold mb-1">Gagal menyimpan penugasan kamar:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- List Wali Asuh & Status Penugasan (Tabel Baris & Kolom) --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 overflow-hidden">
                <div class="p-4 border-b border-violet-100 dark:border-stone-700/40 bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="font-bold text-slate-800 dark:text-stone-100">Daftar Wali Asuh & Kamar Asuhan</h3>
                    {{-- Live Search Input Text --}}
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="searchWaliAsuhTable" placeholder="Ketik pencarian nama/email/kamar..." class="form-input text-xs pl-8">
                        <svg class="w-4 h-4 text-slate-400 dark:text-stone-500 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table" id="tableWaliAsuh">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>Wali Asuh</th>
                                <th>Email</th>
                                <th>Kamar Asuhan (Eksklusif)</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waliAsuhList as $index => $wali)
                            @php
                                $assignedKamars = $wali->penugasanKamar->map(fn($p) => $p->kamar->nama ?? null)->filter()->implode(', ');
                            @endphp
                            <tr class="row-wali-asuh hover:bg-violet-50/40 transition">
                                <td class="text-center font-medium text-slate-500 dark:text-stone-400">{{ $index + 1 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-100 to-indigo-100 text-violet-700 font-bold flex items-center justify-center border border-violet-200 shrink-0">
                                            {{ strtoupper(substr($wali->nama, 0, 1)) }}
                                        </div>
                                        <div class="font-bold text-slate-800 dark:text-stone-100 wali-nama">{{ $wali->nama }}</div>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-600 dark:text-stone-300 wali-email">{{ $wali->email }}</td>
                                <td class="wali-kamar">
                                    @if($assignedKamars)
                                        <span class="badge badge-emerald text-xs">{{ $assignedKamars }}</span>
                                    @else
                                        <span class="badge badge-gray text-xs">Belum Ada Kamar</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-outline btn-sm text-xs py-1 px-2 text-violet-600 border-violet-200 hover:bg-violet-50" onclick="selectWaliAsuh('{{ $wali->id }}', '{{ addslashes($wali->nama) }}')">
                                        Pilih & Edit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-500 dark:text-stone-400">Belum ada user dengan role Wali Asuh.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Form Penugasan Kamar dengan Searchable Text Input --}}
        <div>
            <div class="bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40 backdrop-blur-sm rounded-2xl shadow-sm border border-violet-100 dark:border-stone-700/40 p-6 sticky top-6">
                <h3 class="font-bold text-slate-800 dark:text-stone-100 mb-4">Atur Penugasan Kamar</h3>
                <form method="POST" action="{{ route('penugasan-wali-asuh.store') }}" class="space-y-4">
                    @csrf
                    
                    {{-- Text Search Input untuk Wali Asuh --}}
                    <div>
                        <label class="form-label text-xs">Pencarian & Pilih Wali Asuh <span class="text-rose-500">*</span></label>
                        <div class="relative mb-2">
                            <input type="text" id="inputSearchWaliAsuhForm" placeholder="Ketik nama Wali Asuh..." class="form-input text-xs pl-8" onkeyup="filterWaliAsuhSelect()">
                            <svg class="w-4 h-4 text-slate-400 dark:text-stone-500 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <select name="user_id" class="form-select text-xs" required id="selectWaliAsuh" size="4">
                            @foreach($waliAsuhList as $wali)
                                <option value="{{ $wali->id }}" {{ old('user_id') == $wali->id ? 'selected' : '' }} class="option-wali-asuh py-1">
                                    {{ $wali->nama }} ({{ $wali->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Pilih Kamar / Sektor Asuhan <span class="text-rose-500">*</span></label>
                        <div class="space-y-2 max-h-56 overflow-y-auto p-3 border border-violet-100 dark:border-stone-700/40 rounded-xl bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300/50">
                            @foreach($kamarList as $kamar)
                            @php
                                $assignedUserId = $assignedKamarIds[$kamar->id] ?? null;
                                $isAssignedToOther = $assignedUserId && $assignedUserId !== old('user_id');
                            @endphp
                            <label class="flex items-center justify-between text-xs p-2 rounded-lg bg-white dark:bg-[#272422] border border-slate-100 dark:border-stone-700/50 hover:border-violet-200 cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="kamar_ids[]" value="{{ $kamar->id }}" class="form-checkbox text-violet-600 rounded focus:ring-violet-500" {{ $isAssignedToOther ? 'disabled' : '' }}>
                                    <span class="font-medium text-slate-700 dark:text-stone-200">{{ $kamar->nama }}</span>
                                </div>
                                @if($assignedUserId)
                                    <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Terisi</span>
                                @else
                                    <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Tersedia</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">* Kamar yang sudah ditugaskan ke Wali Asuh lain dinonaktifkan (eksklusif).</p>
                    </div>

                    <button type="submit" class="btn btn-primary w-full justify-center">
                        Simpan Penugasan Kamar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Filter Live pada Tabel Wali Asuh
document.getElementById('searchWaliAsuhTable').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('.row-wali-asuh');

    rows.forEach(row => {
        const nama = row.querySelector('.wali-nama')?.textContent.toLowerCase() || '';
        const email = row.querySelector('.wali-email')?.textContent.toLowerCase() || '';
        const kamar = row.querySelector('.wali-kamar')?.textContent.toLowerCase() || '';

        if (nama.includes(filter) || email.includes(filter) || kamar.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Filter Text Input pada Form Select Wali Asuh
function filterWaliAsuhSelect() {
    const input = document.getElementById('inputSearchWaliAsuhForm');
    const filter = input.value.toLowerCase();
    const options = document.querySelectorAll('.option-wali-asuh');

    options.forEach(opt => {
        const text = opt.textContent.toLowerCase();
        if (text.includes(filter)) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
}

// Fungsi pilih Wali Asuh dari tabel
function selectWaliAsuh(id, nama) {
    const select = document.getElementById('selectWaliAsuh');
    select.value = id;
    const searchInput = document.getElementById('inputSearchWaliAsuhForm');
    if (searchInput) searchInput.value = nama;
    filterWaliAsuhSelect();
}
</script>
@endsection
