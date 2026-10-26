<?php

namespace App\Support;

class DeviceDetector
{
    public static function detect(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return self::systemResult();
        }

        $ua = $userAgent;

        // 1. Bots
        if (preg_match('/(googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|facebookexternalhit)/i', $ua, $m)) {
            $botName = ucfirst($m[1]);
            return [
                'device_type'  => 'Bot',
                'device_label' => 'Web Crawler / Bot',
                'platform'     => 'Bot',
                'browser'      => $botName,
                'brand'        => null,
                'is_mobile'    => false,
                'is_tablet'    => false,
                'is_desktop'   => false,
                'icon'         => 'fa-robot',
                'badge_class'  => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                'formatted'    => "Bot · {$botName}",
            ];
        }

        // 2. Platform
        $platform  = 'Unknown OS';
        $isMobile  = false;
        $isTablet  = false;
        $isDesktop = false;

        if (preg_match('/windows nt 10\.0/i', $ua)) {
            $platform = 'Windows 10/11'; $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $platform = 'Windows 8.1'; $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $platform = 'Windows 8'; $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $platform = 'Windows 7'; $isDesktop = true;
        } elseif (preg_match('/windows/i', $ua)) {
            $platform = 'Windows'; $isDesktop = true;
        } elseif (preg_match('/ipad/i', $ua)) {
            $platform = 'iPadOS';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isTablet = true;
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform = 'iOS (iPhone)';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isMobile = true;
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
            if (preg_match('/Android (\d+(\.\d+)?)/i', $ua, $m)) {
                $platform .= ' ' . $m[1];
            }
            if (preg_match('/mobile/i', $ua)) {
                $isMobile = true;
            } else {
                $isTablet = true;
            }
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
            if (preg_match('/Mac OS X (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isDesktop = true;
        } elseif (preg_match('/cros/i', $ua)) {
            $platform = 'Chrome OS'; $isDesktop = true;
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux'; $isDesktop = true;
        }

        // 3. Browser
        $browser = 'Browser';
        if (preg_match('/edg\/([0-9.]+)/i', $ua, $m)) {
            $browser = 'Edge ' . explode('.', $m[1])[0];
        } elseif (preg_match('/samsungbrowser\/([0-9.]+)/i', $ua, $m)) {
            $browser = 'Samsung Internet ' . explode('.', $m[1])[0];
        } elseif (preg_match('/opr\/([0-9.]+)|opera\/([0-9.]+)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : $m[2];
            $browser = 'Opera ' . explode('.', $ver)[0];
        } elseif (preg_match('/chrome\/([0-9.]+)/i', $ua, $m)) {
            $browser = 'Chrome ' . explode('.', $m[1])[0];
        } elseif (preg_match('/firefox\/([0-9.]+)/i', $ua, $m)) {
            $browser = 'Firefox ' . explode('.', $m[1])[0];
        } elseif (preg_match('/version\/([0-9.]+).*safari/i', $ua, $m)) {
            $browser = 'Safari ' . explode('.', $m[1])[0];
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        }

        // 4. Device type
        if ($isTablet) {
            $deviceType = 'Tablet';
            $icon       = 'fa-tablet-screen-button';
            $badgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
        } elseif ($isMobile) {
            $deviceType = 'Mobile';
            $icon       = 'fa-mobile-screen-button';
            $badgeClass = 'bg-purple-50 text-purple-700 border border-purple-200';
        } else {
            $deviceType = 'Desktop';
            $isDesktop  = true;
            $icon       = 'fa-desktop';
            $badgeClass = 'bg-sky-50 text-sky-700 border border-sky-200';
        }

        // 5. Brand
        $brand = self::detectBrand($ua);

        return [
            'device_type'  => $deviceType,
            'device_label' => $deviceType,
            'platform'     => $platform,
            'browser'      => $browser,
            'brand'        => $brand,
            'is_mobile'    => $isMobile,
            'is_tablet'    => $isTablet,
            'is_desktop'   => $isDesktop,
            'icon'         => $icon,
            'badge_class'  => $badgeClass,
            'formatted'    => self::buildFormatted($brand, $platform, $browser),
        ];
    }

    public static function detectBrand(string $ua): ?string
    {
        if (preg_match('/iphone/i', $ua)) return 'Apple iPhone';
        if (preg_match('/ipad/i', $ua))   return 'Apple iPad';
        if (preg_match('/ipod/i', $ua))   return 'Apple iPod';
        if (preg_match('/macintosh|mac os x/i', $ua)) return 'Apple (Mac)';

        if (preg_match('/(?:samsung[\s;\/]?)?(SM-[A-Z]\d{3,4}[A-Z0-9]*|GT-[A-Z]\d{4}[A-Z0-9]*)/i', $ua, $m)) {
            return 'Samsung ' . strtoupper($m[1]);
        }
        if (preg_match('/samsung/i', $ua)) return 'Samsung';

        if (preg_match('/asus[_\s;]?([A-Z][A-Z0-9_\-]+)/i', $ua, $m)) {
            return 'ASUS ' . strtoupper(rtrim($m[1], '_-'));
        }
        if (preg_match('/(?:zenfone|zenpad|rog phone)[\s]*([\d]*[a-z]*)/i', $ua, $m)) {
            return 'ASUS ' . ucwords(strtolower($m[0]));
        }
        if (preg_match('/\basus\b/i', $ua)) return 'ASUS';

        if (preg_match('/\b(Redmi\s?(?:Note\s?)?\d+[A-Za-z]*\s?(?:Pro|Ultra|Plus)?)\b/i', $ua, $m)) {
            return 'Xiaomi ' . $m[1];
        }
        if (preg_match('/\b(POCO\s?[A-Z]\d+\s?(?:Pro|Ultra|NFC)?)\b/i', $ua, $m)) {
            return 'Xiaomi ' . $m[1];
        }
        if (preg_match('/xiaomi|redmi|miui|SHARK/i', $ua)) return 'Xiaomi';

        if (preg_match('/\b(CPH\d{4})\b/i', $ua, $m)) return 'OPPO ' . strtoupper($m[1]);
        if (preg_match('/\boppo\b|ColorOS/i', $ua))    return 'OPPO';

        if (preg_match('/\b(RMX\d{4})\b/i', $ua, $m)) return 'Realme ' . strtoupper($m[1]);
        if (preg_match('/\brealme\s?([A-Z\d]+\s?(?:Pro|GT|Ultra)?)/i', $ua, $m)) return 'Realme ' . $m[1];
        if (preg_match('/\brealme\b/i', $ua))           return 'Realme';

        if (preg_match('/\bvivo\s?([A-Z\d]+)/i', $ua, $m)) return 'Vivo ' . strtoupper($m[1]);
        if (preg_match('/OriginOS/i', $ua))             return 'Vivo';

        if (preg_match('/\bhonor\s?([A-Z\d]+)/i', $ua, $m)) return 'Honor ' . strtoupper($m[1]);

        if (preg_match('/\bhuawei\s?([A-Z\d\-]+)/i', $ua, $m)) return 'Huawei ' . strtoupper($m[1]);
        if (preg_match('/huawei|HRY-|VOG-|ELS-|ANA-|CLT-/i', $ua)) return 'Huawei';

        if (preg_match('/(?:OnePlus|oneplus)\s?([A-Z\d]+)/i', $ua, $m)) return 'OnePlus ' . strtoupper($m[1]);
        if (preg_match('/\bOP[A-Z]\d{4}\b/i', $ua, $m)) return 'OnePlus ' . strtoupper($m[0]);

        if (preg_match('/pixel\s?(\d[a-z]?)/i', $ua, $m)) return 'Google Pixel ' . strtoupper($m[1]);

        if (preg_match('/xperia\s?([A-Z\d ]+)/i', $ua, $m)) return 'Sony Xperia ' . trim($m[1]);
        if (preg_match('/\bsony\b/i', $ua)) return 'Sony';

        if (preg_match('/moto[la]?\s?([A-Z\d\s]+)/i', $ua, $m)) return 'Motorola ' . trim($m[1]);

        if (preg_match('/\blenovo\b/i', $ua)) return 'Lenovo';
        if (preg_match('/\bnokia\b/i', $ua))  return 'Nokia';
        if (preg_match('/\bLGE?\b|lge\s/i', $ua)) return 'LG';
        if (preg_match('/\binfinix\b/i', $ua)) return 'Infinix';
        if (preg_match('/\btecno\b/i', $ua))   return 'Tecno';
        if (preg_match('/\bhtc\b/i', $ua))     return 'HTC';

        return null;
    }

    private static function buildFormatted(?string $brand, string $platform, string $browser): string
    {
        $parts = array_filter([
            $brand,
            ($platform !== 'Unknown OS') ? $platform : null,
            ($browser !== 'Browser')     ? $browser  : null,
        ]);
        return implode(' · ', $parts) ?: "{$platform} · {$browser}";
    }

    public static function fromActivity(mixed $activity): array
    {
        $props = is_array($activity->properties)
            ? $activity->properties
            : (is_object($activity->properties) ? $activity->properties->toArray() : []);

        if (!empty($props['device']) && is_array($props['device'])) {
            $dev     = $props['device'];
            $devType = $dev['device_type'] ?? 'Desktop';

            $icon = match ($devType) {
                'Mobile' => 'fa-mobile-screen',
                'Tablet' => 'fa-tablet-screen-button',
                'Bot'    => 'fa-robot',
                'Sistem' => 'fa-server',
                default  => 'fa-desktop',
            };
            $badge = match ($devType) {
                'Mobile' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'Tablet' => 'bg-amber-50 text-amber-700 border border-amber-200',
                'Bot'    => 'bg-zinc-100 text-zinc-600 border border-zinc-200',
                'Sistem' => 'bg-slate-100 text-slate-700 border border-slate-200',
                default  => 'bg-sky-50 text-sky-700 border border-sky-200',
            };

            // Prioritas brand: 1) Client Hints (device_brand), 2) tersimpan di device array, 3) detect dari UA
            $brand = null;
            if (!empty($props['device_brand'])) {
                $brand = $props['device_brand'];         // dari Client Hints JS (paling akurat)
            } elseif (!empty($dev['brand'])) {
                $brand = $dev['brand'];                  // sudah ada di array device
            } elseif (!empty($props['user_agent'])) {
                $brand = self::detectBrand($props['user_agent']); // fallback UA parsing
            }

            $platform  = $dev['platform'] ?? 'Unknown OS';
            $browser   = $dev['browser']  ?? 'Browser';
            $formatted = self::buildFormatted($brand, $platform, $browser);

            return [
                'device_type'  => $devType,
                'device_label' => $dev['device_label'] ?? $devType,
                'platform'     => $platform,
                'browser'      => $browser,
                'brand'        => $brand,
                'is_mobile'    => $dev['is_mobile'] ?? ($devType === 'Mobile'),
                'is_tablet'    => $dev['is_tablet'] ?? ($devType === 'Tablet'),
                'is_desktop'   => $dev['is_desktop'] ?? ($devType === 'Desktop'),
                'icon'         => $dev['icon'] ?? $icon,
                'badge_class'  => $dev['badge_class'] ?? $badge,
                'formatted'    => $formatted,
            ];
        }

        // Ada user_agent — detect device lalu override brand dengan Client Hints jika ada
        if (!empty($props['user_agent'])) {
            $detected = self::detect($props['user_agent']);
            // Override brand dengan data Client Hints yang lebih akurat jika tersedia
            if (!empty($props['device_brand'])) {
                $detected['brand']     = $props['device_brand'];
                $detected['formatted'] = self::buildFormatted(
                    $props['device_brand'],
                    $detected['platform'],
                    $detected['browser']
                );
            }
            return $detected;
        }

        if ($activity->causer && in_array($activity->causer->role, ['admin', 'super_admin'])) {
            return [
                'device_type'  => 'Desktop',
                'device_label' => 'Desktop',
                'platform'     => 'Admin Console',
                'browser'      => 'Web Browser',
                'brand'        => null,
                'is_mobile'    => false,
                'is_tablet'    => false,
                'is_desktop'   => true,
                'icon'         => 'fa-desktop',
                'badge_class'  => 'bg-sky-50 text-sky-700 border border-sky-200',
                'formatted'    => 'Admin Console',
            ];
        }

        if ($activity->causer && $activity->causer->role === 'customer') {
            return [
                'device_type'  => 'Mobile / Web',
                'device_label' => 'Customer App / Web',
                'platform'     => 'Customer Session',
                'browser'      => 'Web Browser',
                'brand'        => null,
                'is_mobile'    => true,
                'is_tablet'    => false,
                'is_desktop'   => false,
                'icon'         => 'fa-mobile-screen-button',
                'badge_class'  => 'bg-purple-50 text-purple-700 border border-purple-200',
                'formatted'    => 'Customer · Mobile/Web',
            ];
        }

        if (empty($activity->causer_id)) {
            $isShopAction = in_array($activity->log_name, [
                'pesanan','ulasan','pengembalian','checkout','keranjang','toko','rpay','produk','pencarian',
            ]);
            if ($isShopAction) {
                return [
                    'device_type'  => 'Desktop / Mobile',
                    'device_label' => 'Pengunjung Toko',
                    'platform'     => 'Guest Browser',
                    'browser'      => 'Web Store',
                    'brand'        => null,
                    'is_mobile'    => false,
                    'is_tablet'    => false,
                    'is_desktop'   => true,
                    'icon'         => 'fa-globe',
                    'badge_class'  => 'bg-amber-50 text-amber-700 border border-amber-200',
                    'formatted'    => 'Pengunjung Toko',
                ];
            }
        }

        return self::systemResult();
    }

    public static function actorInfo(mixed $activity): array
    {
        if ($activity->causer) {
            $role = $activity->causer->role ?? 'user';

            if ($role === 'super_admin') {
                return [
                    'type'        => 'super_admin',
                    'role_label'  => 'Super Admin',
                    'badge_class' => 'bg-purple-100 text-purple-800 border border-purple-200',
                    'icon'        => 'fa-shield-halved',
                    'name'        => $activity->causer->name,
                    'email'       => $activity->causer->email,
                    'is_guest'    => false,
                ];
            }

            if ($role === 'admin') {
                return [
                    'type'        => 'admin',
                    'role_label'  => 'Admin',
                    'badge_class' => 'bg-blue-100 text-blue-800 border border-blue-200',
                    'icon'        => 'fa-user-shield',
                    'name'        => $activity->causer->name,
                    'email'       => $activity->causer->email,
                    'is_guest'    => false,
                ];
            }

            return [
                'type'        => 'customer',
                'role_label'  => 'Customer',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'icon'        => 'fa-user',
                'name'        => $activity->causer->name,
                'email'       => $activity->causer->email,
                'is_guest'    => false,
            ];
        }

        $isGuestStoreAction = in_array($activity->log_name, [
            'pesanan','ulasan','pengembalian','checkout','keranjang','toko','rpay','produk','pencarian','evaluasi_web',
        ]);

        if ($isGuestStoreAction) {
            return [
                'type'        => 'guest',
                'role_label'  => 'Guest (Tamu)',
                'badge_class' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'icon'        => 'fa-user-secret',
                'name'        => 'Pengunjung Tamu',
                'email'       => 'Belum / Tidak Login',
                'is_guest'    => true,
            ];
        }

        return [
            'type'        => 'system',
            'role_label'  => 'Sistem',
            'badge_class' => 'bg-gray-100 text-gray-700 border border-gray-200',
            'icon'        => 'fa-robot',
            'name'        => 'Sistem Otomatis',
            'email'       => '—',
            'is_guest'    => false,
        ];
    }

    private static function systemResult(): array
    {
        return [
            'device_type'  => 'Sistem',
            'device_label' => 'Sistem Otomatis',
            'platform'     => 'Server Task',
            'browser'      => 'Artisan / Cron',
            'brand'        => null,
            'is_mobile'    => false,
            'is_tablet'    => false,
            'is_desktop'   => false,
            'icon'         => 'fa-server',
            'badge_class'  => 'bg-slate-100 text-slate-700 border border-slate-200',
            'formatted'    => 'Sistem / CLI',
        ];
    }
}
