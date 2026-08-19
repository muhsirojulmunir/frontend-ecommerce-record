<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>

    <div class="mb-6 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Verifikasi Email Anda</h1>
        <p class="text-sm text-text-light mt-1.5 leading-relaxed">
            Terima kasih sudah mendaftar! Sebelum mulai, silakan verifikasi email Anda dengan mengklik tautan yang
            baru saja kami kirim. Belum menerima email? Kami akan mengirimkan yang baru.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-sm p-3 text-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Tautan verifikasi baru sudah dikirim ke email yang Anda daftarkan.</span>
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="text-xs">
                {{ __('Kirim Ulang Email Verifikasi') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-text-light hover:text-primary underline underline-offset-2">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
