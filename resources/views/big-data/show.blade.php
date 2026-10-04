@extends('layouts.app')
@section('title', 'Big Data — ' . $wilayahModel->nama)

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-stone-400">
        <a href="{{ route('dashboard') }}" class="hover:text-violet-600 transition">Dashboard</a>
        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('big-data-daerah.index') }}" class="hover:text-violet-600 transition">Big Data Daerah</a>
        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-violet-700 font-medium">{{ $wilayahModel->nama }}</span>
    </nav>

    {{-- Banner Header Wilayah --}}
    <div class="banner-header rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold">{{ $wilayahModel->nama }}</h2>
                    <p class="text-teal-100 text-sm">Wilayah Pesantren</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-4 mt-4">
                <div class="bg-white/15 backdrop-blur-md rounded-xl px-4 py-2">
                    <div class="text-teal-200 text-xs font-medium">Total Daerah</div>
                    <div class="text-white text-xl font-bold">{{ $daerahs->count() }}</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-xl px-4 py-2">
                    <div class="text-teal-200 text-xs font-medium">Total Kamar</div>
                    <div class="text-white text-xl font-bold">{{ $daerahs->sum(fn($d) => $d->children->count()) }}</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-xl px-4 py-2">
                    <div class="text-teal-200 text-xs font-medium">Estimasi Santri</div>
                    <div class="text-white text-xl font-bold">{{ $daerahs->sum(fn($d) => $d->children->sum('santri_count')) }}</div>
                </div>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
        <div class="absolute bottom-0 right-16 -mb-8 w-32 h-32 rounded-full bg-teal-400/20 blur-lg"></div>
    </div>

    {{-- Daftar Daerah (Accordion) --}}
    @if($daerahs->isEmpty())
        <div class="glass-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
            <h4 class="text-slate-700 dark:text-stone-200 font-semibold mb-1">Belum Ada Data Daerah</h4>
            <p class="text-slate-500 dark:text-stone-400 text-sm">Jalankan seeder <code class="bg-violet-50 px-1.5 py-0.5 rounded text-violet-700">DemoSeeder</code> untuk mengisi data.</p>
        </div>
    @else
        <div class="space-y-3" id="accordion-daerah">
            @foreach($daerahs as $daerahIndex => $daerah)
                @php
                    $daerahColors = [
                        ['from-teal-500', 'to-emerald-400', 'bg-teal-100', 'text-teal-700', 'border-teal-200'],
                        ['from-violet-500', 'to-purple-400', 'bg-violet-100', 'text-violet-700', 'border-violet-200'],
                        ['from-blue-500', 'to-cyan-400', 'bg-blue-100', 'text-blue-700', 'border-blue-200'],
                        ['from-amber-500', 'to-yellow-400', 'bg-amber-100', 'text-amber-700', 'border-amber-200'],
                        ['from-rose-500', 'to-pink-400', 'bg-rose-100', 'text-rose-700', 'border-rose-200'],
                        ['from-indigo-500', 'to-blue-400', 'bg-indigo-100', 'text-indigo-700', 'border-indigo-200'],
                        ['from-emerald-500', 'to-green-400', 'bg-emerald-100', 'text-emerald-700', 'border-emerald-200'],
                    ];
                    $color = $daerahColors[$daerahIndex % count($daerahColors)];
                    $kamars = $daerah->children; // children tipe sektor = kamar
                @endphp

                <div class="daerah-accordion glass-card overflow-hidden fade-in" style="animation-delay: {{ $daerahIndex * 0.04 }}s">
                    {{-- Accordion Header --}}
                    <button type="button"
                            class="accordion-trigger w-full flex items-center justify-between p-4 sm:p-5 hover:bg-violet-50/30 transition-colors"
                            onclick="toggleAccordion('daerah-{{ $daerah->id }}')"
                            aria-expanded="false"
                            aria-controls="daerah-body-{{ $daerah->id }}"
                            id="daerah-trigger-{{ $daerah->id }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $color[0] }} {{ $color[1] }} flex items-center justify-center shadow-md flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                            </div>
                            <div class="text-left">
                                <h4 class="font-bold text-slate-800 dark:text-stone-100 text-sm sm:text-base">{{ $daerah->nama }}</h4>
                                <p class="text-xs text-slate-500 dark:text-stone-400">{{ $kamars->count() }} kamar tersedia</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="hidden sm:inline-flex items-center gap-1 text-xs font-medium {{ $color[3] }} {{ $color[2] }} px-2.5 py-1 rounded-full border {{ $color[4] }}">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                ~{{ $kamars->sum('santri_count') }} santri
                            </span>
                            <svg class="w-5 h-5 text-slate-400 dark:text-stone-500 accordion-chevron transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>

                    {{-- Accordion Body — Grid Kamar --}}
                    <div class="accordion-body hidden" id="daerah-body-{{ $daerah->id }}">
                        <div class="border-t border-violet-100/50 dark:border-stone-700/40 bg-gradient-to-b from-violet-50/20 to-transparent p-4 sm:p-5">
                            @if($kamars->isEmpty())
                                <div class="text-center py-6 text-slate-500 dark:text-stone-400 text-sm">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    Belum ada kamar di daerah/asrama ini.
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($kamars as $kamar)
                                        <button type="button"
                                                class="kamar-card group text-left"
                                                onclick="openKamarModal('{{ $kamar->id }}', '{{ addslashes($kamar->nama) }}', '{{ addslashes($daerah->nama) }}')"
                                                id="kamar-card-{{ $kamar->id }}">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg {{ $color[2] }} {{ $color[3] }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-semibold text-slate-800 dark:text-stone-100 text-sm truncate group-hover:text-violet-700 transition-colors">{{ $kamar->nama }}</div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-xs font-medium text-teal-600">{{ $kamar->santri_count }} santri</span>
                                                    </div>
                                                </div>
                                                <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Modal Data Santri --}}
