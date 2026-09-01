<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /**
     * Hitung jumlah pesanan yang belum dibayar (unpaid & tidak dibatalkan).
     */
    public function unpaidOrdersCount(): int
    {
        return $this->orders()
            ->where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    /**
 * @use HasFactory<UserFactory>
 */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'role',
        'email',
        'phone',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'rpay_balance' => 'decimal:2',
            'referral_issued_at' => 'datetime',
        ];
    }

    // Saldo R_Pay sengaja TIDAK dimasukkan ke $fillable.

    public function rpayTransactions(): HasMany
    {
        return $this->hasMany(RpayTransaction::class)->latest('id');
    }

    public function rpayWithdrawals(): HasMany
    {
        return $this->hasMany(RpayWithdrawal::class)->latest('id');
    }

    public function orderReturns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    // ─── Role Helpers ──────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ─── Relationships ─────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the user's default address.
     */
    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
    /**
     * Cek apakah ada pesanan yang baru saja selesai/sampai dalam 3 hari terakhir.
     */
    public function hasRecentlyDeliveredOrders(): bool
    {
        return $this->orders()
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(3))
            ->exists();
    }

}
