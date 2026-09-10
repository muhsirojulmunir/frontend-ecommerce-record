@props(['product'])

@php
    $discountEndsAt = $product->activeDiscount?->ends_at;
    $discountEndsAtTimestamp = $discountEndsAt ? $discountEndsAt->timestamp : null;
@endphp

<div class="bg-white border border-border rounded-sm hover:shadow-md transition-all duration-300 flex flex-col group">

    {{-- ── Blok Gambar (relative) ────────────────────────────────── --}}
    {{-- Badge KIRI-ATAS, Keranjang KANAN-ATAS = dua sisi berlawanan, tidak tabrakan --}}
    <div class="relative overflow-hidden bg-gray-50 aspect-square rounded-sm flex-shrink-0">

        {{-- Gambar --}}
        <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
            <img src="{{ $product->image_url }}"
                 alt="{{ $product->name }}"
                 class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                 loading="lazy"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80';">
        </a>

        {{-- Badge DISKON/SOLD — absolute kiri atas --}}
        @if(!$product->isInStock())
            <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start">
                <span class="bg-warning text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider rounded-sm shadow">SOLD</span>
            </div>
        @elseif($product->hasDiscount())
            <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start">
                <span class="bg-accent text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider rounded-sm shadow">DISKON {{ $product->discount_percentage }}%</span>
                {{-- Countdown tepat di bawah badge DISKON --}}
                @if($discountEndsAtTimestamp)
                    <div x-data="discountCountdown({{ $discountEndsAtTimestamp }})"
                         x-init="init()"
                         x-show="!expired"
                         x-cloak
                         class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-sm border border-rose-300 text-rose-700 text-[8px] sm:text-[9px] font-bold px-1.5 py-0.5 rounded-sm shadow"
                         title="Diskon berakhir {{ $discountEndsAt->translatedFormat('d M Y') }}">
                        <i class="fa-regular fa-clock text-rose-500 animate-pulse"></i>
                        <span class="font-mono font-black" x-text="display"></span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Tombol Keranjang — absolute kanan atas --}}
        <div class="absolute top-2 right-2 z-10" x-data="{ adding: false, added: false }">
            <form
                @submit.prevent="
                    if (adding) return;
                    adding = true;
                    const form = $event.target;
                    const imgEl = form.closest('.group')?.querySelector('img');
                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: new FormData(form),
                    })
                    .then(r => r.json())
                    .then(data => {
                        adding = false; added = true;
                        if (imgEl) window.flyToCart(imgEl);
                        window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                        setTimeout(() => (added = false), 1500);
                    })
                    .catch(() => { adding = false; });
                "
                action="{{ route('cart.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                @if($product->variants->isNotEmpty())
                    <input type="hidden" name="product_variant_id" value="{{ $product->variants->first()->id }}">
                @endif

                <button type="submit" :disabled="adding"
                    class="h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center shadow-md transition-all duration-200 border"
                    :class="added ? 'bg-emerald-500 border-emerald-500 text-white scale-110' : 'bg-white/90 backdrop-blur-sm text-gray-600 hover:text-primary hover:scale-110 border-white/80'"
                    title="Masukkan Keranjang">
                    <svg x-show="!added" class="h-3.5 w-3.5 sm:h-4 sm:w-4" :class="adding ? 'animate-pulse' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <svg x-show="added" x-transition.scale.duration.300ms class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </form>
        </div>

    </div>{{-- /blok gambar --}}

    {{-- ── Info Produk ─────────────────────────────────────────────── --}}
    <div class="flex-grow flex flex-col p-2 sm:p-3 text-center">

        {{-- Kategori --}}
        <p class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-text-light mb-0.5">
            {{ $product->category->name }}
        </p>

        {{-- Nama --}}
        <a href="{{ route('products.show', $product->slug) }}" class="block mb-1.5">
            <h4 class="text-[11px] sm:text-xs font-bold text-text hover:text-primary line-clamp-2 leading-snug" style="min-height: 2.6em;">
                {{ $product->name }}
            </h4>
        </a>

        {{-- Bintang --}}
        @isset($product->jumlah_ulasan)
            @if($product->jumlah_ulasan > 0)
                <a href="{{ route('products.show', $product->slug) }}#ulasan" class="kartu-bintang mb-1.5">
                    @for($b = 1; $b <= 5; $b++)
                        <i class="fa-solid fa-star {{ $b <= round($product->bintang_rata) ? 'kb-isi' : 'kb-kosong' }}"></i>
                    @endfor
                    <span class="kartu-bintang-jml">({{ $product->jumlah_ulasan }})</span>
                </a>
            @endif
        @endisset

        {{-- Harga — nowrap, tidak pernah wrap --}}
        <div class="kartu-harga mt-auto mb-2">
            @if($product->hasDiscount())
                <span class="kartu-harga-coret">{{ $product->formatted_original_price }}</span>
                <span class="kartu-harga-diskon">{{ $product->formatted_price }}</span>
            @else
                <span class="kartu-harga-normal">{{ $product->formatted_price }}</span>
            @endif
        </div>

        {{-- Tombol Beli --}}
        @if(!$product->isInStock())
            <button disabled class="w-full bg-gray-200 text-gray-400 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 rounded-sm uppercase tracking-wide cursor-not-allowed">
                STOK HABIS
            </button>
        @else
            <a href="{{ route('products.show', $product->slug) }}"
               class="block w-full bg-accent hover:bg-accent-light text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 rounded-sm transition uppercase tracking-wide text-center">
                BELI SEKARANG
            </a>
        @endif

    </div>

