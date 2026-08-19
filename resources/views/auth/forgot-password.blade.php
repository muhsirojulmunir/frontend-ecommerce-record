<x-guest-layout>
    <x-slot name="title">Lupa Password</x-slot>

    <div class="mb-8 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Lupa Password?</h1>
        <p class="text-sm text-text-light mt-1.5">
            Masukkan email Anda, kami akan mengirimkan kode verifikasi 5 digit untuk mengatur ulang password.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full py-3.5 text-sm">
            {{ __('Kirim Kode Verifikasi') }}
        </x-primary-button>
    </form>
</x-guest-layout>
