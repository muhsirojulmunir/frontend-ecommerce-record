<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}Record - LANGKAHPENUHGAYA</title>

        {{-- Favicon / Icon Tab Browser --}}
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-record.png?v=2') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon-record.png?v=2') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/favicon-record.png?v=2') }}">

        {{-- Font Inter dari Google --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        {{-- Ikon Font Awesome --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        {{-- CSS & JS dikompilasi oleh Vite --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- ÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚Â Kenyamanan di layar sentuh ÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚ÂÃƒÂ¢Ã¢â‚¬Â¢Ã‚Â --}}
        <style>
            @media (max-width: 640px) {
                /* Bar atas: dua tautan kecil yang berdempetan. */
                .bar-atas-tautan {
                    display: inline-flex; align-items: center;
                    min-height: 34px; padding: 0 4px;
                }

                /* Tautan footer disusun menurun dalam daftar, jadi yang ditambah cukup ruang atas-bawahnya. */
                footer li > a {
                    display: block;
                    padding: 7px 0;
                    min-height: 34px;
                }

                /* Ikon media sosial di footer: lingkarannya sudah cukup besar, tetapi jarak antar ikon dirapatkan o... */
                footer .rounded-full { min-width: 38px; min-height: 38px; }

                /* Tautan teks kecil yang bertebaran di kepala kartu dan kepala halaman: "Dashboard Saya", "Lihat Se... */
                a.text-xs.font-bold,
                button.text-xs.font-bold,
                a.text-\[11px\].font-bold,
                button.text-\[11px\].font-bold {
                    padding-block: 9px;
                }

                /* Bintang di kartu produk ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â sekaligus tautan ke ulasannya. */
                .kartu-bintang { min-height: 30px; padding: 4px 0; }

                /* Tanda "Belum Dinilai" di riwayat pesanan. */
                .belum-nilai { min-height: 32px; padding: 6px 11px; }

                /* Baris sebaran bintang yang berfungsi sebagai penyaring. */
                .ulasan-baris-klik { min-height: 36px; }

                /* Tautan menuju detail di halaman lacak pesanan. */
                .lacak-detail {
                    display: inline-flex; align-items: center;
                    min-height: 34px;
                }
            }
        </style>

        {{-- Gaya tambahan khusus halaman, dikirim lewat @push('styles') --}}
        @stack('styles')
    </head>
    <body class="font-sans antialiased text-text bg-bg">
        <div class="min-h-screen flex flex-col justify-between">
            <div>
                {{-- Bar pengumuman di paling atas --}}
                <x-top-bar />

                {{-- Navbar / Menu navigasi --}}
                <x-navbar />

                {{-- Notifikasi berhasil (misal setelah tambah ke keranjang) --}}
                @if (session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-sm p-4 text-sm flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 font-bold">ÃƒÆ’Ã¢â‚¬â€</button>
                        </div>
                    </div>
                @endif

                {{-- Notifikasi error --}}
                @if (session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-sm p-4 text-sm flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-700 font-bold">ÃƒÆ’Ã¢â‚¬â€</button>
                        </div>
                    </div>
                @endif

                {{-- Konten utama halaman --}}
                <main class="py-4">
                    {{ $slot }}
                </main>
            </div>

            {{-- Footer di bagian bawah --}}
            <x-footer />
        </div>

        {{-- Toast Notifikasi Pembelian Otomatis (Fake Purchase Toast) --}}
        <x-pembelian-terbaru :pembelian="$pembelianTerbaru ?? []" />

        {{-- Tombol WhatsApp Melayang Admin --}}
        <x-floating-whatsapp nomor="6281323065554" />

        {{-- Skrip dari masing-masing halaman. --}}
        @stack('scripts')
    </body>
</html>
