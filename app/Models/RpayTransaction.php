<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris buku besar R_Pay.
 */
class RpayTransaction extends Model
{
    protected $fillable = [
        'user_id', 'direction', 'amount', 'balance_after',
        'source', 'reference_type', 'reference_id', 'description', 'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getArahLabelAttribute(): string
    {
        return $this->direction === 'credit' ? 'Masuk' : 'Keluar';
    }

    public function getSumberLabelAttribute(): string
    {
        return match ($this->source) {
            'refund'            => 'Pengembalian Dana',
            'checkout'          => 'Pembayaran Pesanan',
            'withdrawal'        => 'Pencairan ke Bank',
            'reversal'          => 'Pengembalian karena Batal',
            'adjustment'        => 'Penyesuaian Admin',
            'referral'          => 'Komisi Referal',
            'referral_reversal' => 'Komisi Referal Ditarik',
            default      => $this->source,
        };
    }

    /** Nominal bertanda, untuk ditampilkan (+/-). */
    public function getNominalBertandaAttribute(): float
    {
        return $this->direction === 'credit'
            ? (float) $this->amount
            : -1 * (float) $this->amount;
    }
}