</div>

@once
<style>
    .kartu-bintang {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: 10px; text-decoration: none;
    }
    .kb-isi    { color: #F59E0B; }
    .kb-kosong { color: #dfe3ea; }
    .kartu-bintang-jml { margin-left: 2px; font-size: 10px; color: #9ca3af; }

    /* Harga — selalu satu baris */
    .kartu-harga {
        display: flex; align-items: baseline;
        justify-content: center; flex-wrap: nowrap;
        gap: 3px; overflow: hidden;
    }
    .kartu-harga-coret {
        font-size: 9px; color: #9ca3af;
        text-decoration: line-through;
        white-space: nowrap; flex-shrink: 1;
        overflow: hidden; text-overflow: ellipsis; min-width: 0;
    }
    .kartu-harga-diskon {
        font-size: 12px; font-weight: 900;
        color: var(--color-accent, #e53e3e);
        white-space: nowrap; flex-shrink: 0;
    }
    .kartu-harga-normal {
        font-size: 12px; font-weight: 900;
        color: #1a202c; white-space: nowrap;
    }
    @media (min-width: 380px) {
        .kartu-harga-coret  { font-size: 10px; }
        .kartu-harga-diskon { font-size: 13px; }
        .kartu-harga-normal { font-size: 13px; }
    }
    @media (min-width: 640px) {
        .kartu-harga         { gap: 5px; }
        .kartu-harga-coret   { font-size: 11px; }
        .kartu-harga-diskon  { font-size: 14px; }
        .kartu-harga-normal  { font-size: 14px; }
    }
</style>

<script>
if (typeof window.discountCountdown === 'undefined') {
    window.discountCountdown = function(endsAtTimestamp) {
        return {
            display: '', expired: false, _timer: null,
            init() { this._update(); this._timer = setInterval(() => this._update(), 1000); },
            _update() {
                const diff = endsAtTimestamp - Math.floor(Date.now() / 1000);
                if (diff <= 0) { this.expired = true; clearInterval(this._timer); return; }
                const d = Math.floor(diff / 86400);
                const h = Math.floor((diff % 86400) / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                const p = n => String(n).padStart(2, '0');
                this.display = d > 0 ? d + 'hr ' + p(h) + ':' + p(m) : p(h) + ':' + p(m) + ':' + p(s);
            },
        };
    };
}
</script>
@endonce