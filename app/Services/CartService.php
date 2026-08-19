<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get the active cart for the current session or authenticated user.
     */
    public function getCart(): ?Cart
    {
        if (Auth::check()) {
            // Find or create cart for authenticated user
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        // For guest user
        $sessionId = Session::getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(Product $product, ?ProductVariant $variant, int $quantity = 1): CartItem
    {
        $cart = $this->getCart();

        $variantId = $variant ? $variant->id : null;

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;

            // Barang yang baru saja ditambahkan selalu ikut tercentang. Kalau
            // sebelumnya sempat dilepas centangnya, membiarkannya tetap lepas
            // membuat pembeli mengira tombolnya tidak berfungsi.
            $cartItem->dipilih = true;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'dipilih' => true,
            ]);
        }

        return $cartItem;
    }

    /**
     * Update item quantity in the cart.
     */
    public function updateItem(int $cartItemId, int $quantity): ?CartItem
    {
        $cart = $this->getCart();
        $cartItem = CartItem::where('cart_id', $cart->id)->find($cartItemId);

        if ($cartItem) {
            if ($quantity <= 0) {
                $cartItem->delete();
                return null;
            }

            // Check variant stock or product stock
            $maxStock = $cartItem->productVariant ? $cartItem->productVariant->stock : $cartItem->product->stock;
            $cartItem->quantity = min($quantity, $maxStock);
            $cartItem->save();
        }

        return $cartItem;
    }

    /**
 * Menandai baris mana saja yang ikut dibayar.
 *
 * @param  array<int>  $idTerpilih  id baris keranjang yang dicentang
 */
    public function pilih(array $idTerpilih): void
    {
        $cart = $this->getCart();

        if (! $cart) {
            return;
        }

        $idTerpilih = array_map('intval', $idTerpilih);

        // Dikerjakan sebagai dua perintah massal, bukan satu per satu, supaya
        // keranjang berisi banyak barang tidak menghasilkan puluhan query.
        CartItem::where('cart_id', $cart->id)->update(['dipilih' => false]);

        if ($idTerpilih !== []) {
            CartItem::where('cart_id', $cart->id)
                ->whereIn('id', $idTerpilih)
                ->update(['dipilih' => true]);
        }
    }

    /**
 * Menyisakan satu baris saja sebagai yang dibayar.
 */
    public function pilihSatu(int $cartItemId): void
    {
        $this->pilih([$cartItemId]);
    }

    /** Baris yang dicentang saja. */
    public function itemTerpilih()
    {
        $cart = $this->getCart();

        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->where('dipilih', true)
            ->with(['product.activeDiscount', 'productVariant.activeDiscount'])
            ->get();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId): void
    {
        $cart = $this->getCart();
        CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->delete();
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart(): void
    {
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    /**
     * Merge guest cart items into user cart upon login.
     */
    public function mergeGuestCart(string $guestSessionId, int $userId): void
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->first();
        if (!$guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $guestItem) {
            // Check if user cart already has this item
            $existingItem = CartItem::where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $guestItem->quantity;
                $existingItem->save();
            } else {
                $guestItem->cart_id = $userCart->id;
                $guestItem->save();
            }
        }

        // Delete guest cart
        $guestCart->delete();
    }

    /**
     * Get cart count (sum of quantities).
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return $cart ? $cart->items()->sum('quantity') : 0;
    }
}
