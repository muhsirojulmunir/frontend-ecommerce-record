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
            'kode'      => 'required|string|max:50',
            'device_id' => 'nullable|string|max:64',
        ]);

        $kode = strtoupper(trim($data['kode']));
        $voucher = Voucher::with('category')->where('code', $kode)->first();

        if (! $voucher) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        $userId   = auth()->id();
        $deviceId = $request->input('device_id') ?? $request->query('device_id') ?? $request->header('X-Device-Id');

        $validation = $voucher->validateForUsage($userId, $deviceId);
        if (! $validation['valid']) {
            return response()->json([
                'sah'    => false,
                'alasan' => $validation['message'],
            ]);
        }

        if ($voucher->used_by && auth()->check() && $voucher->used_by !== auth()->id()) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Kode voucher ini milik akun pengguna lain.',
            ]);
        }

        $itemsTerpilih = $this->cart->itemTerpilih();
        if ($itemsTerpilih->isEmpty()) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Keranjang kosong atau belum ada produk yang dipilih.',
            ]);
        }

        $keranjang = $this->cart->getCart();
        $subtotal = (float) ($keranjang?->total_terpilih ?? 0);

        if ($subtotal <= 0) {
            return response()->json([
                'sah'    => false,
                'alasan' => 'Subtotal belanja tidak valid.',
            ]);
        }

        // Pemeriksaan kategori voucher
        if ($voucher->category_id) {
            $eligibleItems = $itemsTerpilih->filter(function ($item) use ($voucher) {
                return (int) $item->product?->category_id === (int) $voucher->category_id;
            });

            if ($eligibleItems->isEmpty()) {
                $namaKategori = $voucher->category ? $voucher->category->name : 'kategori khusus';
                return response()->json([
                    'sah'    => false,
                    'alasan' => "Voucher ini hanya berlaku untuk produk dalam kategori {$namaKategori}.",
                ]);
            }

            // Diskon hanya memotong subtotal produk pada kategori yang bersangkutan
            $subtotalKategori = (float) $eligibleItems->sum(fn ($item) => (float) $item->subtotal);
            $potongan = min((float) $voucher->amount, $subtotalKategori);
            $namaKategori = $voucher->category?->name;
            $pesan = "Voucher khusus kategori {$namaKategori} berhasil digunakan! Hemat Rp " . number_format($potongan, 0, ',', '.');
        } else {
            // Voucher universal (semua kategori)
            $potongan = min((float) $voucher->amount, $subtotal);
            $pesan = 'Voucher berhasil digunakan! Hemat Rp ' . number_format($potongan, 0, ',', '.');
        }

        return response()->json([
            'sah'               => true,
            'kode'              => $voucher->code,
            'nominal'           => (int) $voucher->amount,
            'diskon'            => $potongan,
            'category_id'       => $voucher->category_id,
            'category_name'     => $voucher->category?->name ?? 'Semua Produk',
            'formatted_nominal' => 'Rp ' . number_format($voucher->amount, 0, ',', '.'),
            'formatted_diskon'  => 'Rp ' . number_format($potongan, 0, ',', '.'),
            'pesan'             => $pesan,
        ]);
    }
}