<div id="santri-modal" class="modal-overlay hidden" onclick="closeKamarModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        {{-- Modal Header --}}
        <div class="modal-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-teal-400 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 id="modal-kamar-title" class="font-bold text-slate-800 dark:text-stone-100 text-lg">Kamar</h3>
                    <p id="modal-asrama-subtitle" class="text-xs text-slate-500 dark:text-stone-400">Daerah</p>
                </div>
            </div>
            <button type="button" onclick="closeKamarModal()" class="p-2 rounded-lg hover:bg-rose-50 text-slate-400 dark:text-stone-500 hover:text-rose-500 transition" aria-label="Tutup modal">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="modal-body" id="modal-body-content">
            {{-- Loading State --}}
            <div id="modal-loading" class="py-10 text-center">
                <div class="shimmer-container">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="shimmer-card"></div>
                        <div class="shimmer-card"></div>
                        <div class="shimmer-card"></div>
                        <div class="shimmer-card"></div>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-stone-400 text-sm mt-4">Memuat data santri...</p>
            </div>

            {{-- Santri Grid (diisi via JS) --}}
            <div id="modal-santri-grid" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3">
            </div>

            {{-- Empty State --}}
            <div id="modal-empty" class="hidden py-10 text-center">
                <div class="w-14 h-14 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h4 class="text-slate-700 dark:text-stone-200 font-semibold mb-1">Belum Ada Data Santri</h4>
                <p class="text-slate-500 dark:text-stone-400 text-sm">Data santri akan tersedia setelah integrasi API Pedatren aktif.</p>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer">
            <span class="text-xs text-slate-400 dark:text-stone-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Sumber data: Placeholder (API Pedatren belum aktif)
            </span>
            <button type="button" onclick="closeKamarModal()" class="btn btn-outline btn-sm">Tutup</button>
        </div>
    </div>
</div>

