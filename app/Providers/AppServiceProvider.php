<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer(['layouts.app', 'components.navbar', 'home', 'products.index', 'products.show'], function ($view) {
            $view->with('categories', \App\Models\Category::active()
                ->withCount('activeProducts')
                ->ordered()
                ->get());
            
            $cartService = app(\App\Services\CartService::class);
            $view->with('cartCount', $cartService->getCartCount());

            $view->with('pembelianTerbaru', app(\App\Services\PembelianTerbaruService::class)->ambil());
        });
    }
}
