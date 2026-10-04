<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIKESAN') }} — Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
            .login-bg {
                min-height: 100vh;
                background: linear-gradient(135deg, #e0f2fe 0%, #ede9fe 30%, #fce7f3 60%, #f0fdfa 100%);
                position: relative;
                overflow: hidden;
            }
            .login-bg::before {
                content: '';
                position: absolute;
                top: -150px;
                right: -150px;
                width: 400px;
                height: 400px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 70%);
            }
            .login-bg::after {
                content: '';
                position: absolute;
                bottom: -100px;
                left: -100px;
                width: 350px;
                height: 350px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(20,184,166,0.1) 0%, transparent 70%);
            }
            .login-card {
                background: rgba(255, 255, 255, 0.75);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(196, 181, 253, 0.25);
                border-radius: 1.25rem;
                box-shadow: 0 8px 40px rgba(139, 92, 246, 0.08), 0 2px 8px rgba(0,0,0,0.04);
            }
            .login-input {
                width: 100%;
                padding: 0.625rem 0.875rem;
                border: 1px solid rgba(196, 181, 253, 0.35);
                border-radius: 0.625rem;
                font-size: 0.875rem;
                transition: all 0.25s ease;
                background: rgba(255, 255, 255, 0.6);
            }
            .login-input:focus {
                outline: none;
                border-color: #14b8a6;
                box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
                background: #fff;
            }
            .login-btn {
                width: 100%;
                padding: 0.625rem 1rem;
                background: linear-gradient(135deg, #14b8a6, #8b5cf6);
                color: #fff;
                border: none;
                border-radius: 0.625rem;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.25s ease;
            }
            .login-btn:hover {
                box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
                transform: translateY(-1px);
            }
            .login-btn:active {
                transform: translateY(0);
            }
            .floating-orb {
                position: absolute;
                border-radius: 50%;
                opacity: 0.08;
                animation: floatOrb 8s ease-in-out infinite;
            }
            @keyframes floatOrb {
                0%, 100% { transform: translateY(0) scale(1); }
                50% { transform: translateY(-20px) scale(1.05); }
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="login-bg flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            {{-- Floating decorative orbs --}}
            <div class="floating-orb bg-violet-400" style="top: 15%; left: 10%; width: 80px; height: 80px; animation-delay: 0s;"></div>
            <div class="floating-orb bg-teal-400" style="top: 60%; right: 15%; width: 60px; height: 60px; animation-delay: 2s;"></div>
            <div class="floating-orb bg-pink-400" style="bottom: 20%; left: 25%; width: 45px; height: 45px; animation-delay: 4s;"></div>

            {{-- Logo --}}
            <div class="mb-6 flex items-center gap-3 relative z-10">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-teal-400 to-violet-500 flex items-center justify-center shadow-lg shadow-violet-200">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="text-violet-900 font-bold text-lg">SIKESAN</div>
                    <div class="text-violet-400 text-xs">Sistem Kewalian Santri</div>
                </div>
            </div>

            {{-- Login Card --}}
            <div class="w-full sm:max-w-md px-6 py-8 login-card relative z-10">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <div class="mt-6 text-center text-xs text-violet-300 relative z-10">
                &copy; {{ date('Y') }} SIKESAN. Sistem Santri v2.
            </div>
        </div>
    </body>
</html>
