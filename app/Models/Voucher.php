<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'reserved_for',
        'reserved_until',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'integer',
            'is_used'        => 'boolean',
            'used_at'        => 'datetime',
            'expires_at'     => 'datetime',
            'reserved_until' => 'datetime',
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

    public function reservedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_for');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Voucher yang masih bisa dipakai (belum digunakan & belum kadaluarsa).
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Voucher tersedia untuk game: belum digunakan, tidak kadaluarsa,
     * dan belum direservasi (atau reservasinya sudah expired).
     */
    public function scopeAvailableForGame($query, int $amount)
    {
        return $query->where('is_used', false)
            ->where('amount', $amount)
            ->whereNull('used_by')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                // Belum direservasi, ATAU reservasinya sudah kedaluwarsa
                $q->whereNull('reserved_for')
                  ->orWhere('reserved_until', '<', now());
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

    /**
     * Generate kode voucher unik 4 karakter (huruf besar + angka).
     */
    public static function generateUniqueCode(int $length = 4): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa I, O, 0, 1 (menghindari ambigu)

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Tandai voucher sebagai terpakai.
     */
    public function markAsUsed(int $userId, int $orderId): void
    {
        $this->update([
            'is_used'        => true,
            'used_by'        => $userId,
            'used_at'        => now(),
            'order_id'       => $orderId,
            'reserved_for'   => null,
            'reserved_until' => null,
        ]);
    }

    /**
     * Reservasi sementara voucher untuk pemain (5 menit).
     */
    public function reserveFor(int $userId): void
    {
        $this->update([
            'reserved_for'   => $userId,
            'reserved_until' => now()->addMinutes(5),
        ]);
    }

    /**
     * Lepaskan reservasi (voucher hangus / tidak diklaim).
     */
    public function releaseReservation(): void
    {
        $this->update([
            'reserved_for'   => null,
            'reserved_until' => null,
        ]);
    }

    /**
     * Klaim permanen voucher untuk user (setelah klik Simpan di game).
     * Voucher dipegang user tapi belum `is_used` (belum dipakai checkout).
     */
    public function claimByUser(int $userId): void
    {
        $this->update([
            'used_by'        => $userId,
            'reserved_for'   => null,
            'reserved_until' => null,
        ]);
    }
}
