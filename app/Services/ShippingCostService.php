<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingCostService
{
    /**
     * Koordinat toko/gudang asal pengiriman.
     * Default: Surabaya, Jawa Timur
     */
    protected float $storeLat;
    protected float $storeLng;
    protected string $storeCity;

    public function __construct()
    {
        $this->storeLat  = (float) config('pengiriman.toko.lintang');
        $this->storeLng  = (float) config('pengiriman.toko.bujur');
        $this->storeCity = (string) config('pengiriman.toko.kota');
    }

    /**
     * Hitung jarak (km) antara dua titik koordinat menggunakan rumus Haversine.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371; // Radius bumi dalam km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($R * $c, 2);
    }

    /**
     * Apakah tujuan cukup dekat untuk dilayani pengiriman instan?
     *
     * Kurir instan mengantar memakai motor dalam hitungan jam, jadi hanya
     * masuk akal untuk tujuan di sekitar toko. Di luar radius ini pilihannya
     * tidak ditampilkan sama sekali — bukan ditampilkan lalu ditolak saat
     * pemesanan, yang hanya membuat pembeli kecewa di akhir.
     */
    /**
     * Apakah tujuan cukup dekat untuk dilayani pengiriman instan?
     *
     * Kurir instan hanya melayani pengiriman di dalam wilayah Kota Surabaya
     * dengan radius maksimal 15 KM dari toko. Wilayah luar kota (misal: Mojokerto,
     * Malang, Gresik, Sidoarjo jauh, dll) ditolak secara tegas (return false).
     */
    public function bolehInstan(float $destLat, float $destLng, ?string $destCity = null): bool
    {
        if (! config('pengiriman.instan.aktif', true)) {
            return false;
        }

        // Pengecekan 1: Nama Kota/Kabupaten
        // Jika nama kota diisi dan BUKAN mengandung "surabaya", instan DITOLAK MUTLAK.
        if (filled($destCity)) {
            $cityLower = strtolower(trim((string) $destCity));
            if (! str_contains($cityLower, 'surabaya')) {
                return false;
            }
        }

        // Pengecekan 2: Jika koordinat menggunakan default toko (-7.2575, 112.7521)
        // tetapi kota bukan Surabaya, jangan pernah izinkan instan.
        if (abs($destLat - $this->storeLat) < 0.0001 && abs($destLng - $this->storeLng) < 0.0001) {
            if (filled($destCity) && ! str_contains(strtolower((string) $destCity), 'surabaya')) {
                return false;
            }
        }

        // Pengecekan 3: Radius Haversine (Maksimal 15 KM dari koordinat toko Surabaya)
        $jarak = $this->haversineDistance($this->storeLat, $this->storeLng, $destLat, $destLng);
        $maxRadius = (float) config('pengiriman.instan.radius_km', 15);

        return $jarak <= $maxRadius;
    }

    /**
     * Menaikkan tarif kurir sekian persen sebelum ditagihkan ke pembeli.
     *
     * Selisihnya menjadi keuntungan toko dan dicatat per pesanan. Ditaruh di
     * satu tempat ini supaya semua jalur — tarif Biteship maupun perhitungan
     * cadangan saat Biteship tak terhubung — memakai aturan yang sama persis.
     * Kalau markupnya dihitung di beberapa tempat, cepat atau lambat salah
     * satunya akan tertinggal saat angkanya diubah.
     *
     * Hasilnya dibulatkan KE ATAS ke kelipatan yang disetel, jadi markup yang
     * sebenarnya tidak pernah kurang dari yang diniatkan.
     */
    public function denganMarkup(int $tarifAsli): int
    {
        $persen = (float) config('biaya.markup_ongkir_persen', 0);

        if ($persen <= 0 || $tarifAsli <= 0) {
            return $tarifAsli;
        }

        $setelahMarkup = $tarifAsli * (1 + $persen / 100);
        $bulat = max(1, (int) config('biaya.pembulatan_ongkir', 500));

        return (int) (ceil($setelahMarkup / $bulat) * $bulat);
    }

    /**
     * Kapan paket instan akan dijemput, dan bagaimana menyebutkannya.
     *
     * Instan tetap bisa dipesan kapan saja — yang berubah hanya janjinya.
     * Kurir instan baru bekerja pada jam tertentu, jadi pesanan di luar jam
     * itu tidak batal, melainkan menunggu jam kerja berikutnya:
     *
     *   - Dalam jam kerja        -> dijemput sekarang, sampai hitungan jam.
     *   - Sebelum jam buka       -> dijemput hari ini juga saat jam buka.
     *   - Setelah jam tutup      -> dijemput besok saat jam buka.
     *
     * Disebutkan apa adanya sejak halaman checkout supaya pembeli tahu persis
     * yang ia beli. Menjanjikan "1-3 jam" pada pesanan pukul sebelas malam
     * hanya menunda kekecewaan sampai besok pagi.
     *
     * Jamnya WIB, sedangkan aplikasi berjalan pada UTC — selisih 7 jam. Tanpa
     * konversi, jadwalnya akan bergeser sama sekali.
     */
    public function jadwalInstan(): array
    {
        $mulai   = (int) config('pengiriman.instan.jam_mulai', 8);
        $selesai = (int) config('pengiriman.instan.jam_selesai', 16);
        $zona    = config('pengiriman.instan.zona_waktu', 'Asia/Jakarta');

        $sekarang = now()->setTimezone($zona);
        $jam      = (int) $sekarang->format('G');

        // Dalam jam kerja: berjalan seperti biasa.
        if ($jam >= $mulai && $jam < $selesai) {
            return [
                'etd'      => '1-3 jam',
                'catatan'  => null,
                'dijemput' => $sekarang->copy(),
            ];
        }

        $jamBuka = sprintf('%02d.00', $mulai);

        // Sebelum jam buka: masih hari ini.
        if ($jam < $mulai) {
            return [
                'etd'      => 'Dijemput hari ini pukul ' . $jamBuka,
                'catatan'  => 'Pesanan masuk di luar jam kurir. Dijemput begitu kurir mulai '
                            . 'bekerja pukul ' . $jamBuka . ' WIB, tetap hari ini.',
                'dijemput' => $sekarang->copy()->setTime($mulai, 0),
            ];
        }

        // Setelah jam tutup: baru besok.
        return [
            'etd'      => 'Dijemput besok pukul ' . $jamBuka,
            'catatan'  => 'Kurir instan sudah tutup hari ini. Pesananmu dijemput besok '
                        . 'pukul ' . $jamBuka . ' WIB.',
            'dijemput' => $sekarang->copy()->addDay()->setTime($mulai, 0),
        ];
    }

    /**
     * Estimasi berat total dalam gram berdasarkan jumlah item.
     * Asumsi: rata-rata 1 pasang sepatu = 800 gram
     */
    protected function estimateWeight(int $totalQty): int
    {
        $gramsPerPair = (int) config('pengiriman.berat_per_pasang_gram', 800);
        $weight = $totalQty * $gramsPerPair;

        // Minimum 500g, bulatkan ke atas per 1000g untuk perhitungan tarif
        return max(500, (int) ceil($weight / 1000) * 1000);
    }

    /**
     * Tentukan zona pengiriman berdasarkan jarak (km).
     * Ini disesuaikan dengan zona tarif riil kurir Indonesia.
     */
    protected function getZone(float $distanceKm): string
    {
        if ($distanceKm <= 50)    return 'lokal';    // Dalam kota / sekitar
        if ($distanceKm <= 300)   return 'jawa1';    // Dalam pulau Jawa dekat
        if ($distanceKm <= 800)   return 'jawa2';    // Lintas kota di Jawa
        if ($distanceKm <= 1500)  return 'luar1';    // Bali, Sumatera, Kalimantan dekat
        if ($distanceKm <= 3000)  return 'luar2';    // Kalimantan jauh, Sulawesi
        return 'luar3';                               // Papua, Maluku, NTT
    }

    /**
     * Hitung biaya pengiriman semua kurir berdasarkan jarak + berat + API Biteship.
     *
     * @param float       $destLat Latitude tujuan (customer)
     * @param float       $destLng Longitude tujuan (customer)
     * @param int         $totalQty Total jumlah item di keranjang
     * @param string|null $destCity Nama kota tujuan
     * @param string|null $destPostalCode Kode pos tujuan
     * @return array Daftar kurir dengan biaya dan estimasi waktu
     */
    public function calculate(
        float $destLat,
        float $destLng,
        int $totalQty = 1,
        ?string $destCity = null,
        ?string $destPostalCode = null
    ): array {
        $jarakKm = $this->haversineDistance($this->storeLat, $this->storeLng, $destLat, $destLng);
        $instan  = $this->bolehInstan($destLat, $destLng, $destCity);

        $hasil = $this->dariBiteship($destLat, $destLng, $totalQty, $jarakKm, $instan, $destPostalCode)
            ?? $this->dariPerhitunganSendiri($jarakKm, $totalQty, $instan);

        return $this->urutkan($hasil);
    }

    /**
     * Ambil tarif resmi dari API Biteship secara real-time.
     */
    protected function dariBiteship(
        float $destLat,
        float $destLng,
        int $totalQty,
        float $jarakKm,
        bool $instan,
        ?string $destPostalCode = null
    ): ?array {
        $kunci = env('BITESHIP_API_KEY');

        if (blank($kunci)) {
            return null;
        }

        $kurir = config('pengiriman.kurir_reguler', []);

        if ($instan) {
            $kurir = array_merge(config('pengiriman.instan.kurir', []), $kurir);
        }

        try {
            $body = [
                'origin_latitude'       => $this->storeLat,
                'origin_longitude'      => $this->storeLng,
                'destination_latitude'  => $destLat,
                'destination_longitude' => $destLng,
                'couriers'              => implode(',', array_unique($kurir)),
                'items'                 => [[
                    'name'     => 'Sepatu RECORD',
                    'weight'   => $this->estimateWeight($totalQty),
                    'value'    => 150000,
                    'quantity' => $totalQty,
                ]],
            ];

            if (filled($destPostalCode)) {
                $body['destination_postal_code'] = (int) $destPostalCode;
            }

            $respons = Http::timeout(6)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $kunci,
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim(env('BITESHIP_API_URL', 'https://api.biteship.com/v1'), '/') . '/rates/couriers', $body);

            if (! $respons->successful() || empty($respons->json()['pricing'])) {
                return null;
            }

            $daftar = [];

            foreach ($respons->json()['pricing'] as $tarif) {
                $kodeKurir    = strtolower($tarif['courier_code'] ?? '');
                $adalahInstan = $this->tergolongInstan($tarif, $kodeKurir);

                // Jaring pengaman mutlak: jika Biteship mengembalikan instan tetapi $instan == false (misal ke Mojokerto), buang!
                if ($adalahInstan && ! $instan) {
                    continue;
                }

                $layanan = $tarif['courier_service_name'] ?? 'REG';

                $tarifAsli = (int) ($tarif['price'] ?? 0);

                $daftar[] = [
                    // Kode harus unik per layanan, sebab satu kurir bisa
                    // menawarkan beberapa layanan sekaligus (Instant & Same Day).
                    'code'        => $kodeKurir . ':' . strtolower($tarif['courier_service_code'] ?? 'reg'),
                    'name'        => ($tarif['courier_name'] ?? strtoupper($kodeKurir)) . ' (' . $layanan . ')',
                    'cost'        => $this->denganMarkup($tarifAsli),
                    // Tarif yang benar-benar ditagihkan Biteship ke toko.
                    // Disimpan berdampingan supaya keuntungannya bisa dihitung
                    // per pesanan tanpa perlu menanyakan ulang ke Biteship.
                    'cost_actual' => $tarifAsli,
                    // Perkiraan untuk instan memakai jadwal jemput, bukan
                    // durasi mentah dari Biteship — durasi itu dihitung sejak
                    // paket DIJEMPUT, dan di luar jam kerja penjemputannya
                    // sendiri baru terjadi besok.
                    'etd'         => $adalahInstan
                        ? $this->jadwalInstan()['etd']
                        : ($tarif['duration'] ?? '1-3 Hari'),
                    'catatan'     => $adalahInstan ? $this->jadwalInstan()['catatan'] : null,
                    'jenis'       => $adalahInstan ? 'instan' : 'reguler',
                    'weight_g'    => $this->estimateWeight($totalQty),
                    'distance_km' => $jarakKm,
                    'zone'        => 'biteship_api',
                ];
            }

            return $daftar ?: null;
        } catch (\Throwable $e) {
            Log::warning('Biteship tidak bisa dihubungi, memakai perhitungan sendiri', [
                'pesan' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Biteship menandai layanan cepat lewat service_type, tetapi tidak selalu
     * konsisten antar kurir. Nama layanannya ikut diperiksa sebagai cadangan.
     */
    protected function tergolongInstan(array $tarif, string $kodeKurir): bool
    {
        $jenis = strtolower((string) ($tarif['service_type'] ?? ''));

        if (in_array($jenis, ['instant', 'same_day', 'same day'], true)) {
            return true;
        }

        $nama = strtolower(($tarif['courier_service_name'] ?? '') . ' ' . ($tarif['courier_service_code'] ?? ''));

        if (str_contains($nama, 'instant') || str_contains($nama, 'same day') || str_contains($nama, 'sameday')) {
            return true;
        }

        // Kurir yang memang hanya melayani antar instan.
        return in_array($kodeKurir, ['gojek', 'grab', 'lalamove', 'borzo', 'deliveree'], true);
    }

    /**
     * Perhitungan cadangan bila Biteship sedang tidak bisa dihubungi.
     */
    protected function dariPerhitunganSendiri(float $distanceKm, int $totalQty, bool $instan): array
    {
        $zone     = $this->getZone($distanceKm);
        $weightG  = $this->estimateWeight($totalQty);
        $weightKg = ceil($weightG / 1000); // Bulatkan ke atas per kg

        // Tarif dasar (Rp per kg) per zona per kurir
        // Dikalibrasi berdasarkan rata-rata tarif publik 2024
        $rates = [
            'jne' => [
                'name'    => 'JNE Reguler (REG)',
                'lokal'   => ['cost' => 8000,  'etd' => '1 Hari'],
                'jawa1'   => ['cost' => 9000,  'etd' => '1-2 Hari'],
                'jawa2'   => ['cost' => 11000, 'etd' => '2-3 Hari'],
                'luar1'   => ['cost' => 18000, 'etd' => '3-4 Hari'],
                'luar2'   => ['cost' => 24000, 'etd' => '4-5 Hari'],
                'luar3'   => ['cost' => 35000, 'etd' => '5-7 Hari'],
            ],
            'jnt' => [
                'name'    => 'J&T Express',
                'lokal'   => ['cost' => 7000,  'etd' => '1 Hari'],
                'jawa1'   => ['cost' => 8000,  'etd' => '1-2 Hari'],
                'jawa2'   => ['cost' => 10000, 'etd' => '2-3 Hari'],
                'luar1'   => ['cost' => 16000, 'etd' => '3-4 Hari'],
                'luar2'   => ['cost' => 22000, 'etd' => '4-5 Hari'],
                'luar3'   => ['cost' => 32000, 'etd' => '5-7 Hari'],
            ],
            'sicepat' => [
                'name'    => 'SiCepat REG',
                'lokal'   => ['cost' => 7000,  'etd' => '1 Hari'],
                'jawa1'   => ['cost' => 8000,  'etd' => '1-2 Hari'],
                'jawa2'   => ['cost' => 10000, 'etd' => '2-3 Hari'],
                'luar1'   => ['cost' => 17000, 'etd' => '3-5 Hari'],
                'luar2'   => ['cost' => 23000, 'etd' => '4-6 Hari'],
                'luar3'   => ['cost' => 33000, 'etd' => '6-8 Hari'],
            ],
            'anteraja' => [
                'name'    => 'AnterAja Reguler',
                'lokal'   => ['cost' => 6500,  'etd' => '1 Hari'],
                'jawa1'   => ['cost' => 7500,  'etd' => '1-2 Hari'],
                'jawa2'   => ['cost' => 9500,  'etd' => '2-3 Hari'],
                'luar1'   => ['cost' => 16000, 'etd' => '3-5 Hari'],
                'luar2'   => ['cost' => 21000, 'etd' => '4-6 Hari'],
                'luar3'   => ['cost' => 30000, 'etd' => '6-8 Hari'],
            ],
            'pos' => [
                'name'    => 'Pos Indonesia',
                'lokal'   => ['cost' => 7000,  'etd' => '1-2 Hari'],
                'jawa1'   => ['cost' => 8000,  'etd' => '2-3 Hari'],
                'jawa2'   => ['cost' => 10000, 'etd' => '3-4 Hari'],
                'luar1'   => ['cost' => 15000, 'etd' => '4-6 Hari'],
                'luar2'   => ['cost' => 20000, 'etd' => '5-8 Hari'],
                'luar3'   => ['cost' => 28000, 'etd' => '7-14 Hari'],
            ],
        ];

        $result = [];

        // Pilihan instan didahulukan agar tampil paling atas.
        if ($instan) {
            foreach (config('pengiriman.instan.cadangan', []) as $layanan) {
                $kelebihanKm = max(0, $distanceKm - (float) $layanan['jarak_dasar_km']);
                $biaya       = $layanan['dasar'] + ($kelebihanKm * $layanan['per_km']);

                // Barang berat butuh lebih dari satu kali antar.
                $biaya *= max(1, (int) ceil($weightG / 20000));

                $result[] = [
                    'code'        => $layanan['kode'] . ':instant',
                    'name'        => $layanan['nama'],
                    'cost'        => $this->denganMarkup((int) (ceil($biaya / 500) * 500)),
                    'cost_actual' => (int) (ceil($biaya / 500) * 500),
                    'etd'         => $this->jadwalInstan()['etd'],
                    'catatan'     => $this->jadwalInstan()['catatan'],
                    'jenis'       => 'instan',
                    'weight_g'    => $weightG,
                    'distance_km' => $distanceKm,
                    'zone'        => 'instan_lokal',
                ];
            }
        }

        foreach ($rates as $code => $info) {
            $ratePerKg = $info[$zone]['cost'];
            $etd       = $info[$zone]['etd'];

            // Biaya = tarif per kg × jumlah kg (minimum 1 kg)
            $totalCost = $ratePerKg * max(1, $weightKg);

            // Tambahkan biaya jarak (lebih jauh = lebih mahal)
            // Rp 10 per km sebagai surcharge jarak
            $distanceSurcharge = max(0, round($distanceKm * 10 / 500) * 500);
            $totalCost += $distanceSurcharge;

            // Bulatkan ke ratusan terdekat
            $totalCost = (int) ceil($totalCost / 500) * 500;

            $result[] = [
                'code'        => $code,
                'name'        => $info['name'],
                'cost'        => $this->denganMarkup($totalCost),
                'cost_actual' => $totalCost,
                'etd'         => $etd,
                'catatan'     => null,
                'jenis'       => 'reguler',
                'weight_g'    => $weightG,
                'distance_km' => $distanceKm,
                'zone'        => $zone,
            ];
        }

        return $result;
    }

    /**
     * Instan lebih dulu, lalu yang termurah — pembeli yang butuh cepat
     * langsung melihat pilihannya tanpa menggulir.
     */
    protected function urutkan(array $daftar): array
    {
        usort($daftar, function (array $a, array $b) {
            $jenisA = ($a['jenis'] ?? 'reguler') === 'instan' ? 0 : 1;
            $jenisB = ($b['jenis'] ?? 'reguler') === 'instan' ? 0 : 1;

            return $jenisA === $jenisB
                ? ($a['cost'] ?? 0) <=> ($b['cost'] ?? 0)
                : $jenisA <=> $jenisB;
        });

        return $daftar;
    }
}