<style>
    /* ─── Kamar Cards ─── */
    .kamar-card {
        padding: 0.875rem;
        background: rgba(255,255,255,0.85);
        border-radius: 0.75rem;
        border: 1px solid rgba(196, 181, 253, 0.2);
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .kamar-card:hover {
        background: rgba(255,255,255,1);
        border-color: rgba(139, 92, 246, 0.3);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.1);
        transform: translateY(-2px);
    }

    /* ─── Accordion ─── */
    .accordion-trigger { cursor: pointer; outline: none; }
    .accordion-trigger:focus-visible { box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.3); border-radius: 0.5rem; }
    .accordion-trigger[aria-expanded="true"] .accordion-chevron { transform: rotate(180deg); }
    .accordion-body { transition: max-height 0.35s ease, opacity 0.3s ease; overflow: hidden; }
    .accordion-body.hidden { display: none; }

    /* ─── Modal ─── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(139, 92, 246, 0.12);
        backdrop-filter: blur(6px);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: fadeInOverlay 0.2s ease;
    }
    .modal-overlay.hidden { display: none; }
    .modal-content {
        background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.95) 100%);
        border-radius: 1.25rem;
        box-shadow: 0 25px 60px rgba(139, 92, 246, 0.18), 0 0 0 1px rgba(196, 181, 253, 0.2);
        width: 100%;
        max-width: 680px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        animation: slideUpModal 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(196, 181, 253, 0.2);
    }
    .modal-body {
        padding: 1.25rem 1.5rem;
        overflow-y: auto;
        flex: 1;
    }
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(196, 181, 253, 0.2);
        background: rgba(245, 243, 255, 0.5);
        border-radius: 0 0 1.25rem 1.25rem;
    }

    /* ─── Santri Card ─── */
    .santri-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem;
        background: rgba(255,255,255,0.85);
        border-radius: 0.75rem;
        border: 1px solid rgba(196, 181, 253, 0.15);
        transition: all 0.2s ease;
    }
    .santri-card:hover {
        background: rgba(237, 233, 254, 0.25);
        border-color: rgba(139, 92, 246, 0.2);
    }
    .santri-avatar {
        width: 40px;
        height: 40px;
        border-radius: 0.625rem;
        background: linear-gradient(135deg, #a78bfa, #14b8a6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    /* ─── Shimmer Loading ─── */
    .shimmer-card {
        height: 60px;
        border-radius: 0.75rem;
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease infinite;
    }

    /* ─── Animations ─── */
    @keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpModal { from { opacity: 0; transform: translateY(20px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>

@push('scripts')
<script>
    // ─── Accordion Toggle ───
    function toggleAccordion(daerahId) {
        const body = document.getElementById(daerahId.replace('daerah-', 'daerah-body-'));
        const trigger = document.getElementById(daerahId.replace('daerah-', 'daerah-trigger-'));

        if (!body || !trigger) return;

        const isHidden = body.classList.contains('hidden');
        body.classList.toggle('hidden');
        trigger.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    }

    // ─── Modal Kamar ───
    function openKamarModal(kamarId, kamarNama, daerahNama) {
        const modal = document.getElementById('santri-modal');
        const titleEl = document.getElementById('modal-kamar-title');
        const subtitleEl = document.getElementById('modal-asrama-subtitle');
        const loadingEl = document.getElementById('modal-loading');
        const gridEl = document.getElementById('modal-santri-grid');
        const emptyEl = document.getElementById('modal-empty');

        // Set header
        titleEl.textContent = kamarNama;
        subtitleEl.textContent = daerahNama + ' — {{ $wilayahModel->nama }}';

        // Reset state
        loadingEl.classList.remove('hidden');
        gridEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
        gridEl.innerHTML = '';

        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Fetch data santri
        fetch('{{ url("/admin/big-data-daerah/kamar") }}/' + kamarId + '/santri', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingEl.classList.add('hidden');

            if (!data.santri || data.santri.length === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }

            // Render santri cards
            let html = '';
            data.santri.forEach(function(santri) {
                const initials = santri.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                const statusBadge = santri.status === 'aktif'
                    ? '<span class="badge badge-green" style="font-size:0.65rem; padding: 0.15rem 0.5rem;">Aktif</span>'
                    : '<span class="badge badge-yellow" style="font-size:0.65rem; padding: 0.15rem 0.5rem;">Cuti</span>';

                html += `
                    <div class="santri-card">
                        <div class="santri-avatar">${initials}</div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-800 dark:text-stone-100 text-sm truncate">${santri.nama}</div>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-slate-500 dark:text-stone-400">${santri.nis}</span>
                                <span class="text-xs text-slate-300">|</span>
                                <span class="text-xs text-slate-500 dark:text-stone-400">NIK: ${santri.nik}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            ${statusBadge}
                        </div>
                    </div>
                `;
            });

            gridEl.innerHTML = html;
            gridEl.classList.remove('hidden');
        })
        .catch(function(err) {
            loadingEl.classList.add('hidden');
            gridEl.innerHTML = `
                <div class="col-span-2 text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-slate-700 dark:text-stone-200 font-semibold mb-1">Gagal Memuat Data</h4>
                    <p class="text-slate-500 dark:text-stone-400 text-sm">Terjadi kesalahan saat mengambil data santri. Silakan coba lagi.</p>
                </div>
            `;
            gridEl.classList.remove('hidden');
        });
    }

    function closeKamarModal(event) {
        if (event && event.target !== event.currentTarget) return;
        const modal = document.getElementById('santri-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeKamarModal();
    });
</script>
@endpush
@endsection
