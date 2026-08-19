<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Maksimal 3 banner hero ditampilkan di slider halaman utama
        $heroBanners = Banner::active()->byPosition('hero')->ordered()->take(3)->get();
        $promoBanners = Banner::active()->byPosition('promo')->ordered()->get();
        // Catatan: $categories di bawah ini akan ditimpa oleh View composer di
        // AppServiceProvider (yang sudah menyertakan withCount('activeProducts')).
        $categories = Category::active()->ordered()->get();
        $featuredProducts = Product::active()->featured()
            ->with(['category', 'activeDiscount'])
            ->take(8)
            ->get();
        $newArrivals = Product::active()
            ->with(['category', 'activeDiscount'])
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact(
            'heroBanners',
            'promoBanners',
            'categories',
            'featuredProducts',
            'newArrivals'
        ));
    }
}
