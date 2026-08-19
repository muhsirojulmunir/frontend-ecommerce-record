<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    <div class="mb-8 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Masuk ke Akun Anda</h1>
        <p class="text-sm text-text-light mt-1.5">Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-primary-light underline underline-offset-2">Daftar di sini</a>
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me + Forgot -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" type="checkbox"
                    class="rounded-sm border-border text-primary focus:ring-primary focus:ring-offset-0" name="remember">
                <span class="text-sm text-text-light">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-primary hover:text-primary-light underline underline-offset-2"
                    href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full py-3.5 text-sm">
            {{ __('Masuk') }}
        </x-primary-button>
    </form>
</x-guest-layout>
