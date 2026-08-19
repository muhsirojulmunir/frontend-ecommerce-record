<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pencarian kode pos wilayah Indonesia.
 *
 * Sumbernya Biteship — kurir yang sudah dipakai toko ini — lewat endpoint
 * pencarian wilayahnya. Dipilih karena data kode pos Indonesia di peta terbuka
 * tidak lengkap: Kecamatan Dlanggu, misalnya, tidak punya kode pos sama sekali
 * di OpenStreetMap, sedangkan Biteship mengembalikannya dengan benar (61371).
 *
 * Satu kecamatan bisa punya beberapa kode pos. Semuanya dikembalikan sebagai
 * pilihan, bukan ditebak salah satu — pembeli yang paling tahu alamatnya.
 */
class KodePosService
{
    private const PANGKALAN = 'https://api.biteship.com/v1/maps/areas';

    /** Wilayah dan kode posnya nyaris tidak pernah berubah. */
    private const UMUR_CACHE_HARI = 30;

    /**
     * @return array<int, array{kode: string, label: string, wilayah: string}>
     */
    public function cari(string $kueri): array
    {
        $kueri = trim(preg_replace('/\s+/u', ' ', $kueri));

        if (mb_strlen($kueri) < 3) {
            return [];
        }

        $kunciCache = 'kodepos:' . md5(mb_strtolower($kueri));

        $tersimpan = Cache::get($kunciCache);
        if ($tersimpan !== null) {
            return $tersimpan;
        }

        $hasil = $this->ambil($kueri);

        // Kegagalan jaringan sengaja tidak disimpan, supaya gangguan sesaat
        // tidak membuat wilayah itu dianggap tak berkode pos selama sebulan.
        if ($hasil === false) {
            return [];
        }

        Cache::put($kunciCache, $hasil, now()->addDays(self::UMUR_CACHE_HARI));

        return $hasil;
    }

    /**
     * @return array<int, array>|false  false bila gagal dihubungi.
     */
    private function ambil(string $kueri): array|false
    {
        $kunci = env('BITESHIP_API_KEY');

        if (blank($kunci)) {
            return false;
        }

        try {
            $respons = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . $kunci])
                ->get(self::PANGKALAN, [
                    'countries' => 'ID',
                    'input'     => $kueri,
                    'type'      => 'single',
                ]);

            if (! $respons->successful()) {
                Log::warning('Pencarian kode pos ditolak Biteship', ['status' => $respons->status()]);

                return false;
            }

            return $this->rapikan($respons->json()['areas'] ?? []);
        } catch (\Throwable $e) {
            Log::warning('Pencarian kode pos gagal dihubungi', ['pesan' => $e->getMessage()]);

            return false;
        }
    }

    private function rapikan(array $areas): array
    {
        $pilihan = [];

        foreach ($areas as $area) {
            $kode = trim((string) ($area['postal_code'] ?? ''));

            if (! preg_match('/^\d{5}$/', $kode)) {
                continue;
            }

            // Satu kode pos cukup muncul sekali, meski dikembalikan berulang
            // untuk beberapa kelurahan di bawahnya.
            if (isset($pilihan[$kode])) {
                continue;
            }

            $wilayah = implode(', ', array_filter([
                $area['administrative_division_level_4'] ?? null,
                $area['administrative_division_level_3'] ?? null,
                $area['administrative_division_level_2'] ?? null,
            ]));

            if ($wilayah === '') {
                // Nama bawaan Biteship berbentuk "Dlanggu, Mojokerto, Jawa Timur. 61371"
                $wilayah = trim(preg_replace('/\.\s*\d{5}\s*$/', '', (string) ($area['name'] ?? '')));
            }

            $pilihan[$kode] = [
                'kode'    => $kode,
                'wilayah' => $wilayah,
                'label'   => $wilayah !== '' ? $kode . ' — ' . $wilayah : $kode,
            ];
        }

        return array_values($pilihan);
    }
}
