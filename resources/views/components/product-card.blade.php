@props(['product'])

@php
    $discountEndsAt = $product->activeDiscount?->ends_at;
    $discountEndsAtTimestamp = $discountEndsAt ? $discountEndsAt->timestamp : null;
@endphp

{{--
    Kartu Produk - Layout bersih dan presisi mobile/desktop.
    - Badge DISKON/SOLD: di dalam flow (tidak absolute) tidak bertabrakan
    - Tombol keranjang: absolute di sudut kanan bawah DALAM gambar
    - Countdown: di bawah nama produk, selalu ada ruangnya (min-h)
    - Harga: flex nowrap, tidak pernah wrap ke bawah
--}}
<div class="kartu-produk group">

    {{-- Gambar + Tombol Keranjang (dalam satu blok relatif) --}}
    <div class="kartu-produk-gambar-wrap">
        <a href="{{ route('products.show', $product->slug) }}" class="kartu-produk-gambar-link">
            <img src="{{ $product->image_url }}"
                 alt="{{ $product->name }}"
                 class="kartu-produk-img"
                 loading="lazy"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80';">
        </a>

        {{-- Tombol keranjang absolute di sudut kanan bawah DALAM gambar --}}
        <div class="kartu-produk-cart-wrap" x-data="{ adding: false, added: false }">
            <form @submit.prevent="
                if (adding) return;
                adding = true;
                const form = $event.target;
                const imgEl = form.closest('.kartu-produk')?.querySelector('img');
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
            " action="{{ route('cart.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                @if($product->variants->isNotEmpty())
                    <input type="hidden" name="product_variant_id" value="{{ $product->variants->first()->id }}">
                @endif

                <button type="submit" :disabled="adding"
                    class="kartu-produk-cart-btn"
                    :class="added ? 'kpc-done' : 'kpc-idle'"
                    title="Masukkan Keranjang">
                    <svg x-show="!added" :class="adding ? 'animate-pulse' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <svg x-show="added" x-transition.scale.duration.300ms xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Konten bawah gambar --}}
    <div class="kartu-produk-body">

        {{-- Badge DISKON / SOLD di dalam flow, tidak melayang --}}
        <div class="kartu-badge-wrap">
            @if(!$product->isInStock())
                <span class="kartu-badge kartu-badge-sold">SOLD OUT</span>
            @elseif($product->hasDiscount())
                <span class="kartu-badge kartu-badge-diskon">DISKON {{ $product->discount_percentage }}%</span>
            @else
                <span class="kartu-badge-ph"></span>
            @endif
        </div>

        {{-- Kategori --}}
        <p class="kartu-kategori">{{ $product->category->name }}</p>

        {{-- Nama Produk --}}
        <a href="{{ route('products.show', $product->slug) }}" class="kartu-nama-link">
            <h4 class="kartu-nama">{{ $product->name }}</h4>
        </a>

        {{-- Rating Bintang --}}
        @isset($product->jumlah_ulasan)
            @if($product->jumlah_ulasan > 0)
                <a href="{{ route('products.show', $product->slug) }}#ulasan" class="kartu-bintang">
                    @for($b = 1; $b <= 5; $b++)
                        <i class="fa-solid fa-star {{ $b <= round($product->bintang_rata) ? 'kb-isi' : 'kb-kosong' }}"></i>
                    @endfor
                    <span class="kartu-bintang-jml">({{ $product->jumlah_ulasan }})</span>
                </a>
            @else
                <div class="kartu-bintang-ph"></div>
            @endif
        @else
            <div class="kartu-bintang-ph"></div>
        @endisset

        {{-- Countdown Flash Sale selalu ada ruang min-height --}}
        <div class="kartu-countdown-wrap">
            @if($discountEndsAtTimestamp && $product->hasDiscount())
                <div x-data="discountCountdown({{ $discountEndsAtTimestamp }})"
                     x-init="init()"
                     x-show="!expired"
                     x-cloak
                     class="kartu-countdown"
                     title="Diskon berakhir {{ $discountEndsAt->translatedFormat('d M Y') }}">
                    <i class="fa-regular fa-clock kartu-cd-ikon animate-pulse"></i>
                    <span class="kartu-cd-label">Berakhir:</span>
                    <span class="kartu-cd-waktu font-mono" x-text="display"></span>
                </div>
            @endif
        </div>

        {{-- Harga selalu satu baris --}}
        <div class="kartu-harga">
            @if($product->hasDiscount())
                <span class="kartu-harga-coret">{{ $product->formatted_original_price }}</span>
                <span class="kartu-harga-diskon">{{ $product->formatted_price }}</span>
            @else
                <span class="kartu-harga-normal">{{ $product->formatted_price }}</span>
            @endif
        </div>

        {{-- Tombol CTA --}}
        @if(!$product->isInStock())
            <button disabled class="kartu-btn kartu-btn-habis">STOK HABIS</button>
        @else
            <a href="{{ route('products.show', $product->slug) }}" class="kartu-btn kartu-btn-beli">BELI SEKARANG</a>
        @endif

    </div>
</div>

@once
<style>
/* ════════════════════════════════════════
   KARTU PRODUK - Sistem gaya terpusat
   ════════════════════════════════════════ */

/* Wrapper utama */
.kartu-produk {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: box-shadow .25s ease, transform .2s ease;
}
.kartu-produk:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,.10);
    transform: translateY(-2px);
}

/* Blok gambar */
.kartu-produk-gambar-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #f9fafb;
    flex-shrink: 0;
}
.kartu-produk-gambar-link {
    display: block; width: 100%; height: 100%;
}
.kartu-produk-img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .35s ease;
    display: block;
}
.kartu-produk:hover .kartu-produk-img { transform: scale(1.06); }

