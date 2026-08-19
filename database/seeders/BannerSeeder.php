<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::create([
            'title' => 'NEW COLLECTION',
            'subtitle' => 'FASHION FOR EVERY YOU - UNLEASH YOUR STYLE',
            'image' => 'banners/hero-1.png',
            'link' => '/products',
            'position' => 'hero',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Banner::create([
            'title' => '10 + 10',
            'subtitle' => 'Shopee Mall 100% ORI',
            'image' => 'banners/promo-1.png',
            'link' => '/promo',
            'position' => 'promo',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Banner::create([
            'title' => 'RECORD SHOES',
            'subtitle' => '#1 Spesialis Sepatu Sekolah',
            'image' => 'banners/promo-2.png',
            'link' => '/category/sepatu-sekolah',
            'position' => 'promo',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Banner::create([
            'title' => 'BELI 1 GRATIS 1',
            'subtitle' => 'Voucher s/d 250RB',
            'image' => 'banners/promo-3.png',
            'link' => '/promo',
            'position' => 'promo',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Banner::create([
            'title' => 'EKSTRA DISKON 50%',
            'subtitle' => 'DENGAN SpayLater',
            'image' => 'banners/promo-4.png',
            'link' => '/promo',
            'position' => 'promo',
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
