<x-guest-layout>
    <x-slot name="title">Konfirmasi Password</x-slot>

    <div class="mb-8 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Konfirmasi Password</h1>
        <p class="text-sm text-text-light mt-1.5">
            Ini area aman. Mohon konfirmasi password Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full py-3.5 text-sm">
            {{ __('Konfirmasi') }}
        </x-primary-button>
    </form>
</x-guest-layout>
