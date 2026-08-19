<?php

namespace App\Services;

use App\Services\Geocoding\Geocoder;
use Illuminate\Support\Facades\Cache;

/**
 * Pencarian koordinat alamat untuk halaman checkout.
 */
class GeocodingService
{
    public function __construct(private Geocoder $penyedia)
    {
    }

    public function cari(string $kueri): ?array
    {
        $kueri = trim(preg_replace('/\s+/u', ' ', $kueri));

        if ($kueri === '') {
            return null;
        }

        return $this->lewatCache(
            $this->kunci('cari', mb_strtolower($kueri)),
            fn () => $this->penyedia->cari($kueri)
        );
    }

    public function balik(float $lintang, float $bujur): ?array
    {
        // Dibulatkan lima angka di belakang koma (kira-kira satu meter) supaya
        // geseran pin yang sangat kecil tetap memakai hasil dari cache.
        return $this->lewatCache(
            $this->kunci('balik', sprintf('%.5f,%.5f', $lintang, $bujur)),
            fn () => $this->penyedia->balik($lintang, $bujur)
        );
    }

    /**
     * Nama penyedia ikut masuk ke kunci cache, supaya hasil lama dari
     * penyedia sebelumnya tidak terpakai setelah pengaturannya diganti.
     */
    private function kunci(string $jenis, string $isi): string
    {
        return sprintf('geocode:%s:%s:%s', config('geocoding.driver', 'osm'), $jenis, md5($isi));
    }

    private function lewatCache(string $kunci, callable $ambil): ?array
    {
        $tersimpan = Cache::get($kunci);

        if ($tersimpan !== null) {
            return $tersimpan === 'kosong' ? null : $tersimpan;
        }

        $this->tahanLaju();

        $hasil = $ambil();

        // Kegagalan layanan sengaja tidak disimpan, supaya gangguan sesaat
        // tidak membuat alamat itu dianggap tidak ada selama berjam-jam.
        if ($hasil === false) {
            return null;
        }

        $umur = $hasil
            ? config('geocoding.umur_cache.ketemu', 60 * 24 * 30)
            : config('geocoding.umur_cache.kosong', 60 * 6);

        Cache::put($kunci, $hasil ?? 'kosong', $umur * 60);

        return $hasil;
    }

    /**
     * Menahan laju di satu tempat agar seluruh pembeli bersama-sama tidak
     * pernah melampaui batas yang ditetapkan penyedianya.
     */
    private function tahanLaju(): void
    {
        $kunci    = 'geocode:permintaan-terakhir:' . config('geocoding.driver', 'osm');
        $terakhir = (float) Cache::get($kunci, 0);
        $jeda     = $this->penyedia->jedaDetik() - (microtime(true) - $terakhir);

        if ($jeda > 0) {
            usleep((int) ($jeda * 1_000_000));
        }

        Cache::put($kunci, microtime(true), 60);
    }
}
