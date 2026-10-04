<x-guest-layout>
    {{-- Header --}}
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-stone-100">Masuk ke Akun Anda</h2>
        <p class="text-sm text-slate-500 dark:text-stone-400 mt-1">Silakan masukkan kredensial untuk melanjutkan</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-violet-900 mb-1.5">Username</label>
            <input id="username" class="login-input" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="masukkan username">
            @error('username')
                <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-violet-900 mb-1.5">Password</label>
            <input id="password" class="login-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            @error('password')
                <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-violet-300 text-violet-600 shadow-sm focus:ring-teal-500" name="remember">
                <span class="ms-2 text-sm text-slate-600 dark:text-stone-300">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-violet-500 hover:text-violet-700 transition" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="login-btn">
            Masuk
        </button>
    </form>
</x-guest-layout>
