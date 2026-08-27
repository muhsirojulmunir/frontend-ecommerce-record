<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Data notifikasi pembelian terbaru (Social Proof) di pojok halaman.
 */
class PembelianTerbaruService
{
    private const UMUR_CACHE_DETIK = 120;

    /**
     * Mengambil daftar pembelian bervariasi dengan produk nyata dari katalog toko.
     *
     * @return array<int, array{nama: string, kota: string, produk: string, gambar: ?string, slug: ?string, nominal: float, menit: int}>
     */
    public function ambil(): array
    {
        return Cache::remember('pembelian-terbaru-v2', self::UMUR_CACHE_DETIK, function () {
            $daftar = [];

            // 1. Ambil pesanan nyata yang sudah dibayar (bila ada)
            $pesananNyata = Order::query()
                ->where('payment_status', 'paid')
                ->where('status', '!=', 'cancelled')
                ->with(['user:id,name', 'items' => fn ($q) => $q->with('product:id,name,slug,image,price')])
                ->latest()
                ->take(15)
                ->get();

            $kotaVariasi = [
                'Surabaya', 'Jakarta', 'Bandung', 'Semarang', 'Malang',
                'Yogyakarta', 'Sidoarjo', 'Medan', 'Tangerang', 'Bekasi',
                'Depok', 'Bogor', 'Solo', 'Denpasar', 'Makassar',
                'Palembang', 'Banjarmasin', 'Cirebon', 'Kediri', 'Gresik',
                'Jember', 'Pontianak', 'Batam', 'Pekanbaru', 'Samarinda',
            ];

            foreach ($pesananNyata as $order) {
                $item = $order->items->first();
                if ($item && $item->product) {
                    $alamat = (array) $order->shipping_address;
                    $kota = !empty($alamat['city']) ? $alamat['city'] : $kotaVariasi[array_rand($kotaVariasi)];

                    $daftar[] = [
                        'nama'    => $this->formatNama($order->user?->name),
                        'kota'    => $kota,
                        'produk'  => $item->product_name ?: $item->product->name,
                        'gambar'  => $item->product->image,
                        'slug'    => $item->product->slug,
                        'nominal' => (float) $item->price,
                        'menit'   => rand(1, 35),
                    ];
                }
            }

            // 2. Ambil katalog produk aktif untuk melengkapi variasi
            $produkAktif = Product::query()
                ->where('status', 'active')
                ->get(['id', 'name', 'slug', 'image', 'price']);

            if ($produkAktif->isNotEmpty()) {
                $namaVariasi = [
                    'Munir S.', 'Rizky P.', 'Dimas A.', 'Aditya W.', 'Bayu K.',
                    'Fikri H.', 'Farhan M.', 'Deni R.', 'Hendro P.', 'Bagus S.',
                    'Aris K.', 'Bagas T.', 'Kevin L.', 'Wahyu D.', 'Ilham N.',
                    'Aldi F.', 'Fajar B.', 'Reza A.', 'Eko P.', 'Doni S.',
                    'Andika W.', 'Prasetyo H.', 'Satria M.', 'Yusuf G.', 'Syahrul I.',
                    'Maulana F.', 'Teguh B.', 'Arief R.', 'Dian K.', 'Rian S.'
                ];

                // Campurkan produk aktif dengan nama dan kota acak
                $indeksNama = 0;
                foreach ($produkAktif as $p) {
                    $namaTerpilih = $namaVariasi[$indeksNama % count($namaVariasi)];
                    $kotaTerpilih = $kotaVariasi[($indeksNama * 3) % count($kotaVariasi)];

                    $daftar[] = [
                        'nama'    => $namaTerpilih,
                        'kota'    => $kotaTerpilih,
                        'produk'  => $p->name,
                        'gambar'  => $p->image,
                        'slug'    => $p->slug,
                        'nominal' => (float) $p->price,
                        'menit'   => rand(1, 45),
                    ];

                    $indeksNama++;
                }
            }

            // Acak urutan agar bervariasi setiap refresh
            shuffle($daftar);

            return $daftar;
        });
    }

    private function formatNama(?string $nama): string
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return 'Pelanggan';
        }

        $parts = explode(' ', $nama);
        if (count($parts) > 1) {
            return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '.';
        }

        return $parts[0];
    }
}