<nav class="bg-white border-b border-border sticky top-0 z-50 shadow-sm"
    x-data="{ mobileMenuOpen: false, cartCount: {{ (int) $cartCount }}, cartBump: false }"
    @cart-count-updated.window="cartCount = $event.detail.count"
    @cart-bump.window="cartBump = true; setTimeout(() => cartBump = false, 400)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Jarak navbar ditulis sebagai CSS sendiri, bukan kelas Tailwind. --}}
        <div class="bar-utama">
            <a href="{{ route('home') }}" class="bar-logo" aria-label="Beranda Record">
                <x-logo-brand ukuran="sedang" varian="terang" />
            </a>

            {{-- Menu navigasi desktop --}}
            <div class="bar-menu">
                <a href="{{ route('home') }}"
                    class="text-sm font-semibold text-gray-700 hover:text-primary transition">Beranda</a>
                <a href="{{ route('about') }}"
                    class="text-sm font-semibold text-gray-700 hover:text-primary transition">About</a>
                <a href="{{ route('products.index') }}"
                    class="text-sm font-semibold text-gray-700 hover:text-primary transition">Catalog</a>
                <a href="{{ route('faq') }}"
                    class="text-sm font-semibold text-gray-700 hover:text-primary transition">FAQ</a>
                <a href="{{ route('kontak') }}"
                    class="text-sm font-semibold text-gray-700 hover:text-primary transition">Kontak</a>
            </div>

            {{-- Bagian kanan: pencarian, profil, keranjang --}}
            <div class="bar-kanan">
                {{-- Kotak pencarian produk --}}
                <form action="{{ route('products.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Cari sepatu..." value="{{ request('search') }}"
                        class="bar-cari bg-bg-secondary text-sm rounded-full pl-4 pr-10 py-2 border-none focus:ring-2 focus:ring-primary transition-all duration-300">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                                                {{-- Dropdown profil pengguna --}}
                @auth
                    @php
                        $unpaidCount = Auth::user()->unpaidOrdersCount();
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        @php $hasDelivered = Auth::user()->hasRecentlyDeliveredOrders(); @endphp
                        <button @click="open = !open" class="relative flex items-center text-gray-600 hover:text-primary transition" style="background: none; border: none; cursor: pointer; padding: 4px;">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            @if($unpaidCount > 0)
                                <span style="position: absolute; top: -1px; right: -2px; background-color: #dc2626; border-radius: 9999px; width: 9px; height: 9px; display: block; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Ada pesanan belum dibayar"></span>
                            @elseif($hasDelivered)
                                <span style="position: absolute; top: -1px; right: -2px; background-color: #16a34a; border-radius: 9999px; width: 9px; height: 9px; display: block; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Ada pesanan yang sudah sampai!"></span>
                            @endif
                        </button>
                        <div x-show="open" @click.away="open = false"
                            style="position: absolute; right: 0; margin-top: 8px; width: 220px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); padding: 6px; border: 1px solid #e5e7eb; z-index: 9999;"
                            x-transition>
                            
                            {{-- Header User Info --}}
                            <div style="padding: 8px 12px 10px; border-bottom: 1px solid #f3f4f6;">
                                <p style="font-size: 11px; color: #6b7280; margin: 0;">Masuk sebagai</p>
                                <p style="font-size: 13px; font-weight: 700; color: #1B3A6B; margin: 2px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->name }}</p>
                            </div>

                            {{-- Menu Akun Saya --}}
                            <a href="{{ route('dashboard') }}"
                                style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; font-size: 13px; font-weight: 600; color: #374151; border-radius: 6px; text-decoration: none; transition: background 0.15s; margin-top: 4px;"
                                onmouseover="this.style.backgroundColor='#f8fafc'"
                                onmouseout="this.style.backgroundColor='transparent'">
                                <span style="display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                                    <i class="fa-solid fa-user-circle" style="color: #1B3A6B; font-size: 14px;"></i>
                                    Akun Saya
                                </span>
                                <span style="display: flex; gap: 4px; align-items: center;">
                                    @if($unpaidCount > 0)
                                        <span style="background-color: #dc2626; border-radius: 9999px; width: 8px; height: 8px; display: inline-block; flex-shrink: 0;" title="Belum bayar"></span>
                                    @endif
                                    @if($hasDelivered)
                                        <span style="background-color: #16a34a; border-radius: 9999px; width: 8px; height: 8px; display: inline-block; flex-shrink: 0;" title="Pesanan sampai!"></span>
                                    @endif
                                </span>
                            </a>

                            {{-- Divider --}}
                            <div style="height: 1px; background-color: #f3f4f6; margin: 4px 0;"></div>

                            {{-- Logout --}}
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 10px 12px; font-size: 13px; font-weight: 600; color: #dc2626; border-radius: 6px; background: none; border: none; text-align: left; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.backgroundColor='#fef2f2'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fa-solid fa-right-from-bracket" style="font-size: 13px;"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-primary transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                @endauth

                {{-- Ikon keranjang belanja --}}
                <a href="{{ route('cart.index') }}" data-cart-icon class="relative text-gray-600 hover:text-primary transition">
                    <svg class="h-6 w-6 transition-transform" :class="cartBump ? 'scale-125' : 'scale-100'"
                        style="transition-duration: 300ms; transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-cloak x-show="cartCount > 0" x-transition.scale
                        :class="cartBump ? 'scale-125' : 'scale-100'"
                        style="transition: transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1);"
                        class="absolute -top-2 -right-2 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white"
                        x-text="cartCount">
                    </span>
                </a>

                {{-- Tombol CTA di pojok kanan navbar --}}
                <a href="{{ route('products.index') }}"
                    class="bar-tombol bg-primary hover:bg-primary-light text-white rounded-sm transition shadow-sm">
                    Buy Online
                </a>
                <a href="https://wa.me/6281323065554?text=Halo%20Record,%20saya%20ingin%20bertanya%20tentang%20sepatu..."
                    target="_blank"
                    class="bar-tombol bar-tombol-wa hover:bg-[#20ba59] text-white rounded-sm transition shadow-sm">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                    {{-- Kata kedua hanya muncul bila layarnya memang lega --}}
                    <span class="bar-tombol-tambahan">Shopping</span>
                </a>
            </div>

            {{-- Bagian kanan pada layar sempit: keranjang, dua ajakan, menu --}}
            <div class="bar-mobile flex items-center lg:hidden">
                {{-- Ikon keranjang di tampilan mobile --}}
                <a href="{{ route('cart.index') }}" data-cart-icon class="relative text-gray-600 hover:text-primary transition">
                    <svg class="h-6 w-6 transition-transform" :class="cartBump ? 'scale-125' : 'scale-100'"
                        style="transition-duration: 300ms; transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-cloak x-show="cartCount > 0" x-transition.scale
                        :class="cartBump ? 'scale-125' : 'scale-100'"
                        style="transition: transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1);"
                        class="absolute -top-2 -right-2 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white"
                        x-text="cartCount">
                    </span>
                </a>

                {{-- ── Dua ajakan utama, tampil langsung di layar sempit ── --}}
                <a href="{{ route('products.index') }}"
                   class="bar-aksi bar-aksi-beli"
                   aria-label="Buy Online">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="bar-aksi-label">Buy Online</span>
                </a>

                <a href="https://wa.me/6281323065554?text=Halo%20Record,%20saya%20ingin%20bertanya%20tentang%20sepatu..."
                   target="_blank"
                   class="bar-aksi bar-aksi-wa"
                   aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                    <span class="bar-aksi-label">WhatsApp</span>
                </a>

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="text-gray-600 hover:text-primary focus:outline-none"
                    aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu dropdown mobile --}}
    <div x-show="mobileMenuOpen" class="lg:hidden bg-white border-t border-border px-4 py-4 space-y-3" x-transition>
        {{-- Pencarian di menu mobile --}}
        <form action="{{ route('products.index') }}" method="GET" class="relative">
            <input type="text" name="search" placeholder="Cari sepatu..." value="{{ request('search') }}"
                class="bg-bg-secondary text-sm rounded-full pl-4 pr-10 py-2 border-none focus:ring-2 focus:ring-primary w-full">
            <button type="submit" class="absolute right-3 top-2.5 text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>

        <div class="space-y-1 py-2">
            <a href="{{ route('home') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-bg-secondary hover:text-primary">Beranda</a>
            <a href="{{ route('about') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-bg-secondary hover:text-primary">About</a>
            <a href="{{ route('products.index') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-bg-secondary hover:text-primary">Catalog</a>
            <a href="{{ route('faq') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-bg-secondary hover:text-primary">FAQ</a>
            <a href="{{ route('kontak') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-bg-secondary hover:text-primary">Kontak</a>
        </div>

        <div class="pt-4 pb-2 border-t border-border space-y-2">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="block text-center text-sm font-semibold text-gray-700 py-2 hover:bg-bg-secondary rounded">Dashboard
                    Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-center text-sm font-semibold text-gray-700 py-2 hover:bg-bg-secondary rounded">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="block text-center text-sm font-semibold text-gray-700 py-2 hover:bg-bg-secondary rounded">Login</a>
                <a href="{{ route('register') }}"
                    class="block text-center text-sm font-semibold text-gray-700 py-2 hover:bg-bg-secondary rounded">Daftar</a>
            @endauth
        </div>

        {{-- "Buy Online" dan "WhatsApp" TIDAK lagi di sini: keduanya sudah --}}
    </div>
