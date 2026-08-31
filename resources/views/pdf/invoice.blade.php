<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->invoice_number ?? $order->order_number }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #1B3A6B;
            padding-bottom: 12px;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 900;
            color: #1B3A6B;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .brand-tagline {
            font-size: 8px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 2px;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .store-info {
            font-size: 9px;
            color: #4b5563;
            line-height: 1.3;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-badge {
            font-size: 20px;
            font-weight: 900;
            color: #1B3A6B;
            letter-spacing: 1px;
        }
        .invoice-number {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin-top: 3px;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .badge-unpaid {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .info-table td {
            vertical-align: top;
            width: 50%;
            padding: 8px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }
        .info-content {
            font-size: 10px;
            color: #1f2937;
        }
        .meta-row {
            margin-bottom: 2px;
        }
        .meta-label {
            color: #6b7280;
            width: 90px;
            display: inline-block;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #1B3A6B;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 7px 8px;
            font-size: 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fcfdfd;
        }
        .item-name {
            font-weight: bold;
            color: #111827;
        }
        .item-variant {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .summary-table td {
            padding: 4px 8px;
            font-size: 10px;
        }
        .summary-label {
            text-align: right;
            color: #4b5563;
            font-weight: 600;
        }
        .summary-val {
            text-align: right;
            width: 130px;
            font-weight: bold;
            color: #111827;
        }
        .grand-total td {
            border-top: 2px solid #1B3A6B;
            padding-top: 8px;
            font-size: 13px;
            font-weight: 900;
            color: #1B3A6B;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    @php
        $alamat = $order->shipping_address ?? [];
        $isPaid = ($order->payment_status === 'paid');
    @endphp

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="brand-logo">RECORD</div>
                <div class="brand-tagline">LANGKAHPENUHGAYA</div>
                <div class="store-info">
                    Jl. Kyai Tambak Deres No.32, Kedung Cowek, Bulak<br>
                    Surabaya, Jawa Timur 60124<br>
                    WhatsApp: +62 813-2306-5554 | Email: admin@recordshoes.com
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;" class="invoice-title">
                <div class="invoice-badge">INVOICE</div>
                <div class="invoice-number">{{ $order->invoice_number ?: $order->order_number }}</div>
                <div>
                    @if($isPaid)
                        <span class="badge-status badge-paid">&#10003; LUNAS</span>
                    @else
                        <span class="badge-status badge-unpaid">MENUNGGU PEMBAYARAN</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Informasi Tagihan & Pengiriman --}}
    <table class="info-table">
        <tr>
            <td>
                <div class="info-title">Ditagihkan Kepada</div>
                <div class="info-content">
                    <strong style="font-size: 11px;">{{ $alamat['recipient_name'] ?? $order->user->name }}</strong><br>
                    {{ $alamat['phone'] ?? $order->user->phone }}<br>
                    {{ $order->user->email }}
                </div>
                <div class="info-title" style="margin-top: 8px;">Alamat Pengiriman</div>
                <div class="info-content">
                    {{ $alamat['address_line'] ?? '-' }}<br>
                    {{ $alamat['city'] ?? '' }}{{ !empty($alamat['province']) ? ', ' . $alamat['province'] : '' }} {{ $alamat['postal_code'] ?? '' }}
                </div>
            </td>
            <td>
                <div class="info-title">Rincian Transaksi</div>
                <div class="info-content">
                    <div class="meta-row"><span class="meta-label">No. Pesanan</span>: <strong>#{{ $order->order_number }}</strong></div>
                    <div class="meta-row"><span class="meta-label">Tanggal Pesanan</span>: {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</div>
                    @if($order->invoice_issued_at)
                    <div class="meta-row"><span class="meta-label">Tanggal Invoice</span>: {{ $order->invoice_issued_at->format('d/m/Y H:i') }}</div>
                    @endif
                    <div class="meta-row"><span class="meta-label">Metode Bayar</span>: {{ strtoupper($order->payment_method ?: 'MIDTRANS') }}</div>
                    <div class="meta-row"><span class="meta-label">Jasa Kurir</span>: {{ strtoupper($order->courier ?: 'Reguler') }}</div>
                    @if($order->tracking_number)
                    <div class="meta-row"><span class="meta-label">Nomor Resi</span>: <strong>{{ $order->tracking_number }}</strong></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Daftar Barang --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 45%;">Rincian Produk</th>
                <th style="width: 15%; text-align: center;">Varian</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 13%; text-align: right;">Harga</th>
                <th style="width: 14%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                @php
                    $variantText = [];
                    if (!empty($item->productVariant?->size)) $variantText[] = 'Size ' . $item->productVariant->size;
                    if (!empty($item->productVariant?->color)) $variantText[] = $item->productVariant->color;
                    $variantStr = !empty($variantText) ? implode(' / ', $variantText) : ($item->variant_name ?: '-');
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->product->name ?? 'Produk RECORD' }}</div>
                    </td>
                    <td style="text-align: center;" class="item-variant">{{ $variantStr }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td style="text-align: right;"><strong>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Ringkasan Perhitungan Biaya --}}
    <table style="width: 100%;">
        <tr>
            <td style="width: 45%; vertical-align: top; padding-right: 15px;">
                <div style="font-size: 9px; color: #4b5563; background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 8px; border-radius: 3px;">
                    <strong>Catatan Pembayaran:</strong><br>
                    • Dokumen ini merupakan bukti transaksi yang sah dari RECORD Official Store.<br>
                    • Hubungi layanan pelanggan kami jika ada kendala pesanan atau pengiriman.
                </div>
            </td>
            <td style="width: 55%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Total Harga Produk:</td>
                        <td class="summary-val">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Ongkos Kirim ({{ $order->courier ?: 'Kurir' }}):</td>
                        <td class="summary-val">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td class="summary-label" style="color: #059669;">Potongan Diskon:</td>
                        <td class="summary-val" style="color: #059669;">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(!empty($order->referral_discount) && $order->referral_discount > 0)
                    <tr>
                        <td class="summary-label" style="color: #059669;">Diskon Referral:</td>
                        <td class="summary-val" style="color: #059669;">- Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="summary-label">TOTAL PEMBAYARAN:</td>
                        <td class="summary-val">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Terima kasih telah berbelanja di <strong>RECORD Official Store</strong>.<br>
        Kunjungi website resmi kami di <a href="https://recordshoes.com" style="color: #1B3A6B; text-decoration: none;">recordshoes.com</a>
    </div>
</body>
</html>
