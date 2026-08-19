<?php

namespace App\Services;

use App\Exceptions\SaldoTidakCukup;
use App\Models\RpayTransaction;
use App\Models\User;
use App\Support\KalenderKerja;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Pengelola saldo R_Pay.
 */
class RpayService
{
    /** Menambah saldo. */
    public function kredit(
        User|int $pengguna,
        float $nominal,
        string $sumber,
        string $keterangan,
        ?Model $referensi = null,
        ?int $olehUserId = null
    ): RpayTransaction {
        return $this->bukukan($pengguna, 'credit', $nominal, $sumber, $keterangan, $referensi, $olehUserId);
    }

    /**
 * Mengurangi saldo.
 *
 * @throws SaldoTidakCukup bila saldo kurang dari nominal yang diminta.
 */
    public function debit(
        User|int $pengguna,
        float $nominal,
        string $sumber,
        string $keterangan,
        ?Model $referensi = null,
        ?int $olehUserId = null
    ): RpayTransaction {
        return $this->bukukan($pengguna, 'debit', $nominal, $sumber, $keterangan, $referensi, $olehUserId);
    }

    /**
     * Saldo terkini, dibaca dari buku besar — bukan dari kolom cache.
     */
    public function saldo(User|int $pengguna): float
    {
        $userId = $pengguna instanceof User ? $pengguna->id : $pengguna;

        return (float) RpayTransaction::where('user_id', $userId)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS saldo")
            ->value('saldo');
    }

    /**
 * Apakah sumber dana ini sudah pernah dibukukan?
 */
    public function sudahDibukukan(Model $referensi, string $sumber): bool
    {
        return RpayTransaction::where('reference_type', $referensi::class)
            ->where('reference_id', $referensi->getKey())
            ->where('source', $sumber)
            ->exists();
    }

    /**
 * Menghitung ulang kolom cache dari buku besar.
 */
    public function selaraskanSaldo(User|int $pengguna): float
    {
        $userId = $pengguna instanceof User ? $pengguna->id : $pengguna;
        $saldo  = $this->saldo($userId);

        User::whereKey($userId)->update(['rpay_balance' => $saldo]);

        return $saldo;
    }

    private function bukukan(
        User|int $pengguna,
        string $arah,
        float $nominal,
        string $sumber,
        string $keterangan,
        ?Model $referensi,
        ?int $olehUserId
    ): RpayTransaction {
        $nominal = round($nominal, 2);

        if ($nominal <= 0) {
            throw new \InvalidArgumentException('Nominal R_Pay harus lebih besar dari nol.');
        }

        $userId = $pengguna instanceof User ? $pengguna->id : $pengguna;

        return DB::transaction(function () use ($userId, $arah, $nominal, $sumber, $keterangan, $referensi, $olehUserId) {
            // Mengunci baris pengguna sampai transaksi selesai. Permintaan lain
            // yang menyentuh saldo orang yang sama akan menunggu di sini.
            $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();

            $saldoSekarang = $this->saldo($userId);

            if ($arah === 'debit' && $saldoSekarang < $nominal) {
                throw new SaldoTidakCukup(
                    'Saldo R_Pay tidak mencukupi. Saldo tersedia Rp '
                    . number_format($saldoSekarang, 0, ',', '.')
                    . ', dibutuhkan Rp ' . number_format($nominal, 0, ',', '.') . '.'
                );
            }

            $saldoBaru = $arah === 'credit'
                ? $saldoSekarang + $nominal
                : $saldoSekarang - $nominal;

            $transaksi = RpayTransaction::create([
                'user_id'        => $userId,
                'direction'      => $arah,
                'amount'         => $nominal,
                'balance_after'  => $saldoBaru,
                'source'         => $sumber,
                'reference_type' => $referensi ? $referensi::class : null,
                'reference_id'   => $referensi?->getKey(),
                'description'    => $keterangan,
                'created_by'     => $olehUserId,
            ]);

            // Kolom cache ikut diperbarui di dalam transaksi yang sama, jadi
            // tidak mungkin buku besar tersimpan sementara kolomnya tidak.
            $user->forceFill(['rpay_balance' => $saldoBaru])->save();

            return $transaksi;
        });
    }

    /**
 * Perkiraan tanggal dana sampai di rekening.
 */
    public function perkiraanCair(?CarbonImmutable $mulai = null, ?int $hariKerja = null): CarbonImmutable
    {
        return KalenderKerja::setelah(
            $hariKerja ?? (int) config('rpay.pencairan.hari_kerja', 2),
            $mulai
        );
    }

    public function hariKerja(CarbonImmutable $tanggal): bool
    {
        return KalenderKerja::hariKerja($tanggal);
    }
}
