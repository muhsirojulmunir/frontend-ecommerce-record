<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Penyedia pencarian koordinat ditentukan lewat config/geocoding.php,
        // sehingga berpindah dari OpenStreetMap ke Google cukup dengan
        // mengubah GEOCODING_DRIVER pada berkas .env.
        $this->app->bind(\App\Services\Geocoding\Geocoder::class, function () {
            return match (config('geocoding.driver', 'osm')) {
                'google' => new \App\Services\Geocoding\GoogleGeocoder,
                default  => new \App\Services\Geocoding\NominatimGeocoder,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share categories and cart count with navbar and layouts.
        // Catatan: composer ini menimpa $categories yang dikirim controller,
        // jadi jumlah produk ikut dihitung di sini agar tersedia di semua
        // halaman (dipakai kartu kategori di beranda).
        \Illuminate\Support\Facades\View::composer(['layouts.app', 'components.navbar', 'home', 'products.index', 'products.show'], function ($view) {
            $view->with('categories', \App\Models\Category::active()
                ->withCount('activeProducts')
                ->ordered()
                ->get());
            
            // Inject CartService to get count
            $cartService = app(\App\Services\CartService::class);
            $view->with('cartCount', $cartService->getCartCount());

            // Pembelian sungguhan untuk notifikasi kecil di pojok halaman.
            // Daftarnya kosong bila belum ada pesanan yang memenuhi syarat,
            // dan notifikasinya ikut tidak tampil — bukan diisi contoh.
            $view->with('pembelianTerbaru', app(\App\Services\PembelianTerbaruService::class)->ambil());
        });
    }
}
