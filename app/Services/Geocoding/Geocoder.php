<?php

namespace App\Services\Geocoding;

/**
 * Kontrak penyedia pencarian koordinat.
 */
interface Geocoder
{
    /**
 * @return array|null|false  null bila tidak ketemu, false bila gagal dihubungi.
 */
    public function cari(string $kueri): array|null|false;

    /**
 * @return array|null|false  null bila tidak ketemu, false bila gagal dihubungi.
 */
    public function balik(float $lintang, float $bujur): array|null|false;

    /** Jeda minimal antar permintaan, dalam detik. */
    public function jedaDetik(): float;
}
