<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

/**
 * Pembelian terbaru untuk notifikasi kecil di pojok halaman.
 *
 * Isinya pesanan yang benar-benar terjadi: sudah lunas dan tidak dibatalkan.
 * Nama pembeli disamarkan supaya tidak ada pelanggan yang transaksinya bisa
 * dikenali orang lain — yang perlu diketahui pengunjung hanya bahwa
 * produknya memang laku, bukan siapa yang membelinya.
 */
class PembelianTerbaruService
{
    /** Seberapa jauh ke belakang pesanan masih layak ditampilkan. */
    private const RENTANG_HARI = 30;

    /** Cukup untuk sekitar satu jam penayangan pada jeda 1-3 menit. */
    private const JUMLAH = 25;

    /**
     * Disimpan sebentar saja: daftar ini dibaca di setiap pemuatan halaman,
     * sedangkan pesanan baru tidak perlu muncul dalam hitungan detik.
     */
    private const UMUR_CACHE_DETIK = 120;

    /**
     * @return array<int, array{nama: string, produk: string, gambar: ?string,
     *                          slug: ?string, nominal: float, waktu: string}>
     */
    public function ambil(): array
    {
        return Cache::remember('pembelian-terbaru', self::UMUR_CACHE_DETIK, function () {
            return Order::query()
                ->where('payment_status', 'paid')
                ->where('status', '!=', 'cancelled')
                ->where('created_at', '>=', now()->subDays(self::RENTANG_HARI))
                ->with(['user:id,name', 'items' => fn ($q) => $q->with('product:id,slug,image')])
                ->latest()
                ->take(self::JUMLAH)
                ->get()
                ->map(fn (Order $order) => $this->rapikan($order))
                ->filter()
                ->values()
                ->all();
        });
    }

    private function rapikan(Order $order): ?array
    {
        $item = $order->items->first();

        if (! $item) {
            return null;
        }

        return [
            'nama'   => $this->samarkan($order->user?->name),
            'produk' => $item->product_name,
            'gambar' => $item->product?->image,
            'slug'   => $item->product?->slug,

            // Nilai barangnya saja, bukan total yang dibayar. Total memuat
            // ongkos kirim, dan ongkir membocorkan kira-kira seberapa jauh
            // pembelinya tinggal.
            'nominal' => (float) $item->price * $item->quantity,

            // Dikirim sebagai waktu mentah agar "x menit lalu" dihitung di
            // browser — halaman yang lama dibuka tidak menampilkan waktu basi.
            'waktu'  => $order->created_at->toIso8601String(),
        ];
    }

    /**
     * "Muhammad Sirojul" menjadi "Mu****".
     *
     * Cukup untuk terasa manusiawi tanpa membuat siapa pun bisa dikenali.
     */
    private function samarkan(?string $nama): string
    {
        $nama = trim((string) $nama);

        if ($nama === '') {
            return 'Se****';
        }

        $depan = mb_substr($nama, 0, 2);

        return mb_convert_case($depan, MB_CASE_TITLE, 'UTF-8') . '****';
    }
}
