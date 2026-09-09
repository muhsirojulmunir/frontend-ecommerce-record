<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pencatat aktivitas untuk aplikasi toko online.
 * Menangkap seluruh jejak customer journey (melihat produk, pencarian, keranjang, checkout, dan login)
 * lengkap dengan IP, User-Agent, dan jenis perangkat (Mobile/Desktop/Tablet).
 */
class CatatAktivitas
{
    /**
     * Catat aktivitas umum ke tabel activity_log.
     */
    public static function tulis(
        string $grup,
        string $keterangan,
        ?Model $subjek = null,
        array $properti = [],
        ?string $peristiwa = null
    ): void {
        try {
            $pelaku = Auth::user();
            $request = request();
            $userAgent = $request ? $request->userAgent() : null;
            $metadataPerangkat = [
                'ip'         => $request ? $request->ip() : null,
                'user_agent' => $userAgent,
                'device'     => self::parseDevice($userAgent),
            ];
            $propertiLengkap = array_merge($metadataPerangkat, $properti ?: []);

            DB::table('activity_log')->insert([
                'log_name'     => $grup,
                'description'  => $keterangan,
                'subject_type' => $subjek ? $subjek::class : null,
                'subject_id'   => $subjek?->getKey(),
                'event'        => $peristiwa,
                'causer_type'  => $pelaku ? $pelaku::class : null,
                'causer_id'    => $pelaku?->getKey(),
                'properties'   => json_encode($propertiLengkap ?: new \stdClass),
                'batch_uuid'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat aktivitas', [
                'keterangan' => $keterangan,
                'pesan'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Catat saat pengunjung/customer melihat detail produk.
     * Dilengkapi anti-spam session guard (throttle 5 menit per produk).
     */
    public static function tulisProdukView(Model $product): void
    {
        try {
            $sessionKey = 'viewed_prod_' . $product->getKey();
            $lastViewed = session()->get($sessionKey);

            // Jika dalam 5 menit terakhir produk yang sama sudah dilihat oleh sesi ini, abaikan agar log tidak spam
            if ($lastViewed && (now()->timestamp - (int) $lastViewed < 300)) {
                return;
            }

            session()->put($sessionKey, now()->timestamp);

            $hargaFormat = number_format($product->price ?? 0, 0, ',', '.');
            $keterangan  = "Melihat produk: {$product->name} (Rp {$hargaFormat})";

            self::tulis(
                grup: 'produk',
                keterangan: $keterangan,
                subjek: $product,
                properti: [
                    'product_id'     => $product->getKey(),
                    'product_name'   => $product->name,
                    'product_slug'   => $product->slug ?? null,
                    'category'       => $product->category?->name ?? '-',
                    'price'          => (float) ($product->price ?? 0),
                    'stock'          => (int) ($product->stock ?? 0),
                    'total_variants' => $product->variants ? $product->variants->count() : 0,
                ],
                peristiwa: 'view'
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat view produk: ' . $e->getMessage());
        }
    }

    /**
     * Catat saat pengunjung melakukan pencarian produk di search bar.
     * Dilengkapi throttle 20 detik untuk kata kunci yang sama.
     */
    public static function tulisPencarian(string $keyword, int $count, ?string $category = null): void
    {
        try {
            $keyword = trim($keyword);
            if (empty($keyword)) {
                return;
            }

            $lastSearchKey = session()->get('last_search_keyword');
            $lastSearchTime = session()->get('last_search_time');

            if ($lastSearchKey === $keyword && $lastSearchTime && (now()->timestamp - (int) $lastSearchTime < 20)) {
                return;
            }

            session()->put('last_search_keyword', $keyword);
            session()->put('last_search_time', now()->timestamp);

            $keterangan = "Mencari produk dengan kata kunci \"{$keyword}\" ({$count} hasil ditemukan)";

            self::tulis(
                grup: 'pencarian',
                keterangan: $keterangan,
                subjek: null,
                properti: [
                    'keyword'         => $keyword,
                    'results_count'   => $count,
                    'category_filter' => $category,
                ],
                peristiwa: 'search'
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat pencarian: ' . $e->getMessage());
        }
    }

    /**
     * Catat aktivitas keranjang belanja.
     */
    public static function tulisKeranjang(string $aksi, string $keterangan, array $data = []): void
    {
        self::tulis(
            grup: 'keranjang',
            keterangan: $keterangan,
            subjek: null,
            properti: $data,
            peristiwa: $aksi
        );
    }

    /**
     * Catat aktivitas proses kasir / checkout.
     */
    public static function tulisCheckout(string $aksi, string $keterangan, array $data = []): void
    {
        self::tulis(
            grup: 'checkout',
            keterangan: $keterangan,
            subjek: null,
            properti: $data,
            peristiwa: $aksi
        );
    }

    /**
     * Catat aktivitas autentikasi customer (login, register, logout).
     */
    public static function tulisAuth(string $aksi, string $keterangan, ?Model $user = null): void
    {
        self::tulis(
            grup: 'auth',
            keterangan: $keterangan,
            subjek: $user,
            properti: [
                'action' => $aksi,
                'email'  => $user?->email,
                'name'   => $user?->name,
                'role'   => $user?->role ?? 'customer',
            ],
            peristiwa: $aksi
        );
    }

    /**
     * Deteksi jenis perangkat, platform sistem operasi, dan browser dari User Agent.
     */
    private static function parseDevice(?string $ua): array
    {
        if (empty($ua)) {
            return [
                'device_type' => 'Sistem',
                'platform'    => 'Server',
                'browser'     => 'CLI',
                'formatted'   => 'Sistem / CLI',
            ];
        }

        // 1. Tipe Perangkat & OS
        if (preg_match('/ipad/i', $ua)) {
            $platform   = 'iPadOS';
            $deviceType = 'Tablet';
        } elseif (preg_match('/android/i', $ua) && ! preg_match('/mobile/i', $ua)) {
            $platform   = 'Android Tablet';
            $deviceType = 'Tablet';
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform   = 'iOS (iPhone)';
            $deviceType = 'Mobile';
        } elseif (preg_match('/android.*mobile/i', $ua)) {
            $platform   = 'Android';
            $deviceType = 'Mobile';
            if (preg_match('/Android (\d+(\.\d+)?)/i', $ua, $m)) {
                $platform .= ' ' . $m[1];
            }
        } elseif (preg_match('/windows nt 10\.0/i', $ua)) {
            $platform   = 'Windows 10/11';
            $deviceType = 'Desktop';
        } elseif (preg_match('/windows/i', $ua)) {
            $platform   = 'Windows';
            $deviceType = 'Desktop';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform   = 'macOS';
            $deviceType = 'Desktop';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform   = 'Linux';
            $deviceType = 'Desktop';
        } else {
            $platform   = 'Unknown OS';
            $deviceType = 'Desktop';
        }

        // 2. Browser
        $browser = 'Web Browser';
        if (preg_match('/edg\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Edge ' . explode('.', $m[1])[0];
        } elseif (preg_match('/samsungbrowser\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Samsung ' . explode('.', $m[1])[0];
        } elseif (preg_match('/chrome\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Chrome ' . explode('.', $m[1])[0];
        } elseif (preg_match('/firefox\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Firefox ' . explode('.', $m[1])[0];
        } elseif (preg_match('/version\/([\d.]+).*safari/i', $ua, $m)) {
            $browser = 'Safari ' . explode('.', $m[1])[0];
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        }

        return [
            'device_type' => $deviceType,
            'platform'    => $platform,
            'browser'     => $browser,
            'formatted'   => $deviceType . ' - ' . $platform . ' - ' . $browser,
        ];
    }
}
