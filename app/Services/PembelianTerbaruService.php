<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Data notifikasi pembelian terbaru (Social Proof) dengan sensor nama (Mu***).
 */
class PembelianTerbaruService
{
    private const UMUR_CACHE_DETIK = 120;

    /**
     * Mengambil daftar pembelian bervariasi dengan sensor nama (misal: Mu***).
     * Menggabungkan pesanan asli (real) dan katalog produk aktif.
     *
     * @return array<int, array{nama: string, kota: string, produk: string, gambar: ?string, slug: ?string, nominal: float, menit: int, asli: bool}>
     */
    public function ambil(): array
    {
        return Cache::remember('pembelian-terbaru-v4', self::UMUR_CACHE_DETIK, function () {
            $daftar = [];

            $kotaVariasi = [
                'Surabaya', 'Jakarta', 'Bandung', 'Semarang', 'Malang',
                'Yogyakarta', 'Sidoarjo', 'Medan', 'Tangerang', 'Bekasi',
                'Depok', 'Bogor', 'Solo', 'Denpasar', 'Makassar',
                'Palembang', 'Banjarmasin', 'Cirebon', 'Kediri', 'Gresik',
                'Jember', 'Pontianak', 'Batam', 'Pekanbaru', 'Samarinda',
            ];

            // 1. Ambil data pesanan ASLI dari database (ditandai asli = true)
            $pesananAsli = Order::query()
                ->where('status', '!=', 'cancelled')
                ->with(['user:id,name', 'items' => fn ($q) => $q->with('product:id,name,slug,image,price')])
                ->latest()
                ->take(20)
                ->get();

            foreach ($pesananAsli as $order) {
                $item = $order->items->first();
                if ($item && $item->product) {
                    $alamat = (array) $order->shipping_address;
                    $kota = !empty($alamat['city']) ? $alamat['city'] : $kotaVariasi[array_rand($kotaVariasi)];
                    $namaAsli = $order->user?->name ?: ($alamat['name'] ?? 'Pelanggan');

                    $daftar[] = [
                        'nama'    => $this->sensorNama($namaAsli),
                        'kota'    => $kota,
                        'produk'  => $item->product_name ?: $item->product->name,
                        'gambar'  => $item->product->image,
                        'slug'    => $item->product->slug,
                        'nominal' => (float) $item->price,
                        'menit'   => rand(1, 20),
                        'asli'    => true,
                    ];
                }
            }

            // 2. Ambil katalog produk aktif untuk melengkapi variasi agar selalu ramai & dinamis
            $produkAktif = Product::query()
                ->where('status', 'active')
                ->get(['id', 'name', 'slug', 'image', 'price']);

            if ($produkAktif->isNotEmpty()) {
                $namaVariasi = [
                    'Munir', 'Rizky', 'Dimas', 'Aditya', 'Bayu',
                    'Fikri', 'Farhan', 'Deni', 'Hendro', 'Bagus',
                    'Aris', 'Bagas', 'Kevin', 'Wahyu', 'Ilham',
                    'Aldi', 'Fajar', 'Reza', 'Eko', 'Doni',
                    'Andika', 'Prasetyo', 'Satria', 'Yusuf', 'Syahrul',
                    'Maulana', 'Teguh', 'Arief', 'Dian', 'Rian',
                    'Cahyo', 'Hendra', 'Surya', 'Irfan', 'Agus'
                ];

                $indeks = 0;
                foreach ($produkAktif as $p) {
                    $namaPilihan = $namaVariasi[$indeks % count($namaVariasi)];
                    $kotaPilihan = $kotaVariasi[($indeks * 3) % count($kotaVariasi)];

                    $daftar[] = [
                        'nama'    => $this->sensorNama($namaPilihan),
                        'kota'    => $kotaPilihan,
                        'produk'  => $p->name,
                        'gambar'  => $p->image,
                        'slug'    => $p->slug,
                        'nominal' => (float) $p->price,
                        'menit'   => rand(1, 45),
                        'asli'    => false,
                    ];

                    $indeks++;
                }
            }

            // Acak urutan
            shuffle($daftar);

            return $daftar;
        });
    }

    /**
     * Sensor nama menjadi 2 huruf awal + 3 bintang.
     * Contoh: "Munir" -> "Mu***", "Aditya" -> "Ad***", "Budi" -> "Bu***"
     */
    private function sensorNama(?string $nama): string
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return 'Pe***';
        }

        // Ambil kata pertama
        $kataPertama = explode(' ', $nama)[0];

        if (mb_strlen($kataPertama) <= 2) {
            return mb_convert_case($kataPertama, MB_CASE_TITLE, 'UTF-8') . '***';
        }

        $duaHuruf = mb_substr($kataPertama, 0, 2);
        return mb_convert_case($duaHuruf, MB_CASE_TITLE, 'UTF-8') . '***';
    }
}