@props(['product'])

@php
    // Ambil ends_at dari activeDiscount (jika ada dan ada tanggal berakhir)
    $discountEndsAt = $product->activeDiscount?->ends_at;
    $discountEndsAtTimestamp = $discountEndsAt ? $discountEndsAt->timestamp : null;
@endphp

<div class="bg-white border border-border rounded-sm hover:shadow-md transition-all duration-300 flex flex-col group relative p-3 sm:p-4">
    <!-- Badge Status (SOLD / DISKON) & Countdown di Pojok Kiri Atas -->
    <div class="absolute top-2.5 left-2.5 sm:top-3.5 sm:left-3.5 z-10 flex flex-col gap-1 items-start">
        @if(!$product->isInStock())
            <span class="bg-warning text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider rounded-sm shadow-sm">
                SOLD
            </span>
        @elseif($product->hasDiscount())
            <span class="bg-accent text-white text-[9px] sm:text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider rounded-sm shadow-sm">
                DISKON {{ $product->discount_percentage }}%
            </span>
            {{-- Countdown ditaruh tepat di bawah badge diskon --}}
            @if($discountEndsAtTimestamp)
                <span
                    x-data="discountCountdown({{ $discountEndsAtTimestamp }})"
                    x-init="init()"
                    x-show="!expired"
                    x-cloak
                    class="inline-flex items-center gap-1 bg-black/80 text-white text-[8px] sm:text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-sm shadow-sm leading-none"
                    title="Diskon berakhir {{ $discountEndsAt->translatedFormat('d M Y') }}"
                >
                    <svg class="w-2.5 h-2.5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="display"></span>
                </span>
            @endif
        @endif
    </div>

    <!-- Quick Add to Cart icon di Pojok Kanan Atas -->
    <div class="absolute top-2.5 right-2.5 sm:top-3.5 sm:right-3.5 z-10" x-data="{ adding: false, added: false }">
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
                    .then((r) => r.json())
                    .then((data) => {
                        adding = false;
                        added = true;
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
                class="h-8 w-8 sm:h-9 sm:w-9 rounded-full flex items-center justify-center border shadow-sm transition-all duration-200"
                :class="added ? 'bg-emerald-500 border-emerald-500 text-white scale-110' : 'bg-white text-gray-600 hover:text-primary hover:scale-110 border-border'"
                title="Masukkan Keranjang">
                <svg x-show="!added" class="h-4 w-4 sm:h-4.5 sm:w-4.5" :class="adding ? 'animate-pulse' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <svg x-show="added" x-transition.scale.duration.300ms class="h-4 w-4 sm:h-4.5 sm:w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Product Image -->
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden mb-3 aspect-square bg-gray-50 rounded-sm">
        <img src="{{ $product->image_url }}"
            alt="{{ $product->name }}"
            class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80';">
    </a>

    <!-- Product Info -->
    <div class="flex-grow flex flex-col justify-between text-center">
        <div>
            <!-- Category name -->
            <p class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-text-light mb-1">
                {{ $product->category->name }}
            </p>

            <!-- Product Title -->
            <a href="{{ route('products.show', $product->slug) }}" class="block">
                <h4 class="text-xs font-bold text-text hover:text-primary line-clamp-2 min-h-[32px] leading-tight mb-2">
                    {{ $product->name }}
                </h4>
            </a>

            {{-- Bintang rata-rata --}}
            @isset($product->jumlah_ulasan)
                @if($product->jumlah_ulasan > 0)
                    <a href="{{ route('products.show', $product->slug) }}#ulasan" class="kartu-bintang">
                        @for($b = 1; $b <= 5; $b++)
                            <i class="fa-solid fa-star {{ $b <= round($product->bintang_rata) ? 'kb-isi' : 'kb-kosong' }}"></i>
                        @endfor
                        <span class="kartu-bintang-jml">({{ $product->jumlah_ulasan }})</span>
                    </a>
                @endif
            @endisset
        </div>

        <div>
            <!-- Price Section — nowrap agar tidak stack ke bawah di mobile -->
            <div class="kartu-harga mb-2.5 sm:mb-3">
                @if($product->hasDiscount())
                    <span class="kartu-harga-coret">{{ $product->formatted_original_price }}</span>
                    <span class="kartu-harga-diskon">{{ $product->formatted_price }}</span>
                @else
                    <span class="kartu-harga-normal">{{ $product->formatted_price }}</span>
                @endif
            </div>

            <!-- "BELI SEKARANG" Button -->
            @if(!$product->isInStock())
                <button disabled class="w-full bg-gray-200 text-gray-400 text-xs font-bold py-2 sm:py-2.5 rounded-sm uppercase tracking-wide cursor-not-allowed">
                    STOK HABIS
                </button>
            @else
                <a href="{{ route('products.show', $product->slug) }}"
                    class="block w-full bg-accent hover:bg-accent-light text-white text-xs font-bold py-2 sm:py-2.5 rounded-sm transition uppercase tracking-wide text-center">
                    BELI SEKARANG
                </a>
            @endif
        </div>
    </div>
</div>

@once
<style>
    .kartu-bintang {
        display: inline-flex; align-items: center; gap: 2px;
        margin-bottom: 8px;
        font-size: 10px; letter-spacing: .5px;
        text-decoration: none;
    }
    .kb-isi    { color: #F59E0B; }
    .kb-kosong { color: #dfe3ea; }

    .kartu-bintang-jml {
        margin-left: 3px;
        font-size: 10px; color: #9ca3af; letter-spacing: 0;
    }
    .kartu-bintang:hover .kartu-bintang-jml { color: #4b5563; }

    /* ── Harga kartu produk ──────────────────────────────────────── */
    /* Selalu dalam satu baris; nowrap mencegah wrap ke bawah di mobile. */
    .kartu-harga {
        display: flex;
        align-items: baseline;
        justify-content: center;
        flex-wrap: nowrap;
        gap: 3px;
        line-height: 1.2;
        max-width: 100%;
        overflow: hidden;
    }
    .kartu-harga-coret {
        font-size: 9px;
        color: #9ca3af;
        text-decoration: line-through;
        white-space: nowrap;
        flex-shrink: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }
    .kartu-harga-diskon {
        font-size: 12px;
        font-weight: 700;
        color: var(--color-accent, #e53e3e);
        white-space: nowrap;
        flex-shrink: 0;
    }
    .kartu-harga-normal {
        font-size: 12px;
        font-weight: 700;
        color: #1a202c;
        white-space: nowrap;
    }
    @media (min-width: 380px) {
        .kartu-harga-coret  { font-size: 10px; }
        .kartu-harga-diskon { font-size: 13px; }
        .kartu-harga-normal { font-size: 13px; }
    }
    @media (min-width: 640px) {
        .kartu-harga {
            gap: 6px;
        }
        .kartu-harga-coret {
            font-size: 11px;
        }
        .kartu-harga-diskon {
            font-size: 14px;
        }
        .kartu-harga-normal {
            font-size: 14px;
        }
    }
</style>

<script>
    if (typeof window.discountCountdown === 'undefined') {
        window.discountCountdown = function discountCountdown(endsAtTimestamp) {
            return {
                display: '',
                expired: false,
                _timer: null,

                init() {
                    this._update();
                    this._timer = setInterval(() => this._update(), 1000);
                },

                _update() {
                    const now = Math.floor(Date.now() / 1000);
                    const diff = endsAtTimestamp - now;

                    if (diff <= 0) {
                        this.expired = true;
                        clearInterval(this._timer);
                        return;
                    }

                    const d = Math.floor(diff / 86400);
                    const h = Math.floor((diff % 86400) / 3600);
                    const m = Math.floor((diff % 3600) / 60);
                    const s = diff % 60;

                    const pad = (n) => String(n).padStart(2, '0');

                    if (d > 0) {
                        this.display = d + 'hr ' + pad(h) + ':' + pad(m);
                    } else {
                        this.display = pad(h) + ':' + pad(m) + ':' + pad(s);
                    }
                },
            };
        };
    }
</script>
@endonce