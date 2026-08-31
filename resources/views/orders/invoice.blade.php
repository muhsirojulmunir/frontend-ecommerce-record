<x-app-layout>
    <x-slot name="title">Invoice {{ $order->invoice_number }}</x-slot>

    @php
        $alamat = $order->shipping_address ?? [];
        $subtotal = $order->items->sum(fn ($i) => $i->price * $i->quantity);
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

        {{-- Tindakan: tidak ikut tercetak --}}
        <div class="inv-aksi">
            <a href="{{ route('orders.show', $order->order_number) }}" class="inv-tautan">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Pesanan
            </a>
            <a href="{{ route('orders.invoice.download', $order->order_number) }}" class="inv-tombol" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:#1B3A6B;color:#fff;"><i class="fa-solid fa-file-arrow-down"></i> Unduh PDF</a>
            <button type="button" onclick="window.print()" class="inv-tombol">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>

        <div class="inv-kertas">

            {{-- Kepala --}}
            <div class="inv-kepala">
                <div>
                    <p class="inv-merek">RECORD</p>
                    <p class="inv-slogan">LANGKAHPENUHGAYA</p>
                    <p class="inv-alamat-toko">
                        Jl. Kyai Tambak Deres No.32, Bulak<br>
                        Surabaya, Jawa Timur 60124<br>
                        WA +62 813-2306-5554
                    </p>
                </div>

                <div class="inv-kepala-kanan">
                    <p class="inv-judul">INVOICE</p>
                    <p class="inv-nomor">{{ $order->invoice_number }}</p>
                    <span class="inv-lunas">LUNAS</span>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="inv-ringkas">
                <div>
                    <p class="inv-label">Ditagihkan Kepada</p>
                    <p class="inv-nama">{{ $alamat['recipient_name'] ?? $order->user->name }}</p>
                    <p class="inv-teks">
                        {{ $alamat['phone'] ?? $order->user->phone }}<br>
                        {{ $order->user->email }}
                    </p>
                </div>

                <div>
                    <p class="inv-label">Dikirim Ke</p>
                    <p class="inv-teks">
                        {{ $alamat['address_line'] ?? '-' }}<br>
                        {{ $alamat['city'] ?? '' }}{{ !empty($alamat['province']) ? ', ' . $alamat['province'] : '' }}
                        {{ $alamat['postal_code'] ?? '' }}
                    </p>
                </div>

                <div>
                    <p class="inv-label">Rincian</p>
                    <table class="inv-meta">
                        <tr><td>No. Pesanan</td><td>{{ $order->order_number }}</td></tr>
                        <tr><td>Tanggal Invoice</td><td>{{ $order->invoice_issued_at?->translatedFormat('d F Y') }}</td></tr>
                        <tr><td>Metode Bayar</td><td>{{ $order->payment_method }}</td></tr>
                        <tr><td>Kurir</td><td>{{ $order->courier ?: '-' }}</td></tr>
                        @if($order->tracking_number)
                            <tr><td>No. Resi</td><td>{{ $order->tracking_number }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Daftar barang --}}
            <table class="inv-barang">
                <thead>
                    <tr>
                        <th class="inv-kiri">Produk</th>
                        <th class="inv-tengah">Jumlah</th>
                        <th class="inv-kanan">Harga</th>
                        <th class="inv-kanan">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="inv-kiri">
                                <span class="inv-produk">{{ $item->product_name }}</span>
                                @if($item->variant_info)
                                    <span class="inv-varian">{{ $item->variant_info }}</span>
                                @endif
                            </td>
                            <td class="inv-tengah">{{ $item->quantity }}</td>
                            <td class="inv-kanan">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="inv-kanan">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Total --}}
            <div class="inv-total-bungkus">
                <table class="inv-total">
                    <tr>
                        <td>Subtotal</td>
                        <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Ongkos Kirim</td>
                        <td>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="inv-total-akhir">
                        <td>Total Dibayar</td>
                        <td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <p class="inv-catatan">
                Invoice ini diterbitkan otomatis oleh sistem dan sah tanpa tanda tangan.
                Terima kasih telah berbelanja di RECORD.
            </p>
        </div>
    </div>

    @push('styles')
        <style>
            .inv-aksi {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 18px;
            }
            .inv-tautan {
                font-size: 12px;
                font-weight: 700;
                color: #6b7280;
            }
            .inv-tautan:hover { color: var(--color-primary, #1B3A6B); }
            .inv-tombol {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 10px 18px;
                font-size: 12px;
                font-weight: 700;
                color: #fff;
                background: var(--color-primary, #1B3A6B);
                border: 0;
                border-radius: 4px;
                cursor: pointer;
            }
            .inv-tombol:hover { background: var(--color-primary-light, #2A4F8F); }

            .inv-kertas {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 36px;
                color: #374151;
                font-size: 13px;
            }

            .inv-kepala {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                justify-content: space-between;
                padding-bottom: 22px;
                border-bottom: 2px solid var(--color-primary, #1B3A6B);
            }
            .inv-merek {
                font-size: 26px;
                font-weight: 900;
                letter-spacing: -0.02em;
                color: var(--color-primary, #1B3A6B);
            }
            .inv-slogan { font-size: 11px; font-style: italic; color: #9ca3af; margin-top: 1px; }
            .inv-alamat-toko { font-size: 11px; line-height: 1.6; color: #6b7280; margin-top: 10px; }

            .inv-kepala-kanan { text-align: right; }
            .inv-judul {
                font-size: 22px;
                font-weight: 900;
                letter-spacing: 0.08em;
                color: #111827;
            }
            .inv-nomor {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 13px;
                font-weight: 700;
                color: #4b5563;
                margin-top: 3px;
            }
            .inv-lunas {
                display: inline-block;
                margin-top: 9px;
                padding: 4px 12px;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.08em;
                color: #065f46;
                background: #d1fae5;
                border: 1px solid #6ee7b7;
                border-radius: 4px;
            }

            .inv-ringkas {
                display: grid;
                grid-template-columns: 1fr;
                gap: 22px;
                padding: 22px 0;
                border-bottom: 1px solid #e5e7eb;
            }
            @media (min-width: 640px) {
                .inv-ringkas { grid-template-columns: repeat(3, 1fr); }
            }
            .inv-label {
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: #9ca3af;
                margin-bottom: 5px;
            }
            .inv-nama { font-size: 13px; font-weight: 700; color: #111827; }
            .inv-teks { font-size: 12px; line-height: 1.65; color: #6b7280; }
            .inv-meta { width: 100%; font-size: 11.5px; }
            .inv-meta td { padding: 2px 0; vertical-align: top; }
            .inv-meta td:first-child { color: #9ca3af; padding-right: 10px; white-space: nowrap; }
            .inv-meta td:last-child { color: #374151; font-weight: 600; }

            .inv-barang { width: 100%; border-collapse: collapse; margin-top: 22px; }
            .inv-barang thead th {
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #6b7280;
                background: #f9fafb;
                padding: 10px 12px;
                border-bottom: 1px solid #e5e7eb;
            }
            .inv-barang td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 12.5px; }
            .inv-kiri { text-align: left; }
            .inv-tengah { text-align: center; }
            .inv-kanan { text-align: right; white-space: nowrap; }
            .inv-produk { display: block; font-weight: 600; color: #111827; }
            .inv-varian { display: block; font-size: 11px; color: #9ca3af; margin-top: 2px; }

            .inv-total-bungkus { display: flex; justify-content: flex-end; margin-top: 18px; }
            .inv-total { width: 100%; max-width: 280px; font-size: 12.5px; }
            .inv-total td { padding: 6px 0; }
            .inv-total td:last-child { text-align: right; font-weight: 600; color: #111827; }
            .inv-total td:first-child { color: #6b7280; }
            .inv-total-akhir td {
                padding-top: 12px;
                border-top: 2px solid #e5e7eb;
                font-size: 15px;
                font-weight: 900;
                color: var(--color-primary, #1B3A6B) !important;
            }

            .inv-catatan {
                margin-top: 30px;
                padding-top: 16px;
                border-top: 1px dashed #e5e7eb;
                font-size: 11px;
                line-height: 1.7;
                color: #9ca3af;
                text-align: center;
            }

            /* Saat dicetak, hanya lembar invoice yang tampil */
            @media print {
                body { background: #fff; }
                .inv-aksi { display: none !important; }
                .inv-kertas { border: 0; padding: 0; border-radius: 0; }
                header, footer, nav, .no-print { display: none !important; }
            }
        </style>
    @endpush
</x-app-layout>
