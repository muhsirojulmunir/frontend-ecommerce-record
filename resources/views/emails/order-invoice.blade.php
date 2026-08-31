<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pesanan {{ $order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; color: #374151;">
    @php
        $alamat = $order->shipping_address ?? [];
        $isPaid = ($order->payment_status === 'paid');
    @endphp

    <div style="max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;">
        
        {{-- Header / Brand --}}
        <div style="background-color: #1B3A6B; padding: 24px 30px; text-align: center;">
            <div style="font-size: 26px; font-weight: 900; color: #ffffff; letter-spacing: 2px; text-transform: uppercase;">RECORD</div>
            <div style="font-size: 10px; font-weight: 700; color: #93c5fd; letter-spacing: 3px; margin-top: 4px;">LANGKAHPENUHGAYA</div>
        </div>

        {{-- Body Content --}}
        <div style="padding: 30px;">
            <div style="text-align: center; margin-bottom: 25px;">
                @if($isPaid)
                    <div style="display: inline-block; background-color: #d1fae5; color: #065f46; font-weight: bold; font-size: 12px; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 10px;">
                        &#10003; Pembayaran Diterima (LUNAS)
                    </div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 6px;">Terima Kasih Atas Pembelianmu!</h2>
                    <p style="font-size: 14px; color: #4b5563; margin: 0;">
                        Pesananmu <strong>#{{ $order->order_number }}</strong> telah berhasil dibayar dan akan segera disiapkan oleh tim RECORD.
                    </p>
                @else
                    <div style="display: inline-block; background-color: #fef3c7; color: #92400e; font-weight: bold; font-size: 12px; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 10px;">
                        Menunggu Pembayaran
                    </div>
                    <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 6px;">Pesanan Baru Berhasil Dibuat</h2>
                    <p style="font-size: 14px; color: #4b5563; margin: 0;">
                        Pesananmu <strong>#{{ $order->order_number }}</strong> sedang menunggu penyelesaian pembayaran.
                    </p>
                @endif
            </div>

            {{-- Ringkasan Pesanan Box --}}
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
                <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">No. Pesanan:</td>
                        <td style="text-align: right; font-weight: bold; color: #111827; padding: 4px 0;">#{{ $order->order_number }}</td>
                    </tr>
                    @if($order->invoice_number)
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">No. Invoice:</td>
                        <td style="text-align: right; font-weight: bold; color: #1B3A6B; padding: 4px 0;">{{ $order->invoice_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Tanggal:</td>
                        <td style="text-align: right; color: #111827; padding: 4px 0;">{{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '-' }} WIB</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Metode Pembayaran:</td>
                        <td style="text-align: right; font-weight: 600; color: #111827; padding: 4px 0;">{{ strtoupper($order->payment_method ?: 'Online Payment') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Jasa Pengiriman:</td>
                        <td style="text-align: right; color: #111827; padding: 4px 0;">{{ strtoupper($order->courier ?: 'Kurir Rekord') }}</td>
                    </tr>
                </table>
            </div>

            {{-- Daftar Produk --}}
            <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; color: #374151; letter-spacing: 0.5px; margin: 0 0 12px; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px;">
                Rincian Barang
            </h3>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                @foreach($order->items as $item)
                    @php
                        $variantText = [];
                        if (!empty($item->productVariant?->size)) $variantText[] = 'Size ' . $item->productVariant->size;
                        if (!empty($item->productVariant?->color)) $variantText[] = $item->productVariant->color;
                        $variantStr = !empty($variantText) ? implode(' / ', $variantText) : ($item->variant_name ?: '-');
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 10px 0; vertical-align: middle;">
                            <div style="font-weight: bold; color: #111827; font-size: 14px;">{{ $item->product->name ?? 'Produk RECORD' }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ $variantStr }} &bull; {{ $item->quantity }} pcs &times; Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                        </td>
                        <td style="padding: 10px 0; text-align: right; vertical-align: middle; font-weight: bold; color: #111827; white-space: nowrap;">
                            Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </table>

            {{-- Rincian Biaya --}}
            <div style="border-top: 1px solid #e5e7eb; padding-top: 12px; margin-bottom: 24px;">
                <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Subtotal Produk:</td>
                        <td style="text-align: right; font-weight: 600; color: #111827; padding: 4px 0;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Ongkos Kirim:</td>
                        <td style="text-align: right; font-weight: 600; color: #111827; padding: 4px 0;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td style="color: #059669; padding: 4px 0;">Potongan Diskon:</td>
                        <td style="text-align: right; font-weight: 600; color: #059669; padding: 4px 0;">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(!empty($order->referral_discount) && $order->referral_discount > 0)
                    <tr>
                        <td style="color: #059669; padding: 4px 0;">Diskon Referral:</td>
                        <td style="text-align: right; font-weight: 600; color: #059669; padding: 4px 0;">- Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid #1B3A6B;">
                        <td style="font-weight: 800; font-size: 15px; color: #1B3A6B; padding: 10px 0 4px;">TOTAL:</td>
                        <td style="text-align: right; font-weight: 800; font-size: 16px; color: #1B3A6B; padding: 10px 0 4px;">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            {{-- Alamat Pengiriman --}}
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px; margin-bottom: 24px; font-size: 12px;">
                <div style="font-weight: bold; color: #374151; margin-bottom: 4px; text-transform: uppercase; font-size: 11px;">Alamat Pengiriman:</div>
                <div style="color: #111827; font-weight: 600;">{{ $alamat['recipient_name'] ?? $order->user->name }} ({{ $alamat['phone'] ?? $order->user->phone }})</div>
                <div style="color: #4b5563; margin-top: 2px;">
                    {{ $alamat['address_line'] ?? '-' }}<br>
                    {{ $alamat['city'] ?? '' }}{{ !empty($alamat['province']) ? ', ' . $alamat['province'] : '' }} {{ $alamat['postal_code'] ?? '' }}
                </div>
            </div>

            {{-- Lampiran PDF Notice & Tombol Web --}}
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 14px; text-align: center; margin-bottom: 24px;">
                <p style="margin: 0 0 12px; font-size: 13px; color: #1e40af;">
                    &#128206; <strong>Dokumen PDF Invoice resmi</strong> telah dilampirkan langsung pada email ini dan siap diunduh.
                </p>
                <a href="{{ $orderUrl }}" style="display: inline-block; background-color: #1B3A6B; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 13px; padding: 10px 22px; border-radius: 6px;">
                    Lihat Status Pesanan & Invoice Web
                </a>
            </div>

        </div>

        {{-- Footer --}}
        <div style="background-color: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px; text-align: center; font-size: 11px; color: #6b7280;">
            <p style="margin: 0 0 6px;">
                Email ini dikirim otomatis oleh sistem <strong>RECORD Official Store</strong>.<br>
                Jl. Kyai Tambak Deres No.32, Surabaya | WA +62 813-2306-5554 | admin@recordshoes.com
            </p>
            <p style="margin: 0;">&copy; {{ date('Y') }} RECORD Shoes. All rights reserved.</p>
        </div>

    </div>
</body>
</html>
