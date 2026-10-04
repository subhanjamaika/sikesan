@extends('layouts.app')
@section('title', 'Catat Pelanggaran')

@section('header-actions')
    <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline">Kembali</a>
@endsection

@section('content')
<style>
    /* Gradient Khusus untuk Form Pelanggaran (Tema Rose/Violet) */
    .form-header-gradient {
        background: linear-gradient(135deg, #fecdd3 0%, #e9d5ff 100%) !important;
    }
    .dark .form-header-gradient {
        background: linear-gradient(135deg, rgba(225, 29, 72, 0.3) 0%, rgba(124, 58, 237, 0.3) 100%) !important;
    }
    
    .form-body-bg {
        background: linear-gradient(180deg, #ffffff 0%, #fff1f2 100%) !important;
    }
    .dark .form-body-bg {
        background: linear-gradient(180deg, #1A1817 0%, #271a1e 100%) !important;
    }
    
    /* Pewarnaan Khusus Form Inputs & Labels */
    .form-body-bg .form-label { color: #881337 !important; font-weight: 700 !important; font-size: 0.85rem; letter-spacing: 0.02em; }
    .dark .form-body-bg .form-label { color: #fda4af !important; }
    
    .form-body-bg .form-input, .form-body-bg .form-select, .form-body-bg .form-textarea {
        background-color: rgba(255, 241, 242, 0.5) !important; border-color: rgba(254, 205, 211, 0.8) !important; color: #4c1d95 !important; font-weight: 600; transition: all 0.2s;
    }
    .form-body-bg .form-input:focus, .form-body-bg .form-select:focus, .form-body-bg .form-textarea:focus {
        background-color: #ffffff !important; border-color: #d8b4fe !important; box-shadow: 0 0 0 4px rgba(216, 180, 254, 0.25) !important;
    }
    
    .dark .form-body-bg .form-input, .dark .form-body-bg .form-select, .dark .form-body-bg .form-textarea {
        background-color: rgba(225, 29, 72, 0.05) !important; border-color: rgba(225, 29, 72, 0.3) !important; color: #f8fafc !important;
    }
    .dark .form-body-bg .form-input:focus, .dark .form-body-bg .form-select:focus, .dark .form-body-bg .form-textarea:focus {
        background-color: rgba(24, 24, 27, 0.8) !important; border-color: #a78bfa !important; box-shadow: 0 0 0 4px rgba(167, 139, 250, 0.2) !important;
    }

    .btn-gradient-submit {
        background: linear-gradient(135deg, #f43f5e 0%, #8b5cf6 100%) !important; color: white !important; border: none !important; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3) !important; font-weight: 700 !important; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.75rem 1.5rem !important;
    }
    .btn-gradient-submit:hover {
        background: linear-gradient(135deg, #e11d48 0%, #7c3aed 100%) !important; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(244, 63, 94, 0.4) !important;
    }
</style>

<div class="max-w-2xl mx-auto form-body-bg rounded-2xl shadow-lg border border-rose-100 dark:border-rose-900/30 overflow-hidden transition-all duration-300 hover:shadow-xl">
    <div class="form-header-gradient px-6 py-6 border-b border-rose-200/60 dark:border-rose-800/40">
        <h2 class="text-xl font-black text-slate-800 dark:text-stone-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/60 dark:bg-black/30 flex items-center justify-center backdrop-blur-md shadow-sm border border-white/50 dark:border-white/10">
                <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            Form Pencatatan Pelanggaran
        </h2>
        <p class="text-sm text-slate-700 dark:text-stone-300 mt-2 font-medium">Lengkapi data pelanggaran santri di bawah ini dengan seksama.</p>
    </div>
    <div class="p-6 sm:p-8">
        <form method="POST" action="{{ route('pelanggaran.store') }}">
        @csrf

        <div class="form-group" x-data="{
            search: '{{ old('santri_nama_nis') }}',
            santris: {{ $santriList->map(fn($s) => ['id' => $s->id, 'text' => $s->nis_induk . ' - ' . $s->nama])->toJson() }},
            get selectedId() {
                let found = this.santris.find(s => s.text === this.search);
                return found ? found.id : '';
            }
        }">
            <label class="form-label" for="santri_input">Santri <span class="text-red-500">*</span></label>
            <input type="hidden" name="santri_id" :value="selectedId">
            <input type="text" name="santri_nama_nis" id="santri_input" x-model="search" list="santri-list" 
                   class="form-input @error('santri_id') border-red-500 @enderror" 
                   placeholder="Ketik/Pilih NIS atau Nama..." autocomplete="off" required>
            <datalist id="santri-list">
                <template x-for="s in santris" :key="s.id">
                    <option :value="s.text"></option>
                </template>
            </datalist>
            @error('santri_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label" for="kategori">Kategori Pelanggaran <span class="text-red-500">*</span></label>
                <select name="kategori" id="kategori" class="form-select @error('kategori') border-red-500 @enderror" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="umum" {{ old('kategori') == 'umum' ? 'selected' : '' }}>Umum</option>
                    <option value="devisi" {{ old('kategori') == 'devisi' ? 'selected' : '' }}>Devisi</option>
                </select>
                @error('kategori') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="tingkat">Tingkat Penanganan <span class="text-red-500">*</span></label>
                <select name="tingkat" id="tingkat" class="form-select @error('tingkat') border-red-500 @enderror" required>
                    <option value="">-- Pilih Tingkat --</option>
                    <option value="sektor" {{ old('tingkat') == 'sektor' ? 'selected' : '' }}>Sektor</option>
                    <option value="daerah" {{ old('tingkat') == 'daerah' ? 'selected' : '' }}>Daerah</option>
                </select>
                @error('tingkat') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="tanggal">Tanggal Kejadian <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" id="tanggal" class="form-input @error('tanggal') border-red-500 @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
            @error('tanggal') <div class="form-error">{{ $message }}</div> @enderror
            <div class="form-helper">Tanggal tidak boleh di masa depan.</div>
        </div>

        <div class="form-group">
            <label class="form-label" for="deskripsi">Deskripsi Pelanggaran <span class="text-red-500">*</span></label>
            <textarea name="deskripsi" id="deskripsi" rows="4" class="form-textarea @error('deskripsi') border-red-500 @enderror" required minlength="10" placeholder="Ceritakan kronologi singkat pelanggaran (min 10 karakter)...">{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="btn btn-gradient-submit rounded-xl">Simpan Pelanggaran</button>
        </div>
        </form>
    </div>
</div>
@endsection
