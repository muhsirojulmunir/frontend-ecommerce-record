<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Tampilkan halaman keranjang belanja.
     */
    public function index()
    {
        $cart = $this->cartService->getCart();
        $cartItems = $cart ? $cart->items()->with(['product.activeDiscount', 'productVariant.activeDiscount'])->get() : collect();

        return view('cart.index', compact('cart', 'cartItems'));
    }

    /**
     * Tambahkan produk ke keranjang.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity'           => 'required|integer|min:1',
        ]);

        $product  = Product::findOrFail($request->product_id);
        $variant  = $request->product_variant_id ? ProductVariant::find($request->product_variant_id) : null;
        $quantity = (int) $request->quantity;

        // Tambah ke keranjang lewat CartService
        $item = $this->cartService->addItem($product, $variant, $quantity);

        // "Beli Sekarang" hanya membayar barang ini saja.
        if ($request->has('buy_now')) {
            $this->cartService->pilihSatu($item->id);

            return redirect()->route('checkout.index');
        }

        // Kalau request AJAX (dipakai untuk animasi tambah keranjang tanpa reload)
        if ($request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Produk berhasil ditambahkan ke keranjang belanja!',
                'cart_count' => $this->cartService->getCartCount(),
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang belanja!');
    }

    /**
     * Ubah jumlah item di keranjang.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $item = $this->cartService->updateItem((int) $id, (int) $request->quantity);

        // Kalau request dari AJAX, kembalikan data JSON
        if ($request->ajax()) {
            $cart = $this->cartService->getCart();

            return response()->json([
                'success'        => true,
                'message'        => 'Keranjang berhasil diperbarui.',
                'cart_count'     => $this->cartService->getCartCount(),
                'item_subtotal'  => $item ? $item->formatted_subtotal : 0,
                'cart_total'     => $cart->formatted_total,

                // Nilai barang yang dicentang saja — dipakai halaman checkout untuk memperbarui total tanpa memuat ...
                'total_terpilih'  => $cart->total_terpilih,
                'jumlah_terpilih' => $cart->jumlah_terpilih,
                'jumlah_item'     => $item ? $item->quantity : 0,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
 * Simpan baris mana saja yang ikut dibayar.
 */
    public function pilih(Request $request)
    {
        $request->validate([
            'ids'   => 'present|array',
            'ids.*' => 'integer',
        ]);

        $this->cartService->pilih($request->input('ids', []));

        $cart = $this->cartService->getCart();

        if ($request->expectsJson()) {
            return response()->json([
                'success'         => true,
                'total_terpilih'  => $cart ? $cart->total_terpilih : 0,
                'jumlah_terpilih' => $cart ? $cart->jumlah_terpilih : 0,
                'total_rapi'      => 'Rp ' . number_format($cart ? $cart->total_terpilih : 0, 0, ',', '.'),
            ]);
        }

        return redirect()->route('cart.index');
    }

    /**
     * Hapus satu item dari keranjang.
     */
    public function destroy($id)
    {
        $this->cartService->removeItem((int) $id);

        return redirect()->route('cart.index')->with('success', 'Item telah dihapus dari keranjang.');
    }
}
