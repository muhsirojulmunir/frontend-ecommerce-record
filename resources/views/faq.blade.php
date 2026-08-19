<x-app-layout>
    <x-slot name="title">FAQ</x-slot>

    @php
        $grup = config('faq', []);

        // Grup dibagi ke dua kolom secara berimbang berdasarkan jumlah
        // pertanyaannya, supaya tinggi kedua kolom kira-kira sama.
        $kiri = $kanan = [];
        $bobotKiri = $bobotKanan = 0;

        foreach ($grup as $nama => $isi) {
            // +1 mewakili tinggi judul grupnya sendiri
            $bobot = count($isi) + 1;

            if ($bobotKiri <= $bobotKanan) {
                $kiri[$nama] = $isi;
                $bobotKiri += $bobot;
            } else {
                $kanan[$nama] = $isi;
                $bobotKanan += $bobot;
            }
        }

        $totalTanya = collect($grup)->sum(fn ($i) => count($i));

        // Teks yang dipakai mencocokkan saat pencarian: pertanyaan + jawaban,
        // huruf kecil semua. Disiapkan di sini agar pencarian di sisi Alpine
        // cukup membandingkan data, tanpa perlu membaca kondisi DOM.
        $kunciDari = fn (array $item) => Str::lower($item['t'] . ' ' . strip_tags($item['j']));
        $semuaKunci = collect($grup)->flatten(1)->map($kunciDari)->values()->all();
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

        {{-- ── Judul halaman ── --}}
        <div class="text-center mb-10">
            <h1 class="text-2xl sm:text-3xl font-black text-primary uppercase tracking-wider">
                Pertanyaan Umum
            </h1>
            <div class="h-1 w-20 bg-accent mx-auto mt-3"></div>
            <p class="text-sm text-gray-500 mt-4 max-w-xl mx-auto leading-relaxed">
                {{ $totalTanya }} pertanyaan yang paling sering ditanyakan pembeli.
                Klik pertanyaannya untuk melihat jawaban.
            </p>
        </div>

        {{-- ── Pencarian ── --}}
        <div class="faq-halaman"
             x-data="{
                cari: '',
                semuaKunci: @js($semuaKunci),

                {{-- Kata kunci yang sudah dirapikan; dipakai semua pencocokan --}}
                get kata() { return this.cari.trim().toLowerCase(); },

                cocok(kunci) {
                    return this.kata === '' || kunci.includes(this.kata);
                },
                adaDiGrup(daftarKunci) {
                    return this.kata === '' || daftarKunci.some(k => k.includes(this.kata));
                },
                get adaHasil() {
                    return this.kata === '' || this.semuaKunci.some(k => k.includes(this.kata));
                }
             }">

            <div class="faq-cari-kotak">
                <svg class="faq-cari-ikon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <input type="text" x-model="cari" placeholder="Cari pertanyaan, misalnya: ongkir, ukuran, retur..."
                       class="faq-cari-input">
                <button type="button" x-show="cari.length > 0" @click="cari = ''"
                        class="faq-cari-hapus" aria-label="Hapus pencarian">&times;</button>
            </div>

            {{-- ── Dua kolom berisi grup pertanyaan ── --}}
            <div class="faq-kolom">
                @foreach ([$kiri, $kanan] as $kolom)
                    <div class="faq-kolom-isi">
                        @foreach ($kolom as $namaGrup => $daftar)
                            @php $kunciGrup = array_map($kunciDari, $daftar); @endphp

                            {{-- Judul grup ikut disembunyikan bila semua isinya tersaring --}}
                            <section class="faq-grup" x-show="adaDiGrup(@js($kunciGrup))">

                                <h2 class="faq-grup-judul">{{ $namaGrup }}</h2>

                                @foreach ($daftar as $item)
                                    <div class="faq-item"
                                         x-data="{ buka: false }"
                                         x-show="cocok(@js($kunciDari($item)))">

                                        <button type="button" class="faq-tanya"
                                                @click="buka = !buka"
                                                :aria-expanded="buka ? 'true' : 'false'">

                                            {{-- Panah: menunjuk ke bawah, berputar saat terbuka --}}
                                            <svg class="faq-panah" :class="buka && 'faq-panah-buka'"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2.2" d="M6 9l6 6 6-6" />
                                            </svg>

                                            <span class="faq-tanya-teks">{{ $item['t'] }}</span>
                                        </button>

                                        {{-- Tinggi jawaban diukur dari isinya, jadi animasinya
                                             pas berapa pun panjang teksnya --}}
                                        <div class="faq-jawab"
                                             :style="buka ? 'max-height: ' + $refs.isi.scrollHeight + 'px' : 'max-height: 0px'">
                                            <div class="faq-jawab-isi" x-ref="isi">
                                                {!! $item['j'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </section>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- ── Tidak ada hasil ── --}}
            <div class="faq-kosong" x-show="!adaHasil" x-cloak>
                <p class="faq-kosong-judul">Tidak ada pertanyaan yang cocok</p>
                <p class="faq-kosong-teks">
                    Coba kata kunci lain, atau tanyakan langsung ke kami lewat WhatsApp.
                </p>
            </div>
        </div>

        {{-- ── Ajakan menghubungi ── --}}
        <div class="faq-bantuan">
            <div>
                <h3 class="faq-bantuan-judul">Masih ada yang ingin ditanyakan?</h3>
                <p class="faq-bantuan-teks">
                    Tim kami siap membantu pada jam kerja, Senin sampai Sabtu pukul 09.00–17.00 WIB.
                </p>
            </div>

            <div class="faq-bantuan-tombol">
                <a href="https://wa.me/6281323065554" target="_blank" rel="noopener"
                   class="faq-tombol faq-tombol-wa">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                </a>
                <a href="{{ route('kontak') }}" class="faq-tombol faq-tombol-biasa">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            // Halaman FAQ.

            [x-cloak] { display: none !important; }

            /* ── Kotak pencarian ── */
            .faq-cari-kotak {
                position: relative;
                max-width: 520px;
                margin: 0 auto 40px;
            }
            .faq-cari-ikon {
                position: absolute;
                left: 16px;
                top: 50%;
                transform: translateY(-50%);
                width: 18px;
                height: 18px;
                color: #9ca3af;
                pointer-events: none;
            }
            .faq-cari-input {
                width: 100%;
                padding: 13px 44px 13px 46px;
                font-size: 14px;
                color: #1f2937;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 9999px;
                outline: none;
                transition: border-color 200ms ease, box-shadow 200ms ease;
            }
            .faq-cari-input::placeholder { color: #9ca3af; }
            .faq-cari-input:focus {
                border-color: var(--color-primary, #1B3A6B);
                box-shadow: 0 0 0 3px rgb(27 58 107 / 0.10);
            }
            .faq-cari-hapus {
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                width: 24px;
                height: 24px;
                font-size: 20px;
                line-height: 1;
                color: #9ca3af;
                background: none;
                border: 0;
                cursor: pointer;
                border-radius: 9999px;
            }
            .faq-cari-hapus:hover { color: #374151; background: #f3f4f6; }

            /* ── Susunan dua kolom ── */
            .faq-kolom {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px 48px;
                align-items: start;
            }
            @media (min-width: 1024px) {
                .faq-kolom { grid-template-columns: 1fr 1fr; }
            }
            .faq-kolom-isi > .faq-grup + .faq-grup { margin-top: 40px; }

            /* ── Judul grup ── */
            .faq-grup-judul {
                font-size: 16px;
                font-weight: 800;
                color: var(--color-primary, #1B3A6B);
                padding-bottom: 12px;
                margin-bottom: 4px;
                border-bottom: 2px solid #e5e7eb;
            }

            /* ── Satu pertanyaan ── */
            .faq-item { border-bottom: 1px solid #f1f3f5; }
            .faq-item[hidden] { display: none; }

            .faq-tanya {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                width: 100%;
                padding: 18px 4px;
                text-align: left;
                background: none;
                border: 0;
                cursor: pointer;
                color: #374151;
                transition: color 200ms ease;
            }
            .faq-tanya:hover { color: var(--color-primary, #1B3A6B); }

            .faq-panah {
                flex: 0 0 auto;
                width: 18px;
                height: 18px;
                margin-top: 2px;
                color: #9ca3af;
                transition: transform 300ms ease, color 200ms ease;
            }
            .faq-tanya:hover .faq-panah { color: var(--color-primary, #1B3A6B); }
            .faq-panah-buka {
                transform: rotate(180deg);
                color: var(--color-accent, #DC2626);
            }

            .faq-tanya-teks {
                font-size: 15px;
                font-weight: 500;
                line-height: 1.55;
            }

            /* ── Jawaban yang membuka ke bawah ── */
            .faq-jawab {
                overflow: hidden;
                max-height: 0;
                transition: max-height 350ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            .faq-jawab-isi {
                padding: 0 4px 20px 46px;   /* sejajar dengan teks pertanyaan */
                font-size: 14px;
                line-height: 1.75;
                color: #6b7280;
            }
            .faq-jawab-isi strong { color: #374151; font-weight: 700; }
            .faq-jawab-isi a { color: var(--color-primary, #1B3A6B); text-decoration: underline; }
            .faq-jawab-isi ul { list-style: disc; padding-left: 18px; margin-top: 6px; }
            .faq-jawab-isi li { margin-bottom: 4px; }

            /* ── Tidak ada hasil ── */
            .faq-kosong {
                text-align: center;
                padding: 48px 16px;
            }
            .faq-kosong-judul { font-size: 15px; font-weight: 700; color: #374151; }
            .faq-kosong-teks { font-size: 13px; color: #9ca3af; margin-top: 6px; }

            /* ── Ajakan menghubungi ── */
            .faq-bantuan {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                margin-top: 56px;
                padding: 28px;
                background: var(--color-bg-secondary, #F3F4F6);
                border-radius: 8px;
            }
            @media (min-width: 768px) {
                .faq-bantuan {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
            .faq-bantuan-judul {
                font-size: 16px;
                font-weight: 800;
                color: var(--color-primary, #1B3A6B);
            }
            .faq-bantuan-teks {
                font-size: 13px;
                color: #6b7280;
                margin-top: 4px;
            }
            .faq-bantuan-tombol {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                flex-shrink: 0;
            }
            .faq-tombol {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 11px 20px;
                font-size: 13px;
                font-weight: 700;
                border-radius: 4px;
                transition: background-color 200ms ease, color 200ms ease;
            }
            .faq-tombol-wa { background: #25D366; color: #fff; }
            .faq-tombol-wa:hover { background: #20ba59; }
            .faq-tombol-biasa {
                background: #fff;
                color: var(--color-primary, #1B3A6B);
                border: 1px solid #e5e7eb;
            }
            .faq-tombol-biasa:hover { background: #fff; border-color: var(--color-primary, #1B3A6B); }
        </style>
    @endpush
</x-app-layout>
