<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Kode referal RECORD-NAMAPENGGUNA.
 *
 * Aturan keabsahan sengaja TIDAK disimpan sebagai penanda menyala/mati,
 * melainkan diturunkan dari keadaan pesanan pemiliknya:
 *
 *   Kode sah  ⇔  pemiliknya punya minimal satu pesanan yang lunas
 *                dan tidak dibatalkan.
 *
 * Dengan begitu tidak ada penanda yang bisa ketinggalan diperbarui. Pesanan
 * yang sudah dibayar lalu dibatalkan otomatis membuat kodenya hangus, dan
 * kalau kelak ia berbelanja lagi kodenya hidup kembali dengan sendirinya —
 * tanpa satu baris pun kode tambahan.
 */
class ReferralService
{
    /**
     * Menerbitkan kode untuk pemilik akun, sekali seumur akun.
     *
     * Dipanggil saat pesanan pertamanya dinyatakan lunas. Pemanggilan
     * berikutnya tidak mengubah apa pun — kode yang sudah beredar tidak boleh
     * berganti, karena bisa jadi sudah dibagikan ke orang lain.
     */
    public function terbitkan(User $pemilik): string
    {
        if (filled($pemilik->referral_code)) {
            return $pemilik->referral_code;
        }

        $kode = $this->kodeUnik($pemilik);

        $pemilik->forceFill([
            'referral_code'      => $kode,
            'referral_issued_at' => now(),
        ])->save();

        return $kode;
    }

    /**
     * Apakah kode ini boleh dipakai oleh $pemakai?
     *
     * @return array{sah: bool, alasan: ?string, pemilik: ?User}
     */
    public function periksa(?string $kode, ?User $pemakai = null): array
    {
        $kode = $this->rapikan($kode);

        if ($kode === '') {
            return ['sah' => false, 'alasan' => null, 'pemilik' => null];
        }

        $pemilik = User::where('referral_code', $kode)->first();

        if (! $pemilik) {
            return ['sah' => false, 'alasan' => 'Kode referal tidak valid.', 'pemilik' => null];
        }

        if ($pemakai && $pemilik->id === $pemakai->id) {
            return [
                'sah'     => false,
                'alasan'  => 'Kode referal milikmu sendiri tidak bisa dipakai.',
                'pemilik' => null,
            ];
        }

        if ($pemilik->is_blocked) {
            return ['sah' => false, 'alasan' => 'Kode referal tidak valid.', 'pemilik' => null];
        }

        // Sengaja memakai pesan yang sama dengan kode tidak dikenal.
        // Menyebutkan "pesanan pemiliknya dibatalkan" berarti membocorkan
        // keadaan akun orang lain kepada siapa pun yang menebak-nebak kode.
        if (! $this->punyaPesananSah($pemilik)) {
            return ['sah' => false, 'alasan' => 'Kode referal tidak valid.', 'pemilik' => null];
        }

        return ['sah' => true, 'alasan' => null, 'pemilik' => $pemilik];
    }

    /**
     * Pemilik kode punya pesanan yang benar-benar jadi?
     *
     * Lunas saja tidak cukup: pesanan yang sudah dibayar lalu dibatalkan
     * tidak boleh menghidupkan kode. Inilah yang membuat kode "hangus".
     */
    public function punyaPesananSah(User $pemilik): bool
    {
        return Order::where('user_id', $pemilik->id)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    /** Potongan untuk pembeli, dari harga barang saja. */
    public function diskon(float $hargaBarang): float
    {
        return $this->persen($hargaBarang, (float) config('referal.persen_diskon', 3));
    }

    /** Komisi untuk pemilik kode, dari harga barang saja. */
    public function komisi(float $hargaBarang): float
    {
        return $this->persen($hargaBarang, (float) config('referal.persen_komisi', 3));
    }

    /** Membakukan penulisan kode: huruf besar, tanpa spasi. */
    public function rapikan(?string $kode): string
    {
        return strtoupper(trim(preg_replace('/\s+/', '', (string) $kode)));
    }

    private function persen(float $nilai, float $persen): float
    {
        // Dibulatkan ke rupiah penuh — pecahan sen tidak ada artinya di sini
        // dan hanya menimbulkan selisih saat dijumlahkan.
        return (float) floor(max(0, $nilai) * $persen / 100);
    }

    /**
     * Menyusun kode dari nama pengguna, lalu memastikan belum terpakai.
     */
    private function kodeUnik(User $pemilik): string
    {
        $awalan = strtoupper(config('referal.awalan', 'RECORD'));
        $nama   = $this->bagianNama($pemilik);

        $kode = $awalan . '-' . $nama;

        // Nama yang sama bisa dimiliki dua orang. Yang kedua diberi imbuhan
        // acak pendek agar kodenya tetap unik tanpa kehilangan bentuk aslinya.
        while (User::where('referral_code', $kode)->exists()) {
            $kode = $awalan . '-' . $nama . strtoupper(Str::random(3));
        }

        return $kode;
    }

    private function bagianNama(User $pemilik): string
    {
        // Hanya huruf dan angka yang dipertahankan: kode ini akan diketik
        // ulang orang lain, jadi tanda baca dan spasi cuma jadi sumber salah.
        $nama = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $pemilik->name));

        if ($nama === '') {
            $nama = 'USER' . $pemilik->id;
        }

        return substr($nama, 0, (int) config('referal.maks_nama', 14));
    }
}
