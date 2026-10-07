<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(
        private CartService $cart,
    ) {
    }

    public function periksa(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50',
        ]);

        $kode = strtoupper(trim($data['kode']));
        $voucher = Voucher::where('code', $kode)->first();

        if (! $voucher) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        if ($voucher->is_used) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher sudah pernah digunakan.',
            ]);
        }

        if ($voucher->used_by && auth()->check() && $voucher->used_by !== auth()->id()) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher ini milik akun pengguna lain.',
            ]);
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher sudah kadaluarsa pada ' . $voucher->expires_at->format('d/m/Y H:i') . '.',
            ]);
        }

        $keranjang = $this->cart->getCart();
        $subtotal = (float) ($keranjang?->total_terpilih ?? 0);

        if ($subtotal <= 0) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Keranjang kosong atau belum ada produk yang dipilih.',
            ]);
        }

        // Nominal diskon voucher tidak melebihi subtotal
        $potongan = min((float) $voucher->amount, $subtotal);

        return response()->json([
            'sah'               => true,
            'kode'              => $voucher->code,
            'nominal'           => (int) $voucher->amount,
            'diskon'            => $potongan,
            'formatted_nominal' => 'Rp ' . number_format($voucher->amount, 0, ',', '.'),
            'formatted_diskon'  => 'Rp ' . number_format($potongan, 0, ',', '.'),
            'pesan'             => 'Voucher berhasil digunakan! Hemat Rp ' . number_format($potongan, 0, ',', '.'),
        ]);
    }
}
