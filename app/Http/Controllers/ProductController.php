<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Tampilkan halaman daftar produk dengan filter dan pencarian.
     */
    public function index(Request $request)
    {
        // Bintang rata-rata ikut dihitung di kueri yang sama supaya daftar
        // produk tidak menembak dua kueri tambahan untuk setiap kartunya.
        $query = Product::active()
            ->with(['category', 'activeDiscount'])
            ->withAvg('reviewsTampil as bintang_rata', 'rating')
            ->withCount('reviewsTampil as jumlah_ulasan');

        // Cari produk berdasarkan kata kunci
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Saring berdasarkan kategori
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Urutan tampil produk
        $sort = $request->get('sort', 'terbaru');
        $query = match ($sort) {
            'termurah' => $query->orderBy('price', 'asc'),
            'termahal' => $query->orderBy('price', 'desc'),
            'terlaris' => $query->orderBy('stock', 'asc'), // Sementara pakai stok, idealnya pakai jumlah pesanan
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('products.index', compact('products', 'categories', 'sort'));
    }

    /**
     * Tampilkan halaman detail satu produk.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images', 'variants.activeDiscount', 'activeDiscount']);

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'activeDiscount'])
            ->withAvg('reviewsTampil as bintang_rata', 'rating')
            ->withCount('reviewsTampil as jumlah_ulasan')
            ->take(4)
            ->get();

        /*
         * Ulasan produk.
         *
         * Sebarannya dihitung dengan satu kueri pengelompokan, bukan dengan
         * mengambil semua ulasan lalu menghitungnya di PHP — produk yang laku
         * bisa punya ribuan ulasan, dan yang ditampilkan hanya sehalaman.
         */
        /*
         * Saringan bintang. Nilai di luar 1-5 dianggap "semua" — termasuk
         * bila alamatnya diutak-atik sendiri oleh pengunjung.
         */
        $saringBintang = (int) request()->query('bintang', 0);
        if ($saringBintang < 1 || $saringBintang > 5) {
            $saringBintang = 0;
        }

        $ulasan = $product->reviewsTampil()
            ->with(['user:id,name', 'orderItem:id,variant_info'])
            ->when($saringBintang > 0, fn ($q) => $q->where('rating', $saringBintang))
            ->latest()
            ->paginate(8, ['*'], 'ulasan');

        // Sebaran sengaja TIDAK ikut disaring: angkanya adalah menu pilihan
        // itu sendiri, dan menu yang menyusut begitu dipakai membuat
        // pengunjung tidak bisa berpindah ke bintang lain.
        $sebaran = $product->reviewsTampil()
            ->selectRaw('rating, COUNT(*) as jumlah')
            ->groupBy('rating')
            ->pluck('jumlah', 'rating');

        $jumlahUlasan = (int) $sebaran->sum();

        // map() meneruskan nilai DAN kuncinya, jadi bintangnya (kunci) bisa
        // dikalikan jumlahnya (nilai). sum() dengan fungsi hanya menerima
        // nilainya saja, dan di sini kuncinya justru yang dibutuhkan.
        $bintangRata = $jumlahUlasan > 0
            ? round($sebaran->map(fn ($jumlah, $bintang) => $jumlah * $bintang)->sum() / $jumlahUlasan, 1)
            : 0.0;

        return view('products.show', compact(
            'product', 'relatedProducts', 'ulasan', 'sebaran',
            'jumlahUlasan', 'bintangRata', 'saringBintang'
        ));
    }
}
