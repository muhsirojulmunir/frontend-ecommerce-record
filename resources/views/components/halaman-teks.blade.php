@props(['judul', 'ringkas' => null])


<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

    <div class="text-center mb-10">
        <h1 class="text-2xl sm:text-3xl font-black text-primary uppercase tracking-wider">{{ $judul }}</h1>
        <div class="h-1 w-20 bg-accent mx-auto mt-3"></div>
        @if($ringkas)
            <p class="text-sm text-gray-500 mt-4 max-w-xl mx-auto leading-relaxed">{{ $ringkas }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-3">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    <article class="halaman-teks">
        {{ $slot }}
    </article>

    <div class="halaman-teks-bantuan">
        <p>Ada yang kurang jelas? Kami siap membantu.</p>
        <div class="halaman-teks-tombol">
            <a href="https://wa.me/6281323065554" target="_blank" rel="noopener" class="ht-tombol ht-tombol-wa">
                <i class="fab fa-whatsapp"></i> Chat WhatsApp
            </a>
            <a href="{{ route('faq') }}" class="ht-tombol ht-tombol-biasa">Lihat FAQ</a>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .halaman-teks {
                font-size: 14.5px;
                line-height: 1.8;
                color: #4b5563;
            }

            .halaman-teks h2 {
                font-size: 16px;
                font-weight: 800;
                color: var(--color-primary, #1B3A6B);
                margin-top: 36px;
                margin-bottom: 10px;
                padding-bottom: 8px;
                border-bottom: 2px solid #e5e7eb;
            }

            .halaman-teks h2:first-child {
                margin-top: 0;
            }

            .halaman-teks h3 {
                font-size: 14px;
                font-weight: 700;
                color: #374151;
                margin-top: 20px;
                margin-bottom: 6px;
            }

            .halaman-teks p {
                margin-bottom: 14px;
            }

            .halaman-teks strong {
                color: #374151;
                font-weight: 700;
            }

            .halaman-teks a {
                color: var(--color-primary, #1B3A6B);
                text-decoration: underline;
            }

            .halaman-teks ul,
            .halaman-teks ol {
                margin: 0 0 16px 0;
                padding-left: 22px;
            }

            .halaman-teks ul {
                list-style: disc;
            }

            .halaman-teks ol {
                list-style: decimal;
            }

            .halaman-teks li {
                margin-bottom: 7px;
            }

            /* Kotak sorotan untuk hal penting */
            .halaman-teks .penting {
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-radius: 6px;
                padding: 14px 16px;
                margin-bottom: 16px;
                font-size: 13.5px;
                color: #92400e;
            }

            .halaman-teks .penting strong {
                color: #78350f;
            }

            /* Tabel sederhana */
            .halaman-teks table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 18px;
                font-size: 13.5px;
            }

            .halaman-teks th,
            .halaman-teks td {
                border: 1px solid #e5e7eb;
                padding: 9px 12px;
                text-align: left;
            }

            .halaman-teks th {
                background: #f9fafb;
                font-weight: 700;
                color: #374151;
            }

            /* Blok bantuan di bawah halaman */
            .halaman-teks-bantuan {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                margin-top: 48px;
                padding: 24px;
                background: var(--color-bg-secondary, #F3F4F6);
                border-radius: 8px;
                font-size: 14px;
                color: #4b5563;
            }

            @media (min-width: 640px) {
                .halaman-teks-bantuan {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }

            .halaman-teks-tombol {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .ht-tombol {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 18px;
                font-size: 13px;
                font-weight: 700;
                border-radius: 4px;
                transition: background-color 200ms ease, border-color 200ms ease;
            }

            .ht-tombol-wa {
                background: #25D366;
                color: #fff;
            }

            .ht-tombol-wa:hover {
                background: #20ba59;
            }

            .ht-tombol-biasa {
                background: #fff;
                color: var(--color-primary, #1B3A6B);
                border: 1px solid #e5e7eb;
            }

            .ht-tombol-biasa:hover {
                border-color: var(--color-primary, #1B3A6B);
            }
        </style>
    @endpush
@endonce