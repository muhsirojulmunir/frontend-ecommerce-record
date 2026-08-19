<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pencatat aktivitas untuk aplikasi toko.
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

            DB::table('activity_log')->insert([
                'log_name'     => $grup,
                'description'  => $keterangan,
                'subject_type' => $subjek ? $subjek::class : null,
                'subject_id'   => $subjek?->getKey(),
                'event'        => $peristiwa,
                'causer_type'  => $pelaku ? $pelaku::class : null,
                'causer_id'    => $pelaku?->getKey(),
                'properties'   => json_encode($properti ?: new \stdClass),
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
}
