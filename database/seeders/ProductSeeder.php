<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['color' => 'Hitam Putih', 'color_hex' => '#1F2937'],
            ['color' => 'Navy', 'color_hex' => '#1E3A5F'],
            ['color' => 'Abu-abu', 'color_hex' => '#6B7280'],
            ['color' => 'Merah', 'color_hex' => '#DC2626'],
            ['color' => 'Biru', 'color_hex' => '#2563EB'],
            ['color' => 'Pink', 'color_hex' => '#EC4899'],
            ['color' => 'Putih', 'color_hex' => '#F9FAFB'],
        ];

        $sizes = ['33', '34', '35', '36', '37', '38', '39', '40', '41', '42'];

        $products = [
            // Sepatu Sport
            [
                'category_slug' => 'sepatu-sport',
                'name' => 'RECORD Suga Sepatu Dewasa Premium HITAM PUTIH',
                'description' => 'Sepatu RECORD dilengkapi SoftFlex Injection Outsole berbahan PVC ringan, kuat, lentur, dan anti selip. Dilengkap LightStep technology untuk langkah lebih ringan dan nyaman, serta ComfortVA Insole yang empuk dan fleksibel untuk kenyamanan sepanjang hari.',
                'details' => ['Dibuat di Indonesia', 'Material Upper Mesh', 'Injection anti jebol', 'Ringan dan Nyaman'],
                'price' => 120000,
                'original_price' => 220000,
                'stock' => 150,
                'is_featured' => true,
                'color_indices' => [0, 1, 2],
            ],
            [
                'category_slug' => 'sepatu-sport',
                'name' => 'RECORD Blaze Sepatu Sport NAVY PUTIH',
                'description' => 'Sepatu sport Record Blaze dengan desain aerodinamis dan teknologi bantalan udara untuk performa maksimal saat berolahraga.',
                'details' => ['Dibuat di Indonesia', 'Material Upper Knit', 'Sole EVA ringan', 'Anti slip'],
                'price' => 165000,
                'original_price' => 250000,
                'stock' => 100,
                'is_featured' => true,
                'color_indices' => [1, 4, 0],
            ],
            [
                'category_slug' => 'sepatu-sport',
                'name' => 'RECORD Venom Running Shoes',
                'description' => 'Sepatu lari RECORD Venom dirancang untuk pelari dengan teknologi AirCushion yang memberikan kenyamanan ekstra pada setiap langkah.',
                'details' => ['Dibuat di Indonesia', 'Mesh breathable', 'AirCushion technology', 'Rubber outsole'],
                'price' => 189000,
                'original_price' => 280000,
                'stock' => 80,
                'is_featured' => false,
                'color_indices' => [0, 3, 4],
            ],
            [
                'category_slug' => 'sepatu-sport',
                'name' => 'RECORD Fury Sepatu Olahraga',
                'description' => 'Sepatu olahraga serbaguna dengan desain modern dan performa tinggi untuk berbagai aktivitas.',
                'details' => ['Dibuat di Indonesia', 'Upper synthetic leather', 'Phylon midsole', 'Durable outsole'],
                'price' => 145000,
                'original_price' => null,
                'stock' => 120,
                'is_featured' => false,
                'color_indices' => [1, 2, 0],
            ],

            // Sepatu Anak
            [
                'category_slug' => 'sepatu-anak',
                'name' => 'RECORD Junior Sepatu Anak Sport',
                'description' => 'Sepatu anak yang dirancang khusus untuk kaki yang sedang bertumbuh. Ringan, nyaman, dan tahan lama.',
                'details' => ['Dibuat di Indonesia', 'Material breathable', 'Velcro strap', 'Anti slip sole'],
                'price' => 89000,
                'original_price' => 150000,
                'stock' => 200,
                'is_featured' => true,
                'color_indices' => [3, 4, 5],
            ],
            [
                'category_slug' => 'sepatu-anak',
                'name' => 'RECORD Kiddo Sepatu Anak Casual',
                'description' => 'Sepatu casual anak dengan desain lucu dan warna menarik. Cocok untuk aktivitas sehari-hari.',
                'details' => ['Dibuat di Indonesia', 'Canvas upper', 'Cushion insole', 'Lightweight'],
                'price' => 75000,
                'original_price' => 120000,
                'stock' => 180,
                'is_featured' => false,
                'color_indices' => [5, 3, 4],
            ],
            [
                'category_slug' => 'sepatu-anak',
                'name' => 'RECORD Tiny Steps Sepatu Balita',
                'description' => 'Sepatu balita dengan sol fleksibel yang mendukung langkah pertama si kecil.',
                'details' => ['Dibuat di Indonesia', 'Soft sole', 'Easy on/off', 'Non-toxic material'],
                'price' => 65000,
                'original_price' => null,
                'stock' => 150,
                'is_featured' => false,
                'color_indices' => [5, 6, 3],
            ],

            // Sepatu Sekolah
            [
                'category_slug' => 'sepatu-sekolah',
                'name' => 'RECORD Scholar Sepatu Sekolah Hitam',
                'description' => 'Sepatu sekolah Record Scholar #1 spesialis sepatu sekolah. Tahan lama, nyaman dipakai seharian.',
                'details' => ['Dibuat di Indonesia', 'PVC injection', 'Anti slip', 'Tahan air'],
                'price' => 95000,
                'original_price' => 160000,
                'stock' => 300,
                'is_featured' => true,
                'color_indices' => [0],
            ],
            [
                'category_slug' => 'sepatu-sekolah',
                'name' => 'RECORD Campus Sepatu Sekolah Premium',
                'description' => 'Sepatu sekolah premium dengan bahan kulit sintetis berkualitas tinggi.',
                'details' => ['Dibuat di Indonesia', 'Synthetic leather', 'Cushion comfort', 'Durable construction'],
                'price' => 125000,
                'original_price' => 200000,
                'stock' => 250,
                'is_featured' => false,
                'color_indices' => [0, 2],
            ],
            [
                'category_slug' => 'sepatu-sekolah',
                'name' => 'RECORD Academy Sepatu Sekolah Putih',
                'description' => 'Sepatu sekolah putih untuk hari-hari tertentu. Mudah dibersihkan dan tahan lama.',
                'details' => ['Dibuat di Indonesia', 'Easy clean material', 'Flexible sole', 'Comfortable fit'],
                'price' => 85000,
                'original_price' => null,
                'stock' => 200,
                'is_featured' => false,
                'color_indices' => [6],
            ],

            // Sepatu Lifestyle
            [
                'category_slug' => 'sepatu-lifestyle',
                'name' => 'RECORD Urban Sepatu Lifestyle',
                'description' => 'Sepatu lifestyle modern untuk tampil stylish sehari-hari. Cocok untuk hangout dan casual.',
                'details' => ['Dibuat di Indonesia', 'Canvas premium', 'Memory foam insole', 'Trendy design'],
                'price' => 175000,
                'original_price' => 260000,
                'stock' => 90,
                'is_featured' => true,
                'color_indices' => [0, 1, 2],
            ],
            [
                'category_slug' => 'sepatu-lifestyle',
                'name' => 'RECORD Street Sepatu Casual',
                'description' => 'Sepatu casual dengan sentuhan streetwear yang kekinian.',
                'details' => ['Dibuat di Indonesia', 'Suede upper', 'Rubber sole', 'Urban style'],
                'price' => 155000,
                'original_price' => null,
                'stock' => 100,
                'is_featured' => false,
                'color_indices' => [2, 0, 1],
            ],
            [
                'category_slug' => 'sepatu-lifestyle',
                'name' => 'RECORD Drift Sepatu Sneakers',
                'description' => 'Sneakers RECORD Drift dengan desain sleek dan bantalan empuk untuk kenyamanan sepanjang hari.',
                'details' => ['Dibuat di Indonesia', 'Knit upper', 'Phylon midsole', 'Modern silhouette'],
                'price' => 195000,
                'original_price' => 300000,
                'stock' => 70,
                'is_featured' => false,
                'color_indices' => [0, 4, 1],
            ],

            // Sepatu Cewek
            [
                'category_slug' => 'sepatu-cewek',
                'name' => 'RECORD Luna Sepatu Wanita Pink',
                'description' => 'Sepatu wanita RECORD Luna dengan desain feminin dan warna pastel yang elegan.',
                'details' => ['Dibuat di Indonesia', 'Soft mesh upper', 'Lightweight EVA', 'Feminine design'],
                'price' => 135000,
                'original_price' => 210000,
                'stock' => 110,
                'is_featured' => true,
                'color_indices' => [5, 3, 6],
            ],
            [
                'category_slug' => 'sepatu-cewek',
                'name' => 'RECORD Aria Sepatu Wanita Sport',
                'description' => 'Sepatu sport wanita dengan desain sporty-chic yang cocok untuk workout maupun jalan-jalan.',
                'details' => ['Dibuat di Indonesia', 'Breathable mesh', 'Flexible sole', 'Arch support'],
                'price' => 155000,
                'original_price' => 230000,
                'stock' => 90,
                'is_featured' => false,
                'color_indices' => [5, 4, 6],
            ],
            [
                'category_slug' => 'sepatu-cewek',
                'name' => 'RECORD Bloom Sepatu Wanita Casual',
                'description' => 'Sepatu casual wanita dengan sentuhan floral dan warna cerah.',
                'details' => ['Dibuat di Indonesia', 'Canvas upper', 'Padded collar', 'Slip resistant'],
                'price' => 115000,
                'original_price' => null,
                'stock' => 130,
                'is_featured' => false,
                'color_indices' => [3, 5, 6],
            ],

            // Sepatu Cowok
            [
                'category_slug' => 'sepatu-cowok',
                'name' => 'RECORD Titan Sepatu Pria Sport',
                'description' => 'Sepatu pria RECORD Titan dengan desain maskulin dan performa tinggi.',
                'details' => ['Dibuat di Indonesia', 'Durable mesh', 'Impact cushioning', 'Sturdy outsole'],
                'price' => 175000,
                'original_price' => 270000,
                'stock' => 100,
                'is_featured' => true,
                'color_indices' => [0, 1, 2],
            ],
            [
                'category_slug' => 'sepatu-cowok',
                'name' => 'RECORD Storm Sepatu Pria Running',
                'description' => 'Sepatu running pria dengan teknologi shock absorber untuk perlindungan maksimal.',
                'details' => ['Dibuat di Indonesia', 'Engineered mesh', 'Shock absorber', 'Grip outsole'],
                'price' => 199000,
                'original_price' => 320000,
                'stock' => 75,
                'is_featured' => false,
                'color_indices' => [1, 0, 4],
            ],
            [
                'category_slug' => 'sepatu-cowok',
                'name' => 'RECORD Force Sepatu Pria Casual',
                'description' => 'Sepatu casual pria untuk gaya sehari-hari yang stylish dan nyaman.',
                'details' => ['Dibuat di Indonesia', 'Synthetic leather', 'Comfort insole', 'Classic design'],
                'price' => 145000,
                'original_price' => null,
                'stock' => 120,
                'is_featured' => false,
                'color_indices' => [0, 2, 1],
            ],
            [
                'category_slug' => 'sepatu-cowok',
                'name' => 'RECORD Apex Sepatu Pria Premium',
                'description' => 'Sepatu premium pria dengan material terbaik dan craftsmanship berkualitas tinggi.',
                'details' => ['Dibuat di Indonesia', 'Premium leather', 'Ortholite insole', 'Handcrafted details'],
                'price' => 249000,
                'original_price' => 380000,
                'stock' => 50,
                'is_featured' => true,
                'color_indices' => [0, 1],
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category_slug'])->first();
            $colorIndices = $productData['color_indices'];

            unset($productData['category_slug'], $productData['color_indices']);

            $product = Product::create([
                'category_id' => $category->id,
                'slug' => Str::slug($productData['name']),
                'image' => 'products/placeholder.png',
                'status' => 'active',
                ...$productData,
            ]);

            // Create variants (size x color combinations)
            $selectedColors = array_map(fn($i) => $colors[$i], $colorIndices);
            $selectedSizes = array_slice($sizes, rand(0, 2), rand(5, 8));

            foreach ($selectedColors as $color) {
                foreach ($selectedSizes as $size) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color['color'],
                        'color_hex' => $color['color_hex'],
                        'stock' => rand(5, 30),
                        'price_adjustment' => 0,
                        'sku' => strtoupper(Str::slug($product->name)) . '-' . $size . '-' . Str::slug($color['color']),
                    ]);
                }
            }
        }
    }
}
