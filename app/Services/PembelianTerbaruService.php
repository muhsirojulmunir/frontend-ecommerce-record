<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Data notifikasi pembelian terbaru (Social Proof) dengan 100+ variasi nama & kota.
 */
class PembelianTerbaruService
{
    private const UMUR_CACHE_DETIK = 120;

    /**
     * Mengambil daftar pembelian bervariasi dengan sensor nama (misal: Mu***).
     *
     * @return array<int, array{nama: string, kota: string, produk: string, gambar: ?string, slug: ?string, nominal: float, asli: bool}>
     */
    public function ambil(): array
    {
        return Cache::remember('pembelian-terbaru-v5', self::UMUR_CACHE_DETIK, function () {
            $daftar = [];

            $kotaVariasi = [
                'Surabaya', 'Jakarta', 'Bandung', 'Semarang', 'Malang',
                'Yogyakarta', 'Sidoarjo', 'Medan', 'Tangerang', 'Bekasi',
                'Depok', 'Bogor', 'Solo', 'Denpasar', 'Makassar',
                'Palembang', 'Banjarmasin', 'Cirebon', 'Kediri', 'Gresik',
                'Jember', 'Pontianak', 'Batam', 'Pekanbaru', 'Samarinda',
                'Balikpapan', 'Bandar Lampung', 'Padang', 'Mataram', 'Manado',
                'Serang', 'Tasikmalaya', 'Madiun', 'Magelang', 'Probolinggo',
                'Pasuruan', 'Tegal', 'Sukabumi', 'Banyuwangi', 'Pekalongan'
            ];

            // 1. Ambil data pesanan ASLI dari database
            $pesananAsli = Order::query()
                ->where('status', '!=', 'cancelled')
                ->with(['user:id,name', 'items' => fn ($q) => $q->with('product:id,name,slug,image,price')])
                ->latest()
                ->take(30)
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
                        'asli'    => true,
                    ];
                }
            }

            // 2. Ambil katalog produk aktif untuk membuat variasi 100+ kombinasi unik
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
                    'Cahyo', 'Hendra', 'Surya', 'Irfan', 'Agus',
                    'Budi', 'David', 'Gilang', 'Galih', 'Haris',
                    'Indra', 'Jaka', 'Kurnia', 'Lukman', 'Marcel',
                    'Naufal', 'Oki', 'Pandu', 'Qori', 'Rahmat',
                    'Sigit', 'Taufik', 'Utomo', 'Viko', 'Wawan',
                    'Yoga', 'Zainal', 'Akbar', 'Brian', 'Candra',
                    'Dwi', 'Erwin', 'Firman', 'Gede', 'Hanif',
                    'Ivan', 'Joko', 'Krisna', 'Lutfi', 'Mario',
                    'Nugraha', 'Panji', 'Radit', 'Sandi', 'Tommy',
                    'Vicky', 'Wisnu', 'Yudha', 'Zaky', 'Angga',
                    'Bobby', 'Danang', 'Erik', 'Fandy', 'Gani',
                    'Hafiz', 'Iqbal', 'Jonathan', 'Kiki', 'Leo',
                    'Michael', 'Nanda', 'Pandji', 'Rio', 'Syafiq',
                    'Tio', 'Wira', 'Yanuar', 'Zidan', 'Andre'
                ];

                $totalProduk = $produkAktif->count();
                $totalNama   = count($namaVariasi);
                $totalKota   = count($kotaVariasi);

                // Buat 100 entri kombinasi acak tanpa pengulangan nama berdekatan
                for ($i = 0; $i < 100; $i++) {
                    $p = $produkAktif[$i % $totalProduk];
                    $namaPilihan = $namaVariasi[$i % $totalNama];
                    $kotaPilihan = $kotaVariasi[($i * 7 + 3) % $totalKota];

                    $daftar[] = [
                        'nama'    => $this->sensorNama($namaPilihan),
                        'kota'    => $kotaPilihan,
                        'produk'  => $p->name,
                        'gambar'  => $p->image,
                        'slug'    => $p->slug,
                        'nominal' => (float) $p->price,
                        'asli'    => false,
                    ];
                }
            }

            // Acak secara menyeluruh
            shuffle($daftar);

            return $daftar;
        });
    }

    /**
     * Sensor nama menjadi 2 huruf awal + 3 bintang.
     * Contoh: "Munir" -> "Mu***", "Aditya" -> "Ad***"
     */
    private function sensorNama(?string $nama): string
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return 'Pe***';
        }

        $kataPertama = explode(' ', $nama)[0];

        if (mb_strlen($kataPertama) <= 2) {
            return mb_convert_case($kataPertama, MB_CASE_TITLE, 'UTF-8') . '***';
        }

        $duaHuruf = mb_substr($kataPertama, 0, 2);
        return mb_convert_case($duaHuruf, MB_CASE_TITLE, 'UTF-8') . '***';
    }
}