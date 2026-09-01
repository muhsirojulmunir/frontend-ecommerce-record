<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dibatalkan {{ $order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; color: #374151;">
    @php
        $alamat   = $order->shipping_address ?? [];
        $alasan   = $order->cancellation_reason ?? '-';
        $catatan  = $order->cancellation_note ?? null;
    @endphp

    <div style="max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">

        {{-- Header --}}
        <div style="background-color: #1B3A6B; padding: 24px 30px; text-align: center;">
            <div style="font-size: 26px; font-weight: 900; color: #ffffff; letter-spacing: 2px; text-transform: uppercase;">RECORD</div>
            <div style="font-size: 10px; font-weight: 700; color: #93c5fd; letter-spacing: 3px; margin-top: 4px;">LANGKAHPENUHGAYA</div>
        </div>

        {{-- Body --}}
        <div style="padding: 30px;">

            {{-- Status Badge --}}
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="display: inline-block; background-color: #fee2e2; color: #991b1b; font-weight: bold; font-size: 12px; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 10px;">
                    &#10007; Pesanan Dibatalkan
                </div>
                <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 6px;">Pesananmu Telah Dibatalkan</h2>
                <p style="font-size: 14px; color: #4b5563; margin: 0;">
                    Halo, <strong>{{ $alamat['recipient_name'] ?? ($order->user?->name ?? 'Pelanggan') }}</strong>!
                    Kami sudah memproses pembatalan pesananmu dengan detail sebagai berikut.
                </p>
            </div>

            {{-- Info Pesanan --}}
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
                <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">No. Pesanan:</td>
                        <td style="text-align: right; font-weight: bold; color: #111827; padding: 4px 0;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Tanggal Pesan:</td>
                        <td style="text-align: right; color: #111827; padding: 4px 0;">{{ $order->created_at?->format('d M Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Tanggal Batal:</td>
                        <td style="text-align: right; color: #111827; padding: 4px 0;">{{ $order->cancelled_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0;">Alasan Pembatalan:</td>
                        <td style="text-align: right; font-weight: 600; color: #dc2626; padding: 4px 0;">{{ $alasan }}</td>
                    </tr>
                    @if($catatan)
                    <tr>
                        <td style="color: #6b7280; padding: 4px 0; vertical-align: top;">Keterangan:</td>
                        <td style="text-align: right; color: #374151; padding: 4px 0;">{{ $catatan }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- Daftar Produk --}}
            <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; color: #374151; letter-spacing: 0.5px; margin: 0 0 12px; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px;">
                Barang yang Dibatalkan
            </h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                @foreach($order->items as $item)
                    @php
                        $variantText = [];
                        if (!empty($item->productVariant?->size))  $variantText[] = 'Size ' . $item->productVariant->size;
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

            {{-- Total --}}
            <div style="border-top: 2px solid #1B3A6B; padding-top: 12px; margin-bottom: 24px; text-align: right;">
                <span style="font-size: 13px; color: #6b7280;">Total Pesanan: </span>
                <span style="font-size: 16px; font-weight: 800; color: #1B3A6B;">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
            </div>

            {{-- Refund Info (jika sudah bayar) --}}
            @if($order->payment_status === 'paid')
            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 14px; margin-bottom: 24px; font-size: 13px; color: #92400e;">
                <strong>&#128176; Pengembalian Dana:</strong> Karena pesanan ini sudah dibayar, dana sebesar
                <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                telah dikembalikan ke saldo R_Pay akun Anda secara otomatis.
            </div>
            @else
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px; margin-bottom: 24px; font-size: 13px; color: #166534;">
                Karena pesanan ini belum dibayar, tidak ada dana yang perlu dikembalikan.
            </div>
            @endif

            {{-- CTA --}}
            <div style="text-align: center; margin-bottom: 24px;">
                <a href="{{ url('/catalog') }}" style="display: inline-block; background-color: #1B3A6B; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 13px; padding: 10px 22px; border-radius: 6px;">
                    Belanja Lagi di RECORD
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