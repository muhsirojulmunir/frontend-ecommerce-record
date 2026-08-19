<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_price',
        'shipping_cost',
        'grand_total',
        'status',
        'shipping_address',
        'courier',
        'courier_code',
        'tracking_number',
        'payment_method',
        'payment_status',
        'notes',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_at',
        'completed_at',
        'referral_code_used',
        'referrer_id',
        'referral_discount',
        'referral_commission',
        'invoice_number',
        'invoice_issued_at',
        'midtrans_fee',
        'shipping_actual_cost',
        'shipping_markup_profit',
        'net_revenue',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'referral_discount' => 'decimal:2',
        'referral_commission' => 'decimal:2',
        'invoice_issued_at' => 'datetime',
        'midtrans_fee' => 'decimal:2',
        'shipping_actual_cost' => 'decimal:2',
        'shipping_markup_profit' => 'decimal:2',
        'net_revenue' => 'decimal:2',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    /** Pengajuan pengembalian dan pembatalan yang menempel pada pesanan ini. */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Ulasan yang lahir dari pesanan ini. */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get formatted grand total.
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Get formatted shipping cost.
     */
    public function getFormattedShippingCostAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_cost, 0, ',', '.');
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get status color for badge styling.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get payment status label in Indonesian.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'               => 'Belum Bayar',
            'pending_verification' => 'Menunggu Konfirmasi Admin',
            'paid'                 => 'Sudah Dibayar',
            'failed'               => 'Gagal',
            'refunded'             => 'Dikembalikan',
            default                => $this->payment_status,
        };
    }

    /**
     * Generate unique order number.
     */
    /**
 * Terbitkan nomor invoice otomatis begitu pembayaran dinyatakan lunas.
 */
    protected static function booted(): void
    {
        static::saving(function (self $order) {
            if ($order->payment_status === 'paid' && blank($order->invoice_number)) {
                $order->invoice_number    = static::buatNomorInvoice();
                $order->invoice_issued_at = now();
            }

            // Biaya transaksi dicatat begitu pesanan lunas.
            if ($order->payment_status === 'paid'
                && ($order->isDirty('payment_status')
                    || $order->isDirty('payment_method')
                    || $order->net_revenue === null)) {
                app(\App\Services\TransactionFeeService::class)->terapkanKe($order);
            }
        });

        // Akibat kode referal dijalankan SETELAH baris pesanan tersimpan, bukan di dalam saving().
        static::saved(function (self $order) {
            $layanan = app(\App\Services\ReferralPayoutService::class);

            $baruLunas = $order->wasChanged('payment_status') && $order->payment_status === 'paid';
            $baruBatal = $order->wasChanged('status') && $order->status === 'cancelled';

            // Pesanan yang langsung tercipta dalam keadaan lunas (mis. dibayar
            // R_Pay) tidak menghasilkan perubahan kolom, jadi ikut diperiksa.
            if ($order->wasRecentlyCreated && $order->payment_status === 'paid') {
                $baruLunas = true;
            }

            if ($baruLunas) {
                $layanan->saatLunas($order);
            }

            if ($baruBatal) {
                $layanan->saatDibatalkan($order);
            }
        });
    }

    /**
     * Nomor invoice berurutan per bulan, contoh: INV/2026/08/0007
     */
    public static function buatNomorInvoice(): string
    {
        $awalan = 'INV/' . now()->format('Y/m') . '/';

        $terakhir = static::where('invoice_number', 'like', $awalan . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = static::where('order_number', 'like', "ORD-{$date}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "ORD-{$date}-{$newNumber}";
    }
}

