<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpayWithdrawal extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'amount',
        'bank_name', 'account_number', 'account_holder',
        'status', 'estimated_ready_at', 'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'estimated_ready_at' => 'date',
        'processed_at'       => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu Diproses',
            'processing' => 'Sedang Diproses',
            'completed'  => 'Dana Sudah Dikirim',
            'rejected'   => 'Ditolak',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'warning',
            'processing' => 'info',
            'completed'  => 'success',
            'rejected'   => 'danger',
            default      => 'secondary',
        };
    }

    /** Nomor rekening disamarkan, hanya empat angka terakhir yang tampak. */
    public function getRekeningSamarAttribute(): string
    {
        $nomor = (string) $this->account_number;

        return mb_strlen($nomor) <= 4
            ? $nomor
            : str_repeat('•', mb_strlen($nomor) - 4) . mb_substr($nomor, -4);
    }

    public static function buatReferensi(): string
    {
        do {
            $referensi = 'WD' . now()->format('ymd') . strtoupper(\Illuminate\Support\Str::random(5));
        } while (static::where('reference', $referensi)->exists());

        return $referensi;
    }
}
