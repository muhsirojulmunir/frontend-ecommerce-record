<?php

namespace App\Services;

/**
 * Kalkulasi biaya transaksi Midtrans (sudah termasuk PPN 11%).
 */
class TransactionFeeService
{
    /**
 * Hitung biaya Midtrans berdasarkan metode pembayaran.
 *
 * @param  string $paymentMethod  Nilai dari kolom payment_method di tabel orders
 * @param  int    $grandTotal     Grand total pesanan dalam Rupiah (integer)
 * @return int    Biaya dalam Rupiah (dibulatkan ke bawah)
 */
    public function hitungBiayaMidtrans(string $paymentMethod, int $grandTotal): int
    {
        $method = strtolower(trim($paymentMethod));

        return match(true) {
            // ─── Virtual Account (flat Rp 4.000 + PPN 11% = Rp 4.440) ─────────────
            in_array($method, ['bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'permata_va', 'cimb_va', 'other_va'])
                => 4_440,

            // Fallback: payment_method mengandung kata 'va' atau 'transfer'
            str_contains($method, 'va') || str_contains($method, 'transfer') || str_contains($method, 'bank')
                => 4_440,

            // ─── E-Wallet (2% + PPN 11% = 2.22%) ────────────────────────────────
            in_array($method, ['gopay', 'ovo', 'shopeepay', 'dana', 'linkaja'])
                => (int) floor($grandTotal * 0.0222),

            // ─── QRIS (0.7% + PPN 11% = 0.777%) ─────────────────────────────────
            $method === 'qris'
                => (int) floor($grandTotal * 0.00777),

            // ─── Kartu Kredit (2.9% + PPN 11% = 3.219%) ─────────────────────────
            in_array($method, ['credit_card', 'kartu_kredit'])
                => (int) floor($grandTotal * 0.03219),

            // ─── Gerai (flat Rp 5.000 + PPN 11% = Rp 5.550) ─────────────────────
            in_array($method, ['indomaret', 'alfamart', 'alfamidi'])
                => 5_550,

            // ─── Akulaku / Cicilan (3% + PPN 11% = 3.33%) ───────────────────────
            str_contains($method, 'akulaku') || str_contains($method, 'kredivo')
                => (int) floor($grandTotal * 0.0333),

            // ─── COD / R_Pay / Manual → tidak ada biaya Midtrans ─────────────────
            in_array($method, ['cod', 'r_pay', 'tunai', 'cash'])
                => 0,

            // ─── Default: anggap VA flat ──────────────────────────────────────────
            default => 4_440,
        };
    }

    /**
 * Hitung markup profit ongkir.
 *
 * @param  int $shippingCostCharged  Ongkir yang dibayar customer
 * @param  int $shippingActualCost   Tarif aktual dari Biteship API
 * @return int Keuntungan markup (bisa 0 jika tidak ada markup)
 */
    public function hitungMarkupOngkir(int $shippingCostCharged, int $shippingActualCost): int
    {
        return max(0, $shippingCostCharged - $shippingActualCost);
    }

    /**
 * Hitung estimasi pendapatan bersih.
 *
 * @param  int $grandTotal    Grand total pesanan
 * @param  int $midtransFee   Biaya Midtrans (sudah termasuk PPN)
 * @return int
 */
    public function hitungNetRevenue(int $grandTotal, int $midtransFee): int
    {
        return max(0, $grandTotal - $midtransFee);
    }

    /**
 * Uang yang benar-benar tinggal di toko dari satu pesanan.
 */
    public function hitungDiterimaBersih(
        int $grandTotal,
        int $midtransFee,
        int $ongkirAsli,
        int $komisiReferal = 0
    ): int {
        return (int) ($grandTotal - $midtransFee - $ongkirAsli - $komisiReferal);
    }

    /**
 * Mengisi kolom biaya pada satu pesanan.
 */
    public function terapkanKe($order): void
    {
        $grandTotal = (int) round((float) $order->grand_total);

        $biaya = $this->hitungBiayaMidtrans((string) ($order->payment_method ?? ''), $grandTotal);

        // Ongkir asli belum tentu tercatat pada pesanan lama; kalau kosong,
        // dianggap sama dengan yang ditagihkan agar markupnya nol, bukan
        // menghasilkan keuntungan semu sebesar seluruh ongkir.
        // Dibandingkan sebagai angka: kolom desimal berisi teks "0.00",
        // dan teks itu dianggap BENAR oleh PHP — hanya "0" dan "" yang salah.
        // Lewat "?:", cadangannya tidak akan pernah terpakai.
        $ongkirTercatat = (float) $order->shipping_actual_cost;
        $ongkirAsli = (int) round($ongkirTercatat > 0
            ? $ongkirTercatat
            : (float) $order->shipping_cost);

        $order->midtrans_fee           = $biaya;
        $order->shipping_actual_cost   = $ongkirAsli;
        $order->shipping_markup_profit = $this->hitungMarkupOngkir(
            (int) round((float) $order->shipping_cost),
            $ongkirAsli
        );
        $order->net_revenue = $this->hitungDiterimaBersih(
            $grandTotal,
            $biaya,
            $ongkirAsli,
            (int) round((float) ($order->referral_commission ?? 0))
        );
    }

    /**
 * Label deskriptif biaya Midtrans untuk ditampilkan di UI admin.
 *
 * @param  string $paymentMethod
 * @return string
 */
    public function labelBiaya(string $paymentMethod): string
    {
        $method = strtolower(trim($paymentMethod));

        if (in_array($method, ['cod', 'r_pay', 'tunai', 'cash'])) {
            return 'Tidak ada (COD / Internal)';
        }

        if (str_contains($method, 'va') || str_contains($method, 'transfer') || str_contains($method, 'bank')) {
            return 'VA Rp 4.000 + PPN 11% = Rp 4.440';
        }

        if (in_array($method, ['gopay', 'ovo', 'shopeepay', 'dana', 'linkaja'])) {
            return 'E-Wallet 2% + PPN 11% = 2.22%';
        }

        if ($method === 'qris') {
            return 'QRIS 0.7% + PPN 11% = 0.777%';
        }

        if (in_array($method, ['credit_card', 'kartu_kredit'])) {
            return 'Kartu Kredit 2.9% + PPN 11% = 3.219%';
        }

        if (in_array($method, ['indomaret', 'alfamart', 'alfamidi'])) {
            return 'Gerai Rp 5.000 + PPN 11% = Rp 5.550';
        }

        return 'VA Rp 4.000 + PPN 11% = Rp 4.440';
    }
}
