<x-app-layout>
    <x-slot name="title">Lacak Pesanan</x-slot>

    @php
        // Tautan halaman pelacakan tiap kurir
        $situsKurir = [
            'jne'      => ['JNE',            'https://www.jne.co.id/tracking-package'],
            'jnt'      => ['J&T Express',    'https://jet.co.id/track'],
            'j&t'      => ['J&T Express',    'https://jet.co.id/track'],
            'sicepat'  => ['SiCepat',        'https://www.sicepat.com/checkAwb'],
            'anteraja' => ['AnterAja',       'https://anteraja.id/tracking'],
            'pos'      => ['POS Indonesia',  'https://www.posindonesia.co.id/id/tracking'],
            'ninja'    => ['Ninja Xpress',   'https://www.ninjaxpress.co/id-id/tracking'],
        ];

        $statusInfo = [
            'pending'    => ['Menunggu Pembayaran', 'lacak-abu'],
            'processing' => ['Sedang Disiapkan',    'lacak-kuning'],
            'shipped'    => ['Dalam Perjalanan',    'lacak-biru'],
            'completed'  => ['Selesai',             'lacak-hijau'],
            'cancelled'  => ['Dibatalkan',          'lacak-merah'],
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

        <div class="text-center mb-10">
            <h1 class="text-2xl sm:text-3xl font-black text-primary uppercase tracking-wider">Lacak Pesanan</h1>
            <div class="h-1 w-20 bg-accent mx-auto mt-3"></div>
            <p class="text-sm text-gray-500 mt-4 max-w-xl mx-auto leading-relaxed">
                Nomor resi terbit setelah paket kami serahkan ke kurir.
                Salin nomornya, lalu cek posisi paket di situs kurir yang bersangkutan.
            </p>
        </div>

        @if($total === 0)
            {{-- Belum pernah memesan --}}
            <div class="lacak-kosong">
                <i class="fa-regular fa-file-lines lacak-kosong-ikon"></i>
                <p class="lacak-kosong-judul">Belum ada pesanan</p>
                <p class="lacak-kosong-teks">
                    Kamu belum pernah melakukan pemesanan. Yuk lihat koleksi kami dulu.
                </p>
                <a href="{{ route('products.index') }}" class="lacak-tombol lacak-tombol-utama">
                    Lihat Katalog
                </a>
            </div>
        @else
            {{-- ── Pesanan yang sudah punya resi ── --}}
            @if($adaResi->isNotEmpty())
                <h2 class="lacak-judul-bagian">Sudah Dikirim</h2>

                <div class="lacak-daftar">
                    @foreach($adaResi as $order)
                        @php
                            $kunciKurir = strtolower(preg_replace('/[^a-zA-Z&]/', '', $order->courier ?? ''));
                            [$namaKurir, $tautanKurir] = $situsKurir[$kunciKurir] ?? [$order->courier ?: 'Kurir', null];
                            [$labelStatus, $warnaStatus] = $statusInfo[$order->status] ?? ['Diproses', 'lacak-abu'];
                        @endphp

                        <div class="lacak-kartu" x-data="{ tersalin: false }">
                            <div class="lacak-kartu-atas">
                                <div>
                                    <p class="lacak-nomor">{{ $order->order_number }}</p>
                                    <p class="lacak-tanggal">
                                        {{ $order->created_at->translatedFormat('d F Y') }} ·
                                        {{ $order->items->sum('quantity') }} barang ·
                                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                    </p>
                                </div>
                                <span class="lacak-status {{ $warnaStatus }}">{{ $labelStatus }}</span>
                            </div>

                            <div class="lacak-resi">
                                <div class="lacak-resi-kiri">
                                    <p class="lacak-resi-label">{{ $namaKurir }}</p>
                                    <p class="lacak-resi-nomor" x-ref="resi">{{ $order->tracking_number }}</p>
                                </div>

                                <div class="lacak-resi-aksi">
                                    {{-- Salin nomor resi ke papan klip --}}
                                    <button type="button" class="lacak-tombol lacak-tombol-biasa"
                                            @click="
                                                navigator.clipboard.writeText($refs.resi.textContent.trim())
                                                    .then(() => { tersalin = true; setTimeout(() => tersalin = false, 2000) })
                                            ">
                                        <template x-if="!tersalin">
                                            <span><i class="fa-regular fa-copy"></i> Salin Resi</span>
                                        </template>
                                        <template x-if="tersalin">
                                            <span class="lacak-tersalin"><i class="fa-solid fa-check"></i> Tersalin</span>
                                        </template>
                                    </button>

                                    @if($tautanKurir)
                                        <a href="{{ $tautanKurir }}" target="_blank" rel="noopener"
                                           class="lacak-tombol lacak-tombol-utama">
                                            Cek di {{ $namaKurir }}
                                            <i class="fa-solid fa-arrow-up-right-from-square lacak-ikon-kecil"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('orders.show', $order->order_number) }}" class="lacak-detail">
                                Lihat rincian pesanan
                                <i class="fa-solid fa-chevron-right lacak-ikon-kecil"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ── Pesanan yang belum punya resi ── --}}
            @if($belumResi->isNotEmpty())
                <h2 class="lacak-judul-bagian {{ $adaResi->isNotEmpty() ? 'lacak-judul-jarak' : '' }}">
                    Belum Ada Nomor Resi
                </h2>
                <p class="lacak-catatan">
                    Pesanan berikut masih kami siapkan atau menunggu pembayaran.
                    Nomor resi akan muncul di sini begitu paket diserahkan ke kurir.
                </p>

                <div class="lacak-daftar">
                    @foreach($belumResi as $order)
                        @php [$labelStatus, $warnaStatus] = $statusInfo[$order->status] ?? ['Diproses', 'lacak-abu']; @endphp

                        <div class="lacak-kartu lacak-kartu-redup">
                            <div class="lacak-kartu-atas">
                                <div>
                                    <p class="lacak-nomor">{{ $order->order_number }}</p>
                                    <p class="lacak-tanggal">
                                        {{ $order->created_at->translatedFormat('d F Y') }} ·
                                        {{ $order->items->sum('quantity') }} barang ·
                                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                    </p>
                                </div>
                                <span class="lacak-status {{ $warnaStatus }}">{{ $labelStatus }}</span>
                            </div>

                            <a href="{{ route('orders.show', $order->order_number) }}" class="lacak-detail">
                                Lihat rincian pesanan
                                <i class="fa-solid fa-chevron-right lacak-ikon-kecil"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    @push('styles')
        <style>
            /* Halaman lacak pesanan — CSS sendiri agar tidak bergantung build Tailwind */
            .lacak-judul-bagian {
                font-size: 14px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--color-primary, #1B3A6B);
                padding-bottom: 10px;
                margin-bottom: 16px;
                border-bottom: 2px solid #e5e7eb;
            }
            .lacak-judul-jarak { margin-top: 44px; }

            .lacak-catatan {
                font-size: 13px;
                color: #6b7280;
                margin-top: -8px;
                margin-bottom: 16px;
                line-height: 1.7;
            }

            .lacak-daftar { display: flex; flex-direction: column; gap: 14px; }

            .lacak-kartu {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 18px;
            }
            .lacak-kartu-redup { background: #fafafa; }

            .lacak-kartu-atas {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            }
            .lacak-nomor { font-size: 14px; font-weight: 800; color: #1f2937; }
            .lacak-tanggal { font-size: 12px; color: #9ca3af; margin-top: 3px; }

            .lacak-status {
                flex-shrink: 0;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                padding: 5px 11px;
                border-radius: 9999px;
            }
            .lacak-abu    { background: #f3f4f6; color: #4b5563; }
            .lacak-kuning { background: #fef3c7; color: #92400e; }
            .lacak-biru   { background: #dbeafe; color: #1e40af; }
            .lacak-hijau  { background: #d1fae5; color: #065f46; }
            .lacak-merah  { background: #fee2e2; color: #991b1b; }

            /* Blok nomor resi */
            .lacak-resi {
                display: flex;
                flex-direction: column;
                gap: 14px;
                margin-top: 16px;
                padding: 14px 16px;
                background: #f9fafb;
                border: 1px dashed #d1d5db;
                border-radius: 6px;
            }
            @media (min-width: 640px) {
                .lacak-resi {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
            .lacak-resi-label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #9ca3af;
            }
            .lacak-resi-nomor {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 15px;
                font-weight: 700;
                color: #1f2937;
                margin-top: 3px;
                word-break: break-all;
            }
            .lacak-resi-aksi { display: flex; flex-wrap: wrap; gap: 8px; flex-shrink: 0; }

            .lacak-tombol {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 16px;
                font-size: 12px;
                font-weight: 700;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 200ms ease, border-color 200ms ease, color 200ms ease;
            }
            .lacak-tombol-utama {
                background: var(--color-primary, #1B3A6B);
                color: #fff;
                border: 1px solid var(--color-primary, #1B3A6B);
            }
            .lacak-tombol-utama:hover { background: var(--color-primary-light, #2A4F8F); }
            .lacak-tombol-biasa {
                background: #fff;
                color: #4b5563;
                border: 1px solid #d1d5db;
            }
            .lacak-tombol-biasa:hover { border-color: var(--color-primary, #1B3A6B); color: var(--color-primary, #1B3A6B); }
            .lacak-tersalin { color: #059669; }

            .lacak-detail {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 14px;
                font-size: 12px;
                font-weight: 700;
                color: var(--color-primary, #1B3A6B);
            }
            .lacak-detail:hover { text-decoration: underline; }
            .lacak-ikon-kecil { font-size: 9px; }

            /* Keadaan kosong */
            .lacak-kosong {
                text-align: center;
                padding: 64px 20px;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
            }
            .lacak-kosong-ikon { font-size: 40px; color: #d1d5db; display: block; margin-bottom: 16px; }
            .lacak-kosong-judul { font-size: 15px; font-weight: 800; color: #374151; }
            .lacak-kosong-teks { font-size: 13px; color: #9ca3af; margin: 6px 0 20px; }
        </style>
    @endpush
</x-app-layout>
