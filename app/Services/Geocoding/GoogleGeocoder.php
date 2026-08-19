<?php

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Penyedia Google Maps Geocoding API.
 */
class GoogleGeocoder implements Geocoder
{
    private const PANGKALAN = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * Padanan jenis komponen alamat Google ke penamaan Nominatim.
     * Diurutkan: yang lebih spesifik didahulukan.
     */
    private const PADANAN = [
        'street_number'               => 'house_number',
        'route'                       => 'road',
        'administrative_area_level_4' => 'village',        // kelurahan/desa
        'administrative_area_level_3' => 'city_district',  // kecamatan
        'administrative_area_level_2' => 'city',           // kota/kabupaten
        'administrative_area_level_1' => 'state',          // provinsi
        'postal_code'                 => 'postcode',
    ];

    public function jedaDetik(): float
    {
        return (float) config('geocoding.jeda_detik.google', 0.05);
    }

    public function cari(string $kueri): array|null|false
    {
        return $this->panggil([
            'address'    => $kueri,
            'components' => 'country:ID',
        ]);
    }

    public function balik(float $lintang, float $bujur): array|null|false
    {
        return $this->panggil([
            'latlng' => $lintang . ',' . $bujur,
        ]);
    }

    private function panggil(array $parameter): array|null|false
    {
        $kunci = config('geocoding.google.key');

        if (blank($kunci)) {
            Log::warning('GEOCODING_DRIVER diatur ke google, tetapi GOOGLE_MAPS_API_KEY belum diisi.');

            return false;
        }

        try {
            $respons = Http::timeout(12)->get(self::PANGKALAN, $parameter + [
                'key'      => $kunci,
                'region'   => config('geocoding.google.region', 'id'),
                'language' => config('geocoding.google.bahasa', 'id'),
            ]);

            if (! $respons->successful()) {
                Log::warning('Geocoding ditolak Google', ['status' => $respons->status()]);

                return false;
            }

            $isi    = $respons->json();
            $status = $isi['status'] ?? 'UNKNOWN';

            if ($status === 'ZERO_RESULTS') {
                return null;
            }

            // Kunci salah, kuota habis, atau penagihan belum aktif — ini
            // gangguan layanan, bukan alamat yang tidak ada, jadi jangan
            // sampai tersimpan di cache sebagai "tidak ketemu".
            if ($status !== 'OK') {
                Log::warning('Geocoding Google mengembalikan galat', [
                    'status' => $status,
                    'pesan'  => $isi['error_message'] ?? null,
                ]);

                return false;
            }

            return $this->terjemahkan($isi['results'][0] ?? []);
        } catch (\Throwable $e) {
            Log::warning('Geocoding Google gagal dihubungi', ['pesan' => $e->getMessage()]);

            return false;
        }
    }

    private function terjemahkan(array $hasil): ?array
    {
        $titik = $hasil['geometry']['location'] ?? null;

        if (! isset($titik['lat'], $titik['lng'])) {
            return null;
        }

        $alamat = [];

        foreach ($hasil['address_components'] ?? [] as $bagian) {
            foreach ($bagian['types'] ?? [] as $jenis) {
                if (isset(self::PADANAN[$jenis]) && ! isset($alamat[self::PADANAN[$jenis]])) {
                    $alamat[self::PADANAN[$jenis]] = $bagian['long_name'] ?? '';
                }
            }
        }

        return [
            'lat'          => (string) $titik['lat'],
            'lon'          => (string) $titik['lng'],
            'display_name' => $hasil['formatted_address'] ?? '',
            'address'      => $alamat,
        ];
    }
}
