<!DOCTYPE html>
<html lang="id"
      x-data="themeSwitch()"
      x-init="init()"
      :class="{ 'dark': darkMode }"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="SIKESAN — Modul absensi kegiatan, absensi puasa, pelanggaran, pembinaan, dan penilaian keseharian santri">

        <title>{{ config('app.name', 'SIKESAN') }} — @yield('title', 'Dashboard')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

        <!-- Flatpickr (Format dd/mm/yyyy 100% Bebas Browser Locale) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
        <!-- Alpine Plugins -->
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', sans-serif; }

            /* ═══════════════════════════════════════
               🌞 LIGHT MODE — Vibrant Gradient Glassmorphism
               ═══════════════════════════════════════ */

            /* ─── Banner Header Pastel Gradient (PROTECTED — no dark override) ─── */
            .banner-header {
                background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #14b8a6 100%);
                border-radius: 1rem;
                color: #ffffff;
                box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.25);
            }

            /* ─── Sidebar ─── */
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: 260px;
                height: 100vh;
                z-index: 40;
                background: linear-gradient(180deg, #e0f2fe 0%, #ede9fe 45%, #fce7f3 100%);
                border-right: 1px solid rgba(196, 181, 253, 0.3);
                transition: transform 0.3s ease, background 0.4s ease, border-color 0.4s ease;
                box-shadow: 2px 0 20px rgba(139, 92, 246, 0.06);
            }
            .sidebar-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.625rem 1rem;
                border-radius: 0.625rem;
                color: #475569;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.25s ease;
                text-decoration: none;
                border-left: 2px solid transparent;
            }
            .sidebar-link:hover {
                background: rgba(255, 255, 255, 0.65);
                color: #7c3aed;
                transform: translateX(2px);
            }
            .sidebar-link.active {
                background: linear-gradient(135deg, #14b8a6, #8b5cf6);
                color: #ffffff;
                border-left: 2px solid transparent;
                box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
            }
            .sidebar-section {
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #8b5cf6;
                padding: 1.25rem 1rem 0.375rem;
            }

            /* ─── Content Background ─── */
            .main-content {
                flex: 1;
                min-width: 0;
                height: 100vh;
                margin-left: 260px;
                background: linear-gradient(135deg, #f5f3ff 0%, #f0fdfa 50%, #fdf2f8 100%);
                transition: background 0.4s ease;
            }

            /* ─── Cards Tinted Pastel Soft & Glassmorphism ─── */
            .stat-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
                backdrop-filter: blur(12px);
                border-radius: 1rem;
                padding: 1.25rem;
                box-shadow: 0 4px 18px rgba(139, 92, 246, 0.05);
                border: 1px solid rgba(196, 181, 253, 0.3);
                border-left: 4px solid #a78bfa;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
            }
            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(139, 92, 246, 0.12);
            }
            .stat-card-teal { border-left-color: #14b8a6; }
            .stat-card-amber { border-left-color: #f59e0b; }
            .stat-card-rose { border-left-color: #f43f5e; }
            .stat-card-indigo { border-left-color: #6366f1; }

            /* ─── Data Tables Soft Design ─── */
            .data-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
            }
            .data-table th {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #475569;
                background-color: rgba(255, 255, 255, 0.9);
                border-bottom: 1px solid #ede9fe;
            }
            .data-table td {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                color: #334155;
                border-bottom: 1px solid #f1f5f9;
                transition: background-color 0.2s ease;
            }
            .data-table tbody tr:hover td {
                background-color: rgba(241, 245, 249, 0.5);
            }
            .data-table tbody tr:last-child td {
                border-bottom: none;
            }

            /* ─── Buttons (PROTECTED — Pastel Gradients, No Dark Override) ─── */
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 0.625rem;
                font-size: 0.875rem;
                font-weight: 600;
                transition: all 0.2s ease;
                cursor: pointer;
                border: none;
                text-decoration: none;
            }
            .btn-primary {
                background: linear-gradient(135deg, #14b8a6 0%, #8b5cf6 100%);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(139, 92, 246, 0.25);
            }
            .btn-primary:hover {
                box-shadow: 0 6px 20px rgba(139, 92, 246, 0.35);
                transform: translateY(-1px);
            }
            .btn-secondary {
                background: #f1f5f9;
                color: #475569;
            }
            .btn-secondary:hover {
                background: #e2e8f0;
                color: #1e293b;
            }
            .btn-outline {
                background: transparent;
                border: 1px solid #cbd5e1;
                color: #475569;
            }
            .btn-outline:hover {
                background: #f8fafc;
                border-color: #94a3b8;
            }
            .btn-danger {
                background: linear-gradient(135deg, #ef4444, #f43f5e);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
            }
            .btn-danger:hover {
                box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
                transform: translateY(-1px);
            }

            /* ─── Form Inputs ─── */
            .form-input, .form-select, .form-textarea {
                width: 100%;
                padding: 0.5rem 0.75rem;
                border-radius: 0.625rem;
                border: 1px solid #cbd5e1;
                background-color: #ffffff;
                color: #1e293b;
                font-size: 0.875rem;
                transition: all 0.2s ease;
            }
            .form-input:focus, .form-select:focus, .form-textarea:focus {
                outline: none;
                border-color: #8b5cf6;
                box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
            }
            .form-input::placeholder {
                color: #94a3b8;
            }
            .form-label {
                display: block;
                font-size: 0.8125rem;
                font-weight: 600;
                color: #334155;
                margin-bottom: 0.375rem;
            }

            /* Custom Radio & Checkbox */
            input[type="radio"], input[type="checkbox"] {
                background-color: #ffffff;
                border-color: #cbd5e1;
            }
            input[type="radio"]:checked, input[type="checkbox"]:checked {
                background-color: #14b8a6;
                border-color: #14b8a6;
            }

            /* ─── Badges ─── */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.625rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                border: 1px solid transparent;
            }
            .badge-green  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
            .badge-yellow { background: #fef9c3; color: #a16207; border-color: #fef08a; }
            .badge-blue   { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
            .badge-red    { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
            .badge-gray   { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
            .badge-purple { background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; }
            .badge-teal   { background: #ccfbf1; color: #0f766e; border-color: #99f6e4; }
            .badge-indigo { background: #e0e7ff; color: #3730a3; border-color: #c7d2fe; }

            /* Mobile Sidebar Toggle Overlay */
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(4px);
                z-index: 35;
            }

            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.open {
                    transform: translateX(0);
                }
                .sidebar-overlay.open {
                    display: block;
                }
                .main-content {
                    margin-left: 0;
                }
            }

            /* ═══════════════════════════════════════
               🌙 DARK MODE — Sleek Cool Slate
               ═══════════════════════════════════════ */

            /* Dark Mode Badges (Soft Glowing Professional Look) */
            .dark .badge-green  { background: rgba(34, 197, 94, 0.15); color: #4ade80; border-color: rgba(74, 222, 128, 0.2); }
            .dark .badge-yellow { background: rgba(234, 179, 8, 0.15); color: #facc15; border-color: rgba(250, 204, 21, 0.2); }
            .dark .badge-blue   { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(96, 165, 250, 0.2); }
            .dark .badge-red    { background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(248, 113, 113, 0.2); }
            .dark .badge-gray   { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(203, 213, 225, 0.2); }
            .dark .badge-purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; border-color: rgba(192, 132, 252, 0.2); }
            .dark .badge-teal   { background: rgba(20, 184, 166, 0.15); color: #2dd4bf; border-color: rgba(45, 212, 191, 0.2); }
            .dark .badge-indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; border-color: rgba(129, 140, 248, 0.2); }

            .dark .sidebar {
                background: #0f172a; /* slate-900 */
                border-right-color: rgba(30, 41, 59, 0.7); /* slate-800 */
                box-shadow: 2px 0 20px rgba(0, 0, 0, 0.3);
            }
            .dark .sidebar-link {
                color: #f1f5f9; /* slate-100 (Brightened) */
            }
            .dark .sidebar-link:hover {
                background: #1e293b; /* slate-800 */
                color: #38bdf8; /* sky-400 */
            }
            /* .sidebar-link.active keeps pastel gradient — no dark override needed */
            .dark .sidebar-section {
                color: #94a3b8; /* slate-400 (Brightened) */
            }

            .dark .main-content {
                background: #0b1120; /* Very dark slate */
            }

            .dark .stat-card {
                background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.8) 100%);
                border-color: rgba(51, 65, 85, 0.5); /* slate-700 */
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.2);
            }
            .dark .stat-card:hover {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            }

            .dark .data-table th {
                color: #94a3b8; /* slate-400 */
                background-color: rgba(15, 23, 42, 0.95);
                border-bottom-color: rgba(30, 41, 59, 0.8);
            }
            .dark .data-table td {
                color: #e2e8f0; /* slate-200 */
                border-bottom-color: rgba(30, 41, 59, 0.4);
            }
            .dark .data-table tbody tr:nth-child(even) td {
                background-color: rgba(15, 23, 42, 0.3);
            }
            .dark .data-table tbody tr:hover td {
                background-color: rgba(30, 41, 59, 0.6);
            }

            .dark .btn-secondary {
                background: #1e293b; 
                color: #e2e8f0;
            }
            .dark .btn-secondary:hover {
                background: #334155; 
                color: #f8fafc;
            }
            .dark .btn-outline {
                border-color: #334155;
                color: #94a3b8;
            }
            .dark .btn-outline:hover {
                background: rgba(30, 41, 59, 0.5);
                border-color: #475569;
            }

            .dark .form-input, .dark .form-select, .dark .form-textarea {
                background-color: #0f172a;
                border-color: #334155;
                color: #f8fafc;
            }
            .dark .form-input:focus, .dark .form-select:focus, .dark .form-textarea:focus {
                border-color: #8b5cf6;
                box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            }
            .dark .form-input::placeholder {
                color: #64748b;
            }
            .dark .form-label {
                color: #cbd5e1;
            }

            .dark input[type="radio"], .dark input[type="checkbox"] {
                background-color: #0f172a;
                border-color: #334155;
            }

            .dark .sidebar-overlay {
                background: rgba(0, 0, 0, 0.7);
            }

            /* ─── Theme Toggle Button ─── */
            .theme-toggle {
                position: relative;
                width: 52px;
                height: 28px;
                border-radius: 9999px;
                cursor: pointer;
                transition: background 0.35s ease;
                border: none;
                outline: none;
                display: flex;
                align-items: center;
                padding: 0 3px;
            }
            .theme-toggle.light {
                background: linear-gradient(135deg, #bfdbfe, #93c5fd);
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
            }
            .theme-toggle.dark-active {
                background: linear-gradient(135deg, #44403c, #292524);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }
            .theme-toggle-knob {
                width: 22px;
                height: 22px;
                border-radius: 50%;
                transition: all 0.35s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
            }
            .theme-toggle.light .theme-toggle-knob {
                transform: translateX(0);
                background: #ffffff;
                box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            }
            .theme-toggle.dark-active .theme-toggle-knob {
                transform: translateX(24px);
                background: #fbbf24;
                box-shadow: 0 1px 6px rgba(251,191,36,0.4);
            }

            /* ─── Smooth page transitions ─── */
            * { transition-property: background-color, border-color, color; transition-duration: 0.15s; transition-timing-function: ease; }
            /* Exclude elements that should transition instantly or have own transitions */
            .sidebar, .sidebar-link, .btn, .stat-card, .theme-toggle, .theme-toggle-knob { transition: all 0.3s ease; }
        </style>

        @yield('styles')
    </head>
    <body class="bg-slate-50 dark:bg-[#1E1C1A] text-slate-800 dark:text-stone-200 antialiased h-screen overflow-hidden">
        <div class="flex h-screen overflow-hidden">
            {{-- Mobile Overlay --}}
            <div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

            {{-- Sidebar Wrapper --}}
            <aside id="sidebar" class="sidebar flex flex-col">
                {{-- Brand Header --}}
                <div class="p-5 border-b border-violet-200/40 dark:border-stone-700/40 flex items-center gap-3 flex-shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-400 via-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold text-lg shadow-md flex-shrink-0">
                        KW
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-slate-800 dark:text-stone-100 text-sm leading-tight truncate">SIKESAN</div>
                        <div class="text-[11px] text-teal-600 dark:text-teal-400 font-semibold truncate">Wilayah Zaid bin Tsabit</div>
                    </div>
                </div>

                {{-- Navigation Items --}}
                <nav class="px-3 py-4 flex-1 space-y-1 overflow-y-auto">
                    @include('layouts.sidebar')
                </nav>

                {{-- User Info --}}
                <div class="p-4 border-t border-violet-200/40 dark:border-stone-700/40 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-violet-500 flex items-center justify-center text-white text-sm font-bold shadow-sm flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-slate-700 dark:text-stone-200 text-sm font-medium truncate">{{ Auth::user()->nama }}</div>
                            <div class="text-violet-500 dark:text-stone-400 text-xs truncate">{{ ucwords(str_replace('_', ' ', Auth::user()->role->nama_role)) }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 dark:text-stone-500 hover:text-rose-500 dark:hover:text-rose-400 transition p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20" title="Logout">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Main Content Container (Locked to 1 Screen Fit) --}}
            <div class="main-content flex flex-col h-screen overflow-hidden flex-1 min-w-0">
                {{-- Top Header Bar (Fixed) --}}
                <header class="bg-white/70 dark:bg-[#272422]/80 backdrop-blur-md border-b border-white/40 dark:border-stone-700/40 px-6 sm:px-8 py-4 flex items-center justify-between flex-shrink-0 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.05)] dark:shadow-[0_2px_15px_-3px_rgba(0,0,0,0.2)]">

                    <div class="flex items-center gap-3">
                        <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg hover:bg-violet-100 dark:hover:bg-stone-700 transition">
                            <svg class="w-5 h-5 text-violet-600 dark:text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-slate-800 dark:text-stone-100 tracking-tight">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        @yield('header-actions')

                        {{-- 🌗 Theme Toggle Switch --}}
                        <button
                            @click="toggle()"
                            :class="darkMode ? 'dark-active' : 'light'"
                            class="theme-toggle"
                            :title="darkMode ? 'Beralih ke Mode Siang' : 'Beralih ke Mode Malam'"
                        >
                            <span class="theme-toggle-knob">
                                <template x-if="!darkMode">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/></svg>
                                </template>
                                <template x-if="darkMode">
                                    <svg class="w-3.5 h-3.5 text-stone-800" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                                </template>
                            </span>
                        </button>
                    </div>
                </header>

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mx-6 sm:mx-8 mt-4 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 border border-emerald-200/60 dark:border-emerald-700/40 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs flex-shrink-0 shadow-sm" id="flash-success">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('success') }}
                        </div>
                    </div>
                    <script>setTimeout(() => document.getElementById('flash-success')?.remove(), 5000);</script>
                @endif

                @if(session('error'))
                    <div class="mx-6 sm:mx-8 mt-4 p-4 bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-900/30 dark:to-pink-900/30 border border-rose-200/60 dark:border-rose-700/40 rounded-xl text-rose-800 dark:text-rose-300 text-xs flex-shrink-0 shadow-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                {{-- Page Content Area (Flex-1 + Min-h-0 + Overflow-y-auto for One-Screen Fit) --}}
                <main class="flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 lg:p-8 flex flex-col">
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            // ─── Alpine.js Theme Switcher ───
            function themeSwitch() {
                return {
                    darkMode: false,
                    init() {
                        // Baca preferensi dari localStorage
                        const stored = localStorage.getItem('darkMode');
                        if (stored !== null) {
                            this.darkMode = stored === 'true';
                        } else {
                            // Default: ikuti preferensi sistem
                            this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                        }
                    },
                    toggle() {
                        this.darkMode = !this.darkMode;
                        localStorage.setItem('darkMode', this.darkMode);
                    }
                }
            }

            // ─── Sidebar Toggle (Mobile) ───
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('open');
                document.getElementById('sidebar-overlay').classList.toggle('open');
            }

            // ─── Flatpickr Init ───
            document.addEventListener('DOMContentLoaded', function() {
                if (window.flatpickr) {
                    flatpickr.localize(flatpickr.l10ns.id);
                    flatpickr(".datepicker", {
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "d/m/Y",
                        allowInput: true,
                        monthSelectorType: "dropdown"
                    });
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>
