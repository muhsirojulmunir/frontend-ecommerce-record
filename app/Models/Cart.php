<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total price of all items in the cart (after discounts).
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getTotalItemsAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
 * Nilai barang yang dicentang saja — inilah yang dibayar saat checkout.
 */
    public function getTotalTerpilihAttribute(): float
    {
        return (float) $this->items
            ->where('dipilih', true)
            ->sum(fn ($item) => $item->subtotal);
    }

    /** Banyaknya satuan barang yang dicentang — dasar perhitungan ongkir. */
    public function getJumlahTerpilihAttribute(): int
    {
        return (int) $this->items->where('dipilih', true)->sum('quantity');
    }
}
