<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductReview;
use App\Support\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Penilaian produk oleh pembeli.
 */
class ProductReviewController extends Controller
{
    public function store(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with('items.review')
            ->firstOrFail();

        if ($order->status !== 'completed') {
            return back()->with('error',
                'Penilaian baru bisa diberikan setelah pesanan kamu tandai selesai.');
        }

        $megaFoto  = (int) config('ulasan.maks_foto_mb', 2);
        $maksFoto  = (int) config('ulasan.maks_foto', 3);
        $batasFoto = $megaFoto * 1024;

        // Bentuk kiriman: penilaian[<id baris pesanan>][rating|comment|photos] Dikelompokkan per baris pesa...
        $data = $request->validate([
            'penilaian'                => ['required', 'array', 'min:1'],
            'penilaian.*.rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'penilaian.*.comment'      => ['nullable', 'string', 'max:1500'],
            'penilaian.*.photos'       => ['nullable', 'array', 'max:' . $maksFoto],
            'penilaian.*.photos.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $batasFoto],
        ], [
            'penilaian.required'         => 'Belum ada produk yang dinilai.',
            'penilaian.*.rating.required' => 'Beri dulu bintang untuk setiap produknya.',
            'penilaian.*.rating.min'      => 'Bintangnya minimal 1.',
            'penilaian.*.rating.max'      => 'Bintangnya maksimal 5.',
            'penilaian.*.comment.max'     => 'Komentarnya maksimal 1500 karakter.',
            'penilaian.*.photos.max'      => 'Foto per produk maksimal ' . $maksFoto . ' buah.',
            'penilaian.*.photos.*.image'  => 'Foto ulasan harus berupa gambar.',
            'penilaian.*.photos.*.max'    => 'Ukuran foto ulasan maksimal ' . $megaFoto . ' MB.',
        ]);

        // Hanya baris milik pesanan ini yang dilayani, dan hanya yang belum
        // pernah dinilai. Id asing atau id yang sudah dinilai diabaikan
        // diam-diam — tidak perlu memberi tahu penyusup mana yang mana.
        $bolehDinilai = $order->items->filter(fn ($item) => $item->review === null)
            ->keyBy('id');

        $tersimpan = 0;

        DB::transaction(function () use ($data, $bolehDinilai, $order, $request, &$tersimpan) {
            foreach ($data['penilaian'] as $idBaris => $isi) {
                $item = $bolehDinilai->get((int) $idBaris);

                if (! $item) {
                    continue;
                }

                $foto = [];
                foreach ($request->file("penilaian.{$idBaris}.photos", []) as $berkas) {
                    $foto[] = $berkas->store('ulasan/' . $order->order_number, 'bersama');
                }

                ProductReview::create([
                    'product_id'    => $item->product_id,
                    'order_id'      => $order->id,
                    'order_item_id' => $item->id,
                    'user_id'       => Auth::id(),
                    'rating'        => (int) $isi['rating'],
                    // Komentar kosong disimpan sebagai null, bukan string
                    // kosong, supaya "tidak berkomentar" bisa dibedakan dari
                    // "berkomentar lalu dihapus isinya".
                    'comment'       => filled($isi['comment'] ?? null) ? trim($isi['comment']) : null,
                    'photos'        => $foto ?: null,
                ]);

                $tersimpan++;
            }
        });

        if ($tersimpan === 0) {
            return back()->with('error', 'Produk yang kamu nilai sudah pernah dinilai sebelumnya.');
        }

        CatatAktivitas::tulis(
            'ulasan',
            'menilai ' . $tersimpan . ' produk dari pesanan: ' . $order->order_number,
            $order,
            ['jumlah' => $tersimpan],
            'created'
        );

        return redirect()
            ->route('orders.show', $order->order_number)
            ->with('success', 'Terima kasih! Penilaianmu sudah tayang di halaman produknya '
                . 'dan sangat membantu pembeli lain.');
    }
}
