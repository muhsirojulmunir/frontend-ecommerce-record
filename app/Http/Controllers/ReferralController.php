<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pemeriksaan kode referal dari halaman checkout.
 */
class ReferralController extends Controller
{
    public function __construct(
        private ReferralService $referral,
        private CartService $cart,
    ) {
    }

    public function periksa(Request $request): JsonResponse
    {
        $data = $request->validate(['kode' => 'required|string|max:60']);

        $hasil = $this->referral->periksa($data['kode'], Auth::user());

        if (! $hasil['sah']) {
            return response()->json([
                'sah'    => false,
                'alasan' => $hasil['alasan'] ?? 'Kode referal tidak valid.',
            ]);
        }

        $keranjang = $this->cart->getCart();
        $hargaBarang = (float) ($keranjang?->total ?? 0);

        return response()->json([
            'sah'     => true,
            'pemilik' => $hasil['pemilik']->name,
            'diskon'  => $this->referral->diskon($hargaBarang),
            'persen'  => (int) config('referal.persen_diskon', 3),
        ]);
    }
}
