<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_position_x',
        'image_position_y',
        'image_zoom',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'image_position_x' => 'integer',
        'image_position_y' => 'integer',
        'image_zoom'       => 'float',
    ];

    /**
     * Gaya CSS gambar sesuai posisi & perbesaran yang diatur admin di
     * Seller Center, supaya tampilannya identik dengan pratinjau di sana.
     */
    public function getImageStyleAttribute(): string
    {
        $x    = $this->image_position_x ?? 50;
        $y    = $this->image_position_y ?? 50;
        $zoom = $this->image_zoom ?: 1;

        return "object-fit:cover;object-position:{$x}% {$y}%;"
            . "transform:scale({$zoom});transform-origin:{$x}% {$y}%;";
    }

    /**
     * Get the products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get only active products.
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('status', 'active');
    }

    /**
     * Scope: only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * URL gambar kategori dari storage backend.
     * Mengembalikan null kalau belum ada gambar, supaya tampilan bisa
     * memakai latar gradasi sebagai gantinya.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Product::storageUrl($this->image) : null;
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
