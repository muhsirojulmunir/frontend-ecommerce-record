<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Pembatalan pesanan oleh pembeli.
 */
class PembatalanPesananService
{
    public function __construct(private RpayService $rpay)
    {
    }

    public const LANGSUNG_TANPA_DANA = 'langsung';
    public const LANGSUNG_REFUND     = 'langsung_refund';
    public const LEWAT_PENGEMBALIAN  = 'lewat_pengembalian';
    public const TIDAK_BISA          = 'tidak_bisa';

    /**
     * Jalur mana yang berlaku untuk pesanan ini?
     */
    public function jalur(Order $order): string
    {
        if (in_array($order->status, ['cancelled', 'completed'], true)) {
            return self::TIDAK_BISA;
        }

        if ($this->sudahDiaturPengiriman($order)) {
            return self::LEWAT_PENGEMBALIAN;
        }

        return $order->payment_status === 'paid'
            ? self::LANGSUNG_REFUND
            : self::LANGSUNG_TANPA_DANA;
    }

    /**
 * Pengiriman dianggap sudah diatur bila statusnya sudah "dikirim" ATAU nomor resinya sudah terisi.
 */
    public function sudahDiaturPengiriman(Order $order): bool
    {
        return $order->status === 'shipped' || filled($order->tracking_number);
    }

    /**
 * Membatalkan pesanan seketika.
 */
    public function batalkan(Order $order, string $alasan, ?string $penjelasan = null): void
    {
        DB::transaction(function () use ($order, $alasan, $penjelasan) {
            /*
             * Barisnya dikunci dan dibaca ulang di dalam transaksi.
             *
             * Tanpa ini, dua permintaan pembatalan yang datang bersamaan
             * sama-sama membaca status "paid", sama-sama lolos pemeriksaan
             * "sudah dibukukan?", lalu sama-sama menuliskan pengembalian dana —
             * pembeli menerima uangnya dua kali. Stok pun ikut dikembalikan
             * dua kali.
             *
             * Yang kedua kini menunggu di sini, lalu mendapati statusnya sudah
             * "cancelled" dan berhenti tanpa melakukan apa-apa.
             */
            $terkunci = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (! $terkunci || $terkunci->status === 'cancelled') {
                return;
            }

            $order->setRawAttributes($terkunci->getAttributes(), true);

            $perluRefund = $order->payment_status === 'paid';
            $nominal     = (float) $order->grand_total;

            $order->status              = 'cancelled';
            $order->cancellation_reason = $alasan;
            $order->cancellation_note   = filled($penjelasan) ? trim($penjelasan) : null;
            $order->cancelled_at        = now();
            $order->save();

            $this->kembalikanStok($order);

            if (! $perluRefund || $nominal <= 0) {
                return;
            }

            // Penjagaan terhadap pemanggilan berulang: satu pesanan hanya
            // boleh menghasilkan satu baris pengembalian dana.
            if ($this->rpay->sudahDibukukan($order, 'refund')) {
                return;
            }

            $this->rpay->kredit(
                $order->user_id,
                $nominal,
                'refund',
                'Pembatalan pesanan ' . $order->order_number,
                $order,
            );
        });
    }

    private function kembalikanStok(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->productVariant) {
                $item->productVariant->increment('stock', $item->quantity);
            }

            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }
}
