<x-guest-layout>
    <x-slot name="title">Reset Password</x-slot>

    <div class="mb-8 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Atur Ulang Password</h1>
        <p class="text-sm text-text-light mt-1.5">
            Buat password baru untuk akun<br>
            <span class="font-semibold text-text">{{ $email }}</span>
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password"
                autofocus placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required
                autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full py-3.5 text-sm">
            {{ __('Simpan Password Baru') }}
        </x-primary-button>
    </form>
</x-guest-layout>
