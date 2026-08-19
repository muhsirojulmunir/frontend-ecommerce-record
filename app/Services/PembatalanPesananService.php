<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Pembatalan pesanan oleh pembeli.
 *
 * Ada tiga jalur, dan yang menentukan adalah SUDAH ATAU BELUM pesanan itu
 * diatur pengirimannya oleh admin:
 *
 *   1. Belum dibayar              → batal seketika, tidak ada uang berpindah.
 *   2. Sudah dibayar, belum diatur pengiriman
 *                                 → batal seketika, dana kembali ke R_Pay.
 *   3. Sudah diatur pengiriman    → tidak bisa dibatalkan; pakai alur
 *                                   pengembalian barang.
 *
 * Batasnya diletakkan di "sudah diatur pengiriman", bukan di "sudah dibayar",
 * karena sejak resi terbit barangnya sudah berada di tangan kurir. Pada tahap
 * itu yang berlaku bukan lagi pembatalan melainkan pengembalian: barangnya
 * harus benar-benar kembali dan diperiksa dulu sebelum dananya cair.
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
     * Pengiriman dianggap sudah diatur bila statusnya sudah "dikirim" ATAU
     * nomor resinya sudah terisi.
     *
     * Keduanya diperiksa karena admin kerap mencatat resi lebih dulu sebelum
     * mengubah statusnya; kalau hanya status yang dilihat, ada celah waktu
     * ketika barang sudah diserahkan ke kurir tetapi pembeli masih bisa
     * membatalkan sendiri.
     */
    public function sudahDiaturPengiriman(Order $order): bool
    {
        return $order->status === 'shipped' || filled($order->tracking_number);
    }

    /**
     * Membatalkan pesanan seketika.
     *
     * Stok dikembalikan, dan bila pesanannya sudah lunas, dananya dikreditkan
     * ke R_Pay pembeli. Seluruhnya dalam satu transaksi database: kalau salah
     * satu gagal, tidak ada yang berubah — jangan sampai stok kembali tetapi
     * uangnya tidak.
     */
    public function batalkan(Order $order, string $alasan, ?string $penjelasan = null): void
    {
        DB::transaction(function () use ($order, $alasan, $penjelasan) {
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
