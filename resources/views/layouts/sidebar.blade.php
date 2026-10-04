@php
    $role = Auth::user()->role->nama_role;
@endphp

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    Dashboard
</a>

{{-- Data Santri (Big Data) --}}
@if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'dewan_pemangku']))
    <div class="sidebar-section">Manajemen</div>

    <a href="{{ route('big-data.index') }}" class="sidebar-link {{ request()->routeIs('big-data.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
        </svg>
        Data Santri
    </a>
@endif

{{-- Manajemen Daerah (Admin & Pengurus Daerah & Dewan Pemangku) --}}
@if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'dewan_pemangku']))
    <div class="sidebar-section">Manajemen Daerah</div>


    <a href="{{ route('kegiatan-daerah.index') }}" class="sidebar-link {{ request()->routeIs('kegiatan-daerah.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Kegiatan Daerah
    </a>

    <a href="{{ route('aspek-penilaian.index') }}" class="sidebar-link {{ request()->routeIs('aspek-penilaian.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        Master Penilaian
    </a>
@endif

{{-- Absensi (Kegiatan Daerah & Puasa) --}}
@if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'pengurus_sektor', 'dewan_pemangku', 'wali_santri']))
    <div class="sidebar-section">Absensi</div>

    @if($role === 'wali_santri')
        {{-- Wali Santri → halaman rekap read-only --}}
        <a href="{{ route('wali-santri.absensi.hari-ini') }}" class="sidebar-link {{ request()->routeIs('wali-santri.absensi.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/>
            </svg>
            Kehadiran Hari Ini
        </a>
    @else
        {{-- Pengurus / Admin → halaman absensi terpadu (form) --}}
        <a href="{{ route('absensi.index') }}" class="sidebar-link {{ request()->routeIs('absensi.*') && !request()->routeIs('arsip-absensi.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/>
            </svg>
            Absensi Terpadu
        </a>
        <a href="{{ route('arsip-absensi.index') }}" class="sidebar-link {{ request()->routeIs('arsip-absensi.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Arsip Absensi
        </a>
    @endif
@endif

{{-- Pelanggaran & Pembinaan --}}
@if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'pengurus_sektor', 'dewan_pemangku', 'wali_santri']))
    <div class="sidebar-section">Disiplin</div>
    <a href="{{ route('pelanggaran.index') }}" class="sidebar-link {{ request()->routeIs('pelanggaran.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        Pelanggaran
    </a>
    @if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'dewan_pemangku']))
    <a href="{{ route('pembinaan.index') }}" class="sidebar-link {{ request()->routeIs('pembinaan.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Riwayat Pembinaan
    </a>
    @endif
@endif

{{-- Penilaian Keseharian --}}
@if(in_array($role, ['admin', 'pengurus_wilayah', 'pengurus_daerah', 'pengurus_sektor', 'dewan_pemangku', 'wali_santri']))
    <div class="sidebar-section">Penilaian</div>
    <a href="{{ route('penilaian-keseharian.index') }}" class="sidebar-link {{ request()->routeIs('penilaian-keseharian.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        Penilaian Keseharian
    </a>
@endif

{{-- Pengurus Sektor --}}
@if($role === 'pengurus_sektor')
    <div class="sidebar-section">Pengurus Sektor</div>
    <a href="{{ route('wali-asuh.santri-asuhan') }}" class="sidebar-link {{ request()->routeIs('wali-asuh.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Santri Asuhan
    </a>
@endif

{{-- Wali Santri --}}
@if($role === 'wali_santri')
    <div class="sidebar-section">Wali Santri</div>
    <a href="{{ route('wali-santri.riwayat-izin') }}" class="sidebar-link {{ request()->routeIs('wali-santri.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Riwayat Anak
    </a>
@endif