</nav>

@once
{{-- Tata letak navbar ditulis sendiri, tidak memakai kelas jarak Tailwind. --}}
<style>
    .bar-utama {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        height: 80px;
    }

    /* Tak satu pun bagian boleh menyusut sampai isinya patah baris. */
    .bar-logo,
    .bar-menu,
    .bar-kanan { flex-shrink: 0; }

    .bar-logo { display: flex; align-items: center; }

    /* ── Menu tengah ── */
    .bar-menu {
        display: none;
        align-items: center;
        gap: 26px;
    }
    @media (min-width: 1024px) { .bar-menu { display: flex; } }

    .bar-menu > a {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
        transition: color 180ms ease;
    }
    .bar-menu > a:hover { color: var(--color-primary, #1B3A6B); }

    /* ── Bagian kanan ── */
    .bar-kanan {
        display: none;
        align-items: center;
        gap: 12px;
    }
    @media (min-width: 768px) { .bar-kanan { display: flex; } }

    .bar-cari { width: 150px; }

    /* ── Tombol ajakan ── */
    .bar-tombol {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 15px;
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .bar-tombol-wa { background: #25D366; }

    /* Kata kedua disembunyikan sampai layarnya benar-benar lega. */
    .bar-tombol-tambahan { display: none; }

    /* Jaraknya diatur di sini, bukan lewat kelas gap-4 Tailwind. */
    .bar-mobile { gap: 8px; }
    @media (min-width: 400px) { .bar-mobile { gap: 10px; } }
    @media (min-width: 640px) { .bar-mobile { gap: 14px; } }

    /* ══════════ Ajakan di layar sempit ══════════ Kembaran ringkas dari .bar-tombol, khusus untuk bar ... */
    .bar-aksi {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 3px;
        color: #fff;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
        transition: background-color 160ms ease;
    }
    .bar-aksi i { font-size: 13px; }

    .bar-aksi-beli    { background: var(--color-primary, #1B3A6B); }
    .bar-aksi-beli:hover  { background: #2C5AA0; }
    .bar-aksi-wa      { background: #25D366; }
    .bar-aksi-wa:hover    { background: #20ba59; }

    /* Tulisannya baru muncul kalau barisnya memang muat. */
    .bar-aksi-label { display: none; }

    @media (min-width: 520px) {
        .bar-aksi { padding: 8px 12px; }
        .bar-aksi-label { display: inline; }
        .bar-aksi i { font-size: 13px; }
    }

    @media (max-width: 519px) {
        .bar-aksi { padding: 8px 10px; }
        .bar-aksi i { font-size: 15px; }
    }

    /* Mulai 768px, .bar-kanan tampil dan sudah memuat "Buy Online" serta "WhatsApp" versi lebarnya. */
    @media (min-width: 768px) {
        .bar-aksi { display: none; }
    }

    /* ── Layar lega: semua jarak dilonggarkan ── */
    @media (min-width: 1280px) {
        .bar-utama { gap: 32px; }
        .bar-menu  { gap: 32px; }
        .bar-kanan { gap: 16px; }
        .bar-cari  { width: 224px; }
        .bar-tombol { padding: 10px 18px; }
        .bar-tombol-tambahan { display: inline; }
    }

    @media (min-width: 1536px) {
        .bar-cari { width: 260px; }
    }
</style>
@endonce

