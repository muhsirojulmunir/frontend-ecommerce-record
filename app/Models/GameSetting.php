<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    protected $fillable = [
        'game_code',
        'name',
        'description',
        'icon',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config'    => 'array',
        ];
    }

    /**
     * Ambil setting berdasarkan game_code dengan fallback default
     */
    public static function forGame(string $code): ?self
    {
        return static::where('game_code', $code)->first();
    }

    /**
     * Ambil nilai config tertentu dengan default fallback
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
