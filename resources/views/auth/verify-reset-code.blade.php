<x-guest-layout>
    <x-slot name="title">Verifikasi Kode</x-slot>

    <div class="mb-8 text-center">
        <h1 class="text-xl font-black uppercase tracking-tight text-text">Verifikasi Kode</h1>
        <p class="text-sm text-text-light mt-1.5 leading-relaxed">
            Kami telah mengirimkan kode verifikasi 5 digit ke<br>
            <span class="font-semibold text-text">{{ $email }}</span>
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 justify-center text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.verify.store') }}"
        x-data="{
            digits: ['', '', '', '', ''],
            get code() { return this.digits.join('') },
            onInput(i, e) {
                const v = e.target.value.replace(/\D/g, '').slice(-1);
                this.digits[i] = v;
                e.target.value = v;
                if (v && i < 4) this.$refs['digit' + (i + 1)].focus();
            },
            onKeydown(i, e) {
                if (e.key === 'Backspace' && !this.digits[i] && i > 0) {
                    this.$refs['digit' + (i - 1)].focus();
                }
            },
            onPaste(e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 5);
                text.split('').forEach((d, i) => {
                    this.digits[i] = d;
                    this.$refs['digit' + i].value = d;
                });
                if (text.length) this.$refs['digit' + Math.min(text.length, 5) - 1].focus();
            }
        }">
        @csrf
        <input type="hidden" name="code" :value="code">

        {{-- Kotak input kode 5 digit --}}
        <div class="flex justify-center gap-3 sm:gap-4" @paste="onPaste">
            @for ($i = 0; $i < 5; $i++)
                <input x-ref="digit{{ $i }}" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                    autocomplete="one-time-code" @if ($i === 0) autofocus @endif
                    @input="onInput({{ $i }}, $event)" @keydown="onKeydown({{ $i }}, $event)"
                    class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold text-primary bg-bg-secondary border border-border rounded-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition">
            @endfor
        </div>

        <x-input-error :messages="$errors->get('code')" class="justify-center text-center mt-3" />

        <x-primary-button class="w-full py-3.5 text-sm mt-8">
            {{ __('Verifikasi Kode') }}
        </x-primary-button>
    </form>

    <form method="POST" action="{{ route('password.verify.resend') }}" class="mt-6 text-center">
        @csrf
        <p class="text-sm text-text-light">
            Tidak menerima kode?
            <button type="submit"
                class="font-semibold text-primary hover:text-primary-light underline underline-offset-2">
                Kirim ulang
            </button>
        </p>
    </form>
</x-guest-layout>
