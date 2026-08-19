<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ulasan produk dari pembeli.
 *
 * Setiap ulasan terikat pada satu baris pesanan, jadi selalu bisa ditelusuri
 * ke pembelian yang benar-benar terjadi. Lihat migrasinya untuk alasannya.
 */
class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'order_id',
        'order_item_id',
        'user_id',
        'rating',
        'comment',
        'photos',
        'is_hidden',
        'hidden_reason',
        'hidden_by',
        'hidden_at',
    ];

    protected function casts(): array
    {
        return [
            'photos'    => 'array',
            'is_hidden' => 'boolean',
            'hidden_at' => 'datetime',
            'rating'    => 'integer',
        ];
    }

    /** Ulasan yang boleh dilihat pembeli lain. */
    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disembunyikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    /**
     * Nama penulis yang disamarkan, misalnya "Muhammad" menjadi "M******d".
     *
     * Ulasan tampil di halaman yang bisa dibaca siapa saja, sedangkan nama
     * lengkap pembeli bukan sesuatu yang perlu terpampang di sana. Huruf
     * pertama dan terakhir disisakan supaya pembeli masih bisa mengenali
     * ulasannya sendiri.
     */
    public function getNamaSamaranAttribute(): string
    {
        $nama = trim((string) ($this->user->name ?? ''));

        if ($nama === '') {
            return 'Pembeli';
        }

        // Nama satu huruf tidak bisa disamarkan tanpa menghilang sama sekali.
        if (mb_strlen($nama) <= 2) {
            return mb_substr($nama, 0, 1) . '*';
        }

        return mb_substr($nama, 0, 1)
            . str_repeat('*', mb_strlen($nama) - 2)
            . mb_substr($nama, -1);
    }

    /** Varian yang dibeli, untuk ditampilkan di bawah nama penulis. */
    public function getVarianDibeliAttribute(): ?string
    {
        return $this->orderItem?->variant_info ?: null;
    }

    /**
     * Foto ulasan yang selalu berupa larik.
     *
     * Kolomnya boleh kosong, dan "photos" mentah akan bernilai null untuk
     * ulasan tanpa foto. Dipakai lewat sini supaya setiap pemakainya tidak
     * perlu mengingat kemungkinan itu satu per satu.
     */
    public function getDaftarFotoAttribute(): array
    {
        return $this->photos ?? [];
    }
}
