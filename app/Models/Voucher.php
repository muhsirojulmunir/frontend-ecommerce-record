<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'is_used',
        'used_by',
        'used_at',
        'order_id',
        'expires_at',
        'batch_label',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'integer',
            'is_used'    => 'boolean',
            'used_at'    => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('is_used', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_used) {
            return 'Terpakai';
        }
        if ($this->is_expired) {
            return 'Kadaluarsa';
        }
        return 'Tersedia';
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public static function generateUniqueCode(int $length = 4): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function markAsUsed(int $userId, int $orderId): void
    {
        $this->update([
            'is_used'  => true,
            'used_by'  => $userId,
            'used_at'  => now(),
            'order_id' => $orderId,
        ]);
    }
}
