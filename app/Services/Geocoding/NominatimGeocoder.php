<?php

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Penyedia gratis berbasis OpenStreetMap.
 */
class NominatimGeocoder implements Geocoder
{
    public function jedaDetik(): float
    {
        return (float) config('geocoding.jeda_detik.osm', 1.1);
    }

    public function cari(string $kueri): array|null|false
    {
        return $this->panggil('/search', [
            'format'         => 'json',
            'addressdetails' => 1,
            'limit'          => 1,
            'countrycodes'   => 'id',
            'q'              => $kueri,
        ]);
    }

    public function balik(float $lintang, float $bujur): array|null|false
    {
        return $this->panggil('/reverse', [
            'format'         => 'json',
            'addressdetails' => 1,
            'lat'            => $lintang,
            'lon'            => $bujur,
        ]);
    }

    private function panggil(string $jalur, array $parameter): array|null|false
    {
        try {
            $respons = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => $this->identitas(),
                    'Accept'     => 'application/json',
                ])
                ->get(rtrim(config('geocoding.osm.pangkalan'), '/') . $jalur, $parameter);

            if (! $respons->successful()) {
                Log::warning('Geocoding ditolak Nominatim', [
                    'jalur'  => $jalur,
                    'status' => $respons->status(),
                ]);

                return false;
            }

            $isi = $respons->json();

            // /search mengembalikan larik hasil, /reverse satu objek.
            $hasil = array_is_list($isi ?? []) ? ($isi[0] ?? null) : $isi;

            return isset($hasil['lat'], $hasil['lon']) ? $hasil : null;
        } catch (\Throwable $e) {
            Log::warning('Geocoding gagal dihubungi', ['pesan' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Kebijakan Nominatim mewajibkan User-Agent yang menyebutkan aplikasinya
     * berikut alamat yang bisa dihubungi.
     */
    private function identitas(): string
    {
        return sprintf(
            '%s/1.0 (+%s; %s)',
            str_replace(' ', '', (string) config('app.name', 'Toko')),
            config('app.url'),
            config('mail.from.address', 'admin@localhost')
        );
    }
}
