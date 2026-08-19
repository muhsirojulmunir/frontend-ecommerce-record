@props(['product'])

<div class="bg-white border border-border rounded-sm hover:shadow-md transition-all duration-300 flex flex-col group relative p-4">
    <!-- Badge Status (SOLD / DISKON) -->
    <div class="absolute top-4 left-4 z-10 flex flex-col gap-1">
        @if($product->status === 'sold_out' || $product->stock <= 0)
            <span class="bg-warning text-white text-[10px] font-bold px-2.5 py-1 uppercase tracking-wider rounded-sm shadow-sm">
                SOLD
            </span>
        @elseif($product->hasDiscount())
            <span class="bg-accent text-white text-[10px] font-bold px-2.5 py-1 uppercase tracking-wider rounded-sm shadow-sm">
                DISKON {{ $product->discount_percentage }}%
            </span>
        @endif
    </div>

    <!-- Quick Add to Cart icon on top-right -->
    <div class="absolute top-4 right-4 z-10" x-data="{ adding: false, added: false }">
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

            <!-- We can pre-select the first variant if size/color are required -->
            @if($product->variants->isNotEmpty())
                <input type="hidden" name="product_variant_id" value="{{ $product->variants->first()->id }}">
            @endif

            <button type="submit" :disabled="adding"
                class="h-9 w-9 rounded-full flex items-center justify-center border shadow-sm transition-all duration-200"
                :class="added ? 'bg-emerald-500 border-emerald-500 text-white scale-110' : 'bg-white text-gray-600 hover:text-primary hover:scale-110 border-border'"
                title="Masukkan Keranjang">
                <svg x-show="!added" class="h-4.5 w-4.5" :class="adding ? 'animate-pulse' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <svg x-show="added" x-transition.scale.duration.300ms class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Product Image -->
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden mb-4 aspect-square bg-gray-50 rounded-sm">
        <img src="{{ $product->image_url }}" 
            alt="{{ $product->name }}" 
            class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80';">
    </a>

    <!-- Product Info -->
    <div class="flex-grow flex flex-col justify-between text-center">
        <div>
            <!-- Category name -->
            <p class="text-[10px] uppercase font-bold tracking-wider text-text-light mb-1">
                {{ $product->category->name }}
            </p>
            
            <!-- Product Title -->
            <a href="{{ route('products.show', $product->slug) }}" class="block">
                <h4 class="text-xs font-bold text-text hover:text-primary line-clamp-2 min-h-[32px] leading-tight mb-2">
                    {{ $product->name }}
                </h4>
            </a>

            {{-- Bintang rata-rata. Hanya tampil bila kartunya memang dimuat
                 dengan hitungan ulasan — sebagian tempat memakai komponen ini
                 tanpa itu, dan kartu tidak boleh ikut mati karenanya. --}}
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
            <!-- Price Section -->
            <div class="flex justify-center items-center gap-2 mb-3">
                @if($product->hasDiscount())
                    <span class="text-xs text-text-light line-through">
                        {{ $product->formatted_original_price }}
                    </span>
                    <span class="text-sm font-bold text-accent">
                        {{ $product->formatted_price }}
                    </span>
                @else
                    <span class="text-sm font-bold text-text">
                        {{ $product->formatted_price }}
                    </span>
                @endif
            </div>

            <!-- "BELI SEKARANG" Button (Figma) -->
            @if($product->status === 'sold_out' || $product->stock <= 0)
                <button disabled class="w-full bg-gray-200 text-gray-400 text-xs font-bold py-2.5 rounded-sm uppercase tracking-wide cursor-not-allowed">
                    STOK HABIS
                </button>
            @else
                <a href="{{ route('products.show', $product->slug) }}" 
                    class="block w-full bg-accent hover:bg-accent-light text-white text-xs font-bold py-2.5 rounded-sm transition uppercase tracking-wide text-center">
                    BELI SEKARANG
                </a>
            @endif
        </div>
    </div>
</div>

@once
{{-- Gaya ditanam di sini, bukan lewat @push('styles').
     Kartu produk dirender di dalam <main>, yaitu SETELAH <head> selesai —
     dorongan ke stack di head tidak akan pernah sampai. @once menjaga blok
     ini hanya keluar sekali per halaman meski kartunya puluhan. --}}
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
</style>
@endonce
