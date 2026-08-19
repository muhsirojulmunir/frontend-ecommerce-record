<?php

namespace App\Services\Geocoding;

/**
 * Kontrak penyedia pencarian koordinat.
 *
 * Semua penyedia mengembalikan bentuk yang sama seperti Nominatim, supaya
 * halaman checkout tidak perlu tahu penyedia mana yang sedang dipakai:
 *
 *   [
 *       'lat'          => '-7.23164',
 *       'lon'          => '112.78549',
 *       'display_name' => 'Jalan Kyai Tambak Deres, Bulak, Surabaya, ...',
 *       'address'      => [
 *           'house_number' => '30',
 *           'road'         => 'Jalan Kyai Tambak Deres',
 *           'village'      => 'Kedung Cowek',   // kelurahan
 *           'city_district'=> 'Bulak',          // kecamatan
 *           'city'         => 'Surabaya',       // kota/kabupaten
 *           'state'        => 'Jawa Timur',     // provinsi
 *           'postcode'     => '60129',
 *       ],
 *   ]
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