/* Tombol keranjang - sudut kanan bawah DALAM blok gambar */
.kartu-produk-cart-wrap {
    position: absolute;
    bottom: 8px;
    right: 8px;
    z-index: 10;
}
.kartu-produk-cart-btn {
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.8);
    background: rgba(255,255,255,.90);
    backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all .2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.kartu-produk-cart-btn svg { width: 15px; height: 15px; }
.kpc-idle { color: #374151; }
.kpc-idle:hover { background: #fff; color: var(--color-primary, #1B3A6B); border-color: var(--color-primary, #1B3A6B); transform: scale(1.12); }
.kpc-done { background: #10b981 !important; border-color: #10b981 !important; color: #fff !important; transform: scale(1.12); }

/* Konten bawah gambar */
.kartu-produk-body {
    padding: 10px 10px 12px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    text-align: center;
}
@media (min-width: 640px) {
    .kartu-produk-body { padding: 12px 14px 14px; }
}

/* Badge DISKON / SOLD - di dalam flow */
.kartu-badge-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 22px;
    margin-bottom: 5px;
}
.kartu-badge {
    display: inline-block;
    font-size: 9px; font-weight: 800;
    letter-spacing: .07em; text-transform: uppercase;
    padding: 3px 8px; border-radius: 3px; line-height: 1.4;
}
.kartu-badge-diskon { background: var(--color-accent, #e53e3e); color: #fff; }
.kartu-badge-sold   { background: #f59e0b; color: #fff; }
.kartu-badge-ph     { display: inline-block; height: 18px; }

/* Kategori */
.kartu-kategori {
    font-size: 9px; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase;
    color: #9ca3af; margin-bottom: 3px;
}

/* Nama produk */
.kartu-nama-link { text-decoration: none; }
.kartu-nama {
    font-size: 11.5px; font-weight: 700;
    color: #111827; line-height: 1.35;
    min-height: 31px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 5px;
    transition: color .15s;
}
.kartu-nama-link:hover .kartu-nama { color: var(--color-primary, #1B3A6B); }
@media (min-width: 640px) { .kartu-nama { font-size: 13px; } }

/* Bintang */
.kartu-bintang {
    display: inline-flex; align-items: center; gap: 2px;
    font-size: 10px; text-decoration: none; margin-bottom: 5px;
}
.kb-isi    { color: #F59E0B; }
.kb-kosong { color: #dde3ea; }
.kartu-bintang-jml { margin-left: 2px; font-size: 10px; color: #9ca3af; }
.kartu-bintang-ph  { height: 16px; margin-bottom: 5px; }

/* Countdown flash sale */
.kartu-countdown-wrap {
    min-height: 26px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 5px;
}
.kartu-countdown {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    border-radius: 99px;
    padding: 3px 10px;
}
.kartu-cd-ikon  { font-size: 9px; color: #f43f5e; }
.kartu-cd-label { font-size: 9px; font-weight: 700; color: #e11d48; text-transform: uppercase; letter-spacing: .06em; }
.kartu-cd-waktu { font-size: 10.5px; font-weight: 900; color: #881337; letter-spacing: -.2px; }

/* Harga */
.kartu-harga {
    display: flex; align-items: baseline;
    justify-content: center; gap: 4px;
    flex-wrap: nowrap; overflow: hidden;
    margin-bottom: 10px;
}
.kartu-harga-coret {
    font-size: 9.5px; color: #9ca3af;
    text-decoration: line-through;
    white-space: nowrap; flex-shrink: 1;
    min-width: 0; overflow: hidden; text-overflow: ellipsis;
}
.kartu-harga-diskon {
    font-size: 12px; font-weight: 900;
    color: var(--color-accent, #e53e3e);
    white-space: nowrap; flex-shrink: 0;
}
.kartu-harga-normal {
    font-size: 13px; font-weight: 900;
    color: #111827; white-space: nowrap;
}
@media (min-width: 380px) {
    .kartu-harga-coret  { font-size: 10px; }
    .kartu-harga-diskon { font-size: 13px; }
    .kartu-harga-normal { font-size: 13px; }
}
@media (min-width: 640px) {
    .kartu-harga         { gap: 6px; margin-bottom: 12px; }
    .kartu-harga-coret   { font-size: 11px; }
    .kartu-harga-diskon  { font-size: 14.5px; }
    .kartu-harga-normal  { font-size: 14.5px; }
}

/* Tombol CTA */
.kartu-btn {
    display: block; width: 100%;
    font-size: 10.5px; font-weight: 800;
    letter-spacing: .07em; text-transform: uppercase;
    text-align: center; text-decoration: none;
    padding: 9px 4px; border-radius: 3px;
    border: none; cursor: pointer;
    transition: background-color .18s ease;
    margin-top: auto;
}
.kartu-btn-beli  { background: var(--color-accent, #e53e3e); color: #fff; }
.kartu-btn-beli:hover { background: #c53030; }
.kartu-btn-habis { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
@media (min-width: 640px) {
    .kartu-btn { font-size: 11.5px; padding: 10px 4px; }
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
                const p = n => String(n).padStart(2,'0');
                this.display = d > 0 ? d + 'hr ' + p(h) + ':' + p(m) : p(h) + ':' + p(m) + ':' + p(s);
            },
        };
    };
}
</script>
@endonce