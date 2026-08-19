<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Perhitungan hari kerja.
 *
 * Dipakai bersama oleh pencairan R_Pay dan tenggat peninjauan pengembalian,
 * supaya keduanya memakai daftar tanggal merah yang sama. Kalau logikanya
 * disalin ke dua tempat, cepat atau lambat salah satunya akan ketinggalan
 * saat daftar liburnya diperbarui.
 */
class KalenderKerja
{
    /** Hari Minggu selalu libur; Sabtu mengikuti pengaturan. */
    public static function hariKerja(CarbonImmutable $tanggal): bool
    {
        if ($tanggal->isSunday()) {
            return false;
        }

        if ($tanggal->isSaturday() && config('rpay.sabtu_libur', true)) {
            return false;
        }

        return ! in_array($tanggal->toDateString(), config('rpay.tanggal_merah', []), true);
    }

    /**
     * Tanggal setelah sekian hari kerja terlewati, menghitung maju dari
     * tanggal awal (tanggal awalnya sendiri tidak ikut dihitung).
     */
    public static function setelah(int $hariKerja, ?CarbonImmutable $mulai = null): CarbonImmutable
    {
        $tanggal  = ($mulai ?? CarbonImmutable::now())->startOfDay();
        $terlewat = 0;

        while ($terlewat < max(1, $hariKerja)) {
            $tanggal = $tanggal->addDay();

            if (self::hariKerja($tanggal)) {
                $terlewat++;
            }
        }

        return $tanggal;
    }
}
