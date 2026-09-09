<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pencatat aktivitas untuk aplikasi toko.
 * Setiap aktivitas yang dicatat juga menyimpan IP, User-Agent, dan informasi perangkat
 * secara otomatis, baik untuk user yang sudah login maupun pengunjung tamu (guest).
 */
class CatatAktivitas
{
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

    private static function parseDevice(?string $ua): array
    {
        if (empty($ua)) {
            return ['device_type' => 'Sistem', 'platform' => 'Server', 'browser' => 'CLI', 'formatted' => 'Sistem / CLI'];
        }
        if (preg_match('/ipad/i', $ua)) {
            $platform = 'iPadOS'; $deviceType = 'Tablet';
        } elseif (preg_match('/android/i', $ua) && !preg_match('/mobile/i', $ua)) {
            $platform = 'Android Tablet'; $deviceType = 'Tablet';
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform = 'iOS (iPhone)'; $deviceType = 'Mobile';
        } elseif (preg_match('/android.*mobile/i', $ua)) {
            $platform = 'Android'; $deviceType = 'Mobile';
            if (preg_match('/Android (\d+(\.\d+)?)/i', $ua, $m)) { $platform .= ' ' . $m[1]; }
        } elseif (preg_match('/windows nt 10\.0/i', $ua)) {
            $platform = 'Windows 10/11'; $deviceType = 'Desktop';
        } elseif (preg_match('/windows/i', $ua)) {
            $platform = 'Windows'; $deviceType = 'Desktop';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS'; $deviceType = 'Desktop';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux'; $deviceType = 'Desktop';
        } else {
            $platform = 'Unknown OS'; $deviceType = 'Desktop';
        }
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
