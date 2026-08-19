<?php

namespace App\Services;

use App\Exceptions\SaldoTidakCukup;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pergerakan uang yang timbul dari kode referal.
 */
class ReferralPayoutService
{
    public function __construct(
        private ReferralService $referral,
        private RpayService $rpay,
    ) {
    }

    /**
 * Dipanggil begitu sebuah pesanan dinyatakan lunas.
 */
    public function saatLunas(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $pemesan = $order->user ?: User::find($order->user_id);

            if ($pemesan) {
                $this->referral->terbitkan($pemesan);
            }

            if (! $this->punyaKomisi($order) || $this->sudahDibayar($order)) {
                return;
            }

            $this->rpay->kredit(
                $order->referrer_id,
                (float) $order->referral_commission,
                'referral',
                'Komisi referal dari pesanan ' . $order->order_number,
                $order,
            );
        });
    }

    /**
 * Dipanggil saat pesanan dibatalkan.
 */
    public function saatDibatalkan(Order $order): void
    {
        if (! $this->punyaKomisi($order)) {
            return;
        }

        if (! $this->sudahDibayar($order) || $this->sudahDitarik($order)) {
            return;
        }

        DB::transaction(function () use ($order) {
            $komisi = (float) $order->referral_commission;
            $saldo  = $this->rpay->saldo($order->referrer_id);

            // Komisi bisa saja sudah terpakai belanja atau dicairkan sebelum pesanannya dibatalkan.
            $ditarik = min($komisi, $saldo);

            if ($ditarik <= 0) {
                Log::warning('Komisi referal tidak bisa ditarik: saldo pemilik kode sudah habis', [
                    'pesanan' => $order->order_number,
                    'pemilik' => $order->referrer_id,
                    'komisi'  => $komisi,
                ]);

                return;
            }

            if ($ditarik < $komisi) {
                Log::warning('Komisi referal hanya bisa ditarik sebagian', [
                    'pesanan'     => $order->order_number,
                    'pemilik'     => $order->referrer_id,
                    'komisi'      => $komisi,
                    'ditarik'     => $ditarik,
                    'kekurangan'  => $komisi - $ditarik,
                ]);
            }

            try {
                $this->rpay->debit(
                    $order->referrer_id,
                    $ditarik,
                    'referral_reversal',
                    'Komisi referal pesanan ' . $order->order_number . ' ditarik kembali (pesanan dibatalkan)',
                    $order,
                );
            } catch (SaldoTidakCukup $e) {
                // Saldo berubah di sela pemeriksaan dan penarikan. Dicatat,
                // bukan dilempar — pembatalan pesanan tidak boleh gagal
                // hanya karena komisinya tidak bisa ditarik.
                Log::warning('Penarikan komisi referal gagal', [
                    'pesanan' => $order->order_number,
                    'pesan'   => $e->getMessage(),
                ]);
            }
        });
    }

    private function punyaKomisi(Order $order): bool
    {
        return $order->referrer_id && (float) $order->referral_commission > 0;
    }

    private function sudahDibayar(Order $order): bool
    {
        return $this->rpay->sudahDibukukan($order, 'referral');
    }

    private function sudahDitarik(Order $order): bool
    {
        return $this->rpay->sudahDibukukan($order, 'referral_reversal');
    }
}
